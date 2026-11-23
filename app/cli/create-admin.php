<?php
declare(strict_types=1);

/*
 * Cria uma conta de administrador ou muda a password de uma existente.
 * Uso (na pasta do projeto):  php app/cli/create-admin.php
 * A password é pedida no terminal e só se guarda o hash.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/helpers.php';
require dirname(__DIR__) . '/db.php';
require dirname(__DIR__) . '/auth.php';

function ask(string $question, string $default = ''): string
{
    echo $question . ($default !== '' ? " [$default]" : '') . ': ';
    $answer = trim((string) fgets(STDIN));
    return $answer === '' ? $default : $answer;
}

/** Lê a password sem a mostrar no ecrã (Windows: PowerShell; Linux/macOS: stty). */
function ask_secret(string $question): string
{
    echo $question . ': ';
    if (PHP_OS_FAMILY === 'Windows') {
        $cmd = 'powershell -NoProfile -Command "$p = Read-Host -AsSecureString; '
            . '[Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
        $value = shell_exec($cmd);
        if (is_string($value)) {
            return rtrim($value, "\r\n");
        }
    } elseif (shell_exec('stty -echo 2>/dev/null; echo ok') !== null) {
        $value = (string) fgets(STDIN);
        shell_exec('stty echo');
        echo PHP_EOL;
        return rtrim($value, "\r\n");
    }
    return rtrim((string) fgets(STDIN), "\r\n");
}

$content = require dirname(__DIR__) . '/content.php';
$email = mb_strtolower(ask('Email', $content['email']));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Email inválido.\n");
    exit(1);
}

$st = db()->prepare('SELECT id, name FROM admins WHERE email = ?');
$st->execute([$email]);
$existing = $st->fetch();

if ($existing) {
    echo "Já existe uma conta para $email ({$existing['name']}). Vai definir uma nova password.\n";
    $name = $existing['name'];
} else {
    $name = ask('Nome a mostrar no painel', 'Direção ADMSC');
}

$password = ask_secret('Password (mínimo ' . PASSWORD_MIN_LENGTH . ' caracteres)');
$confirm = ask_secret('Repetir password');
if (($problem = password_problem($password, $confirm)) !== null) {
    fwrite(STDERR, $problem . "\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($existing) {
    db()->prepare('UPDATE admins SET password_hash = ?, is_active = 1 WHERE id = ?')->execute([$hash, $existing['id']]);
    db()->prepare("DELETE FROM rate_limits WHERE scope = 'admin_login' AND identifier = ?")->execute([$email]);
    echo "Password alterada. As sessões abertas dessa conta terminaram.\n";
} else {
    db()->prepare('INSERT INTO admins (email, name, password_hash) VALUES (?, ?, ?)')->execute([$email, $name, $hash]);
    echo "Conta criada. Entre em /admin/login com $email.\n";
}
