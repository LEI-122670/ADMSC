<?php
$date = utc_to_local($article['published_at']);
$images = $article['images'];
$cover = array_shift($images);
?>
<main class="article">
  <a class="article__back" href="/noticias">Notícias</a>
  <header class="article__head">
    <time class="meta" datetime="<?= e($date->format('Y-m-d')) ?>"><?= e(date_long($date)) ?></time>
    <h1 class="page-title"><?= e($article['title']) ?></h1>
  </header>
  <?php if ($cover): ?>
  <img class="article__cover" src="<?= e(upload_url($cover['path'])) ?>" alt="">
  <?php endif; ?>
  <div class="article__body"><?= paragraphs($article['body']) ?></div>
  <?php if ($images): ?>
  <ul class="article__gallery">
    <?php foreach ($images as $img): ?>
    <li><a href="<?= e(upload_url($img['path'])) ?>" target="_blank" rel="noopener"><img src="<?= e(upload_url($img['path'])) ?>" alt="" loading="lazy"><span class="sr-only">Abrir foto em tamanho grande</span></a></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</main>
