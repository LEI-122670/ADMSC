<main>
  <section class="split split--line">
    <div class="split__text split__text--left">
      <h1 class="page-title"><?= e($m['nome']) ?></h1>
      <h2 class="h3">O que é o <?= e($m['nome']) ?></h2>
      <div class="prose">
        <?php foreach ($m['texto'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      </div>
      <a class="btn btn--red" href="/#contactos">Inscreve-te</a>
    </div>
    <div class="ph split__media"><?= e($m['foto']) ?></div>
  </section>

  <section class="wrap-section columns">
    <div class="columns__a">
      <h2 class="h2">Benefícios da Prática</h2>
    </div>
    <div class="columns__b">
      <?php foreach ($m['beneficios'] as $i => [$t, $d]): ?>
      <div class="numbered">
        <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
        <div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="band">
    <div class="wrap-section columns">
      <div class="columns__a">
        <h2 class="h2">Classes Disponíveis</h2>
        <?php if ($m['classes_intro']): ?><p class="lead"><?= e($m['classes_intro']) ?></p><?php endif; ?>
      </div>
      <div class="columns__b rows">
        <?php foreach ($m['classes'] as $classe): ?>
        <div class="row"><strong><?= e($classe) ?></strong><span>[IDADES] · [HORÁRIO]</span></div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>
</main>
