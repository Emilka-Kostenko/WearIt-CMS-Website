/**
 * Home hero slider: autoplay (seconds set in the admin), previous/next arrows, dots.
 * Plain JavaScript, no libraries. Without JS the page simply shows slide 1.
 */
(function () {
  'use strict';

  document.querySelectorAll('[data-hero-slider]').forEach(function (root) {
    var track = root.querySelector('.hero-slider__track');
    var slides = Array.prototype.slice.call(root.querySelectorAll('.hero-slider__slide'));
    var dots = Array.prototype.slice.call(root.querySelectorAll('.hero-slider__dot'));
    var prev = root.querySelector('.hero-slider__arrow--prev');
    var next = root.querySelector('.hero-slider__arrow--next');

    if (!track || slides.length < 2) {
      return;
    }

    var seconds = parseFloat(root.getAttribute('data-seconds'));
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var index = 0;
    var timer = null;
    var hovering = false;   // mouse is over the hero
    var focused = false;    // keyboard focus is inside the hero

    function show(n) {
      index = (n + slides.length) % slides.length;
      track.style.transform = 'translateX(' + (-100 * index) + '%)';
      root.setAttribute('data-active', index);

      slides.forEach(function (slide, i) {
        var active = i === index;
        slide.setAttribute('aria-hidden', active ? 'false' : 'true');
        // Hidden slides must not be reachable with the Tab key.
        slide.inert = !active;
      });
      dots.forEach(function (dot, i) {
        var active = i === index;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-current', active ? 'true' : 'false');
      });
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    function start() {
      stop();
      if (seconds > 0 && !reduceMotion && !hovering && !focused && !document.hidden) {
        timer = setInterval(function () {
          show(index + 1);
        }, seconds * 1000);
      }
    }

    // Manual navigation restarts the countdown so the slide does not jump straight away.
    function go(n) {
      show(n);
      start();
    }

    if (prev) {
      prev.addEventListener('click', function () { go(index - 1); });
    }
    if (next) {
      next.addEventListener('click', function () { go(index + 1); });
    }
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { go(i); });
    });

    // Pause while someone is pointing at or tabbing through the hero.
    root.addEventListener('mouseenter', function () { hovering = true; stop(); });
    root.addEventListener('mouseleave', function () { hovering = false; start(); });
    // Only keyboard focus pauses it: a mouse click leaves focus on the arrow, which must not freeze the slider.
    root.addEventListener('focusin', function (e) {
      if (e.target.matches && e.target.matches(':focus-visible')) { focused = true; stop(); }
    });
    root.addEventListener('focusout', function () { focused = false; start(); });
    document.addEventListener('visibilitychange', start);

    root.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowLeft') { go(index - 1); }
      if (e.key === 'ArrowRight') { go(index + 1); }
    });

    root.classList.add('is-ready');
    show(0);
    start();
  });
})();
