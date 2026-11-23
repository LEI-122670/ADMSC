<?php /* Fotos existentes (com opção de remover) e campo para adicionar. Espera $images e $errors. */ ?>
<section class="adm-sec">
  <h2>Fotos</h2>
  <?php if ($images): ?>
  <ul class="adm-imgs">
    <?php foreach ($images as $i => $img): ?>
    <li>
      <img src="<?= e(upload_url($img['path'])) ?>" alt="" loading="lazy">
      <label class="adm-check"><input type="checkbox" name="remove_images[]" value="<?= (int) $img['id'] ?>"> Remover<?= $i === 0 ? ' · capa' : '' ?></label>
    </li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <label class="fld">Adicionar fotos <span class="hint">JPG, PNG ou WebP, até 8 MB cada. A primeira foto é a principal.</span>
    <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple data-preview<?= field_attrs($errors, 'photos') ?>>
    <?= field_error($errors, 'photos') ?>
  </label>
  <ul class="adm-imgs adm-imgs--new" data-preview-list hidden></ul>
</section>
