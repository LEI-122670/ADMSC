<?php
declare(strict_types=1);

$content_data = require __DIR__ . '/content.php';
$path = '/' . trim((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), '/');

if ($path === '/admin' || str_starts_with($path, '/admin/')) {
    require __DIR__ . '/admin/routes.php';
    return;
}

$stubs = [
    '/loja' => ['Loja', 'loja'],
    '/carrinho' => ['Carrinho', 'loja'],
    '/checkout' => ['Checkout', 'loja'],
    '/login' => ['Login', 'none'],
];

$legal = [
    '/privacidade' => 'Política de privacidade',
    '/cookies' => 'Política de cookies',
    '/termos' => 'Termos e condições',
    '/trocas-e-devolucoes' => 'Trocas e devoluções',
];

$base = ['c' => $content_data];

if ($path === '/') {
    render('home', $base + [
        'title' => 'ADMSC — Associação Desportiva Marcial Samora Correia',
        'description' => 'Escola de Kenpo e Ju-Jitsu em Samora Correia. Representantes do Kuroishi Ryu Bujutsu em Portugal.',
        'header' => 'overlay',
        'active' => 'inicio',
        'portal' => true,
        'calendar' => calendar_month($_GET['mes'] ?? null, static fn (string $from, string $to): array => try_db(static fn () => events_by_day($from, $to), [])),
        'upcoming' => try_db(static fn () => upcoming_events(5), []),
        'news' => try_db(static fn () => published_news(5), []),
    ]);
} elseif (isset($content_data['modalidades'][$path])) {
    $m = $content_data['modalidades'][$path];
    render('modalidade', $base + [
        'm' => $m,
        'title' => $m['nome'] . ' — ADMSC',
        'description' => $m['resumo'],
        'active' => 'modalidades',
    ]);
} elseif ($path === '/noticias') {
    $perPage = 12;
    $page = max(1, (int) ($_GET['pagina'] ?? 1));
    $total = try_db(static fn () => published_news_count(), 0);
    render('news', $base + [
        'title' => 'Notícias — ADMSC',
        'description' => 'Notícias da Associação Desportiva Marcial Samora Correia.',
        'active' => 'none',
        'news' => try_db(static fn () => published_news($perPage, ($page - 1) * $perPage), []),
        'page' => $page,
        'pages' => max(1, (int) ceil($total / $perPage)),
    ]);
} elseif (preg_match('#^/noticias/([a-z0-9-]+)$#', $path, $m) === 1 && ($article = try_db(static fn () => published_news_by_slug($m[1]), null)) !== null) {
    render('news-article', $base + [
        'title' => $article['title'] . ' — ADMSC',
        'description' => excerpt($article['body'], 155),
        'active' => 'none',
        'article' => $article,
    ]);
} elseif ($path === '/associacao') {
    render('simple', $base + [
        'title' => 'Associação — ADMSC',
        'description' => 'Direção e informação institucional da ADMSC.',
        'heading' => 'Associação',
        'body' => '[A PREENCHER — direção e informação institucional]',
        'active' => 'associacao',
    ]);
} elseif (isset($legal[$path])) {
    render('simple', $base + [
        'title' => $legal[$path] . ' — ADMSC',
        'description' => $legal[$path] . ' da ADMSC.',
        'heading' => $legal[$path],
        'body' => '[A PREENCHER]',
    ]);
} elseif (isset($stubs[$path])) {
    render('simple', $base + [
        'title' => $stubs[$path][0] . ' — ADMSC',
        'description' => $stubs[$path][0] . ' da ADMSC.',
        'heading' => $stubs[$path][0],
        'body' => 'Esta página estará disponível em breve.',
        'active' => $stubs[$path][1],
        'noindex' => true,
    ]);
} else {
    render('simple', $base + [
        'title' => 'Página não encontrada — ADMSC',
        'description' => 'Página não encontrada.',
        'heading' => 'Página não encontrada',
        'body' => 'A página que procura não existe ou foi movida.',
        'noindex' => true,
    ], 404);
}
