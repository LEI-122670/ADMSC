<?php
declare(strict_types=1);

/*
 * Sessões, CSRF, mensagens flash, limite de tentativas e autenticação da direção.
 * A sessão só é iniciada nas páginas que precisam dela (painel), por isso as
 * páginas públicas não criam cookies.
 */

const ADMIN_IDLE_SECONDS = 7200;          // sessão termina após 2 h sem atividade
const LOGIN_MAX_PER_EMAIL = 5;            // falhas por email...
const LOGIN_MAX_PER_IP = 20;              // ...e por IP...
const LOGIN_WINDOW_SECONDS = 900;         // ...em 15 minutos
const PASSWORD_MIN_LENGTH = 12;

// Hash de uma password aleatória descartada: o login verifica sempre um hash,
// mesmo quando o email não existe, para não revelar pelo tempo de resposta quais existem.
const DUMMY_PASSWORD_HASH = '$2y$12$GqxETx6pj7aFsbGDSzmC5u/fksVyc5uZqa4EAWrHy8j2jU0RcSxlG';

function is_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function session_begin(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string) ADMIN_IDLE_SECONDS);
    session_name('admsc_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https() || (config()['env'] ?? '') === 'production',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- CSRF ---------- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_valid(): bool
{
    $sent = $_POST['_csrf'] ?? '';
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/* ---------- Mensagens flash (mostradas uma vez, após redirecionar) ---------- */

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = [$type, $message];
}

/** @return list<array{0:string,1:string}> */
function take_flashes(): array
{
    $list = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $list;
}

/* ---------- Limite de tentativas ---------- */

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
}

function rate_limited(string $scope, string $identifier, int $max, int $windowSeconds): bool
{
    $st = db()->prepare(
        'SELECT COUNT(*) FROM rate_limits
         WHERE scope = ? AND identifier = ? AND succeeded = 0 AND created_at > NOW() - INTERVAL ? SECOND'
    );
    $st->execute([$scope, $identifier, $windowSeconds]);
    return (int) $st->fetchColumn() >= $max;
}

function rate_record(string $scope, string $identifier, bool $succeeded): void
{
    db()->prepare('INSERT INTO rate_limits (scope, identifier, succeeded) VALUES (?, ?, ?)')
        ->execute([$scope, $identifier, $succeeded ? 1 : 0]);

    // Limpeza ocasional de registos antigos.
    if (random_int(1, 50) === 1) {
        db()->exec('DELETE FROM rate_limits WHERE created_at < NOW() - INTERVAL 1 DAY');
    }
}

/* ---------- Direção ---------- */

/** Identifica a versão da password guardada na sessão: mudar a password termina as outras sessões. */
function password_fingerprint(string $hash): string
{
    return substr(hash('sha256', $hash), 0, 20);
}

/** Administrador com sessão iniciada, ou null. */
function current_admin(): ?array
{
    $id = $_SESSION['admin_id'] ?? null;
    if (!is_int($id)) {
        return null;
    }
    if (time() - (int) ($_SESSION['admin_seen'] ?? 0) > ADMIN_IDLE_SECONDS) {
        admin_logout();
        return null;
    }
    $st = db()->prepare('SELECT id, email, name, password_hash FROM admins WHERE id = ? AND is_active = 1');
    $st->execute([$id]);
    $admin = $st->fetch();
    if (!$admin || !hash_equals(password_fingerprint($admin['password_hash']), (string) ($_SESSION['admin_pw'] ?? ''))) {
        admin_logout();
        return null;
    }
    $_SESSION['admin_seen'] = time();
    unset($admin['password_hash']);
    return $admin;
}

/**
 * Tenta iniciar sessão. Devolve null em caso de sucesso ou a mensagem de erro a mostrar.
 * A mensagem é igual para email inexistente e password errada.
 */
function admin_login(string $email, string $password): ?string
{
    $email = mb_strtolower(trim($email));
    $ip = client_ip();

    if (rate_limited('admin_login', $email, LOGIN_MAX_PER_EMAIL, LOGIN_WINDOW_SECONDS)
        || rate_limited('admin_login_ip', $ip, LOGIN_MAX_PER_IP, LOGIN_WINDOW_SECONDS)) {
        return 'Demasiadas tentativas falhadas. Tente novamente daqui a 15 minutos.';
    }

    $st = db()->prepare('SELECT id, password_hash, is_active FROM admins WHERE email = ?');
    $st->execute([$email]);
    $admin = $st->fetch();

    $valid = password_verify($password, $admin['password_hash'] ?? DUMMY_PASSWORD_HASH);
    if (!$admin || !$valid || !(int) $admin['is_active']) {
        rate_record('admin_login', $email, false);
        rate_record('admin_login_ip', $ip, false);
        return 'Email ou password incorretos.';
    }

    $hash = $admin['password_hash'];
    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([$hash, $admin['id']]);
    }
    db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?')->execute([$admin['id']]);
    db()->prepare("DELETE FROM rate_limits WHERE scope = 'admin_login' AND identifier = ?")->execute([$email]);
    rate_record('admin_login', $email, true);

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id'];
    $_SESSION['admin_seen'] = time();
    $_SESSION['admin_pw'] = password_fingerprint($hash);
    unset($_SESSION['csrf']);
    return null;
}

function admin_logout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_seen'], $_SESSION['admin_pw'], $_SESSION['csrf']);
    session_regenerate_id(true);
}

/** Valida uma nova password; devolve a mensagem de erro ou null. */
function password_problem(string $password, string $confirm): ?string
{
    if (mb_strlen($password) < PASSWORD_MIN_LENGTH) {
        return 'A password tem de ter pelo menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
    }
    if (strlen($password) > 72) {
        return 'A password não pode ter mais de 72 caracteres.';
    }
    if ($password !== $confirm) {
        return 'As passwords não coincidem.';
    }
    return null;
}
