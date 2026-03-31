<?php
$page_title = 'Available Units — PropSpace';
$page_desc  = 'Browse all available rental units. Filter by city, type, price, and amenities.';
$active_nav = 'listings';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="container">
    <span class="section-label" style="color:rgba(200,146,42,.9)">Browse</span>
    <h1>Available Units</h1>
    <p>Find professionally managed units that match your needs.</p>
  </div>
</div>

<section class="section" style="padding-top:48px">
  <div class="container">

    <!-- Top bar: search + sort -->
    <div style="display:flex;align-items:center;gap:16px;flex-wrap:wrap;margin-bottom:32px">
      <div style="flex:1;min-width:220px;max-width:400px;position:relative">
        <svg style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted)" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
        <input
          type="search"
          id="search-input"
          placeholder="Search by unit, property or city…"
          style="width:100%;padding:10px 14px 10px 38px;border:1px solid var(--border);border-radius:var(--r);background:var(--surface);font-size:.875rem;color:var(--text);transition:border-color var(--t)"
          onfocus="this.style.borderColor='var(--navy)'"
          onblur="this.style.borderColor='var(--border)'"
        >
      </div>
      <div style="display:flex;align-items:center;gap:12px;margin-left:auto;flex-shrink:0">
        <span id="listings-count" class="listings-count" style="font-size:.825rem;color:var(--text-muted)"></span>
        <label for="sort-select" style="font-size:.8rem;font-weight:600;color:var(--text-muted);white-space:nowrap">Sort by</label>
        <select id="sort-select" class="sort-select">
          <option value="featured">Featured</option>
          <option value="price_asc">Price: Low to High</option>
          <option value="price_desc">Price: High to Low</option>
          <option value="newest">Newest</option>
        </select>
      </div>
    </div>

    <div class="listings-layout">

      <!-- ── Sidebar Filters ────────────────────────────────────────────────── -->
      <aside class="listings-sidebar" id="filter-form">

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
          <h3 style="font-size:1rem;font-weight:700;font-family:'Inter',sans-serif">Filters</h3>
          <button id="clear-filters" style="font-size:.78rem;font-weight:600;color:var(--gold);background:none;border:none;cursor:pointer;padding:0">Clear all</button>
        </div>

        <!-- City -->
        <div class="sidebar-section">
          <p class="sidebar-heading">City</p>
          <select id="filter-city" class="sort-select" style="width:100%">
            <option value="">All Cities</option>
          </select>
        </div>

        <!-- Unit Type -->
        <div class="sidebar-section">
          <p class="sidebar-heading">Unit Type</p>
          <select id="filter-type" class="sort-select" style="width:100%">
            <option value="">All Types</option>
          </select>
        </div>

        <!-- Bedrooms -->
        <div class="sidebar-section">
          <p class="sidebar-heading">Bedrooms</p>
          <select id="filter-rooms" class="sort-select" style="width:100%">
            <option value="">Any</option>
            <option value="0">Studio</option>
            <option value="1">1 Bedroom</option>
            <option value="2">2 Bedrooms</option>
            <option value="3">3 Bedrooms</option>
            <option value="4">4+ Bedrooms</option>
          </select>
        </div>

        <!-- Price Range -->
        <div class="sidebar-section">
          <p class="sidebar-heading" style="display:flex;justify-content:space-between">
            <span>Max Price</span>
            <span style="font-weight:600;color:var(--text)" id="max-price-label">Any</span>
          </p>
          <div class="range-wrapper">
            <input type="range" id="filter-max-price" class="range-input" min="0" max="200000" step="5000" value="200000">
          </div>
          <div class="range-labels">
            <span>KES 0</span>
            <span id="max-price-label-end">KES 200k+</span>
          </div>
        </div>

        <!-- Min Price -->
        <div class="sidebar-section">
          <p class="sidebar-heading" style="display:flex;justify-content:space-between">
            <span>Min Price</span>
            <span style="font-weight:600;color:var(--text)" id="min-price-label">Any</span>
          </p>
          <div class="range-wrapper">
            <input type="range" id="filter-min-price" class="range-input" min="0" max="200000" step="5000" value="0">
          </div>
        </div>

        <!-- Amenities -->
        <div class="sidebar-section">
          <p class="sidebar-heading">Amenities</p>
          <div class="checkbox-group" id="filter-amenities">
            <p style="font-size:.8rem;color:var(--text-muted)">Loading…</p>
          </div>
        </div>
      </aside>

      <!-- ── Listings Grid ──────────────────────────────────────────────────── -->
      <div>
        <!-- Type Pills -->
        <div class="filter-pills" id="type-pills" style="margin-bottom:24px">
          <button class="pill active" data-type-pill="0">All</button>
        </div>

        <!-- Grid -->
        <div class="units-grid" id="units-grid">
          <!-- filled by listings.js -->
        </div>

        <!-- Pagination -->
        <div class="pagination" id="pagination"></div>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/listings.js"></script>

<script>
// Populate type pills from meta after meta loads
const _origMeta = window.__metaLoaded;
fetch('api/meta.php').then(r=>r.json()).then(data => {
  const pillsEl = document.getElementById('type-pills');
  if (pillsEl && data.unit_types) {
    data.unit_types.forEach(t => {
      const btn = document.createElement('button');
      btn.className = 'pill';
      btn.dataset.typePill = t.id;
      btn.textContent = t.label;
      pillsEl.appendChild(btn);
    });
  }
});
</script>
