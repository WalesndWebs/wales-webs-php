(function () {
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var burger = document.querySelector('.nav-burger');
  var nav = document.getElementById('main-nav');
  if (burger && nav) {
    burger.addEventListener('click', function () {
      var o = burger.getAttribute('aria-expanded') === 'true';
      burger.setAttribute('aria-expanded', String(!o));
      nav.classList.toggle('is-open', !o);
    });
  }
  var menu = document.querySelector('[data-menu]');
  if (menu) {
    var tg = menu.querySelector('.menu-toggle');
    var set = function (o) { tg.setAttribute('aria-expanded', String(o)); menu.classList.toggle('is-open', o); };
    tg.addEventListener('click', function () { set(tg.getAttribute('aria-expanded') !== 'true'); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { set(false); tg.focus(); } });
    document.addEventListener('click', function (e) { if (!menu.contains(e.target)) set(false); });
    menu.addEventListener('focusout', function (e) { if (!menu.contains(e.relatedTarget)) set(false); });
  }
  var header = document.querySelector('[data-header]');
  if (header) {
    var f = function () { header.classList.toggle('is-scrolled', window.scrollY > 8); };
    f(); window.addEventListener('scroll', f, { passive: true });
  }
  document.querySelectorAll('.reveal').forEach(function (el, i) { el.style.animationDelay = (i * 80) + 'ms'; });

  var car = document.querySelector('[data-carousel]');
  if (car) {
    var slides = car.querySelectorAll('.slide'), dots = car.querySelectorAll('.dot');
    var pauseBtn = car.querySelector('[data-pause]'), idx = 0, timer = null, userPaused = reduce;
    var show = function (n) {
      idx = (n + slides.length) % slides.length;
      slides.forEach(function (s, i) { s.classList.toggle('is-active', i === idx); s.setAttribute('aria-hidden', i === idx ? 'false' : 'true'); });
      dots.forEach(function (d, i) { d.classList.toggle('is-active', i === idx); d.setAttribute('aria-current', i === idx ? 'true' : 'false'); });
    };
    var stop = function () { clearInterval(timer); timer = null; };
    var start = function () { stop(); if (!userPaused) timer = setInterval(function () { show(idx + 1); }, 5600); };
    dots.forEach(function (d, i) { d.addEventListener('click', function () { show(i); start(); }); });
    pauseBtn.addEventListener('click', function () {
      userPaused = !userPaused;
      pauseBtn.setAttribute('aria-pressed', String(userPaused));
      pauseBtn.textContent = userPaused ? 'Play' : 'Pause';
      start();
    });
    car.addEventListener('mouseenter', stop); car.addEventListener('mouseleave', start);
    car.addEventListener('focusin', stop); car.addEventListener('focusout', start);
    if (userPaused) { pauseBtn.setAttribute('aria-pressed', 'true'); pauseBtn.textContent = 'Play'; }
    start();
  }

  document.querySelectorAll('[data-copy]').forEach(function (b) {
    b.addEventListener('click', function () {
      var u = b.getAttribute('data-copy'); u = new URL(u, location.origin).href;
      var done = function () { b.textContent = 'Link copied'; setTimeout(function () { b.textContent = 'Copy link'; }, 2000); };
      if (navigator.clipboard) navigator.clipboard.writeText(u).then(done, function () { window.prompt('Copy this link', u); });
      else window.prompt('Copy this link', u);
    });
  });
  var err = document.querySelector('[data-error-summary]');
  if (err) err.focus();
  document.querySelectorAll('form[method="post"]').forEach(function (form) {
    form.addEventListener('submit', function (event) {
      if (event.defaultPrevented || !form.checkValidity()) return;
      if (form.dataset.submitting === '1') { event.preventDefault(); return; }
      form.dataset.submitting = '1';
      var button = event.submitter;
      if (button) {
        button.dataset.originalLabel = button.textContent;
        button.textContent = 'Sending…';
        button.setAttribute('aria-busy', 'true');
      }
    });
  });
  window.addEventListener('pageshow', function () {
    document.querySelectorAll('form[data-submitting]').forEach(function (form) {
      delete form.dataset.submitting;
      form.querySelectorAll('[data-original-label]').forEach(function (button) {
        button.textContent = button.dataset.originalLabel;
        button.removeAttribute('aria-busy');
      });
    });
  });
})();
