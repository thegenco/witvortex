(function () {
  'use strict';

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---- Header scroll state ----
  var header = document.getElementById('siteHeader');
  var siteFooter = document.querySelector('.site-footer');
  var main = document.getElementById('main');
  var menuToggle = document.getElementById('menuToggle');
  var navOverlay = document.getElementById('navOverlay');

  function isMenuOpen() {
    return navOverlay.classList.contains('is-open');
  }

  function onScroll() {
    if (isMenuOpen()) {
      return;
    }

    header.classList.toggle('is-scrolled', window.scrollY > 40);

    if (siteFooter) {
      var footerTop = siteFooter.getBoundingClientRect().top;
      header.classList.toggle('is-dark', footerTop <= header.offsetHeight);
    }
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  // ---- Menu overlay ----
  // Açıkken arka plan kaymaz ve odaklanamaz (inert); Escape kapatır, odak butona döner.
  function setMenu(open) {
    navOverlay.classList.toggle('is-open', open);
    menuToggle.classList.toggle('is-open', open);
    menuToggle.setAttribute('aria-expanded', String(open));
    document.documentElement.classList.toggle('menu-open', open);
    if (main) main.inert = open;
    if (siteFooter) siteFooter.inert = open;

    if (open) {
      header.classList.add('is-dark', 'is-scrolled');
      navOverlay.querySelector('.nav-link').focus({ preventScroll: true });
    } else {
      onScroll();
    }
  }

  menuToggle.addEventListener('click', function () {
    setMenu(!isMenuOpen());
  });

  navOverlay.querySelectorAll('.nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      setMenu(false);
    });
  });

  header.querySelector('.logo').addEventListener('click', function () {
    if (isMenuOpen()) setMenu(false);
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && isMenuOpen()) {
      setMenu(false);
      menuToggle.focus();
    }
  });

  // ---- Language: site defaults to English, switches to Turkish ----
  var langOptions = document.querySelectorAll('.lang-option');
  var translatable = document.querySelectorAll('[data-tr]');
  var translatableLabels = document.querySelectorAll('[data-tr-aria-label]');
  var onLangChange = [];

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

    translatableLabels.forEach(function (el) {
      if (el.dataset.enLabel === undefined) {
        el.dataset.enLabel = el.getAttribute('aria-label');
      }
      el.setAttribute('aria-label', lang === 'tr' ? el.getAttribute('data-tr-aria-label') : el.dataset.enLabel);
    });

    langOptions.forEach(function (option) {
      option.setAttribute('aria-pressed', String(option.dataset.lang === lang));
    });

    onLangChange.forEach(function (fn) { fn(lang); });
  }

  setLang('en');

  langOptions.forEach(function (option) {
    option.addEventListener('click', function () {
      setLang(option.dataset.lang);
    });
  });

  // ---- Scroll reveal ----
  // Aynı anda görünür olan öğeler 80 ms arayla, sırayla açılır.
  var revealEls = document.querySelectorAll('[data-reveal]');

  if ('IntersectionObserver' in window) {
    var revealObserver = new IntersectionObserver(function (entries) {
      entries
        .filter(function (entry) { return entry.isIntersecting; })
        .forEach(function (entry, i) {
          var el = entry.target;
          if (!reducedMotion && i > 0) {
            el.style.transitionDelay = i * 80 + 'ms';
            el.addEventListener('transitionend', function () { el.style.transitionDelay = ''; }, { once: true });
          }
          el.classList.add('is-visible');
          revealObserver.unobserve(el);
        });
    }, { threshold: 0.2 });

    revealEls.forEach(function (el) { revealObserver.observe(el); });
  } else {
    revealEls.forEach(function (el) { el.classList.add('is-visible'); });
  }

  // ---- Hero video ----
  // Ekran dışındayken durur; hareketi azaltma tercihi varsa hiç oynamaz (poster görünür).
  var heroVideo = document.querySelector('.hero-video');
  if (heroVideo) {
    if (reducedMotion) {
      heroVideo.removeAttribute('autoplay');
      heroVideo.pause();
    } else if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        if (entries[0].isIntersecting) {
          heroVideo.play().catch(function () {});
        } else {
          heroVideo.pause();
        }
      }).observe(heroVideo);
    }
  }

  // ---- Scroll hint: jump to the section after the hero ----
  var scrollHint = document.querySelector('.scroll-hint');
  if (scrollHint) {
    scrollHint.addEventListener('click', function () {
      var hero = document.querySelector('.hero');
      var next = hero.nextElementSibling;
      if (next) next.scrollIntoView({ behavior: reducedMotion ? 'auto' : 'smooth' });
    });
  }

  // ---- Contact form ----
  // Durum ve hatalar butonun içine değil, alanların altına ve durum satırına
  // sayfanın dilinde yazılır; dil değişince yeniden çizilir.
  var contactForm = document.getElementById('contactForm');
  var submitBtn = document.getElementById('submitBtn');
  var submitLabel = submitBtn.querySelector('.submit-label');
  var formStatus = document.getElementById('formStatus');
  var formSuccess = document.getElementById('formSuccess');
  var requiredFields = ['name', 'email', 'message'];
  var isSending = false;
  var statusCode = null;
  var fieldErrors = {};

  var formText = {
    send: { en: 'Send', tr: 'Gönder' },
    sending: { en: 'Sending…', tr: 'Gönderiliyor…' },
    required: { en: 'This field is required.', tr: 'Bu alan zorunlu.' },
    email: { en: 'Enter a valid email, like name@example.com.', tr: 'Geçerli bir e-posta girin, örn. ad@ornek.com.' },
    invalid: { en: 'Check the highlighted fields.', tr: 'İşaretli alanları kontrol edin.' },
    network: { en: 'Couldn’t send. Check your connection and try again.', tr: 'Gönderilemedi. Bağlantınızı kontrol edip tekrar deneyin.' },
    server: { en: 'Couldn’t send. Please try again in a moment.', tr: 'Gönderilemedi. Birazdan tekrar deneyin.' }
  };

  function currentLang() {
    return document.documentElement.lang === 'tr' ? 'tr' : 'en';
  }

  function text(key) {
    return formText[key][currentLang()];
  }

  function hasFieldErrors() {
    return requiredFields.some(function (name) { return fieldErrors[name]; });
  }

  function renderForm() {
    // Gönderim sürerken durum satırı "Gönderiliyor…" der; buton etiketi sabit kalır.
    var statusKey = isSending ? 'sending' : statusCode;
    submitLabel.textContent = text('send');
    formStatus.textContent = statusKey ? text(statusKey) : '';
    formStatus.classList.toggle('is-error', !isSending && Boolean(statusCode));

    requiredFields.forEach(function (name) {
      var input = contactForm.elements[name];
      var message = document.getElementById(name + '-error');
      var code = fieldErrors[name];
      if (code) {
        input.setAttribute('aria-invalid', 'true');
      } else {
        input.removeAttribute('aria-invalid');
      }
      message.textContent = code ? text(code) : '';
      message.hidden = !code;
    });
  }

  function checkField(name) {
    var input = contactForm.elements[name];
    if (!input.value.trim()) return 'required';
    if (input.type === 'email' && input.validity.typeMismatch) return 'email';
    return null;
  }

  function focusFirstError() {
    for (var i = 0; i < requiredFields.length; i++) {
      if (fieldErrors[requiredFields[i]]) {
        contactForm.elements[requiredFields[i]].focus();
        return;
      }
    }
  }

  function showErrors(errors) {
    fieldErrors = errors;
    statusCode = 'invalid';
    renderForm();
    focusFirstError();
  }

  // Hata gösterildikten sonra alan düzeldikçe uyarısı kalkar.
  requiredFields.forEach(function (name) {
    contactForm.elements[name].addEventListener('input', function () {
      if (!fieldErrors[name]) return;
      fieldErrors[name] = checkField(name);
      if (!hasFieldErrors()) statusCode = null;
      renderForm();
    });
  });

  function setSending(state) {
    isSending = state;
    submitBtn.setAttribute('aria-disabled', String(state));
    submitBtn.classList.toggle('is-loading', state);
    renderForm();
  }

  function showSuccess() {
    contactForm.reset();
    fieldErrors = {};
    statusCode = null;
    contactForm.hidden = true;
    formSuccess.hidden = false;
    formSuccess.focus();
  }

  function send(isRetry) {
    return fetch(contactForm.action, {
      method: 'POST',
      body: new FormData(contactForm),
      headers: { Accept: 'application/json' }
    })
      .then(function (res) {
        return res.json().catch(function () { return { ok: false, code: 'server' }; });
      })
      .then(function (data) {
        if (data.ok) {
          showSuccess();
        } else if (data.code === 'session' && data.token && !isRetry) {
          // Oturum dolmuş: yeni token ile bir kez sessizce tekrar dene.
          contactForm.elements.csrf_token.value = data.token;
          return send(true);
        } else if (data.code === 'invalid' && data.fields) {
          showErrors(data.fields);
        } else {
          statusCode = 'server';
        }
      })
      .catch(function () {
        statusCode = 'network';
      });
  }

  contactForm.addEventListener('submit', function (e) {
    e.preventDefault();
    if (isSending) return;

    var errors = {};
    requiredFields.forEach(function (name) {
      var code = checkField(name);
      if (code) errors[name] = code;
    });
    if (Object.keys(errors).length) {
      showErrors(errors);
      return;
    }

    fieldErrors = {};
    statusCode = null;
    setSending(true);
    send(false).then(function () { setSending(false); });
  });

  document.getElementById('formReset').addEventListener('click', function () {
    formSuccess.hidden = true;
    contactForm.hidden = false;
    contactForm.elements.name.focus();
  });

  onLangChange.push(renderForm);
  renderForm();

  // ---- Back to top ----
  var backToTop = document.getElementById('backToTop');
  if (backToTop) {
    backToTop.addEventListener('click', function () {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }
})();
