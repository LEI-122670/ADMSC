<?php
$weekdays = ['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'];
$address = $c['morada1'] . ', ' . $c['morada2'];
$hasHero = asset_exists('img/hero.jpg');
?>
<section class="hero" id="inicio" tabindex="-1">
  <?php if ($hasHero): ?>
  <img class="hero__photo" src="<?= e(asset('img/hero.jpg')) ?>" alt="Torii ao pôr do sol" fetchpriority="high">
  <?php else: ?>
  <div class="hero__photo ph ph--hero">[FOTO — torii ao pôr do sol, a sangrar]</div>
  <?php endif; ?>
  <div class="hero__shade"></div>
  <div class="hero__band"><span lang="ja">武道</span></div>
  <div class="hero__header"><?php partial('header', ['variant' => 'overlay', 'active' => $active]); ?></div>
  <div class="hero__body">
    <div class="hero__copy">
      <h1><?= e($c['nome']) ?></h1>
      <p class="hero__motto">Disciplina. Respeito. Superação.</p>
      <p class="hero__line">Promovemos a prática das artes marciais como um caminho para o desenvolvimento físico, mental e humano.</p>
      <div class="hero__cta">
        <a class="btn btn--line" href="#boas-vindas">Saber mais</a>
        <a class="btn btn--red" href="#contactos">Inscreve-te</a>
      </div>
    </div>
  </div>
</section>

<main>
  <section class="split split--line" id="boas-vindas">
    <div class="split__text split__text--left split__text--tall">
      <h2 class="h2">Bem-vindo à ADMSC</h2>
      <div class="prose">
        <p class="prose__lead"><?= e($c['boas_vindas'][0]) ?></p>
        <p><?= e($c['boas_vindas'][1]) ?></p>
      </div>
    </div>
    <div class="ph split__media split__media--tall">[FOTO — treino de grupo no dojo]</div>
  </section>

  <section class="highlights">
    <?php foreach ($c['destaques'] as $i => [$t, $d]): ?>
    <div class="highlight">
      <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
      <h3><?= e($t) ?></h3>
      <p><?= e($d) ?></p>
    </div>
    <?php endforeach; ?>
  </section>

  <section class="modalities" id="modalidades">
    <div class="section-head wrap-head">
      <h2 class="h2">Modalidades</h2>
      <p>[A PREENCHER — frase de introdução às modalidades]</p>
    </div>

    <?php
    // Coordenadas no viewBox 1296x640. Cada ponta roda em torno do nó; as barras ficam a $t px ao longo dela.
    $kanji = ['一', '二', '三', '四', '五', '六', '七', '八', '九', '十'];
    $step = 43;
    $kenpoStripes = [];
    foreach (array_values($c['classes_kenpo']) as $i => $classe) {
        $t = 376 + $i * $step;
        $kenpoStripes[] = ['t' => $t, 'y' => round(62 + $t * .8536 - 12.5), 'x' => round(622 - $t * .521 - 20.5), 'nome' => $classe];
    }
    $jujitsuStripes = [];
    foreach (array_values($c['classes_jujitsu']) as $i => $classe) {
        $t = 460 + $i * $step;
        $jujitsuStripes[] = ['t' => $t, 'y' => round(62 + $t * .8434 - 13), 'x' => round(676 + $t * .5373 + 20), 'nome' => $classe];
    }
    $tagY = 372;
    $tagX = round(676 + ($tagY - 62) * .6371 + 28.5);
    ?>
    <div class="belt" data-belt>
      <div class="belt__stage">
        <svg class="belt__art" viewBox="0 0 1296 640" aria-hidden="true">
          <g class="belt__tail belt__tail--l">
            <rect class="belt__cloth" x="598" y="62" width="48" height="615"/>
            <path class="belt__stitch" d="M608 62v615M622 62v615M636 62v615"/>
            <?php foreach ($kenpoStripes as $i => $s): ?>
            <rect class="belt__stripe" x="598" y="<?= 62 + $s['t'] - 3.5 ?>" width="48" height="7" style="--i:<?= $i ?>;transform-origin:622px <?= 62 + $s['t'] ?>px"/>
            <?php endforeach; ?>
          </g>
          <g class="belt__tail belt__tail--r">
            <rect class="belt__cloth" x="652" y="62" width="48" height="549"/>
            <path class="belt__stitch" d="M662 62v549M676 62v549M690 62v549"/>
            <text class="belt__line-name" transform="translate(672 205) rotate(90)">KUROISHI RYU BUJUTSU</text>
            <?php foreach ($jujitsuStripes as $i => $s): ?>
            <rect class="belt__stripe" x="652" y="<?= 62 + $s['t'] - 3.5 ?>" width="48" height="7" style="--i:<?= $i ?>;transform-origin:676px <?= 62 + $s['t'] ?>px"/>
            <?php endforeach; ?>
          </g>
          <g class="belt__half belt__half--l">
            <rect class="belt__cloth" x="377" y="23" width="275" height="49"/>
            <path class="belt__stitch" d="M377 35h275M377 47h275M377 60h275"/>
            <text class="belt__mark" x="408" y="52">ADMSC</text>
          </g>
          <g class="belt__half belt__half--r">
            <rect class="belt__cloth" x="644" y="23" width="275" height="49"/>
            <path class="belt__stitch" d="M644 35h275M644 47h275M644 60h275"/>
            <text class="belt__kanji" x="846" y="56" lang="ja">武道</text>
          </g>
          <g class="belt__knot">
            <path d="M607 6l79 6 7 73-90 3z"/>
            <path class="belt__fold" d="M615 17l68 63"/>
          </g>
          <?php foreach ($kenpoStripes as $i => $s): ?>
          <line class="belt__leader" pathLength="1" x1="<?= $s['x'] ?>" y1="<?= $s['y'] ?>" x2="238" y2="<?= $s['y'] ?>" style="--i:<?= $i ?>"/>
          <?php endforeach; ?>
          <line class="belt__leader" pathLength="1" x1="<?= $tagX ?>" y1="<?= $tagY ?>" x2="1058" y2="<?= $tagY ?>" style="--i:0"/>
          <?php foreach ($jujitsuStripes as $i => $s): ?>
          <line class="belt__leader" pathLength="1" x1="<?= $s['x'] ?>" y1="<?= $s['y'] ?>" x2="1058" y2="<?= $s['y'] ?>" style="--i:<?= $i + 1 ?>"/>
          <?php endforeach; ?>
        </svg>

        <div class="belt__side belt__side--kenpo">
          <div class="belt__intro" id="kenpo">
            <h3><span lang="ja">拳法</span> Kenpo</h3>
            <p><?= e($c['kenpo_texto']) ?></p>
          </div>
          <ul class="belt__rows">
            <?php foreach ($kenpoStripes as $i => $s): ?>
            <li style="--y:<?= $s['y'] ?>;--i:<?= $i ?>"><i lang="ja" aria-hidden="true"><?= $kanji[$i] ?? '' ?></i><strong><?= e($s['nome']) ?></strong><span>[IDADES]</span></li>
            <?php endforeach; ?>
          </ul>
          <a class="belt__cta" href="#contactos">Experimentar Kenpo</a>
        </div>

        <div class="belt__side belt__side--jujitsu">
          <div class="belt__intro" id="jujitsu">
            <h3><span lang="ja">柔術</span> Ju-Jitsu</h3>
            <p><?= e($c['jujitsu_texto']) ?></p>
          </div>
          <p class="belt__tag" style="--y:<?= $tagY ?>;--i:0"><strong>Kuroishi Ryu Bujutsu</strong> <span>linha</span></p>
          <ul class="belt__rows">
            <?php foreach ($jujitsuStripes as $i => $s): ?>
            <li style="--y:<?= $s['y'] ?>;--i:<?= $i + 1 ?>"><i lang="ja" aria-hidden="true"><?= $kanji[$i] ?? '' ?></i><strong><?= e($s['nome']) ?></strong><span>[IDADES]</span></li>
            <?php endforeach; ?>
          </ul>
          <a class="belt__cta" href="#contactos">Experimentar Ju-Jitsu</a>
        </div>
      </div>
    </div>
  </section>

  <section class="academy" id="academia" data-academy>
    <img class="academy__photo" src="<?= e(asset('img/academia.jpg')) ?>" alt="Entrada da academia, com um torii vermelho pintado na parede" decoding="async">
    <div class="academy__shade"></div>
    <div class="academy__in">
      <div class="academy__copy">
      <h2 class="h2 academy__item" style="--i:0">A nossa academia</h2>
      <p class="academy__lead academy__item" style="--i:1">[A PREENCHER — introdução à academia, do PDF]</p>
      <p class="academy__addr academy__item" style="--i:2"><span><?= e($c['morada1']) ?></span> · <span><?= e($c['morada2']) ?></span></p>
      <div class="academy__cta academy__item" style="--i:3">
        <a class="btn btn--red" href="#calendario"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Ver horários</a>
        <a class="btn btn--line" href="https://www.google.com/maps/dir/?api=1&amp;destination=<?= e(rawurlencode($address)) ?>" target="_blank" rel="noopener">Como chegar</a>
      </div>
      </div>

      <div class="academy__side">
      <div class="academy__scroll" data-scroll>
        <svg class="academy__cord" viewBox="0 0 540 38" aria-hidden="true"><path d="M60 37L270 7L480 37"/><circle cx="270" cy="6" r="4.500"/></svg>
        <div class="academy__rod"></div>
        <div class="academy__sheet" data-scroll-sheet>
          <div class="academy__map" data-map data-address="<?= e($address) ?>">
            <svg class="academy__streets" viewBox="0 0 268 268" preserveAspectRatio="xMidYMid slice" aria-hidden="true"><path d="M0 52L268 38M0 168L268 118M0 250L268 258M58 0L82 268M196 0L170 268"/></svg>
            <svg class="academy__pin" width="30" height="42" viewBox="0 0 30 42" aria-hidden="true"><path d="M15 0C6.7 0 0 6.7 0 15c0 10.500 15 27 15 27s15-16.500 15-27C30 6.700 23.300 0 15 0z"/><circle cx="15" cy="15" r="5.500"/></svg>
            <a class="btn btn--line" data-map-load href="https://www.openstreetmap.org/search?query=<?= e(rawurlencode($address)) ?>">Mostrar mapa</a>
          </div>
        </div>
        <div class="academy__rod academy__rod--bottom"></div>
      </div>
      <small class="academy__note academy__item" style="--i:5">O mapa é carregado a partir do Google Maps só depois de clicar.</small>
      </div>
    </div>
  </section>

  <section class="wrap-section about" id="sobre">
    <div class="about__main">
      <h2 class="h2">Sobre Nós</h2>
      <div class="stat"><span class="stat__n">110</span><span>atletas, aproximadamente</span></div>
      <div class="about__block">
        <h3>A Nossa História e Missão</h3>
        <?php foreach ($c['historia'] as $p): ?><p><?= e($p) ?></p><?php endforeach; ?>
      </div>
      <div class="about__block">
        <h3>A Nossa Visão e Valores</h3>
        <p><?= e($c['visao']) ?></p>
      </div>
    </div>
    <div class="about__values">
      <?php foreach ($c['valores'] as $i => [$t, $d]): ?>
      <div class="numbered numbered--big">
        <span class="num"><?= sprintf('%02d', $i + 1) ?></span>
        <div><h3><?= e($t) ?></h3><p><?= e($d) ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="band band--pad" id="noticias">
    <div class="wrap-stack">
      <div class="section-head">
        <h2 class="h2">Notícias</h2>
        <a class="btn btn--line" href="/noticias">Ver todas</a>
      </div>
      <div class="news">
        <div class="news__main">
          <div class="ph ph--raised news__cover">[FOTO DA NOTÍCIA]</div>
          <span class="meta">[DATA]</span>
          <h3>[TÍTULO DA NOTÍCIA MAIS RECENTE]</h3>
          <p>[A PREENCHER — resumo]</p>
        </div>
        <div class="news__side">
          <?php for ($i = 0; $i < 2; $i++): ?>
          <div class="news__thumb">
            <div class="ph ph--raised">[FOTO]</div>
            <div><span class="meta">[DATA]</span><strong>[TÍTULO DA NOTÍCIA]</strong></div>
          </div>
          <?php endfor; ?>
          <?php for ($i = 0; $i < 3; $i++): ?>
          <div class="news__text"><span>[TÍTULO DA NOTÍCIA]</span><span class="meta">[DATA]</span></div>
          <?php endfor; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="wrap-section calendar" id="calendario">
    <div class="calendar__main">
      <div class="section-head">
        <h2 class="h2">Calendário</h2>
        <div class="calendar__nav">
          <a href="/?mes=<?= e($calendar['prev']) ?>#calendario" rel="nofollow" aria-label="Mês anterior"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg></a>
          <span><?= e($calendar['label']) ?></span>
          <a href="/?mes=<?= e($calendar['next']) ?>#calendario" rel="nofollow" aria-label="Mês seguinte"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></a>
        </div>
      </div>
      <div class="cal">
        <?php foreach ($weekdays as $w): ?><span class="cal__wd"><?= $w ?></span><?php endforeach; ?>
        <?php foreach ($calendar['cells'] as $cell): ?>
        <div class="cal__cell<?= $cell['day'] === null ? ' cal__cell--out' : '' ?>">
          <?php if ($cell['day'] !== null): ?><span class="cal__n<?= $cell['today'] ? ' cal__n--today' : '' ?>"<?= $cell['today'] ? ' aria-current="date"' : '' ?>><?= $cell['day'] ?></span><?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="cal__legend">
        <span><i class="key key--today"></i>Hoje</span>
        <span><i class="key key--event"></i>Evento</span>
      </div>
    </div>
    <div class="calendar__side">
      <h3>Próximos eventos</h3>
      <p class="empty">Sem eventos agendados.</p>
    </div>
  </section>

  <section class="split split--surface" id="senseis">
    <div class="ph ph--raised split__media split__media--portrait">[FOTO — Shihan Luís Coelho, retrato]</div>
    <div class="split__text split__text--right">
      <h2 class="h2">Senseis</h2>
      <div class="sensei__name">
        <h3>Shihan Luís Coelho</h3>
        <span>Diretor Técnico da ADMSC</span>
      </div>
      <div class="ranks">
        <div class="row"><strong>6.º Dan</strong><span>Kenpo · Kuroishi Ryu Bujutsu</span></div>
        <div class="row"><strong>3.º Dan</strong><span>Ju-Jitsu · Kuroishi Ryu Bujutsu</span></div>
        <div class="row"><strong>Maestro</strong><span>Ju-Jitsu · Kuroishi Ryu Bujutsu</span></div>
      </div>
      <a class="btn btn--red" href="#percurso" data-modal-open="percurso">Ver percurso completo</a>
    </div>
  </section>

  <dialog class="modal" id="percurso" aria-labelledby="percurso-title">
    <div class="modal__head">
      <h2 id="percurso-title">Shihan Luís Coelho</h2>
      <a class="modal__close" href="#senseis" data-modal-close aria-label="Fechar"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></a>
    </div>
    <?php foreach ($c['percurso'] as $group => $lines): ?>
    <div class="modal__group">
      <h3><?= e($group) ?></h3>
      <ul>
        <?php foreach ($lines as $line): ?><li><?= e($line) ?></li><?php endforeach; ?>
      </ul>
    </div>
    <?php endforeach; ?>
  </dialog>

  <section class="wrap-section faq" id="faq">
    <div class="faq__intro">
      <h2 class="h2">Perguntas frequentes</h2>
      <p>Não encontras a resposta? <a href="#contactos">Fala connosco</a>.</p>
    </div>
    <div class="faq__list">
      <?php foreach ($c['faq'] as $i => [$q, $a]): ?>
      <details name="faq"<?= $i === 0 ? ' open' : '' ?>>
        <summary><?= e($q) ?><i aria-hidden="true"></i></summary>
        <p><?= e($a) ?></p>
      </details>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="band band--pad" id="loja">
    <div class="wrap-stack">
      <div class="section-head">
        <h2 class="h2">Loja</h2>
        <a class="btn btn--line" href="/loja">Ver loja</a>
      </div>
      <div class="shop">
        <a class="shop__item shop__item--big" href="/loja" aria-label="Ver loja"><div class="ph ph--shop">[FOTO DO PRODUTO]</div></a>
        <?php for ($i = 0; $i < 3; $i++): ?>
        <a class="shop__item" href="/loja" aria-label="Ver loja"><div class="ph ph--shop">[FOTO DO PRODUTO]</div></a>
        <?php endfor; ?>
        <div class="shop__promo">
          <span>Merchandising oficial da ADMSC</span>
          <a href="/loja">Ver todos os produtos</a>
        </div>
      </div>
    </div>
  </section>

  <section class="split contact" id="contactos">
    <div class="split__text split__text--left split__text--tall">
      <h2 class="h2">Venha treinar connosco</h2>
      <p class="lead"><?= e($c['venha_treinar']) ?></p>
      <div class="contact__data">
        <div><strong>Morada</strong><span><?= e($c['morada1']) ?><br><?= e($c['morada2']) ?></span></div>
        <div><strong>Email</strong><a class="wrap" href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></div>
        <div><strong>Telefone</strong><span><?= e($c['telefone']) ?></span></div>
        <div><strong>Redes sociais</strong><span class="social"><a href="<?= e($c['facebook']) ?>">Facebook</a><a href="<?= e($c['instagram']) ?>">Instagram</a></span></div>
      </div>
      <form class="contact__form" onsubmit="return false">
        <label class="fld span2">Nome<input type="text" name="nome" autocomplete="name" disabled></label>
        <label class="fld">Email<input type="email" name="email" autocomplete="email" disabled></label>
        <label class="fld">Telefone<input type="tel" name="telefone" autocomplete="tel" disabled></label>
        <label class="fld span2">Modalidade
          <select name="modalidade" disabled><option>Kenpo</option><option>Ju-Jitsu</option><option>Ainda não sei</option></select>
        </label>
        <label class="fld span2">Mensagem<textarea name="mensagem" disabled></textarea></label>
        <label class="consent span2"><input type="checkbox" name="consentimento" disabled>Aceito que a ADMSC use estes dados para me contactar, de acordo com a <a href="/privacidade">política de privacidade</a>.</label>
        <button type="submit" class="btn btn--red span2" disabled>Enviar</button>
        <p class="form-note span2">O formulário estará disponível em breve. Entretanto, contacte-nos por email.</p>
      </form>
    </div>
    <div class="map" data-map data-address="<?= e($address) ?>">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#B3201F" stroke-width="1.8" aria-hidden="true"><path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/></svg>
      <span><?= e($address) ?></span>
      <a class="btn btn--line" data-map-load href="https://www.openstreetmap.org/search?query=<?= e(rawurlencode($address)) ?>">Mostrar mapa</a>
      <small>O mapa é carregado a partir do Google Maps só depois de clicar.</small>
    </div>
  </section>
</main>
