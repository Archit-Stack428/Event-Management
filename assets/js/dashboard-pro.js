/* ============================================================
   EVENTHUB PRO — Phase 4: Dashboard Scripts
   Animated counters, tabs, form wizard, chart init, gallery,
   sidebar toggle, notifications. Respects prefers-reduced-motion.
   ============================================================ */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  // ---- Sidebar toggle (mobile) ----
  var sideToggle = document.getElementById('ehSideToggle');
  var sidebar = document.getElementById('ehSidebar');
  if (sideToggle && sidebar) {
    sideToggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
  }

  // ---- Sidebar nav (scroll to section) ----
  document.querySelectorAll('.eh-side-link[data-section]').forEach(function (link) {
    link.addEventListener('click', function () {
      document.querySelectorAll('.eh-side-link').forEach(function (l) { l.classList.remove('active'); });
      link.classList.add('active');
      var target = document.getElementById(link.getAttribute('data-section'));
      if (target) {
        target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
      }
      if (sidebar) { sidebar.classList.remove('open'); }
    });
  });

  // ---- Animated counters ----
  var counters = document.querySelectorAll('.eh-kpi .num[data-count]');
  function animateCount(el) {
    var target = parseFloat(el.getAttribute('data-count')) || 0;
    var suffix = el.getAttribute('data-suffix') || '';
    var prefix = el.getAttribute('data-prefix') || '';
    if (reduceMotion) { el.textContent = prefix + target.toLocaleString() + suffix; return; }
    var dur = 1400, t0 = null;
    function step(ts) {
      if (!t0) t0 = ts;
      var p = Math.min((ts - t0) / dur, 1);
      var val = Math.floor(target * (1 - Math.pow(1 - p, 3)));
      el.textContent = prefix + val.toLocaleString() + suffix;
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = prefix + target.toLocaleString() + suffix;
    }
    requestAnimationFrame(step);
  }
  if (counters.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { animateCount(en.target); io.unobserve(en.target); }
      });
    }, { threshold: 0.4 });
    counters.forEach(function (c) { io.observe(c); });
  } else {
    counters.forEach(animateCount);
  }

  // ---- Event management tabs (filter cards) ----
  var evTabs = document.querySelectorAll('.eh-mini-tab[data-filter]');
  var evCards = document.querySelectorAll('.eh-admin-card[data-status]');
  evTabs.forEach(function (tab) {
    tab.addEventListener('click', function () {
      evTabs.forEach(function (t) { t.classList.remove('active'); });
      tab.classList.add('active');
      var f = tab.getAttribute('data-filter');
      evCards.forEach(function (c) {
        var show = (f === 'all') || (c.getAttribute('data-status') === f);
        c.style.display = show ? '' : 'none';
      });
    });
  });

  // ---- Form wizard (createevent + editevent) ----
  var wizard = document.getElementById('ehWizard');
  if (wizard) {
    var panes = wizard.querySelectorAll('.eh-step-pane');
    var pills = wizard.querySelectorAll('.eh-step-pill');
    var prevBtn = document.getElementById('ehWizPrev');
    var nextBtn = document.getElementById('ehWizNext');
    var submitBtn = document.getElementById('ehWizSubmit');
    var idx = 0;

    function showStep(i) {
      idx = Math.max(0, Math.min(i, panes.length - 1));
      panes.forEach(function (p, n) { p.classList.toggle('active', n === idx); });
      pills.forEach(function (p, n) {
        p.classList.toggle('active', n === idx);
        p.classList.toggle('done', n < idx);
      });
      if (prevBtn) { prevBtn.style.visibility = idx === 0 ? 'hidden' : 'visible'; }
      if (nextBtn) { nextBtn.style.display = (idx === panes.length - 1) ? 'none' : 'inline-flex'; }
      if (submitBtn) { submitBtn.style.display = (idx === panes.length - 1) ? 'inline-flex' : 'none'; }
      if (idx > 0) { window.scrollTo({ top: 120, behavior: reduceMotion ? 'auto' : 'smooth' }); }
    }
    window.ehShowStep = showStep;

    function validatePane(i) {
      var pane = panes[i];
      var inputs = pane.querySelectorAll('input[required], select[required], textarea[required]');
      var ok = true;
      inputs.forEach(function (inp) {
        var field = inp.closest('.eh-field');
        var val = inp.value.trim();
        var valid = val !== '' && (inp.type !== 'email' || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val));
        if (field) {
          field.classList.toggle('invalid', !valid);
          if (inp.tagName === 'SELECT' && val === 'Choose') { field.classList.add('invalid'); valid = false; }
        }
        if (!valid) ok = false;
      });
      return ok;
    }

    if (nextBtn) nextBtn.addEventListener('click', function () {
      if (validatePane(idx)) { showStep(idx + 1); }
    });
    if (prevBtn) prevBtn.addEventListener('click', function () { showStep(idx - 1); });
    if (submitBtn) {
      submitBtn.addEventListener('click', function (e) {
        if (!validatePane(idx)) {
          e.preventDefault();
        }
      });
    }
    showStep(0);
  }

  // ---- Dynamic rule/prize rows ----
  document.querySelectorAll('[data-dynamic-add]').forEach(function (addBtn) {
    addBtn.addEventListener('click', function () {
      var wrap = document.getElementById(addBtn.getAttribute('data-dynamic-add'));
      if (!wrap) return;
      var row = document.createElement('div');
      row.className = 'eh-dynamic-row';
      var input = document.createElement('input');
      input.type = 'text';
      input.name = addBtn.getAttribute('data-name') || 'items[]';
      input.placeholder = addBtn.getAttribute('data-ph') || 'Item';
      input.className = 'eh-input';
      var del = document.createElement('button');
      del.type = 'button';
      del.innerHTML = '<i class="fas fa-times"></i>';
      del.className = 'eh-action-btn danger';
      del.setAttribute('aria-label', 'Remove field');
      del.addEventListener('click', function () { wrap.removeChild(row); });
      row.appendChild(input);
      row.appendChild(del);
      wrap.appendChild(row);
    });
  });
  document.querySelectorAll('.eh-dynamic-row').forEach(function (row) {
    var del = row.querySelector('button');
    if (del) del.addEventListener('click', function () { row.parentNode.removeChild(row); });
  });

  // ---- Image preview (single thumbnail) ----
  var thumbInput = document.getElementById('ehThumbInput');
  var thumbPrev = document.getElementById('ehThumbPreview');
  if (thumbInput && thumbPrev) {
    thumbInput.addEventListener('change', function () {
      var file = thumbInput.files[0];
      if (file) {
        thumbPrev.src = URL.createObjectURL(file);
        thumbPrev.style.display = 'block';
      }
    });
  }

  // ---- Dropzone (gallery upload) ----
  var dropzone = document.getElementById('ehDropzone');
  var fileInput = document.getElementById('ehGalFiles');
  var prevWrap = document.getElementById('ehDropPreview');
  if (dropzone && fileInput) {
    dropzone.addEventListener('click', function () { fileInput.click(); });
    ['dragover', 'dragenter'].forEach(function (e) {
      dropzone.addEventListener(e, function (ev) { ev.preventDefault(); dropzone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(function (e) {
      dropzone.addEventListener(e, function (ev) { ev.preventDefault(); dropzone.classList.remove('dragover'); });
    });
    dropzone.addEventListener('drop', function (ev) { fileInput.files = ev.dataTransfer.files; previewFiles(); });
    fileInput.addEventListener('change', previewFiles);
    function previewFiles() {
      if (!prevWrap) return;
      prevWrap.innerHTML = '';
      Array.prototype.forEach.call(fileInput.files, function (f) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        img.alt = f.name;
        prevWrap.appendChild(img);
      });
    }
  }

  // ---- Notifications dropdown ----
  var notifBtn = document.getElementById('ehNotifBtn');
  var notifPanel = document.getElementById('ehNotifPanel');
  if (notifBtn && notifPanel) {
    notifBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      var isOpen = notifPanel.classList.contains('open');
      document.querySelectorAll('.eh-notif-panel.open').forEach(function (p) { p.classList.remove('open'); });
      if (!isOpen) { notifPanel.classList.add('open'); }
    });
    document.addEventListener('click', function () { notifPanel.classList.remove('open'); });
  }

  // ---- Chart.js (dashboard analytics) ----
  window.EHCharts = function (configs) {
    if (!window.Chart) return;
    (configs || []).forEach(function (c) {
      var el = document.getElementById(c.id);
      if (!el) return;
      var data = c.data || { labels: [], datasets: [] };
      new Chart(el, { type: c.type || 'bar', data: data, options: Object.assign({ responsive: true, maintainAspectRatio: false }, c.options || {}) });
    });
  };
})();
