(function () {
  'use strict';

  /* Abertura automática: aproxima o torii do próprio símbolo e atravessa o vão entre os pilares. */
  function initPortal() {
    var root = document.documentElement;
    var portal = document.getElementById('portal');
    if (!portal || !root.classList.contains('portal-on')) return;

    var veil = document.getElementById('portal-veil');
    var symbol = document.getElementById('portal-symbol');
    var HOLD = 1500;
    var DURATION = 1800;
    /* Vão do torii, em frações da imagem do símbolo. */
    var GAP = { x: .762, y: .4015, halfW: .0909, halfH: .1344 };
    var started = false;

    function remember() {
      try { sessionStorage.setItem('admsc-portal', '1'); } catch (err) {}
    }

    function finish() {
      remember();
      root.classList.remove('portal-on');
      portal.remove();
      document.removeEventListener('keydown', onKey);
    }

    function frame(p) {
      var vw = window.innerWidth, vh = window.innerHeight;
      var size = symbol.offsetWidth;
      var offX = (GAP.x - .5) * size, offY = (GAP.y - .5) * size;
      var sEnd = Math.max(vw / (GAP.halfW * 2 * size), vh / (GAP.halfH * 2 * size)) * 1.08;
      var e = p < .5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;
      var s = Math.pow(sEnd, e);
      var open = Math.min(1, Math.max(0, (e - .12) / .3));

      symbol.style.transform = 'translate(' + (-offX * e) + 'px,' + (-offY * e) + 'px) scale(' + s + ')';

      var hw = GAP.halfW * open;
      var il = (GAP.x - hw) * 100 + '%', ir = (GAP.x + hw) * 100 + '%';
      var it = (GAP.y - GAP.halfH) * 100 + '%', ib = (GAP.y + GAP.halfH) * 100 + '%';
      symbol.style.clipPath = 'polygon(evenodd,0 0,100% 0,100% 100%,0 100%,0 0,' +
        il + ' ' + it + ',' + ir + ' ' + it + ',' + ir + ' ' + ib + ',' + il + ' ' + ib + ',' + il + ' ' + it + ')';

      var hx = vw / 2 + offX * (1 - e), hy = vh / 2 + offY * (1 - e);
      var l = hx - hw * size * s, r = hx + hw * size * s;
      var t = hy - GAP.halfH * size * s, b = hy + GAP.halfH * size * s;
      veil.style.clipPath = 'polygon(evenodd,0 0,' + vw + 'px 0,' + vw + 'px ' + vh + 'px,0 ' + vh + 'px,0 0,' +
        l + 'px ' + t + 'px,' + r + 'px ' + t + 'px,' + r + 'px ' + b + 'px,' + l + 'px ' + b + 'px,' + l + 'px ' + t + 'px)';
    }

    function start() {
      if (started) return;
      started = true;

      if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        portal.style.transition = 'opacity .4s ease';
        portal.style.opacity = '0';
        setTimeout(finish, 420);
        return;
      }

      var t0 = null;
      function tick(now) {
        if (t0 === null) t0 = now;
        var p = Math.min(1, (now - t0) / DURATION);
        frame(p);
        if (p < 1) requestAnimationFrame(tick); else finish();
      }
      requestAnimationFrame(tick);
    }

    function onKey(ev) {
      if (ev.key === 'Escape') finish();
    }

    function schedule() {
      setTimeout(start, HOLD);
    }

    document.addEventListener('keydown', onKey);
    if (document.readyState === 'complete') schedule();
    else window.addEventListener('load', schedule, { once: true });
  }

  /* Modal do percurso: sem JS funciona por :target. */
  function initModal() {
    document.querySelectorAll('[data-modal-open]').forEach(function (trigger) {
      var dialog = document.getElementById(trigger.getAttribute('data-modal-open'));
      if (!dialog || !dialog.showModal) return;
      trigger.addEventListener('click', function (ev) {
        ev.preventDefault();
        dialog.showModal();
      });
      dialog.addEventListener('click', function (ev) {
        if (ev.target === dialog) dialog.close();
      });
      dialog.querySelectorAll('[data-modal-close]').forEach(function (btn) {
        btn.addEventListener('click', function (ev) {
          ev.preventDefault();
          dialog.close();
        });
      });
    });
  }

  function initMobileNav() {
    document.querySelectorAll('.mobile-nav').forEach(function (nav) {
      nav.addEventListener('toggle', function () {
        document.documentElement.classList.toggle('menu-open', nav.open);
      });
      nav.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () { nav.open = false; });
      });
      document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape') nav.open = false;
      });
    });
  }

  /* Só contacta o Google depois do clique do visitante. */
  function initMap() {
    document.querySelectorAll('[data-map]').forEach(function (map) {
      var load = map.querySelector('[data-map-load]');
      if (!load) return;
      load.addEventListener('click', function (ev) {
        ev.preventDefault();
        var frame = document.createElement('iframe');
        frame.title = 'Mapa: ' + map.dataset.address;
        frame.loading = 'lazy';
        frame.referrerPolicy = 'no-referrer';
        frame.src = 'https://www.google.com/maps?q=' + encodeURIComponent(map.dataset.address) + '&output=embed';
        map.replaceChildren(frame);
      });
    });
  }

  /* Os textos aparecem ao chegar à secção; o pergaminho abre e fecha com o scroll. */
  function initAcademy() {
    var section = document.querySelector('[data-academy]');
    if (!section || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    if ('IntersectionObserver' in window) {
      section.classList.add('is-waiting');
      var seen = new IntersectionObserver(function (entries) {
        if (!entries[0].isIntersecting) return;
        section.classList.remove('is-waiting');
        seen.disconnect();
      }, { threshold: 0.15 });
      seen.observe(section);
    }

    var scroll = section.querySelector('[data-scroll]');
    var sheet = section.querySelector('[data-scroll-sheet]');
    if (!scroll || !sheet) return;

    var current = 0, frame = 0, forced = false;
    function target() {
      if (forced) return 1;
      var top = scroll.getBoundingClientRect().top;
      var start = window.innerHeight * 0.88;
      var end = Math.max(96, window.innerHeight - scroll.offsetHeight - 32);
      return Math.min(1, Math.max(0, (start - top) / (start - end)));
    }
    function paint() {
      scroll.style.setProperty('--p', current.toFixed(4));
      scroll.style.setProperty('--up', ((current - 1) * sheet.offsetHeight).toFixed(1) + 'px');
    }
    function step() {
      var goal = target();
      current += (goal - current) * 0.16;
      if (Math.abs(goal - current) < 0.002) {
        current = goal;
        frame = 0;
      } else {
        frame = requestAnimationFrame(step);
      }
      paint();
    }
    function schedule() { if (!frame) frame = requestAnimationFrame(step); }

    current = target();
    paint();
    window.addEventListener('scroll', schedule, { passive: true });
    window.addEventListener('resize', function () { paint(); schedule(); });
    /* Com teclado, abre ao chegar ao botão do mapa. */
    scroll.addEventListener('focusin', function () { forced = true; schedule(); });
  }

  /* Transparente no topo; fundo translúcido e borda assim que há scroll. */
  function initHeader() {
    var header = document.querySelector('.site-header');
    if (!header) return;
    document.documentElement.classList.add('js-header');
    var update = function () {
      header.classList.toggle('is-scrolled', window.scrollY > 8);
    };
    window.addEventListener('scroll', update, { passive: true });
    update();
  }

  /* Roda os destaques; o tempo de cada um é a animação da barra (definida no CSS). */
  function initHighlights() {
    var root = document.querySelector('.highlights');
    if (!root || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var slides = Array.prototype.slice.call(root.querySelectorAll('.highlight'));
    if (slides.length < 2) return;

    var nav = document.createElement('div');
    nav.className = 'highlights__nav';
    var buttons = slides.map(function (slide, i) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = slide.querySelector('.num').textContent;
      button.setAttribute('aria-label', 'Destaque ' + (i + 1) + ': ' + slide.querySelector('h3').textContent);
      var bar = document.createElement('span');
      bar.className = 'highlights__bar';
      button.appendChild(bar);
      button.addEventListener('click', function () { show(i); });
      nav.appendChild(button);
      return button;
    });
    root.appendChild(nav);
    root.classList.add('is-rotating');

    var current = -1;
    function show(next) {
      if (next === current) return;
      slides.forEach(function (slide) { slide.classList.remove('is-leaving'); });
      if (current >= 0) {
        slides[current].classList.remove('is-active');
        slides[current].classList.add('is-leaving');
        buttons[current].removeAttribute('aria-current');
      }
      slides[next].classList.add('is-active');
      buttons[next].setAttribute('aria-current', 'true');
      current = next;
    }
    show(0);

    nav.addEventListener('animationend', function (ev) {
      if (ev.animationName === 'highlight-fill') show((current + 1) % slides.length);
    });
  }

  /* O cinto começa desapertado e aperta ao passar o rato. Sem rato (ou ecrã estreito), quando entra no ecrã. */
  function initBelt() {
    var belt = document.querySelector('[data-belt]');
    if (!belt || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    belt.classList.add('is-loose');
    var tie = function () { belt.classList.remove('is-loose'); };

    belt.addEventListener('pointerenter', function (ev) { if (ev.pointerType === 'mouse') tie(); });
    belt.addEventListener('focusin', tie);

    var usesHover = window.matchMedia('(hover: hover) and (pointer: fine) and (min-width: 1280px)').matches;
    if (usesHover) return;
    if (!('IntersectionObserver' in window)) { tie(); return; }
    var seen = new IntersectionObserver(function (entries) {
      if (!entries[0].isIntersecting) return;
      tie();
      seen.disconnect();
    }, { threshold: 0.5 });
    seen.observe(belt.querySelector('.belt__art'));
  }

  initPortal();
  initHeader();
  initBelt();
  initHighlights();
  initModal();
  initMobileNav();
  initMap();
  initAcademy();
})();
