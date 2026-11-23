<div class="adm-head"><h1>Conta</h1></div>
<section class="adm-sec">
  <h2>Sessão iniciada como</h2>
  <p><?= e($admin['name']) ?> · <span class="email"><?= email_html($admin['email']) ?></span></p>
</section>
<form class="adm-sec adm-narrow" method="post" action="/admin/conta">
  <?= csrf_field() ?>
  <h2>Alterar password</h2>
  <label class="fld">Password atual
    <input type="password" name="current_password" autocomplete="current-password" required<?= field_attrs($errors, 'current_password') ?>>
    <?= field_error($errors, 'current_password') ?>
  </label>
  <label class="fld">Nova password <span class="hint">Pelo menos <?= PASSWORD_MIN_LENGTH ?> caracteres.</span>
    <input type="password" name="new_password" autocomplete="new-password" minlength="<?= PASSWORD_MIN_LENGTH ?>" maxlength="72" required<?= field_attrs($errors, 'new_password') ?>>
    <?= field_error($errors, 'new_password') ?>
  </label>
  <label class="fld">Repetir nova password
    <input type="password" name="confirm_password" autocomplete="new-password" required>
  </label>
  <button class="btn btn--red" type="submit">Alterar password</button>
  <p class="hint">Ao alterar a password, as sessões abertas noutros dispositivos terminam.</p>
</form>
