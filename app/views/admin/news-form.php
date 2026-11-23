<?php $published = $news !== null && $news['published_at'] !== null; ?>
<div class="adm-head">
  <div>
    <a class="adm-back" href="/admin/noticias">Notícias</a>
    <h1><?= e($title) ?></h1>
  </div>
  <?php if ($published): ?>
  <p class="adm-muted">Publicada a <?= e(utc_to_local($news['published_at'])->format('d/m/Y \à\s H:i')) ?> · <a href="/noticias/<?= e($news['slug']) ?>" target="_blank" rel="noopener">Ver no site</a></p>
  <?php endif; ?>
</div>
<?php if ($errors): ?><p class="flash flash--error" role="alert">Corrija os campos assinalados.</p><?php endif; ?>
<form class="adm-form" method="post" enctype="multipart/form-data" action="<?= $news ? '/admin/noticias/' . (int) $news['id'] : '/admin/noticias/nova' ?>">
  <?= csrf_field() ?>
  <section class="adm-sec">
    <label class="fld">Título
      <input type="text" name="title" value="<?= e($v['title']) ?>" maxlength="200" required<?= field_attrs($errors, 'title') ?>>
      <?= field_error($errors, 'title') ?>
    </label>
    <label class="fld fld--tall">Descrição <span class="hint">Deixe uma linha em branco entre parágrafos.</span>
      <textarea name="body" required<?= field_attrs($errors, 'body') ?>><?= e($v['body']) ?></textarea>
      <?= field_error($errors, 'body') ?>
    </label>
  </section>
  <?php require __DIR__ . '/_photos.php'; ?>
  <div class="adm-submit">
    <?php if ($published): ?>
    <button class="btn btn--red" type="submit" name="action" value="publish">Guardar alterações</button>
    <button class="btn btn--line" type="submit" name="action" value="draft">Retirar do site</button>
    <?php else: ?>
    <button class="btn btn--red" type="submit" name="action" value="publish">Publicar</button>
    <button class="btn btn--line" type="submit" name="action" value="draft">Guardar rascunho</button>
    <?php endif; ?>
    <a class="adm-link" href="/admin/noticias">Cancelar</a>
  </div>
  <p class="hint">A data da notícia é o momento em que é publicada.</p>
</form>
<?php if ($news) { $deleteUrl = '/admin/noticias/' . (int) $news['id'] . '/apagar'; $deleteLabel = 'Apagar notícia'; require __DIR__ . '/_delete.php'; } ?>
