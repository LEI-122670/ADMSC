<main class="news-page">
  <h1 class="page-title">Notícias</h1>
  <?php if (!$news): ?>
  <p class="lead">Ainda não há notícias publicadas.</p>
  <?php else: ?>
  <ul class="news-list">
    <?php foreach ($news as $n): $date = utc_to_local($n['published_at']); ?>
    <li>
      <a class="news-card" href="/noticias/<?= e($n['slug']) ?>">
        <?php if ($n['cover']): ?>
        <img class="news-card__img" src="<?= e(upload_url($n['cover'])) ?>" alt="" loading="lazy">
        <?php else: ?>
        <div class="ph ph--raised news-card__img" aria-hidden="true">ADMSC</div>
        <?php endif; ?>
        <time class="meta" datetime="<?= e($date->format('Y-m-d')) ?>"><?= e(date_long($date)) ?></time>
        <h2><?= e($n['title']) ?></h2>
        <p><?= e(excerpt($n['body'])) ?></p>
      </a>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php if ($pages > 1): ?>
  <nav class="pager" aria-label="Páginas de notícias">
    <?php if ($page > 1): ?><a href="/noticias<?= $page > 2 ? '?pagina=' . ($page - 1) : '' ?>">Mais recentes</a><?php endif; ?>
    <span>Página <?= $page ?> de <?= $pages ?></span>
    <?php if ($page < $pages): ?><a href="/noticias?pagina=<?= $page + 1 ?>">Mais antigas</a><?php endif; ?>
  </nav>
  <?php endif; ?>
  <?php endif; ?>
</main>
