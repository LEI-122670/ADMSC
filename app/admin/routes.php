<?php
declare(strict_types=1);

/*
 * Painel de administração (/admin). Incluído por app/routes.php com $path definido.
 * Todas as páginas exigem sessão de administrador, exceto /admin/login.
 * Todos os POST verificam o token CSRF.
 */

require dirname(__DIR__) . '/auth.php';
require dirname(__DIR__) . '/uploads.php';
require __DIR__ . '/news.php';
require __DIR__ . '/events.php';
require __DIR__ . '/products.php';

session_begin();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');
header('Cache-Control: no-store');
header("Content-Security-Policy: default-src 'self'; img-src 'self' blob:; script-src 'self'; style-src 'self'; font-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'; object-src 'none'");

/** Mostra uma vista do painel dentro do layout do painel e termina. */
function admin_render(string $view, array $data = [], int $status = 200): never
{
    http_response_code($status);
    extract($data, EXTR_SKIP);
    ob_start();
    require dirname(__DIR__) . '/views/admin/' . $view . '.php';
    $content = ob_get_clean();
    require dirname(__DIR__) . '/views/admin/layout.php';
    exit;
}

/** Mensagem de erro de um campo (vazio se não houver). */
function field_error(array $errors, string $field): string
{
    return isset($errors[$field]) ? '<span class="adm-err" id="err-' . e($field) . '">' . e($errors[$field]) . '</span>' : '';
}

/** Atributos de acessibilidade de um campo com erro. */
function field_attrs(array $errors, string $field): string
{
    return isset($errors[$field]) ? ' aria-invalid="true" aria-describedby="err-' . e($field) . '"' : '';
}

function post_string(string $key, int $max = 10000): string
{
    $value = $_POST[$key] ?? '';
    return is_string($value) ? mb_substr(trim($value), 0, $max) : '';
}

/** Data Y-m-d válida, ou null. */
function valid_date(string $value): ?string
{
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
    return $d !== false && $d->format('Y-m-d') === $value ? $value : null;
}

/** Hora H:i válida, ou null. */
function valid_time(string $value): ?string
{
    return preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $value) === 1 ? $value : null;
}

/** Verifica as fotos enviadas e o total; devolve erro ou null. */
function photos_problem(array $files, int $kept, int $max = 12): ?string
{
    foreach ($files as $file) {
        if (($problem = image_problem($file)) !== null) {
            return $problem;
        }
    }
    return $kept + count($files) > $max ? "Pode ter no máximo $max fotos." : null;
}

/** Guarda as fotos e associa-as (dentro de uma transação já aberta). */
function attach_photos(string $type, int $id, array $files, array &$stored): void
{
    [$table, $column, $dir] = match ($type) {
        'news' => ['news_images', 'news_id', 'noticias'],
        'product' => ['product_images', 'product_id', 'loja'],
    };
    $st = db()->prepare("SELECT COALESCE(MAX(sort_order), -1) FROM $table WHERE $column = ?");
    $st->execute([$id]);
    $order = (int) $st->fetchColumn();
    $insert = db()->prepare("INSERT INTO $table ($column, path, sort_order) VALUES (?, ?, ?)");
    foreach ($files as $file) {
        $path = store_image($file, $dir);
        $stored[] = $path;
        $insert->execute([$id, $path, ++$order]);
    }
}

/** Apaga as fotos marcadas (dentro da transação); devolve os ficheiros a remover depois do commit. */
function detach_photos(string $type, int $id, array $imageIds): array
{
    [$table, $column] = match ($type) {
        'news' => ['news_images', 'news_id'],
        'product' => ['product_images', 'product_id'],
    };
    $paths = [];
    $select = db()->prepare("SELECT path FROM $table WHERE id = ? AND $column = ?");
    $delete = db()->prepare("DELETE FROM $table WHERE id = ? AND $column = ?");
    foreach ($imageIds as $imageId) {
        $select->execute([$imageId, $id]);
        if (($path = $select->fetchColumn()) !== false) {
            $delete->execute([$imageId, $id]);
            $paths[] = $path;
        }
    }
    return $paths;
}

/** IDs das fotos marcadas para remover no formulário. */
function posted_ids(string $key): array
{
    $ids = $_POST[$key] ?? [];
    return is_array($ids) ? array_values(array_unique(array_filter(array_map('intval', $ids), static fn (int $i) => $i > 0))) : [];
}

/* ---------- Pedido ---------- */

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$sub = substr($path, strlen('/admin')) ?: '/';

if ($method === 'POST') {
    // Se o envio ultrapassar o post_max_size do PHP, $_POST chega vazio.
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        flash('error', 'O envio era demasiado grande. Envie menos fotos de cada vez.');
        redirect($_SERVER['REQUEST_URI']);
    }
    if (!csrf_valid()) {
        flash('error', 'A sessão expirou ou o pedido não é válido. Tente novamente.');
        redirect($_SERVER['REQUEST_URI']);
    }
}

if ($sub === '/login') {
    if (current_admin() !== null) {
        redirect('/admin');
    }
    $email = '';
    $error = null;
    if ($method === 'POST') {
        $email = post_string('email', 255);
        $error = admin_login($email, (string) ($_POST['password'] ?? ''));
        if ($error === null) {
            redirect('/admin');
        }
    }
    admin_render('login', ['title' => 'Iniciar sessão', 'admin' => null, 'email' => $email, 'error' => $error], $error ? 422 : 200);
}

$admin = current_admin();
if ($admin === null) {
    redirect('/admin/login');
}
$base = ['admin' => $admin];

if ($sub === '/sair' && $method === 'POST') {
    admin_logout();
    flash('ok', 'Sessão terminada.');
    redirect('/admin/login');
}

if ($sub === '/') {
    $count = static fn (string $sql): int => (int) db()->query($sql)->fetchColumn();
    admin_render('dashboard', $base + [
        'title' => 'Painel',
        'section' => 'painel',
        'stats' => [
            'published' => $count('SELECT COUNT(*) FROM news WHERE published_at IS NOT NULL'),
            'drafts' => $count('SELECT COUNT(*) FROM news WHERE published_at IS NULL'),
            'upcoming' => $count("SELECT COUNT(*) FROM events WHERE COALESCE(ends_on, starts_on) >= '" . date('Y-m-d') . "'"),
            'products' => $count('SELECT COUNT(*) FROM products WHERE is_active = 1'),
            'sold_out' => $count('SELECT COUNT(*) FROM product_variants v JOIN products p ON p.id = v.product_id WHERE p.is_active = 1 AND v.stock = 0'),
        ],
    ]);
}

if ($sub === '/conta') {
    $errors = [];
    if ($method === 'POST') {
        $st = db()->prepare('SELECT password_hash FROM admins WHERE id = ?');
        $st->execute([$admin['id']]);
        $new = (string) ($_POST['new_password'] ?? '');
        if (!password_verify((string) ($_POST['current_password'] ?? ''), (string) $st->fetchColumn())) {
            $errors['current_password'] = 'A password atual está incorreta.';
        } elseif (($problem = password_problem($new, (string) ($_POST['confirm_password'] ?? ''))) !== null) {
            $errors['new_password'] = $problem;
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([$hash, $admin['id']]);
            session_regenerate_id(true);
            $_SESSION['admin_pw'] = password_fingerprint($hash);   // as outras sessões abertas terminam
            flash('ok', 'Password alterada.');
            redirect('/admin/conta');
        }
    }
    admin_render('account', $base + ['title' => 'Conta', 'section' => 'conta', 'errors' => $errors], $errors ? 422 : 200);
}

if (str_starts_with($sub, '/noticias')) {
    news_routes(substr($sub, strlen('/noticias')) ?: '/', $method, $base);
}
if (str_starts_with($sub, '/eventos')) {
    events_routes(substr($sub, strlen('/eventos')) ?: '/', $method, $base);
}
if (str_starts_with($sub, '/loja')) {
    products_routes(substr($sub, strlen('/loja')) ?: '/', $method, $base);
}

admin_render('not-found', $base + ['title' => 'Página não encontrada'], 404);
