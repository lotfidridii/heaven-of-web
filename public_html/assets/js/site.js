/* Heaven of Web — public site interactions (plain ES2020, no build step). */
(() => {
  'use strict';

  const $ = (sel, root = document) => root.querySelector(sel);
  const $$ = (sel, root = document) => Array.from(root.querySelectorAll(sel));
  const html = document.documentElement;
  const rtl = html.dir === 'rtl';
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const finePointer = matchMedia('(hover: hover) and (pointer: fine)').matches;
  const hasIO = 'IntersectionObserver' in window;

  /* ---------- Loading screen, then the hero entrance ---------- */
  const preloader = $('[data-preloader]');
  $$('[data-hero-item]').forEach((el, i) => el.style.setProperty('--i', i));
  let loaded = false;
  const reveal = () => {
    if (loaded) return;
    loaded = true;
    try { sessionStorage.setItem('how-loaded', '1'); } catch { /* private mode */ }
    preloader?.classList.add('is-done');
    html.classList.add('is-loaded');
    document.dispatchEvent(new CustomEvent('site:loaded'));
  };
  if (!preloader || html.classList.contains('no-preload') || reduceMotion) {
    setTimeout(reveal, 30);
  } else {
    const started = performance.now();
    const done = () => setTimeout(reveal, Math.max(0, 900 - (performance.now() - started)));
    if (document.readyState === 'complete') done(); else addEventListener('load', done, { once: true });
  }
  setTimeout(reveal, 3500); // never keep visitors waiting on a slow image

  /* ---------- Scroll animations ---------- */
  if (reduceMotion || !hasIO) {
    html.classList.add('anim-off');
  } else {
    $$('[data-anim="stagger"]').forEach((group) => Array.from(group.children).forEach((c, i) => c.style.setProperty('--i', i % 8)));
    $$('[data-anim="process"] .process-step').forEach((s, i) => s.style.setProperty('--i', i));
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        // Also reveal elements that were skipped over (anchor jumps land below them).
        if (!entry.isIntersecting && entry.boundingClientRect.top > 0) return;
        entry.target.classList.add('is-in');
        io.unobserve(entry.target);
      });
    }, { rootMargin: '0px 0px -12% 0px', threshold: 0.08 });
    $$('[data-anim]').forEach((el) => io.observe(el));
  }

  /* Counters in the stats row: "+5", "100%", "8" count up from 0 */
  const countUp = (el) => {
    const m = el.textContent.trim().match(/^(\D*)(\d+)(.*)$/);
    if (!m || reduceMotion) return;
    const [, pre, num, post] = m;
    const target = parseInt(num, 10);
    const start = performance.now();
    const step = (now) => {
      const p = Math.min(1, (now - start) / 1400);
      el.textContent = pre + Math.round(target * (1 - Math.pow(1 - p, 3))) + post;
      if (p < 1) requestAnimationFrame(step);
    };
    el.textContent = pre + '0' + post;
    requestAnimationFrame(step);
    setTimeout(() => { el.textContent = pre + num + post; }, 1800);
  };
  if (hasIO) {
    const cio = new IntersectionObserver((entries) => entries.forEach((e) => {
      if (e.isIntersecting) { countUp(e.target); cio.unobserve(e.target); }
    }), { threshold: 0.6 });
    $$('[data-count]').forEach((el) => cio.observe(el));
  }

  /* Hero photo parallax */
  const heroBg = $('[data-parallax]');
  if (heroBg && !reduceMotion) {
    let ticking = false;
    addEventListener('scroll', () => {
      if (ticking) return;
      ticking = true;
      requestAnimationFrame(() => {
        const y = scrollY;
        if (y < innerHeight * 1.2) heroBg.style.transform = `translate3d(0, ${y * 0.25}px, 0)`;
        ticking = false;
      });
    }, { passive: true });
  }

  /* ---------- Header: solid after scrolling, hidden while scrolling down (desktop) ---------- */
  const header = $('[data-header]');
  let lastY = scrollY;
  const onScroll = () => {
    const y = scrollY;
    header?.classList.toggle('is-scrolled', y > 30);
    header?.classList.toggle('is-hidden', y > 500 && y > lastY + 4);
    if (y < lastY - 4) header?.classList.remove('is-hidden');
    lastY = y;
  };
  addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile menu: bottom sheet ---------- */
  const menu = $('[data-menu]');
  if (menu) {
    const panel = $('[data-menu-panel]', menu);
    const toggles = $$('[data-menu-open]');
    let lastFocus = null;
    const focusables = () => $$('a[href], button:not([disabled])', panel);
    const open = () => {
      lastFocus = document.activeElement;
      menu.hidden = false;
      void menu.offsetHeight; // start the slide-up from the closed position
      menu.classList.add('is-open');
      document.body.classList.add('menu-open');
      window.__lenis?.stop();
      toggles.forEach((t) => t.setAttribute('aria-expanded', 'true'));
      setTimeout(() => $('.menu-close', panel)?.focus(), 80);
    };
    const close = () => {
      if (!menu.classList.contains('is-open')) return;
      menu.classList.remove('is-open', 'is-dragging');
      panel.style.removeProperty('--drag');
      document.body.classList.remove('menu-open');
      window.__lenis?.start();
      toggles.forEach((t) => t.setAttribute('aria-expanded', 'false'));
      setTimeout(() => { if (!menu.classList.contains('is-open')) menu.hidden = true; }, reduceMotion ? 0 : 500);
      lastFocus?.focus?.({ preventScroll: true });
    };
    toggles.forEach((t) => t.addEventListener('click', open));
    $$('[data-menu-close]', menu).forEach((b) => b.addEventListener('click', close));
    panel.addEventListener('click', (e) => { if (e.target.closest('a[href]')) close(); });
    addEventListener('keydown', (e) => {
      if (!menu.classList.contains('is-open')) return;
      if (e.key === 'Escape') { close(); return; }
      if (e.key === 'Tab') { // keep focus inside the sheet
        const f = focusables();
        if (!f.length) return;
        if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
        else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
      }
    });
    // Swipe down on the handle or the top bar to close
    let startY = null;
    panel.addEventListener('pointerdown', (e) => {
      if (!e.target.closest('[data-menu-handle], .menu-head') || e.target.closest('a, button')) return;
      startY = e.clientY;
      menu.classList.add('is-dragging');
    });
    addEventListener('pointermove', (e) => {
      if (startY === null) return;
      panel.style.setProperty('--drag', Math.max(0, e.clientY - startY) + 'px');
    }, { passive: true });
    const endDrag = (e) => {
      if (startY === null) return;
      const dy = e.clientY - startY;
      startY = null;
      menu.classList.remove('is-dragging');
      if (dy > 90) close(); else panel.style.setProperty('--drag', '0px');
    };
    addEventListener('pointerup', endDrag);
    addEventListener('pointercancel', endDrag);
    matchMedia('(min-width: 1024px)').addEventListener('change', (e) => { if (e.matches) close(); });
  }

  /* Language menu: close when clicking elsewhere */
  const langMenu = $('[data-lang-menu]');
  document.addEventListener('click', (e) => { if (langMenu?.open && !langMenu.contains(e.target)) langMenu.open = false; });

  /* Active section in the nav and tab bar (home page anchors only) */
  const navLinks = $$('[data-nav-link]').filter((a) => a.getAttribute('href')?.startsWith('#'));
  if (navLinks.length && hasIO) {
    const io = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (!entry.isIntersecting) return;
        navLinks.forEach((a) => a.classList.toggle('is-active', a.dataset.navLink === entry.target.id));
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    new Set(navLinks.map((a) => a.dataset.navLink)).forEach((id) => { const s = document.getElementById(id); if (s) io.observe(s); });
  }

  /* ---------- Carousels: prev/next buttons and page dots ---------- */
  $$('[data-carousel-wrap]').forEach((wrap) => {
    const track = $('[data-carousel]', wrap);
    const dotsBox = $('[data-carousel-dots]', wrap);
    const controls = $('.carousel-controls', wrap);
    const dir = rtl ? -1 : 1;
    let dots = [];
    const pages = () => Math.max(1, Math.round(track.scrollWidth / track.clientWidth));
    const current = () => Math.round(Math.abs(track.scrollLeft) / track.clientWidth);
    const go = (i) => track.scrollTo({ left: dir * i * track.clientWidth, behavior: reduceMotion ? 'auto' : 'smooth' });
    const build = () => {
      const n = pages();
      controls.hidden = n < 2;
      dotsBox.replaceChildren(...Array.from({ length: n }, (_, i) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', `${i + 1} / ${n}`);
        b.addEventListener('click', () => go(i));
        return b;
      }));
      dots = Array.from(dotsBox.children);
      sync();
    };
    const sync = () => { const c = current(); dots.forEach((d, i) => d.setAttribute('aria-current', String(i === c))); };
    $('[data-carousel-prev]', wrap)?.addEventListener('click', () => go(Math.max(0, current() - 1)));
    $('[data-carousel-next]', wrap)?.addEventListener('click', () => go(Math.min(pages() - 1, current() + 1)));
    track.addEventListener('scroll', () => requestAnimationFrame(sync), { passive: true });
    track.setAttribute('data-lenis-prevent-wheel', '');
    addEventListener('resize', build);
    build();
  });

  /* ---------- Projects page: category filter with a FLIP animation ---------- */
  const grid = $('[data-project-grid]');
  const filterBtns = $$('[data-filter]');
  filterBtns.forEach((btn) => {
    btn.addEventListener('click', () => {
      const f = btn.dataset.filter;
      filterBtns.forEach((b) => b.setAttribute('aria-pressed', String(b === btn)));
      const cards = $$('.project-tile', grid);
      const before = new Map(cards.map((c) => [c, c.getBoundingClientRect()]));
      cards.forEach((c) => c.classList.toggle('is-hidden', f !== '*' && c.dataset.category !== f));
      if (reduceMotion) return;
      cards.forEach((c) => {
        if (c.classList.contains('is-hidden')) return;
        const a = before.get(c);
        const b = c.getBoundingClientRect();
        if (!a.width) {
          c.animate([{ opacity: 0, transform: 'scale(.94)' }, { opacity: 1, transform: 'none' }], { duration: 450, easing: 'cubic-bezier(.22,1,.36,1)' });
        } else if (a.left !== b.left || a.top !== b.top) {
          c.animate([{ transform: `translate(${a.left - b.left}px, ${a.top - b.top}px)` }, { transform: 'none' }], { duration: 550, easing: 'cubic-bezier(.22,1,.36,1)' });
        }
      });
    });
  });

  /* ---------- Palette swatches: click to copy the hex code ---------- */
  $$('[data-copy]').forEach((btn) => {
    btn.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(btn.dataset.copy); } catch { return; }
      btn.classList.add('is-copied');
      clearTimeout(btn._t);
      btn._t = setTimeout(() => btn.classList.remove('is-copied'), 1600);
    });
  });

  /* ---------- Custom select (select-only combobox; the native <select> still holds the value) ---------- */
  const chevron = '<svg class="icon select-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>';
  const tick = '<svg class="icon select-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 12.5 4.5 4.5L19 7.5"/></svg>';
  $$('select[data-select]').forEach((sel, n) => {
    const id = sel.id || `select-${n}`;
    const wrap = document.createElement('div');
    wrap.className = 'select';
    sel.before(wrap);
    wrap.append(sel);
    sel.classList.add('select-native');
    sel.tabIndex = -1;
    sel.setAttribute('aria-hidden', 'true');

    const label = document.querySelector(`label[for="${sel.id}"]`);
    if (label) { label.id = label.id || `${id}-label`; label.htmlFor = `${id}-btn`; }
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.id = `${id}-btn`;
    btn.className = 'select-trigger';
    btn.setAttribute('role', 'combobox');
    btn.setAttribute('aria-haspopup', 'listbox');
    btn.setAttribute('aria-expanded', 'false');
    btn.setAttribute('aria-controls', `${id}-list`);
    if (label) btn.setAttribute('aria-labelledby', label.id);
    btn.innerHTML = '<span class="select-value"></span>' + chevron;

    const list = document.createElement('ul');
    list.id = `${id}-list`;
    list.className = 'select-list';
    list.setAttribute('role', 'listbox');
    if (label) list.setAttribute('aria-labelledby', label.id);
    const opts = Array.from(sel.options).map((o, i) => {
      const li = document.createElement('li');
      li.id = `${id}-opt-${i}`;
      li.className = 'select-option' + (o.value === '' ? ' is-placeholder' : '');
      li.setAttribute('role', 'option');
      li.innerHTML = '<span></span>' + tick;
      li.firstChild.textContent = o.textContent;
      li.addEventListener('mousedown', (e) => e.preventDefault()); // keep focus on the button
      li.addEventListener('click', () => choose(i));
      li.addEventListener('mousemove', () => setActive(i));
      list.append(li);
      return li;
    });
    wrap.append(btn, list);

    let active = sel.selectedIndex;
    const render = () => {
      const o = sel.options[sel.selectedIndex];
      $('.select-value', btn).textContent = o ? o.textContent : '';
      btn.classList.toggle('is-placeholder', !o || o.value === '');
      opts.forEach((li, i) => li.setAttribute('aria-selected', String(i === sel.selectedIndex)));
    };
    const setActive = (i) => {
      active = Math.max(0, Math.min(opts.length - 1, i));
      opts.forEach((li, j) => li.classList.toggle('is-active', j === active));
      btn.setAttribute('aria-activedescendant', opts[active].id);
      const li = opts[active];
      if (li.offsetTop < list.scrollTop) list.scrollTop = li.offsetTop - 6;
      else if (li.offsetTop + li.offsetHeight > list.scrollTop + list.clientHeight) list.scrollTop = li.offsetTop + li.offsetHeight - list.clientHeight + 6;
    };
    const isOpen = () => wrap.classList.contains('is-open');
    const openList = () => {
      const below = innerHeight - btn.getBoundingClientRect().bottom;
      wrap.classList.toggle('is-up', below < 300 && btn.getBoundingClientRect().top > below);
      wrap.classList.add('is-open');
      btn.setAttribute('aria-expanded', 'true');
      setActive(sel.selectedIndex);
    };
    const closeList = () => {
      wrap.classList.remove('is-open');
      btn.setAttribute('aria-expanded', 'false');
      btn.removeAttribute('aria-activedescendant');
    };
    const choose = (i) => {
      if (sel.selectedIndex !== i) {
        sel.selectedIndex = i;
        sel.dispatchEvent(new Event('change', { bubbles: true }));
      }
      render();
      closeList();
      btn.focus();
    };
    let typed = '';
    let typedTimer;
    btn.addEventListener('click', () => (isOpen() ? closeList() : openList()));
    btn.addEventListener('keydown', (e) => {
      const k = e.key;
      if (['ArrowDown', 'ArrowUp', 'Home', 'End', 'Enter', ' ', 'Escape'].includes(k)) e.preventDefault();
      if (!isOpen()) {
        if (['ArrowDown', 'ArrowUp', 'Enter', ' '].includes(k)) openList();
      } else if (k === 'ArrowDown') setActive(active + 1);
      else if (k === 'ArrowUp') setActive(active - 1);
      else if (k === 'Home') setActive(0);
      else if (k === 'End') setActive(opts.length - 1);
      else if (k === 'Enter' || k === ' ') choose(active);
      else if (k === 'Escape' || k === 'Tab') closeList();
      if (k.length === 1 && k !== ' ') { // type-ahead
        clearTimeout(typedTimer);
        typed += k.toLowerCase();
        typedTimer = setTimeout(() => { typed = ''; }, 600);
        const hit = Array.from(sel.options).findIndex((o) => o.value !== '' && o.textContent.trim().toLowerCase().startsWith(typed));
        if (hit >= 0) { if (!isOpen()) openList(); setActive(hit); }
      }
    });
    btn.addEventListener('blur', () => setTimeout(() => { if (!wrap.contains(document.activeElement)) closeList(); }, 120));
    document.addEventListener('click', (e) => { if (!wrap.contains(e.target)) closeList(); });
    sel.addEventListener('change', render);
    render();
  });

  /* ---------- Contact form (AJAX with server-side validation; works without JS too) ---------- */
  const form = $('[data-contact-form]');
  if (form) {
    const status = $('[data-form-status]', form);
    const submit = $('[data-submit]', form);
    const label = $('[data-submit-label]', form);
    const msg = form.dataset;
    const fields = ['name', 'email', 'message', 'consent', 'phone'];
    const setError = (name, text) => {
      const p = $(`[data-error-for="${name}"]`, form);
      const input = form.elements[name];
      if (p) { p.textContent = text || ''; p.id = p.id || `err-${name}`; }
      p?.closest('.field')?.classList.toggle('has-error', !!text);
      if (input?.setAttribute) {
        if (text) input.setAttribute('aria-invalid', 'true'); else input.removeAttribute('aria-invalid');
        if (p) input.setAttribute('aria-describedby', p.id);
      }
    };
    const validate = () => {
      const errors = {};
      if (!form.elements.name.value.trim()) errors.name = msg.msgRequired;
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.elements.email.value.trim())) errors.email = msg.msgEmail;
      if (form.elements.message.value.trim().length < 10) errors.message = msg.msgMessage;
      if (!form.elements.consent.checked) errors.consent = msg.msgConsent;
      return errors;
    };
    fields.forEach((n) => ['input', 'change'].forEach((ev) => form.elements[n]?.addEventListener(ev, () => setError(n, ''))));

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      fields.forEach((n) => setError(n, ''));
      const errors = validate();
      if (Object.keys(errors).length) {
        Object.entries(errors).forEach(([n, t]) => setError(n, t));
        form.elements[Object.keys(errors)[0]]?.focus();
        return;
      }
      const original = label.textContent;
      submit.disabled = true;
      label.textContent = msg.msgSending;
      status.hidden = true;
      try {
        const res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'fetch' }, credentials: 'same-origin' });
        const data = await res.json().catch(() => ({}));
        status.hidden = false;
        if (res.ok && data.ok) {
          status.className = 'form-alert is-success';
          status.textContent = data.message;
          form.classList.add('is-sent');
        } else {
          Object.entries(data.errors || {}).forEach(([n, t]) => setError(n, t));
          status.className = 'form-alert is-error';
          status.textContent = data.message || msg.msgNetwork;
        }
      } catch {
        status.hidden = false;
        status.className = 'form-alert is-error';
        status.textContent = msg.msgNetwork;
      } finally {
        submit.disabled = false;
        label.textContent = original;
      }
    });
  }

  /* ---------- Back to top (with reading progress ring) ---------- */
  const toTop = $('[data-to-top]');
  if (toTop) {
    const update = () => {
      const max = document.documentElement.scrollHeight - innerHeight;
      toTop.classList.toggle('is-visible', scrollY > 600);
      toTop.style.setProperty('--progress', max > 0 ? Math.min(100, (scrollY / max) * 100).toFixed(1) : 0);
    };
    addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
    update();
    toTop.addEventListener('click', () => {
      if (window.__lenis) window.__lenis.scrollTo(0); else scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
      $('.site-header .brand')?.focus({ preventScroll: true });
    });
  }

  /* ---------- Legal pages: highlight the section being read in the table of contents ---------- */
  const tocLinks = $$('[data-toc-link]');
  if (tocLinks.length && hasIO) {
    const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
      if (!entry.isIntersecting) return;
      tocLinks.forEach((a) => a.classList.toggle('is-active', a.hash === '#' + entry.target.id));
    }), { rootMargin: '-30% 0px -60% 0px' });
    $$('[data-toc-section]').forEach((s) => io.observe(s));
  }

  /* ---------- Cookie banner: only anonymous statistics are optional ---------- */
  const CONSENT_KEY = 'how-consent';
  const readConsent = () => { try { return JSON.parse(localStorage.getItem(CONSENT_KEY) || 'null'); } catch { return null; } };
  const cookie = $('[data-cookie]');
  if (cookie) {
    const prefs = $('[data-cookie-prefs]', cookie);
    const statsBox = $('[data-cookie-stats]', cookie);
    const btn = (n) => $(`[data-cookie-${n}]`, cookie);
    const open = (withPrefs = false) => {
      const c = readConsent();
      statsBox.checked = c ? c.stats !== false : true;
      prefs.hidden = !withPrefs;
      btn('customize').hidden = withPrefs;
      btn('refuse').hidden = withPrefs;
      btn('accept').hidden = withPrefs;
      btn('save').hidden = !withPrefs;
      cookie.hidden = false;
      cookie.classList.remove('is-closing');
      cookie.classList.add('is-open');
    };
    const close = (stats) => {
      try { localStorage.setItem(CONSENT_KEY, JSON.stringify({ stats, date: new Date().toISOString().slice(0, 10) })); } catch { /* ignore */ }
      cookie.classList.add('is-closing');
      setTimeout(() => { cookie.hidden = true; cookie.classList.remove('is-open', 'is-closing'); }, reduceMotion ? 0 : 450);
    };
    btn('accept').addEventListener('click', () => close(true));
    btn('refuse').addEventListener('click', () => close(false));
    btn('save').addEventListener('click', () => close(statsBox.checked));
    btn('customize').addEventListener('click', () => open(true));
    $$('[data-cookie-open]').forEach((b) => b.addEventListener('click', () => open(true)));
    if (!readConsent()) {
      const show = () => setTimeout(() => open(false), 1400);
      if (html.classList.contains('is-loaded')) show(); else document.addEventListener('site:loaded', show, { once: true });
    }
  }

  /* ---------- Cookieless analytics beacon (skipped if the visitor refused statistics) ---------- */
  const beacon = (payload) => {
    const body = JSON.stringify({ p: location.pathname, lang: html.lang, w: innerWidth, ...payload });
    if (navigator.sendBeacon) navigator.sendBeacon('/api/hit', new Blob([body], { type: 'application/json' }));
    else fetch('/api/hit', { method: 'POST', body, keepalive: true, headers: { 'Content-Type': 'application/json' } });
  };
  if (navigator.doNotTrack !== '1' && !navigator.webdriver && readConsent()?.stats !== false) {
    beacon({ t: 'pageview', r: document.referrer });
    if (hasIO) {
      const seen = new Set();
      const io = new IntersectionObserver((entries) => entries.forEach((entry) => {
        const id = entry.target.dataset.track;
        if (entry.isIntersecting && !seen.has(id)) { seen.add(id); beacon({ t: 'section', l: id }); io.unobserve(entry.target); }
      }), { threshold: 0.35 });
      $$('[data-track]').forEach((s) => io.observe(s));
    }
  }

  /* ---------- Smooth scrolling (Lenis, desktop only) + anchor links ---------- */
  if (!reduceMotion && window.Lenis && finePointer) {
    const lenis = new Lenis({ duration: 1.1, smoothWheel: true });
    window.__lenis = lenis;
    const raf = (t) => { lenis.raf(t); requestAnimationFrame(raf); };
    requestAnimationFrame(raf);
    document.addEventListener('click', (e) => {
      const a = e.target.closest('a[href*="#"]');
      if (!a || a.origin !== location.origin || a.pathname !== location.pathname) return;
      const target = a.hash.length > 1 && document.getElementById(a.hash.slice(1));
      if (!target) return;
      e.preventDefault();
      lenis.scrollTo(a.hash === '#top' ? 0 : target, { offset: -((header?.offsetHeight || 70) + 8) });
      history.pushState(null, '', a.hash);
    });
  }

  /* ---------- Service worker (offline page + installable PWA) ---------- */
  if ('serviceWorker' in navigator && (location.protocol === 'https:' || location.hostname === 'localhost')) {
    addEventListener('load', () => navigator.serviceWorker.register('/sw.js').catch(() => {}));
  }
})();
