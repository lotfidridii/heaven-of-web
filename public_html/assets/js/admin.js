/* Heaven of Web — admin panel interactions. */
(() => {
  'use strict';

  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => Array.from(r.querySelectorAll(s));
  const html = document.documentElement;
  const csrf = $('meta[name="csrf-token"]')?.content || '';
  const store = {
    get(k) { try { return localStorage.getItem(k); } catch { return null; } },
    set(k, v) { try { localStorage.setItem(k, v); } catch { /* ignore */ } },
  };
  const post = (url, body, json = true) => fetch(url, {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'X-CSRF-Token': csrf, 'X-Requested-With': 'fetch', Accept: 'application/json', ...(json ? { 'Content-Type': 'application/json' } : {}) },
    body: json ? JSON.stringify(body) : body,
  });

  /* Toasts */
  const liveToast = $('[data-live-toast]');
  const toast = (text, type = 'success') => {
    if (!liveToast) return;
    liveToast.className = `toast is-${type}`;
    liveToast.textContent = text;
    liveToast.hidden = false;
    clearTimeout(toast.t);
    toast.t = setTimeout(() => { liveToast.hidden = true; }, 4000);
  };
  const flashToast = $('[data-toast]');
  if (flashToast) setTimeout(() => flashToast.remove(), 4500);

  /* Theme */
  $$('[data-theme-toggle]').forEach((b) => b.addEventListener('click', () => {
    const next = html.dataset.theme === 'dark' ? 'light' : 'dark';
    html.dataset.theme = next;
    store.set('how-theme', next);
    document.dispatchEvent(new Event('themechange'));
  }));

  /* Sidebar drawer (tablet/mobile) */
  const sidebar = $('[data-sidebar]');
  const scrim = $('.sidebar-scrim');
  const setSidebar = (open) => {
    sidebar?.classList.toggle('is-open', open);
    scrim?.classList.toggle('is-open', open);
    $$('[data-sidebar-open]').forEach((b) => b.setAttribute('aria-expanded', String(open)));
    if (open) sidebar?.querySelector('a')?.focus();
  };
  $$('[data-sidebar-open]').forEach((b) => b.addEventListener('click', () => setSidebar(true)));
  scrim?.addEventListener('click', () => setSidebar(false));
  addEventListener('keydown', (e) => { if (e.key === 'Escape') setSidebar(false); });

  /* MFA login step: switch between the authenticator code and a recovery code */
  const mfaToggle = $('[data-mfa-toggle]');
  if (mfaToggle) {
    const input = $('#code');
    let recovery = false;
    mfaToggle.addEventListener('click', () => {
      recovery = !recovery;
      $$('[data-mfa-hint]').forEach((p) => { p.hidden = p.dataset.mfaHint !== (recovery ? 'recovery' : 'app'); });
      $('[data-mfa-label]').textContent = recovery ? 'Code de secours' : 'Code de vérification';
      input.value = '';
      input.inputMode = recovery ? 'text' : 'numeric';
      input.autocomplete = recovery ? 'off' : 'one-time-code';
      input.placeholder = recovery ? 'ABCD-EFGH' : '000 000';
      if (recovery) input.removeAttribute('pattern'); else input.setAttribute('pattern', '[0-9 ]{6,7}');
      mfaToggle.textContent = recovery ? 'Utiliser l’application d’authentification' : 'Utiliser un code de secours';
      input.focus();
    });
  }
  // Auto-submit once 6 digits are typed (authenticator codes only)
  $$('input.code-input').forEach((input) => {
    input.addEventListener('input', () => {
      const digits = input.value.replace(/\D/g, '');
      if (input.inputMode === 'numeric' && digits.length === 6 && input.form?.checkValidity()) input.form.requestSubmit();
    });
  });

  /* Recovery codes: copy / download as a text file */
  const codesList = $('[data-recovery-codes]');
  if (codesList) {
    const text = () => 'Heaven of Web — codes de secours (usage unique)\n' + $$('code', codesList).map((c) => c.textContent).join('\n') + '\n';
    $('[data-copy-codes]')?.addEventListener('click', async () => {
      try { await navigator.clipboard.writeText(text()); toast('Codes copiés.'); } catch { toast('Copie impossible : sélectionnez les codes à la main.', 'error'); }
    });
    $('[data-download-codes]')?.addEventListener('click', () => {
      const a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([text()], { type: 'text/plain' }));
      a.download = 'heavenofweb-codes-de-secours.txt';
      a.click();
      setTimeout(() => URL.revokeObjectURL(a.href), 1000);
    });
  }

  /* Header bell: dropdown with the latest new messages */
  const notif = $('[data-notif]');
  const notifPanel = $('[data-notif-panel]');
  const notifToggle = $('[data-notif-toggle]');
  const setNotif = (open) => {
    if (!notifPanel) return;
    notifPanel.hidden = !open;
    notifToggle.setAttribute('aria-expanded', String(open));
  };
  notifToggle?.addEventListener('click', (e) => { e.stopPropagation(); setNotif(notifPanel.hidden); });
  document.addEventListener('click', (e) => { if (notif && !notif.contains(e.target)) setNotif(false); });
  addEventListener('keydown', (e) => { if (e.key === 'Escape' && notifPanel && !notifPanel.hidden) { setNotif(false); notifToggle.focus(); } });
  const renderNotif = (items, count) => {
    const list = $('[data-notif-list]');
    if (!list) return;
    list.replaceChildren(...items.map((m) => {
      const li = document.createElement('li');
      const a = document.createElement('a');
      a.href = `/admin/messages/${m.id}`;
      const av = document.createElement('span'); av.className = 'avatar avatar-sm'; av.textContent = (m.name || '?').charAt(0).toUpperCase();
      const txt = document.createElement('span'); txt.className = 'notif-text';
      const st = document.createElement('strong'); st.textContent = m.name;
      const sm = document.createElement('small'); sm.textContent = m.excerpt;
      txt.append(st, sm);
      const t = document.createElement('time'); t.textContent = m.ago;
      a.append(av, txt, t); li.append(a);
      return li;
    }));
    $('[data-notif-empty]').hidden = items.length > 0;
    $('[data-notif-count]').textContent = count ? `${count} non lu${count > 1 ? 's' : ''}` : 'Tout est lu';
    notifToggle?.classList.toggle('has-new', count > 0);
  };
  notifToggle?.classList.toggle('has-new', Number($('[data-unread]')?.textContent || 0) > 0);

  /* Messages: select several rows and apply one action (bar like a mail client) */
  const bulk = $('[data-bulk]');
  if (bulk) {
    const items = $$('[data-bulk-item]', bulk);
    const all = $('[data-bulk-all]', bulk);
    const bar = $('[data-bulk-bar]', bulk);
    const count = $('[data-bulk-count]', bulk);
    const sync = () => {
      const n = items.filter((i) => i.checked).length;
      all.checked = n > 0 && n === items.length;
      all.indeterminate = n > 0 && n < items.length;
      bar.classList.toggle('is-active', n > 0);
      count.textContent = n ? `${n} sélectionné${n > 1 ? 's' : ''} sur ${items.length}` : 'Tout sélectionner';
      items.forEach((i) => i.closest('[data-bulk-row]')?.classList.toggle('is-selected', i.checked));
    };
    all.addEventListener('change', () => { items.forEach((i) => { i.checked = all.checked; }); sync(); });
    items.forEach((i) => i.addEventListener('change', sync));
    $('[data-bulk-clear]', bulk)?.addEventListener('click', () => { items.forEach((i) => { i.checked = false; }); sync(); });
    // Shift+click selects a range
    let last = null;
    items.forEach((i, idx) => i.addEventListener('click', (e) => {
      if (e.shiftKey && last !== null) {
        const [a, b] = [Math.min(last, idx), Math.max(last, idx)];
        for (let k = a; k <= b; k++) items[k].checked = i.checked;
        sync();
      }
      last = idx;
    }));
    bulk.addEventListener('submit', (e) => {
      const msg = e.submitter?.dataset.bulkConfirm;
      if (msg && !confirm(msg)) e.preventDefault();
    });
    sync();
  }

  /* Confirm destructive actions */
  document.addEventListener('submit', (e) => {
    const form = e.target.closest('form[data-confirm]') || (e.submitter?.form?.matches('[data-confirm]') ? e.submitter.form : null);
    if (form && !confirm(form.dataset.confirm)) e.preventDefault();
  }, true);

  /* Language tabs in editors */
  $$('.lang-tabs').forEach((tabs) => {
    const card = tabs.closest('.card');
    const select = (lang) => {
      $$('[data-lang-tab]', tabs).forEach((t) => t.setAttribute('aria-selected', String(t.dataset.langTab === lang)));
      $$('.lang-panel', card).forEach((p) => { p.hidden = p.id !== `panel-${lang}`; });
    };
    $$('[data-lang-tab]', tabs).forEach((t) => t.addEventListener('click', () => select(t.dataset.langTab)));
    tabs.addEventListener('keydown', (e) => {
      if (!['ArrowLeft', 'ArrowRight'].includes(e.key)) return;
      const all = $$('[data-lang-tab]', tabs);
      const i = all.indexOf(document.activeElement);
      const next = all[(i + (e.key === 'ArrowRight' ? 1 : all.length - 1)) % all.length];
      next.focus(); next.click();
    });
    // A required field in a hidden language panel: show that panel so the browser can point at it.
    card.addEventListener('invalid', (e) => {
      const panel = e.target.closest('.lang-panel');
      if (panel?.hidden) select(panel.id.replace('panel-', ''));
    }, true);
  });

  /* Image previews */
  $$('input[type="file"][data-preview]').forEach((input) => {
    input.addEventListener('change', () => {
      const target = document.getElementById(input.dataset.preview);
      if (!target) return;
      target.replaceChildren();
      Array.from(input.files || []).forEach((file) => {
        if (!file.type.startsWith('image/')) return;
        const img = document.createElement('img');
        img.alt = '';
        img.src = URL.createObjectURL(file);
        target.append(img);
      });
    });
    const zone = input.closest('.dropzone');
    if (zone) {
      ['dragenter', 'dragover'].forEach((ev) => zone.addEventListener(ev, () => zone.classList.add('is-over')));
      ['dragleave', 'drop'].forEach((ev) => zone.addEventListener(ev, () => zone.classList.remove('is-over')));
    }
  });

  /* Colour palette editor: colour picker and hex field stay in sync, live preview strip */
  $$('[data-palette-editor]').forEach((ed) => {
    const rows = $('[data-palette-rows]', ed);
    const tpl = $('[data-palette-template]', ed);
    const preview = $('[data-palette-preview]', ed);
    const addBtn = $('[data-palette-add]', ed);
    const norm = (v) => { const h = v.trim().replace(/^#?/, '#').toUpperCase(); return /^#[0-9A-F]{6}$/.test(h) ? h : null; };
    const render = () => {
      const hexes = $$('[data-hex]', rows).map((i) => norm(i.value)).filter(Boolean);
      preview.replaceChildren(...hexes.map((h) => { const s = document.createElement('span'); s.style.background = h; return s; }));
      preview.hidden = !hexes.length;
      addBtn.hidden = $$('.palette-row', rows).length >= 8;
    };
    ed.addEventListener('input', (e) => {
      const row = e.target.closest('.palette-row');
      if (!row) return;
      if (e.target.matches('[data-swatch]')) $('[data-hex]', row).value = e.target.value.toUpperCase();
      if (e.target.matches('[data-hex]')) { const h = norm(e.target.value); if (h) $('[data-swatch]', row).value = h; }
      render();
    });
    ed.addEventListener('click', (e) => {
      if (e.target.closest('[data-palette-remove]')) { e.target.closest('.palette-row').remove(); render(); }
    });
    addBtn.addEventListener('click', () => {
      rows.append(tpl.content.cloneNode(true));
      $$('[data-swatch]', rows).at(-1).click();
      render();
    });
    render();
  });

  /* Publish switches without reloading */
  $$('form[data-toggle-form]').forEach((form) => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const btn = $('.switch', form);
      try {
        const res = await post(form.action, new FormData(form), false);
        const data = await res.json();
        btn.setAttribute('aria-checked', String(!!data.published));
        form.closest('li')?.classList.toggle('is-draft', !data.published);
        toast(data.published ? 'Publié sur le site.' : 'Masqué du site.');
      } catch { form.submit(); }
    });
  });

  /* Drag-to-reorder lists (mouse and touch, via pointer events on the handle) */
  $$('[data-sortable]').forEach((list) => {
    let dragging = null;
    const save = async () => {
      const ids = $$('li[data-id]', list).map((li) => Number(li.dataset.id));
      const res = await post(list.dataset.sortable, { ids }).catch(() => null);
      toast(res?.ok ? 'Ordre enregistré.' : 'L’ordre n’a pas pu être enregistré.', res?.ok ? 'success' : 'error');
    };
    list.addEventListener('pointerdown', (e) => {
      const handle = e.target.closest('.handle');
      if (!handle) return;
      dragging = handle.closest('li');
      dragging.classList.add('is-dragging');
      handle.setPointerCapture(e.pointerId);
      e.preventDefault();
    });
    list.addEventListener('pointermove', (e) => {
      if (!dragging) return;
      const over = document.elementFromPoint(e.clientX, e.clientY)?.closest('li[data-id]');
      if (!over || over === dragging || over.parentElement !== list) return;
      const r = over.getBoundingClientRect();
      list.insertBefore(dragging, e.clientY < r.top + r.height / 2 ? over : over.nextSibling);
    });
    const end = () => {
      if (!dragging) return;
      dragging.classList.remove('is-dragging');
      dragging = null;
      save();
    };
    list.addEventListener('pointerup', end);
    list.addEventListener('pointercancel', end);
    // Native draggable="true" is only kept for semantics; pointer events do the work.
    list.addEventListener('dragstart', (e) => e.preventDefault());
  });

  /* Dashboard chart */
  const chartEl = $('#traffic-chart');
  const drawChart = () => {
    if (!chartEl || !window.Chart) return;
    const series = JSON.parse($('#traffic-data').textContent);
    const css = getComputedStyle(html);
    const v = (n) => css.getPropertyValue(n).trim();
    const fmt = new Intl.DateTimeFormat('fr-FR', { day: 'numeric', month: 'short' });
    Chart.getChart(chartEl)?.destroy();
    new Chart(chartEl, {
      type: 'line',
      data: {
        labels: series.map((s) => fmt.format(new Date(s.day + 'T12:00:00'))),
        datasets: [
          { label: 'Visiteurs', data: series.map((s) => s.visitors), borderColor: v('--chart-a'), backgroundColor: v('--chart-a'), borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointHoverBorderWidth: 2, pointHoverBorderColor: v('--surface'), tension: 0.3 },
          { label: 'Pages vues', data: series.map((s) => s.views), borderColor: v('--chart-b'), backgroundColor: v('--chart-b'), borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointHoverBorderWidth: 2, pointHoverBorderColor: v('--surface'), tension: 0.3 },
        ],
      },
      options: {
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'top', align: 'end', labels: { color: v('--muted'), usePointStyle: true, pointStyle: 'line', boxWidth: 18, font: { family: 'Poppins', size: 12 } } },
          tooltip: { backgroundColor: v('--text'), titleColor: v('--bg'), bodyColor: v('--bg'), padding: 10, cornerRadius: 10, displayColors: true, boxPadding: 4 },
        },
        scales: {
          x: { grid: { display: false }, border: { color: v('--grid') }, ticks: { color: v('--muted'), maxTicksLimit: 8, font: { family: 'Poppins', size: 11 } } },
          y: { beginAtZero: true, grid: { color: v('--grid') }, border: { display: false }, ticks: { color: v('--muted'), precision: 0, maxTicksLimit: 5, font: { family: 'Poppins', size: 11 } } },
        },
      },
    });
  };
  if (chartEl) {
    addEventListener('load', drawChart);
    document.addEventListener('themechange', drawChart);
  }

  /* Unread counter: refreshed by the service worker on push, and polled every minute */
  const badges = $$('[data-unread]');
  let lastCount = badges.length ? Number(badges[0].textContent) || 0 : 0;
  const setUnread = (n) => {
    badges.forEach((b) => { b.textContent = n; b.hidden = !n; });
    if ('setAppBadge' in navigator) (n ? navigator.setAppBadge(n) : navigator.clearAppBadge()).catch(() => {});
  };
  const refreshUnread = async () => {
    try {
      const res = await fetch('/admin/api/unread', { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
      if (!res.ok) return;
      const data = await res.json();
      if (data.count > lastCount && data.latest) {
        toast(`Nouveau message de ${data.latest.name}`);
        notifToggle?.classList.remove('ring');
        void notifToggle?.offsetWidth; // restart the bell animation
        notifToggle?.classList.add('ring');
      }
      lastCount = data.count;
      setUnread(data.count);
      renderNotif(data.items || [], data.count);
    } catch { /* offline */ }
  };
  if (badges.length) {
    setUnread(lastCount);
    setInterval(() => { if (!document.hidden) refreshUnread(); }, 60000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshUnread(); });
    navigator.serviceWorker?.addEventListener('message', (e) => { if (e.data?.type === 'new-message') refreshUnread(); });
  }

  /* Service worker (needed for push and to install the admin as an app) */
  const swReady = 'serviceWorker' in navigator
    ? navigator.serviceWorker.register('/sw.js').then(() => navigator.serviceWorker.ready).catch(() => null)
    : Promise.resolve(null);

  /* Install prompt (Chrome/Edge/Android) */
  let installEvent = null;
  const installBtn = $('[data-install]');
  addEventListener('beforeinstallprompt', (e) => { e.preventDefault(); installEvent = e; if (installBtn) installBtn.hidden = false; });
  installBtn?.addEventListener('click', async () => { if (!installEvent) return; installEvent.prompt(); await installEvent.userChoice; installEvent = null; installBtn.hidden = true; });

  /* Push notifications page */
  const pushBox = $('[data-push]');
  if (pushBox) {
    const status = $('[data-push-status]', pushBox);
    const btnOn = $('[data-push-enable]', pushBox);
    const btnTest = $('[data-push-test]', pushBox);
    const btnOff = $('[data-push-disable]', pushBox);
    const show = (text, { on = false, test = false, off = false } = {}) => {
      if (status) status.textContent = text;
      if (btnOn) btnOn.hidden = !on;
      if (btnTest) btnTest.hidden = !test;
      if (btnOff) btnOff.hidden = !off;
    };
    const keyToBytes = (b64) => {
      const pad = '='.repeat((4 - (b64.length % 4)) % 4);
      const raw = atob((b64 + pad).replace(/-/g, '+').replace(/_/g, '/'));
      return Uint8Array.from(raw, (c) => c.charCodeAt(0));
    };
    const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
    const standalone = matchMedia('(display-mode: standalone)').matches || navigator.standalone;

    const refresh = async () => {
      if (!('serviceWorker' in navigator) || !('PushManager' in window) || !('Notification' in window)) {
        return show(isIos && !standalone
          ? 'Sur iPhone, installez d’abord l’administration sur l’écran d’accueil (voir ci-dessous), puis ouvrez-la depuis l’icône pour activer les notifications.'
          : 'Ce navigateur ne gère pas les notifications push.');
      }
      if (Notification.permission === 'denied') {
        return show('Les notifications sont bloquées pour ce site. Autorisez-les dans les réglages du navigateur, puis rechargez la page.');
      }
      const reg = await swReady;
      const sub = reg && await reg.pushManager.getSubscription();
      if (sub) {
        await post('/admin/push/subscribe', sub.toJSON()).catch(() => {}); // keep the server copy in sync
        show('Notifications activées sur cet appareil.', { test: true, off: true });
      } else {
        show('Notifications désactivées sur cet appareil.', { on: true });
      }
    };

    btnOn?.addEventListener('click', async () => {
      btnOn.disabled = true;
      try {
        const permission = await Notification.requestPermission();
        if (permission !== 'granted') { await refresh(); return; }
        const reg = await swReady;
        const sub = await reg.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyToBytes(pushBox.dataset.vapid) });
        const res = await post('/admin/push/subscribe', sub.toJSON());
        if (!res.ok) throw new Error('save');
        toast('Notifications activées.');
        await refresh();
        setTimeout(() => location.reload(), 900);
      } catch {
        toast('Impossible d’activer les notifications sur cet appareil.', 'error');
      } finally { btnOn.disabled = false; }
    });

    btnTest?.addEventListener('click', async () => {
      btnTest.disabled = true;
      const res = await post('/admin/push/test', {}).then((r) => r.json()).catch(() => null);
      toast(res?.ok ? 'Notification de test envoyée.' : 'La notification n’a pas pu être envoyée.', res?.ok ? 'success' : 'error');
      btnTest.disabled = false;
    });

    btnOff?.addEventListener('click', async () => {
      const reg = await swReady;
      const sub = reg && await reg.pushManager.getSubscription();
      if (sub) {
        await post('/admin/push/unsubscribe', { endpoint: sub.endpoint }).catch(() => {});
        await sub.unsubscribe();
      }
      toast('Notifications désactivées sur cet appareil.');
      setTimeout(() => location.reload(), 700);
    });

    refresh();
  }
})();
