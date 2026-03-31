<?php
$page_title  = 'PropSpace — Find Your Perfect Rental Home';
$page_desc   = 'Browse professionally managed, available rental units. Studios, 1, 2 & 3-bedroom apartments — find your perfect space today.';
$active_nav  = 'home';
require_once __DIR__ . '/includes/header.php';
?>

<!-- ── Hero ──────────────────────────────────────────────────────────────────── -->
<section class="hero" id="hero">
  <div class="hero-bg" id="hero-bg"></div>
  <div class="hero-gradient"></div>

  <div class="hero-content container">
    <div class="hero-eyebrow">
      <span class="hero-eyebrow-dot"></span>
      Verified Listings · Professionally Managed
    </div>
    <h1 class="hero-title">
      Find your perfect<br><em>rental home.</em>
    </h1>
    <p class="hero-sub">
      Browse curated, move-in ready units across premier locations. No hidden fees, no hassle.
    </p>

    <!-- Search bar -->
    <form class="hero-search" id="hero-search" onsubmit="handleHeroSearch(event)">
      <div class="search-field">
        <svg class="search-field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/></svg>
        <div style="flex:1;min-width:0">
          <span class="search-field-label">Location</span>
          <select id="hero-city" name="city" aria-label="Select city">
            <option value="">Any city</option>
          </select>
        </div>
      </div>

      <div class="search-field">
        <svg class="search-field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <div style="flex:1;min-width:0">
          <span class="search-field-label">Type</span>
          <select id="hero-type" name="type_id" aria-label="Select unit type">
            <option value="">Any type</option>
          </select>
        </div>
      </div>

      <div class="search-field">
        <svg class="search-field-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v13M21 7v13M3 14h18M5 7h14a2 2 0 0 1 2 2v5H3V9a2 2 0 0 1 2-2Z"/></svg>
        <div style="flex:1;min-width:0">
          <span class="search-field-label">Bedrooms</span>
          <select id="hero-rooms" name="rooms" aria-label="Select bedrooms">
            <option value="">Any</option>
            <option value="0">Studio</option>
            <option value="1">1 Bedroom</option>
            <option value="2">2 Bedrooms</option>
            <option value="3">3+ Bedrooms</option>
          </select>
        </div>
      </div>

      <button type="submit" class="search-submit">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        Search
      </button>
    </form>
  </div>

  <!-- Stats bar -->
  <div class="container">
    <div class="hero-stats">
      <div class="hero-stat">
        <span class="hero-stat-num" data-stat="units">—</span>
        <span class="hero-stat-label">Available Units</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" data-stat="properties">—</span>
        <span class="hero-stat-label">Properties</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num" data-stat="cities">—</span>
        <span class="hero-stat-label">Cities</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat-num">100%</span>
        <span class="hero-stat-label">Managed</span>
      </div>
    </div>
  </div>
</section>

<!-- ── Available Now ──────────────────────────────────────────────────────────── -->
<section class="section section-alt" id="available-now">
  <div class="container">
    <div class="section-header">
      <div class="section-header-left">
        <span class="section-label">Available Now</span>
        <h2>Listings you'll love</h2>
      </div>
      <a href="listings.php" class="link-all">
        View all listings
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>

    <!-- Type filter pills -->
    <div class="filter-pills" id="type-pills">
      <button class="pill active" data-type-pill="0">All</button>
      <!-- More pills injected by JS from meta -->
    </div>

    <!-- Units grid -->
    <div class="units-grid" id="featured-grid">
      <!-- Skeleton placeholders while loading -->
      <div class="unit-card unit-card-skeleton"><div class="unit-card-img"></div><div class="unit-card-body" style="gap:10px"><div class="skeleton" style="height:12px;width:50%"></div><div class="skeleton" style="height:18px;width:80%"></div><div class="skeleton" style="height:12px;width:60%"></div></div></div>
      <div class="unit-card unit-card-skeleton"><div class="unit-card-img"></div><div class="unit-card-body" style="gap:10px"><div class="skeleton" style="height:12px;width:50%"></div><div class="skeleton" style="height:18px;width:80%"></div><div class="skeleton" style="height:12px;width:60%"></div></div></div>
      <div class="unit-card unit-card-skeleton"><div class="unit-card-img"></div><div class="unit-card-body" style="gap:10px"><div class="skeleton" style="height:12px;width:50%"></div><div class="skeleton" style="height:18px;width:80%"></div><div class="skeleton" style="height:12px;width:60%"></div></div></div>
    </div>

    <div style="text-align:center;margin-top:40px">
      <a href="listings.php" class="btn btn-outline-navy btn-lg">
        Browse All Available Units
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
      </a>
    </div>
  </div>
</section>

<!-- ── How it works ───────────────────────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="section-header" style="margin-bottom:56px">
      <div class="section-header-left">
        <span class="section-label">Simple Process</span>
        <h2>Find &amp; move in, stress-free.</h2>
      </div>
    </div>

    <div class="steps-grid">
      <div class="step">
        <div class="step-number">01</div>
        <h3>Search &amp; Filter</h3>
        <p>Browse available units by location, size, and budget. Use our filters to zero in on exactly what you need.</p>
      </div>
      <div class="step">
        <div class="step-number">02</div>
        <h3>View Details</h3>
        <p>Each listing includes photos, full specifications, amenities, and property information — everything you need to decide.</p>
      </div>
      <div class="step">
        <div class="step-number">03</div>
        <h3>Enquire</h3>
        <p>Reach out directly to the property manager. Fast response, no middlemen, transparent process.</p>
      </div>
      <div class="step">
        <div class="step-number">04</div>
        <h3>Move In</h3>
        <p>Sign your lease and move in. Properties are professionally managed for a seamless experience.</p>
      </div>
    </div>
  </div>
</section>

<!-- ── Why choose us ──────────────────────────────────────────────────────────── -->
<section class="section section-alt">
  <div class="container">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:64px;align-items:center">
      <div>
        <span class="section-label">Why PropSpace</span>
        <h2 style="margin-top:12px;margin-bottom:20px">Managed properties, real peace of mind.</h2>
        <p style="color:var(--text-muted);line-height:1.8;margin-bottom:32px">
          Every listing on PropSpace is a professionally managed property. That means maintained facilities, responsive management, and transparent lease processes.
        </p>
        <div style="display:flex;flex-direction:column;gap:16px">
          <?php
          $features = [
            ['Verified Listings',       'Only managed, real units. No ghost listings.'],
            ['Transparent Pricing',     'Rent amount stated upfront, no surprise fees.'],
            ['Fast Response',           'Property managers respond quickly to enquiries.'],
            ['Professional Management', 'Facilities maintained to high standards.'],
          ];
          foreach ($features as [$title, $desc]):
          ?>
          <div style="display:flex;gap:14px;align-items:flex-start">
            <div style="width:32px;height:32px;border-radius:var(--r-sm);background:var(--gold-pale);border:1px solid var(--gold-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#c8922a" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div style="font-weight:600;font-size:.9375rem;margin-bottom:2px"><?= htmlspecialchars($title) ?></div>
              <div style="color:var(--text-muted);font-size:.875rem"><?= htmlspecialchars($desc) ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: visual card stack -->
      <div style="position:relative;height:420px" class="hide-mobile">
        <div style="position:absolute;top:0;left:5%;right:0;height:280px;background:var(--navy);border-radius:var(--r-xl);overflow:hidden">
          <div style="background-image:url('https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=800&q=80&auto=format&fit=crop');background-size:cover;background-position:center;height:100%;opacity:.4"></div>
        </div>
        <div style="position:absolute;bottom:0;left:0;right:10%;background:#fff;border:1px solid var(--border);border-radius:var(--r-xl);padding:24px;box-shadow:var(--shadow-lg)">
          <div style="display:flex;align-items:center;gap:12px">
            <div style="width:48px;height:48px;background:var(--gold-pale);border-radius:50%;display:flex;align-items:center;justify-content:center">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#c8922a" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            </div>
            <div>
              <div style="font-weight:700;font-size:1.4rem;font-family:'Playfair Display',serif" data-stat-card="units">—</div>
              <div style="font-size:.78rem;color:var(--text-muted);font-weight:500;text-transform:uppercase;letter-spacing:.06em">Units Available</div>
            </div>
          </div>
          <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);display:flex;gap:20px">
            <div>
              <div style="font-weight:700;font-size:1.1rem;font-family:'Playfair Display',serif" data-stat-card="properties">—</div>
              <div style="font-size:.72rem;color:var(--text-muted)">Properties</div>
            </div>
            <div>
              <div style="font-weight:700;font-size:1.1rem;font-family:'Playfair Display',serif" data-stat-card="cities">—</div>
              <div style="font-size:.72rem;color:var(--text-muted)">Cities</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ────────────────────────────────────────────────────────────────────── -->
<section class="section">
  <div class="container">
    <div class="cta-banner">
      <div class="cta-text">
        <span class="section-label">Get Started Today</span>
        <h2>Ready to find your next home?</h2>
        <p>Hundreds of available units across multiple cities, all professionally managed.</p>
      </div>
      <div class="cta-actions">
        <a href="listings.php" class="btn btn-primary btn-lg">
          Browse All Listings
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
        <a href="<?= APP_URL ?>" target="_blank" rel="noopener" class="btn btn-outline-white btn-lg">
          Owner Dashboard
        </a>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
(function(){
  // Populate hero search dropdowns from meta
  fetch('api/meta.php')
    .then(r => r.json())
    .then(data => {
      // Cities
      const cityEl = document.getElementById('hero-city');
      if (cityEl && data.cities) {
        data.cities.forEach(c => {
          const o = document.createElement('option');
          o.value = o.textContent = c;
          cityEl.appendChild(o);
        });
      }

      // Types
      const typeEl = document.getElementById('hero-type');
      if (typeEl && data.unit_types) {
        data.unit_types.forEach(t => {
          const o = document.createElement('option');
          o.value = t.id;
          o.textContent = t.label;
          typeEl.appendChild(o);
        });
      }

      // Stats
      if (data.counts) {
        document.querySelectorAll('[data-stat="units"]').forEach(el => el.textContent = data.counts.units.toLocaleString());
        document.querySelectorAll('[data-stat="properties"]').forEach(el => el.textContent = data.counts.properties.toLocaleString());
        document.querySelectorAll('[data-stat="cities"]').forEach(el => el.textContent = data.counts.cities);
        document.querySelectorAll('[data-stat-card="units"]').forEach(el => el.textContent = data.counts.units.toLocaleString());
        document.querySelectorAll('[data-stat-card="properties"]').forEach(el => el.textContent = data.counts.properties.toLocaleString());
        document.querySelectorAll('[data-stat-card="cities"]').forEach(el => el.textContent = data.counts.cities);
      }

      // Type pills
      const pillsEl = document.getElementById('type-pills');
      if (pillsEl && data.unit_types) {
        data.unit_types.slice(0, 5).forEach(t => {
          const btn = document.createElement('button');
          btn.className = 'pill';
          btn.dataset.typePill = t.id;
          btn.textContent = t.label;
          pillsEl.appendChild(btn);
        });
        // Wire pill clicks
        pillsEl.querySelectorAll('[data-type-pill]').forEach(p => {
          p.addEventListener('click', () => {
            pillsEl.querySelectorAll('[data-type-pill]').forEach(x => x.classList.remove('active'));
            p.classList.add('active');
            loadFeatured(parseInt(p.dataset.typePill) || 0);
          });
        });
      }
    });

  // Hero search form
  window.handleHeroSearch = function(e) {
    e.preventDefault();
    const params = new URLSearchParams();
    const city   = document.getElementById('hero-city').value;
    const typeId = document.getElementById('hero-type').value;
    const rooms  = document.getElementById('hero-rooms').value;
    if (city)   params.set('city', city);
    if (typeId) params.set('type_id', typeId);
    if (rooms)  params.set('rooms', rooms);
    window.location.href = 'listings.php' + (params.toString() ? '?' + params : '');
  };

  // Load featured grid
  function loadFeatured(typeId) {
    const grid = document.getElementById('featured-grid');
    if (!grid) return;
    grid.innerHTML = buildSkeletons(6);
    const params = new URLSearchParams({ limit: 6, sort: 'featured' });
    if (typeId) params.set('type_id', typeId);
    fetch('api/listings.php?' + params)
      .then(r => r.json())
      .then(data => {
        const units = data.data || [];
        if (!units.length) {
          grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><div class="empty-state-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg></div><h3>No units yet</h3><p>No available units match this filter.</p></div>';
        } else {
          grid.innerHTML = units.map(buildUnitCard).join('');
        }
      })
      .catch(() => {
        grid.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><p style="color:var(--text-muted)">Could not load listings.</p></div>';
      });
  }

  loadFeatured(0);
})();
</script>

<style>
.hide-mobile { display: block; }
@media (max-width: 900px) {
  .section > .container > div[style*="grid"] { grid-template-columns: 1fr !important; }
  .hide-mobile { display: none; }
}
</style>
