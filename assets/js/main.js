(function () {
  'use strict';

  // ---- Header scroll state ----
  var header = document.getElementById('siteHeader');
  var siteFooter = document.querySelector('.site-footer');
  var headerHeight = header.offsetHeight;
  var menuToggle = document.getElementById('menuToggle');
  var navOverlay = document.getElementById('navOverlay');

  function onScroll() {
    if (navOverlay.classList.contains('is-open')) {
      return;
    }

    if (window.scrollY > 40) {
      header.classList.add('is-scrolled');
    } else {
      header.classList.remove('is-scrolled');
    }

    if (siteFooter) {
      var footerTop = siteFooter.getBoundingClientRect().top;
      header.classList.toggle('is-dark', footerTop <= headerHeight);
    }
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ---- Mobile menu ----
  menuToggle.addEventListener('click', function () {
    var isOpen = navOverlay.classList.toggle('is-open');
    menuToggle.classList.toggle('is-open', isOpen);
    menuToggle.setAttribute('aria-expanded', isOpen);

    if (isOpen) {
      header.classList.add('is-dark');
      header.classList.add('is-scrolled');
    } else {
      onScroll();
    }
  });
  navOverlay.querySelectorAll('.nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      navOverlay.classList.remove('is-open');
      menuToggle.classList.remove('is-open');
      menuToggle.setAttribute('aria-expanded', 'false');
      onScroll();
    });
  });

  // ---- Language toggle: site defaults to English, switches to Turkish ----
  var langToggle = document.getElementById('langToggle');
  var translatable = document.querySelectorAll('[data-tr]');

  function setLang(lang) {
    document.documentElement.lang = lang;

    translatable.forEach(function (el) {
      if (el.dataset.enCache === undefined) {
        el.dataset.enCache = el.tagName === 'META' ? el.getAttribute('content') : el.innerHTML;
      }
      var trValue = el.getAttribute('data-tr');
      if (el.tagName === 'META') {
        el.setAttribute('content', lang === 'tr' ? trValue : el.dataset.enCache);
      } else if (el.tagName === 'TITLE') {
        document.title = lang === 'tr' ? trValue : el.dataset.enCache;
      } else {
        el.innerHTML = lang === 'tr' ? trValue : el.dataset.enCache;
      }
    });

    langToggle.querySelectorAll('[data-lang]').forEach(function (span) {
      span.classList.toggle('lang-active', span.dataset.lang === lang);
    });
  }

  setLang('en');

  langToggle.addEventListener('click', function (e) {
    var target = e.target.closest('[data-lang]');
    var current = document.documentElement.lang === 'tr' ? 'tr' : 'en';
    var next = target ? target.dataset.lang : (current === 'tr' ? 'en' : 'tr');
    setLang(next);
  });

  // ---- Scroll reveal ----
  var revealEls = document.querySelectorAll('[data-reveal]');
  var manifestoLines = document.querySelectorAll('.manifesto-line');

  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          revealObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.2 });

    revealEls.forEach(function (el) { revealObserver.observe(el); });

    var manifestoObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry, i) {
        if (entry.isIntersecting) {
          var index = Array.prototype.indexOf.call(manifestoLines, entry.target);
          setTimeout(function () {
            entry.target.classList.add('is-visible');
          }, index * 60);
          manifestoObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.4 });

    manifestoLines.forEach(function (el) { manifestoObserver.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    manifestoLines.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // ---- Scroll hint: jump to the section after the hero ----
  var scrollHint = document.querySelector('.scroll-hint');
  if (scrollHint) {
    scrollHint.addEventListener('click', function () {
      var hero = document.querySelector('.hero');
      var next = hero.nextElementSibling;
      if (next) next.scrollIntoView({ behavior: 'smooth' });
    });
  }

  // ---- Contact form: AJAX submit ----
  var contactForm = document.getElementById('contactForm');
  var submitBtn = document.getElementById('submitBtn');
  var submitLabel = submitBtn.querySelector('.submit-label');

  var formMessages = {
    sending: { en: 'Sending…', tr: 'Gönderiliyor…' },
    sent: { en: 'Sent ✓', tr: 'Gönderildi ✓' },
    error: { en: 'Something went wrong, please try again.', tr: 'Bir hata oluştu, tekrar deneyin.' }
  };

  function currentLang() {
    return document.documentElement.lang === 'tr' ? 'tr' : 'en';
  }

  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();
    submitBtn.disabled = true;
    submitLabel.textContent = formMessages.sending[currentLang()];

    fetch(contactForm.action, {
      method: 'POST',
      body: new FormData(contactForm)
    })
      .then(function (res) { return res.json(); })
      .then(function (data) {
        if (data.ok) {
          submitLabel.textContent = formMessages.sent[currentLang()];
          submitBtn.classList.add('is-sent');
          contactForm.reset();
        } else {
          submitLabel.textContent = data.message || formMessages.error[currentLang()];
          submitBtn.disabled = false;
        }
      })
      .catch(function () {
        submitLabel.textContent = formMessages.error[currentLang()];
        submitBtn.disabled = false;
      });
  });

  // ---- Back to top ----
  var backToTop = document.getElementById('backToTop');
  if (backToTop) {
    backToTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
})();
