/**
 * PropSpace · Listings Page
 * Handles filter state, API calls, and rendering units grid with pagination.
 */

(function () {
  'use strict';

  const grid       = document.getElementById('units-grid');
  const paginEl    = document.getElementById('pagination');
  const countEl    = document.getElementById('listings-count');
  const sortEl     = document.getElementById('sort-select');
  const filterForm = document.getElementById('filter-form');
  const clearBtn   = document.getElementById('clear-filters');
  const searchInput= document.getElementById('search-input');

  let state = {
    page:      1,
    limit:     12,
    sort:      'featured',
    city:      '',
    type_id:   0,
    min_price: 0,
    max_price: 0,
    rooms:     0,
    amenities: [],
    q:         '',
  };

  let debounceTimer;
  let abortController;

  /* ── Load filter metadata ─────────────────────────────────────────────────── */
  async function loadMeta() {
    try {
      const res  = await fetch(`${API}/meta.php`);
      const data = await res.json();

      // Populate city filter
      const citySelect = document.getElementById('filter-city');
      if (citySelect && data.cities) {
        citySelect.innerHTML = '<option value="">All Cities</option>' +
          data.cities.map(c => `<option value="${escHtml(c)}">${escHtml(c)}</option>`).join('');
      }

      // Populate unit type filter
      const typeSelect = document.getElementById('filter-type');
      if (typeSelect && data.unit_types) {
        typeSelect.innerHTML = '<option value="">All Types</option>' +
          data.unit_types.map(t => `<option value="${t.id}">${escHtml(t.label)}</option>`).join('');
      }

      // Populate amenities checkboxes
      const amenContainer = document.getElementById('filter-amenities');
      if (amenContainer && data.amenities) {
        amenContainer.innerHTML = data.amenities.map(a => `
          <label class="checkbox-item">
            <input type="checkbox" name="amenity" value="${a.id}">
            ${escHtml(a.name)}
          </label>`).join('');
        amenContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
          cb.addEventListener('change', () => {
            state.amenities = [...amenContainer.querySelectorAll('input:checked')].map(i => i.value);
            resetAndFetch();
          });
        });
      }

      // Price range
      if (data.price_range && data.price_range.max > 0) {
        const maxInput  = document.getElementById('filter-max-price');
        const maxLabel  = document.getElementById('max-price-label');
        if (maxInput) {
          maxInput.max   = data.price_range.max;
          maxInput.value = data.price_range.max;
          if (maxLabel) maxLabel.textContent = fmtCurrency(data.price_range.max);
          state.max_price = 0; // no initial filter
        }
      }

      // Counts
      if (data.counts) {
        document.querySelectorAll('[data-stat="units"]').forEach(el => el.textContent = data.counts.units.toLocaleString());
        document.querySelectorAll('[data-stat="properties"]').forEach(el => el.textContent = data.counts.properties.toLocaleString());
        document.querySelectorAll('[data-stat="cities"]').forEach(el => el.textContent = data.counts.cities);
      }
    } catch (e) {
      console.warn('Failed to load meta:', e);
    }
  }

  /* ── Fetch listings ───────────────────────────────────────────────────────── */
  async function fetchListings() {
    if (abortController) abortController.abort();
    abortController = new AbortController();

    grid.innerHTML = buildSkeletons(state.limit);

    const params = new URLSearchParams();
    params.set('page',  state.page);
    params.set('limit', state.limit);
    params.set('sort',  state.sort);
    if (state.city)      params.set('city', state.city);
    if (state.type_id)   params.set('type_id', state.type_id);
    if (state.min_price) params.set('min_price', state.min_price);
    if (state.max_price && state.max_price > 0) params.set('max_price', state.max_price);
    if (state.rooms)     params.set('rooms', state.rooms);
    if (state.amenities.length) params.set('amenities', state.amenities.join(','));
    if (state.q)         params.set('q', state.q);

    try {
      const res  = await fetch(`${API}/listings.php?${params}`, { signal: abortController.signal });
      const data = await res.json();
      renderListings(data);
    } catch (e) {
      if (e.name !== 'AbortError') {
        grid.innerHTML = `<div class="empty-state" style="grid-column:1/-1">
          <div class="empty-state-icon"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div>
          <h3>Something went wrong</h3>
          <p>Could not load listings. Please try again.</p>
          <button class="btn btn-outline-navy" onclick="fetchListings()">Retry</button>
        </div>`;
      }
    }
  }

  /* ── Render ───────────────────────────────────────────────────────────────── */
  function renderListings(data) {
    const { units = data.data || [], total = 0, pages = 1 } = data;

    if (countEl) {
      countEl.innerHTML = `<strong>${total.toLocaleString()}</strong> unit${total !== 1 ? 's' : ''} available`;
    }

    if (!units.length) {
      grid.innerHTML = `
        <div class="empty-state" style="grid-column:1/-1">
          <div class="empty-state-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
          </div>
          <h3>No units found</h3>
          <p>Try adjusting your filters or search terms.</p>
          <button class="btn btn-outline-navy" id="empty-clear">Clear filters</button>
        </div>`;
      const emptyClr = document.getElementById('empty-clear');
      if (emptyClr) emptyClr.addEventListener('click', clearAllFilters);
    } else {
      grid.innerHTML = units.map(buildUnitCard).join('');
    }

    buildPagination(paginEl, state.page, pages, (p) => {
      state.page = p;
      fetchListings();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  }

  /* ── Helpers ──────────────────────────────────────────────────────────────── */
  function resetAndFetch() {
    state.page = 1;
    fetchListings();
  }

  function clearAllFilters() {
    state = { ...state, city: '', type_id: 0, min_price: 0, max_price: 0, rooms: 0, amenities: [], q: '', page: 1 };
    if (filterForm) filterForm.reset();
    if (searchInput) searchInput.value = '';
    fetchListings();
  }

  /* ── Event wiring ─────────────────────────────────────────────────────────── */
  if (sortEl) {
    sortEl.addEventListener('change', () => {
      state.sort = sortEl.value;
      resetAndFetch();
    });
  }

  if (searchInput) {
    searchInput.addEventListener('input', () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => {
        state.q = searchInput.value.trim();
        resetAndFetch();
      }, 400);
    });
  }

  // City filter
  document.addEventListener('change', (e) => {
    if (e.target.id === 'filter-city') {
      state.city = e.target.value;
      resetAndFetch();
    }
    if (e.target.id === 'filter-type') {
      state.type_id = parseInt(e.target.value) || 0;
      resetAndFetch();
    }
    if (e.target.id === 'filter-rooms') {
      state.rooms = parseInt(e.target.value) || 0;
      resetAndFetch();
    }
  });

  // Price range
  const maxPriceInput = document.getElementById('filter-max-price');
  if (maxPriceInput) {
    maxPriceInput.addEventListener('input', () => {
      const val = parseInt(maxPriceInput.value);
      state.max_price = val;
      const label = document.getElementById('max-price-label');
      if (label) label.textContent = fmtCurrency(val);
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(resetAndFetch, 500);
    });
  }

  const minPriceInput = document.getElementById('filter-min-price');
  if (minPriceInput) {
    minPriceInput.addEventListener('input', () => {
      state.min_price = parseInt(minPriceInput.value) || 0;
      const label = document.getElementById('min-price-label');
      if (label) label.textContent = fmtCurrency(state.min_price);
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(resetAndFetch, 500);
    });
  }

  if (clearBtn) {
    clearBtn.addEventListener('click', clearAllFilters);
  }

  // Type filter pills (on index page or top of listings)
  document.querySelectorAll('[data-type-pill]').forEach(pill => {
    pill.addEventListener('click', () => {
      document.querySelectorAll('[data-type-pill]').forEach(p => p.classList.remove('active'));
      pill.classList.add('active');
      state.type_id = parseInt(pill.dataset.typePill) || 0;
      state.page    = 1;
      fetchListings();
    });
  });

  /* ── URL param pre-fill ───────────────────────────────────────────────────── */
  (function readUrlParams() {
    const sp = new URLSearchParams(window.location.search);
    if (sp.get('city'))      state.city      = sp.get('city');
    if (sp.get('type_id'))   state.type_id   = parseInt(sp.get('type_id'));
    if (sp.get('min_price')) state.min_price = parseFloat(sp.get('min_price'));
    if (sp.get('max_price')) state.max_price = parseFloat(sp.get('max_price'));
    if (sp.get('rooms'))     state.rooms     = parseInt(sp.get('rooms'));
    if (sp.get('q'))         state.q         = sp.get('q');
    if (sp.get('sort'))      state.sort      = sp.get('sort');
    if (searchInput && state.q) searchInput.value = state.q;
    if (sortEl && state.sort) sortEl.value = state.sort;
  })();

  /* ── Init ─────────────────────────────────────────────────────────────────── */
  loadMeta().then(fetchListings);
})();
