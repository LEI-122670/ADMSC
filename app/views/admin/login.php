<div class="adm-login">
  <div class="adm-login__brand">
    <img class="brand__logo" src="<?= e(asset('img/simbolo-admsc-sm.png')) ?>" width="160" height="160" alt="">
    <span class="brand__name">ADMSC</span>
  </div>
  <h1>Painel da direção</h1>
  <?php if ($error): ?><p class="flash flash--error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form class="adm-stack" method="post" action="/admin/login">
    <?= csrf_field() ?>
    <label class="fld">Email
      <input type="email" name="email" value="<?= e($email) ?>" autocomplete="username" required autofocus>
    </label>
    <label class="fld">Password
      <input type="password" name="password" autocomplete="current-password" required>
    </label>
    <button class="btn btn--red" type="submit">Entrar</button>
  </form>
  <a class="adm-link adm-login__back" href="/">Voltar ao site</a>
</div>
