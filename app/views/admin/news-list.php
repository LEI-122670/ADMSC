<div class="adm-head">
  <h1>Notícias</h1>
  <a class="btn btn--red" href="/admin/noticias/nova">Nova notícia</a>
</div>
<?php if (!$news): ?>
<p class="adm-empty">Ainda não há notícias. As notícias publicadas aparecem na página inicial e em «Notícias».</p>
<?php else: ?>
<div class="adm-scroll">
  <table class="adm-table">
    <thead><tr><th scope="col"><span class="sr-only">Foto</span></th><th scope="col">Título</th><th scope="col">Estado</th><th scope="col">Fotos</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
    <tbody>
      <?php foreach ($news as $n): ?>
      <tr>
        <td class="adm-table__thumb"><?php if ($n['cover']): ?><img class="adm-thumb" src="<?= e(upload_url($n['cover'])) ?>" alt="" loading="lazy"><?php else: ?><span class="adm-thumb"></span><?php endif; ?></td>
        <td><a class="adm-title" href="/admin/noticias/<?= (int) $n['id'] ?>"><?= e($n['title']) ?></a></td>
        <td>
          <?php if ($n['published_at']): ?>
          <span class="state state--on">Publicada</span>
          <span class="adm-muted"><?= e(utc_to_local($n['published_at'])->format('d/m/Y H:i')) ?></span>
          <?php else: ?>
          <span class="state">Rascunho</span>
          <?php endif; ?>
        </td>
        <td><?= (int) $n['photos'] ?></td>
        <td class="adm-actions"><a class="adm-link" href="/admin/noticias/<?= (int) $n['id'] ?>">Editar</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
