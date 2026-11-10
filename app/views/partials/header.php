<?php
$cart = 0;
$items = [
    'sobre' => ['/#sobre', 'Sobre Nós'],
    'senseis' => ['/#senseis', 'Senseis'],
    'loja' => ['/loja', 'Loja'],
    'associacao' => ['/associacao', 'Associação'],
    'contactos' => ['/#contactos', 'Contactos'],
];
$current = static fn (string $key): string => $key === $active ? ' aria-current="page"' : '';
?>
<header class="site-header site-header--<?= e($variant) ?>">
  <nav class="site-nav" aria-label="Principal">
    <a class="brand" href="/" aria-label="ADMSC — Início">
      <img class="brand__logo" src="<?= e(asset('img/simbolo-admsc-sm.png')) ?>" width="160" height="160" alt="">
      <span class="brand__name">ADMSC</span>
    </a>

    <ul class="menu">
      <li><a href="/"<?= $current('inicio') ?>>Início</a></li>
      <li class="menu__has-sub">
        <a href="/#modalidades"<?= $current('modalidades') ?>>Modalidades <svg width="10" height="10" viewBox="0 0 10 10" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M2 3.5l3 3 3-3"/></svg></a>
        <div class="submenu">
          <a href="/modalidades/kenpo">Kenpo</a>
          <a href="/modalidades/ju-jitsu">Ju-Jitsu</a>
        </div>
      </li>
      <?php foreach ($items as $key => [$href, $label]): ?>
      <li><a href="<?= e($href) ?>"<?= $current($key) ?>><?= e($label) ?></a></li>
      <?php endforeach; ?>
    </ul>

    <div class="actions">
      <a class="cart" href="/carrinho" aria-label="Carrinho, <?= $cart ?> artigos">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="M5 8h14l-1.2 12H6.2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
        <span class="cart__count"><?= $cart ?></span>
      </a>
      <a class="login" href="/login">Login</a>
      <a class="signup" href="/#contactos">Inscreve-te</a>

      <details class="mobile-nav">
        <summary aria-label="Menu">
          <svg class="icon-open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h18M3 12h18M9 17h12"/></svg>
          <svg class="icon-close" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
        </summary>
        <div class="mobile-nav__panel">
          <span class="mobile-nav__brand">ADMSC</span>
          <div class="mobile-nav__links">
            <a href="/"<?= $current('inicio') ?>>Início</a>
            <span class="mobile-nav__group">Modalidades</span>
            <a class="sub" href="/modalidades/kenpo">Kenpo</a>
            <a class="sub" href="/modalidades/ju-jitsu">Ju-Jitsu</a>
            <?php foreach ($items as $key => [$href, $label]): ?>
            <a href="<?= e($href) ?>"<?= $current($key) ?>><?= e($label) ?></a>
            <?php endforeach; ?>
          </div>
          <div class="mobile-nav__cta">
            <a class="btn btn--line" href="/login">Login</a>
            <a class="btn btn--red" href="/#contactos">Inscreve-te</a>
          </div>
        </div>
      </details>
    </div>
  </nav>
</header>
