<div class="adm-head">
  <div>
    <a class="adm-back" href="/admin/eventos">Eventos</a>
    <h1><?= e($title) ?></h1>
  </div>
</div>
<?php if ($errors): ?><p class="flash flash--error" role="alert">Corrija os campos assinalados.</p><?php endif; ?>
<form class="adm-form" method="post" action="<?= $event ? '/admin/eventos/' . (int) $event['id'] : '/admin/eventos/novo' ?>">
  <?= csrf_field() ?>
  <section class="adm-sec">
    <label class="fld">Nome do evento
      <input type="text" name="title" value="<?= e($v['title']) ?>" maxlength="200" required<?= field_attrs($errors, 'title') ?>>
      <?= field_error($errors, 'title') ?>
    </label>
    <div class="adm-grid">
      <label class="fld">Data de início
        <input type="date" name="starts_on" value="<?= e($v['starts_on']) ?>" required<?= field_attrs($errors, 'starts_on') ?>>
        <?= field_error($errors, 'starts_on') ?>
      </label>
      <label class="fld">Hora de início <span class="hint">Opcional</span>
        <input type="time" name="start_time" value="<?= e($v['start_time']) ?>"<?= field_attrs($errors, 'start_time') ?>>
        <?= field_error($errors, 'start_time') ?>
      </label>
      <label class="fld">Data de fim <span class="hint">Só se durar mais de um dia</span>
        <input type="date" name="ends_on" value="<?= e($v['ends_on']) ?>"<?= field_attrs($errors, 'ends_on') ?>>
        <?= field_error($errors, 'ends_on') ?>
      </label>
      <label class="fld">Hora de fim <span class="hint">Opcional</span>
        <input type="time" name="end_time" value="<?= e($v['end_time']) ?>"<?= field_attrs($errors, 'end_time') ?>>
        <?= field_error($errors, 'end_time') ?>
      </label>
    </div>
    <label class="fld">Local <span class="hint">Opcional</span>
      <input type="text" name="location" value="<?= e($v['location']) ?>" maxlength="200">
    </label>
    <label class="fld">Descrição <span class="hint">Opcional</span>
      <textarea name="description"><?= e($v['description']) ?></textarea>
    </label>
  </section>
  <div class="adm-submit">
    <button class="btn btn--red" type="submit"><?= $event ? 'Guardar alterações' : 'Adicionar evento' ?></button>
    <a class="adm-link" href="/admin/eventos">Cancelar</a>
  </div>
</form>
<?php if ($event) { $deleteUrl = '/admin/eventos/' . (int) $event['id'] . '/apagar'; $deleteLabel = 'Apagar evento'; require __DIR__ . '/_delete.php'; } ?>
