<?php /* Botão de apagar com confirmação. Espera $deleteUrl e $deleteLabel. */ ?>
<form class="adm-delete" method="post" action="<?= e($deleteUrl) ?>" data-confirm="<?= e($deleteLabel) ?>? Esta ação não pode ser desfeita.">
  <?= csrf_field() ?>
  <button class="adm-link adm-link--danger" type="submit"><?= e($deleteLabel) ?></button>
</form>
