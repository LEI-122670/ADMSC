<div class="adm-head">
  <h1>Olá, <?= e($admin['name']) ?></h1>
</div>
<div class="adm-stats">
  <a class="adm-stat" href="/admin/noticias">
    <strong><?= $stats['published'] ?></strong>
    <span>Notícias publicadas<?= $stats['drafts'] ? ' · ' . $stats['drafts'] . ' em rascunho' : '' ?></span>
  </a>
  <a class="adm-stat" href="/admin/eventos">
    <strong><?= $stats['upcoming'] ?></strong>
    <span>Próximos eventos</span>
  </a>
  <a class="adm-stat" href="/admin/loja">
    <strong><?= $stats['products'] ?></strong>
    <span>Produtos visíveis na loja</span>
  </a>
  <a class="adm-stat<?= $stats['sold_out'] ? ' adm-stat--alert' : '' ?>" href="/admin/loja">
    <strong><?= $stats['sold_out'] ?></strong>
    <span>Tamanhos esgotados</span>
  </a>
</div>
<div class="adm-quick">
  <a class="btn btn--red" href="/admin/noticias/nova">Nova notícia</a>
  <a class="btn btn--line" href="/admin/eventos/novo">Novo evento</a>
  <a class="btn btn--line" href="/admin/loja/novo">Novo produto</a>
</div>
