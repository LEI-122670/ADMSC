<?php
$section = $section ?? '';
$nav = [
    'painel' => ['/admin', 'Painel'],
    'noticias' => ['/admin/noticias', 'Notícias'],
    'eventos' => ['/admin/eventos', 'Eventos'],
    'loja' => ['/admin/loja', 'Loja'],
    'conta' => ['/admin/conta', 'Conta'],
];
$flashes = take_flashes();
?>
<!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> — Painel ADMSC</title>
<link rel="icon" href="<?= e(asset('img/favicon-48.png')) ?>" type="image/png" sizes="48x48">
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
<script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</head>
<body class="adm-body">
<?php if ($admin !== null): ?>
<header class="adm-bar">
  <div class="adm-bar__in">
    <a class="brand" href="/admin" aria-label="Painel ADMSC">
      <img class="brand__logo" src="<?= e(asset('img/simbolo-admsc-sm.png')) ?>" width="160" height="160" alt="">
      <span class="brand__name">ADMSC</span>
    </a>
    <nav class="adm-nav" aria-label="Painel">
      <?php foreach ($nav as $key => [$href, $label]): ?>
      <a href="<?= e($href) ?>"<?= $key === $section ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="adm-bar__end">
      <a class="adm-link" href="/" target="_blank" rel="noopener">Ver site</a>
      <form method="post" action="/admin/sair">
        <?= csrf_field() ?>
        <button class="adm-link" type="submit">Sair</button>
      </form>
    </div>
  </div>
</header>
<?php endif; ?>
<main class="adm-main<?= $admin === null ? ' adm-main--bare' : '' ?>" id="conteudo">
  <?php foreach ($flashes as [$type, $message]): ?>
  <p class="flash flash--<?= e($type) ?>" role="<?= $type === 'error' ? 'alert' : 'status' ?>"><?= e($message) ?></p>
  <?php endforeach; ?>
  <?= $content ?>
</main>
</body>
</html>
