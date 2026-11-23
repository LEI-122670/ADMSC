<?php
$table = static function (array $events): void { ?>
<div class="adm-scroll">
  <table class="adm-table">
    <thead><tr><th scope="col">Data</th><th scope="col">Evento</th><th scope="col">Local</th><th scope="col"><span class="sr-only">Ações</span></th></tr></thead>
    <tbody>
      <?php foreach ($events as $ev): ?>
      <tr>
        <td class="adm-nowrap"><?= e(event_when($ev)) ?></td>
        <td><a class="adm-title" href="/admin/eventos/<?= (int) $ev['id'] ?>"><?= e($ev['title']) ?></a></td>
        <td class="adm-muted"><?= e($ev['location'] ?? '') ?></td>
        <td class="adm-actions"><a class="adm-link" href="/admin/eventos/<?= (int) $ev['id'] ?>">Editar</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php }; ?>
<div class="adm-head">
  <h1>Eventos</h1>
  <a class="btn btn--red" href="/admin/eventos/novo">Novo evento</a>
</div>
<section class="adm-sec">
  <h2>Próximos</h2>
  <?php if ($upcoming) { $table($upcoming); } else { ?>
  <p class="adm-empty">Sem eventos agendados. Os eventos vão aparecer no calendário do site.</p>
  <?php } ?>
</section>
<?php if ($past): ?>
<section class="adm-sec">
  <h2>Passados</h2>
  <?php $table($past); ?>
</section>
<?php endif; ?>
