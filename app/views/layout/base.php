<?php
$header = $header ?? 'solid';
$active = $active ?? 'none';
$portal = $portal ?? false;
?>
<!doctype html>
<html lang="pt-PT">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?></title>
<meta name="description" content="<?= e($description) ?>">
<?php if (!empty($noindex)): ?><meta name="robots" content="noindex">
<?php endif; ?>
<link rel="icon" href="<?= e(asset('img/favicon-48.png')) ?>" type="image/png" sizes="48x48">
<link rel="apple-touch-icon" href="<?= e(asset('img/apple-touch-icon.png')) ?>">
<link rel="preload" href="/assets/fonts/montserrat-latin-500-normal.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/site.css')) ?>">
<?php if ($portal): ?>
<script>
try {
  if (!location.hash && !sessionStorage.getItem('admsc-portal')) document.documentElement.classList.add('portal-on');
} catch (err) {}
</script>
<?php endif; ?>
<script src="<?= e(asset('js/site.js')) ?>" defer></script>
</head>
<body>
<?php if ($portal) partial('portal'); ?>
<?php if ($header !== 'overlay') partial('header', ['variant' => 'solid', 'active' => $active]); ?>
<?= $content ?>
<?php partial('footer', ['c' => $c]); ?>
</body>
</html>
