/* EVENTHUB PRO - Phase 1 + Phase 3 */
(function () {
  'use strict';
  function debounce(func, wait) {
    var timeout;
    return function () {
      var context = this, args = arguments;
      clearTimeout(timeout);
      timeout = setTimeout(function () {
        func.apply(context, args);
      }, wait);
    };
  }
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isMobile = window.innerWidth < 720;
  if (window.Lenis && !reduceMotion) {
    var lenis = new Lenis({ duration: 1.1, smoothWheel: true });
    window.lenis = lenis;
    function raf(time) { lenis.raf(time); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
  }
  var nav = document.querySelector('.eh-nav');
  if (nav) {
    window.addEventListener('scroll', function () {
      nav.style.background = window.scrollY > 40 ? 'rgba(9,9,11,0.75)' : 'rgba(9,9,11,0.55)';
    });
  }
  var toggle = document.querySelector('.eh-nav-toggle');
  var links = document.querySelector('.eh-nav-links');
  if (toggle && links) { toggle.addEventListener('click', function () { links.classList.toggle('open'); }); }
  var light = document.querySelector('.eh-mouse-light');
  if (light) {
    document.addEventListener('mousemove', function (e) { light.style.left = e.clientX + 'px'; light.style.top = e.clientY + 'px'; });
  }
  if (window.tsParticles && !reduceMotion && !isMobile) {
    tsParticles.load('eh-particles', {
      fullScreen: { enable: false },
      particles: {
        number: { value: 60, density: { enable: true } },
        color: { value: ['#7C3AED', '#2563EB', '#06B6D4'] },
        shape: { type: 'circle' },
        opacity: { value: 0.5 },
        size: { value: { min: 1, max: 3 } },
        move: { enable: true, speed: 0.6, random: true }
      },
      interactivity: { events: { onhover: { enable: true, mode: 'repulse' } }, modes: { repulse: { distance: 80 } } }
    });
  }
  if (window.gsap && !reduceMotion) {
    gsap.from('.eh-hero .eh-reveal', { opacity: 0, y: 40, duration: 1, stagger: 0.12, ease: 'power3.out', delay: 0.2 });
    gsap.from('.eh-dash-card, .eh-dash-float', { opacity: 0, y: 60, duration: 1, stagger: 0.15, ease: 'power3.out', delay: 0.5 });
    gsap.to('.eh-dash-float.f1', { y: -18, duration: 3, yoyo: true, repeat: -1, ease: 'sine.inOut' });
    gsap.to('.eh-dash-float.f2', { y: 18, duration: 3.4, yoyo: true, repeat: -1, ease: 'sine.inOut' });
  }
  if (window.AOS) { AOS.init({ once: true, duration: 800, offset: 80 }); }
  var revealEls = document.querySelectorAll('[data-reveal]');
  if (revealEls.length && 'IntersectionObserver' in window) {
    var revealIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { en.target.classList.add('eh-in'); revealIO.unobserve(en.target); } });
    }, { threshold: 0.12 });
    revealEls.forEach(function (el) { revealIO.observe(el); });
  } else { revealEls.forEach(function (el) { el.classList.add('eh-in'); }); }
  function animateCount(el) {
    var target = parseInt(el.getAttribute('data-target'), 10);
    var dur = 1600, start = 0, t0 = null;
    function step(ts) {
      if (!t0) t0 = ts;
      var p = Math.min((ts - t0) / dur, 1);
      el.textContent = Math.floor(start + (target - start) * (1 - Math.pow(1 - p, 3))).toLocaleString();
      if (p < 1) requestAnimationFrame(step); else el.textContent = target.toLocaleString() + (el.getAttribute('data-suffix') || '');
    }
    requestAnimationFrame(step);
  }
  var counters = document.querySelectorAll('.eh-stat .num');
  if (counters.length && 'IntersectionObserver' in window) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { animateCount(en.target); io.unobserve(en.target); } });
    }, { threshold: 0.4 });
    counters.forEach(function (c) { io.observe(c); });
  }
  var trusted = document.querySelector('.eh-trusted-track');
  if (trusted && !reduceMotion) {
    var width = trusted.scrollWidth / 2;
    if (window.gsap) { gsap.to(trusted, { x: -width, duration: 22, ease: 'none', repeat: -1 }); }
  }
  if (window.Swiper && document.querySelector('.eh-testi-swiper')) {
    new Swiper('.eh-testi-swiper', {
      slidesPerView: 1, spaceBetween: 24, loop: true,
      autoplay: reduceMotion ? false : { delay: 4000 },
      pagination: { el: '.eh-testi-pagination', clickable: true },
      breakpoints: { 768: { slidesPerView: 2 }, 1100: { slidesPerView: 3 } }
    });
  }
  document.querySelectorAll('.eh-faq-q').forEach(function (q) {
    q.addEventListener('click', function () {
      var item = q.parentElement;
      var a = item.querySelector('.eh-faq-a');
      var open = item.classList.contains('open');
      document.querySelectorAll('.eh-faq-item.open').forEach(function (o) {
        o.classList.remove('open'); o.querySelector('.eh-faq-a').style.maxHeight = '0px';
      });
      if (!open) { item.classList.add('open'); a.style.maxHeight = a.scrollHeight + 'px'; }
    });
  });
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var target = document.querySelector(this.getAttribute('href'));
      if (target && window.lenis) { e.preventDefault(); lenis.scrollTo(target); }
    });
  });
  /* MARKETPLACE */
  var market = (function () {
    var grid = document.getElementById('ehEventsGrid');
    if (!grid) return;
    var cfg = window.EH_MARKET || {};
    var searchInput = document.getElementById(cfg.searchInputEl || 'ehSearchInput');
    var form = document.getElementById('ehMarketSearch');
    var countEl = document.getElementById(cfg.countEl || 'ehResultCount');
    var emptyEl = document.getElementById(cfg.emptyEl || 'ehEmptyState');
    var loadMore = document.getElementById(cfg.loadMoreEl || 'ehLoadMore');
    var offset = 0, hasMore = false, requestCounter = 0;
    function getParams() {
      return {
        q: (searchInput ? searchInput.value : ''),
        category: document.getElementById('ehFilterCategory') ? document.getElementById('ehFilterCategory').value : '',
        type: document.getElementById('ehFilterType') ? document.getElementById('ehFilterType').value : '',
        price: document.getElementById('ehFilterPrice') ? document.getElementById('ehFilterPrice').value : '',
        status: document.getElementById('ehFilterStatus') ? document.getElementById('ehFilterStatus').value : '',
        when: document.getElementById('ehFilterWhen') ? document.getElementById('ehFilterWhen').value : '',
        venue: document.getElementById('ehFilterVenue') ? document.getElementById('ehFilterVenue').value : '',
        organizer: document.getElementById('ehFilterOrganizer') ? document.getElementById('ehFilterOrganizer').value : '',
        sort: document.getElementById('ehSort') ? document.getElementById('ehSort').value : 'latest'
      };
    }
    function showSkeleton() {
      var cards = '';
      for (var i = 0; i < 3; i++) {
        cards += '<div class="eh-card eh-skeleton"><div class="eh-skeleton-img"></div><div class="eh-card-body"><div class="eh-skeleton-line w70"></div><div class="eh-skeleton-line w50"></div><div class="eh-skeleton-line w90"></div><div class="eh-skeleton-line w40"></div></div>';
      }
      grid.innerHTML = cards;
    }
    function load(reset) {
      var myReq = ++requestCounter;
      if (reset) { offset = 0; showSkeleton(); }
      var params = getParams(); params.offset = offset;
      var qs = Object.keys(params).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
      if (loadMore) { loadMore.style.display = 'none'; }
      fetch('event_search.php?' + qs)
        .then(function (r) { return r.json(); })
        .then(function (res) {
          if (myReq !== requestCounter) return;
          if (reset) { grid.innerHTML = ''; }
          grid.insertAdjacentHTML('beforeend', res.html || '');
          hasMore = res.hasMore;
          if (countEl) { countEl.textContent = (res.count || 0) + ' event' + ((res.count || 0) === 1 ? '' : 's') + ' found'; }
          if (emptyEl) { emptyEl.style.display = (res.html && res.html.trim()) ? 'none' : 'block'; }
          if (loadMore) { loadMore.style.display = hasMore && res.html ? 'inline-flex' : 'none'; loadMore.innerHTML = '<i class="fas fa-spinner"></i> Load More'; }
          Array.prototype.forEach.call(grid.querySelectorAll('[data-reveal]:not(.eh-in)'), function (el) { el.classList.add('eh-in'); });
        })
        .catch(function () { if (countEl) { countEl.textContent = 'Something went wrong. Please try again.'; } });
    }
    if (form) { form.addEventListener('submit', function (e) { e.preventDefault(); load(true); }); }
    if (searchInput) { searchInput.addEventListener('input', debounce(function () { load(true); }, 350)); }
    var filterIds = ['ehFilterCategory','ehFilterType','ehFilterPrice','ehFilterStatus','ehFilterWhen','ehFilterVenue','ehFilterOrganizer','ehSort'];
    filterIds.forEach(function (id) { var el = document.getElementById(id); if (el) { el.addEventListener('change', function () { load(true); }); } });
    var clearBtn = document.getElementById('ehClearFilters');
    if (clearBtn) {
      clearBtn.addEventListener('click', function () {
        filterIds.forEach(function (id) { var el = document.getElementById(id); if (el) { el.selectedIndex = 0; } });
        if (searchInput) { searchInput.value = ''; }
        load(true);
      });
    }
    if (loadMore) {
      loadMore.addEventListener('click', function () {
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        offset += 6; // must match server $limit in event_search.php
        load(false);
      });
    }
    load(true);
  })();
/* GALLERY */
  var gallery = (function () {
    var grid = document.getElementById('ehGalGrid');
    if (!grid) return;
    var cfg = window.EH_GALLERY || {};
    var countEl = document.getElementById(cfg.countEl || 'ehGalCount');
    var emptyEl = document.getElementById(cfg.emptyEl || 'ehGalEmpty');
    var searchInput = document.getElementById(cfg.searchInputEl || 'ehGalSearchInput');
    var form = document.getElementById('ehGalSearch');
    var lightbox = document.getElementById(cfg.lightboxEl || 'ehLightbox');
    var lightboxImg, lightboxCaption, currentIndex = 0, items = [];
    function load() {
      var params = {
        category: document.querySelector('#ehGalCatPills .eh-pill.active') ? document.querySelector('#ehGalCatPills .eh-pill.active').getAttribute('data-cat') : '',
        year: document.querySelector('#ehGalYearPills .eh-pill.active') ? document.querySelector('#ehGalYearPills .eh-pill.active').getAttribute('data-year') : '',
        event_name: document.getElementById('ehGalFilterEvent') ? document.getElementById('ehGalFilterEvent').value : '',
        organizer_name: document.getElementById('ehGalFilterOrganizer') ? document.getElementById('ehGalFilterOrganizer').value : '',
        q: searchInput ? searchInput.value : ''
      };
      var qs = Object.keys(params).map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); }).join('&');
      fetch('gallery_media.php?' + qs)
        .then(function (r) { return r.json(); })
        .then(function (res) {
          grid.innerHTML = res.html || '';
          if (countEl) { countEl.textContent = (res.count || 0) + ' photo' + ((res.count || 0) === 1 ? '' : 's'); }
          if (emptyEl) { emptyEl.style.display = (res.count || 0) === 0 ? 'block' : 'none'; }
          items = Array.prototype.slice.call(grid.querySelectorAll('.eh-gal-item'));
          Array.prototype.forEach.call(grid.querySelectorAll('[data-reveal]:not(.eh-in)'), function (el) { el.classList.add('eh-in'); });
        })
        .catch(function () { if (countEl) { countEl.textContent = 'Something went wrong.'; } });
    }
    ['ehGalCatPills', 'ehGalYearPills'].forEach(function (pillId) {
      var wrap = document.getElementById(pillId);
      if (!wrap) return;
      wrap.addEventListener('click', function (e) {
        var pill = e.target.closest('.eh-pill');
        if (!pill) return;
        Array.prototype.forEach.call(wrap.querySelectorAll('.eh-pill'), function (p) { p.classList.remove('active'); });
        pill.classList.add('active');
        load();
      });
    });
    ['ehGalFilterEvent', 'ehGalFilterOrganizer'].forEach(function (id) {
      var el = document.getElementById(id);
      if (el) { el.addEventListener('change', load); }
    });
    if (form) { form.addEventListener('submit', function (e) { e.preventDefault(); load(); }); }
    if (searchInput) { searchInput.addEventListener('input', debounce(load, 350)); }
    if (lightbox) {
      lightboxImg = lightbox.querySelector('#ehLightboxImg');
      lightboxCaption = lightbox.querySelector('#ehLightboxCaption');
      var closeBtn = document.getElementById('ehLightboxClose');
      var prevBtn = document.getElementById('ehLightboxPrev');
      var nextBtn = document.getElementById('ehLightboxNext');
      grid.addEventListener('click', function (e) {
        var fig = e.target.closest('.eh-gal-item');
        if (!fig) return;
        currentIndex = items.indexOf(fig);
        openLightbox(currentIndex);
      });
      function openLightbox(i) {
        if (i < 0 || i >= items.length) return;
        currentIndex = i;
        var img = items[i].querySelector('img');
        var cap = items[i].querySelector('.eh-gal-name');
        if (lightboxImg) { lightboxImg.src = img.src; lightboxImg.alt = cap ? cap.textContent : ''; }
        if (lightboxCaption) { lightboxCaption.textContent = cap ? cap.textContent : ''; }
        lightbox.classList.add('open');
        document.body.style.overflow = 'hidden';
        if (window.lenis) window.lenis.stop();
      }
      function closeLightbox() { lightbox.classList.remove('open'); document.body.style.overflow = ''; if (window.lenis) window.lenis.start(); }
      function nav(d) { openLightbox(currentIndex + d); }
      if (closeBtn) { closeBtn.addEventListener('click', closeLightbox); }
      if (prevBtn) { prevBtn.addEventListener('click', function () { nav(-1); }); }
      if (nextBtn) { nextBtn.addEventListener('click', function () { nav(1); }); }
      lightbox.addEventListener('click', function (e) { if (e.target === lightbox) { closeLightbox(); } });
      document.addEventListener('keydown', function (e) {
        if (!lightbox.classList.contains('open')) return;
        if (e.key === 'Escape') { closeLightbox(); }
        if (e.key === 'ArrowLeft') { nav(-1); }
        if (e.key === 'ArrowRight') { nav(1); }
      });
    }
    load();
  })();

  /* EVENT DETAILS */
  var details = (function () {
    var cfg = window.EH_EVENT || {};
    var tabs = document.querySelectorAll('.eh-tab');
    if (tabs.length) {
      tabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
          var name = this.getAttribute('data-tab');
          tabs.forEach(function (t) { t.classList.remove('active'); });
          this.classList.add('active');
          document.querySelectorAll('.eh-tab-panel').forEach(function (p) { p.classList.remove('active'); });
          var panel = document.getElementById('tab-' + name);
          if (panel) { panel.classList.add('active'); }
        });
      });
    }
    var cdWrap = document.getElementById('ehCountdown');
    if (cdWrap && cfg.startDate) {
      var state = cdWrap.getAttribute('data-state') || 'upcoming';
      if (state !== 'upcoming') return;
      var target = new Date(cfg.startDate.replace(/-/g, '/')).getTime();
      var cdDays = document.getElementById('ehCdDays');
      var cdHours = document.getElementById('ehCdHours');
      var cdMins = document.getElementById('ehCdMins');
      var cdSecs = document.getElementById('ehCdSecs');
      function tick() {
        var now = Date.now();
        var diff = target - now;
        if (diff <= 0) {
          if (cdDays) cdDays.textContent = '00';
          if (cdHours) cdHours.textContent = '00';
          if (cdMins) cdMins.textContent = '00';
          if (cdSecs) cdSecs.textContent = '00';
          return;
        }
        if (cdDays) cdDays.textContent = String(Math.floor(diff / 86400000)).padStart(2, '0');
        if (cdHours) cdHours.textContent = String(Math.floor((diff % 86400000) / 3600000)).padStart(2, '0');
        if (cdMins) cdMins.textContent = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
        if (cdSecs) cdSecs.textContent = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
      }
      tick();
      setInterval(tick, 1000);
    }
    var copyBtn = document.getElementById('ehCopyLink');
    if (copyBtn) {
      copyBtn.addEventListener('click', function () {
        var url = window.location.href;
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(url).then(function () {
            copyBtn.innerHTML = '<i class="fas fa-check"></i>';
            setTimeout(function () { copyBtn.innerHTML = '<i class="fas fa-link"></i>'; }, 1500);
          });
        }
      });
    }
    var regBtn = document.querySelector('.eh-register-btn');
    if (regBtn && typeof cfg.minTeam !== 'undefined') {
      var x = cfg.minTeam;
      var y = cfg.maxTeam;
      var z = y - x;
      regBtn.addEventListener('click', function () {
        var regForm = document.getElementById('registerform');
        var team3 = document.getElementById('teamdetails3');
        var team = document.getElementById('teamdetails');
        var team1 = document.getElementById('teamdetails1');
        var pay = document.getElementById('payment');
        if (cfg.price != 0 && pay) {
          pay.innerHTML =
            '<label>Card Number</label><input type="text" name="card_num" size="20" autocomplete="off" class="card-number">' +
            '<label>CVC</label><input type="text" name="cvc" size="4" autocomplete="off" class="card-cvc">' +
            '<label>Expiration (MM/YYYY)</label><input type="text" name="exp_month" size="2" class="card-expiry-month"> <span style="color:var(--eh-muted);">/</span> <input type="text" name="exp_year" size="4" class="card-expiry-year">';
        }
        if (x == 0) {
          if (regForm) {
            regForm.innerHTML =
              '<input type="text" name="name" placeholder="Full Name">' +
              '<input type="text" name="rollno" placeholder="Roll No.">' +
              '<input type="text" name="college" placeholder="College Name">' +
              '<select name="dept_name"><option value="None">Choose the Department</option><option value="Hotel Management">Hotel Management</option><option value="B.tech">B.Tech</option><option value="BCA">BCA</option><option value="BBA">BBA</option><option value="B.com">B.Com</option><option value="B.sc">B.Sc</option><option value="MCA">MCA</option><option value="MBA">MBA</option><option value="M.com">M.Com</option><option value="M.sc">M.Sc</option><option value="Other">Other</option></select>' +
              '<input type="email" name="email" placeholder="Email">' +
              '<input type="text" name="mobile" placeholder="Mobile No.">';
          }
        } else {
          if (regForm) {
            regForm.innerHTML =
              '<input type="text" name="teamname" placeholder="Team Name">' +
              '<input type="text" name="college" placeholder="College Name">' +
              '<input type="text" name="member1" placeholder="Member 1 - Name">' +
              '<input type="text" name="roll1" placeholder="Member 1 - Roll No.">' +
              '<input type="email" name="email1" placeholder="Member 1 - Email">' +
              '<input type="text" name="mobile1" placeholder="Member 1 - Mobile">';
            if (z >= 1) {
              regForm.innerHTML +=
                '<input type="text" name="member2" placeholder="Member 2 - Name">' +
                '<input type="text" name="roll2" placeholder="Member 2 - Roll No.">' +
                '<input type="email" name="email2" placeholder="Member 2 - Email">' +
                '<input type="text" name="mobile2" placeholder="Member 2 - Mobile">';
            }
            if (z >= 2) {
              regForm.innerHTML +=
                '<input type="text" name="member3" placeholder="Member 3 - Name">' +
                '<input type="text" name="roll3" placeholder="Member 3 - Roll No.">' +
                '<input type="email" name="email3" placeholder="Member 3 - Email">' +
                '<input type="text" name="mobile3" placeholder="Member 3 - Mobile">';
            }
            if (z >= 3) {
              regForm.innerHTML +=
                '<input type="text" name="member4" placeholder="Member 4 - Name">' +
                '<input type="text" name="roll4" placeholder="Member 4 - Roll No.">' +
                '<input type="email" name="email4" placeholder="Member 4 - Email">' +
                '<input type="text" name="mobile4" placeholder="Member 4 - Mobile">';
            }
            if (z >= 4) {
              regForm.innerHTML +=
                '<input type="text" name="member5" placeholder="Member 5 - Name">' +
                '<input type="text" name="roll5" placeholder="Member 5 - Roll No.">' +
                '<input type="email" name="email5" placeholder="Member 5 - Email">' +
                '<input type="text" name="mobile5" placeholder="Member 5 - Mobile">';
            }
          }
        }
      });
    }
  })();
})();
