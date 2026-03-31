/**
 * PropSpace · Shared Utilities
 * Used across all pages of the public listings site.
 */

const API = window.__CODEX_API_BASE__ || 'api';

/* ── Currency formatter ─────────────────────────────────────────────────────── */
function fmtCurrency(amount, symbol = 'KES') {
  return symbol + '\u00a0' + Number(amount).toLocaleString('en-KE', { maximumFractionDigits: 0 });
}

/* ── Pluralise ──────────────────────────────────────────────────────────────── */
function plural(n, singular, pluralForm) {
  return n === 1 ? `${n} ${singular}` : `${n} ${pluralForm || singular + 's'}`;
}

/* ── Unit type label from room_count ────────────────────────────────────────── */
function unitTypeLabel(roomCount, unitType) {
  if (unitType && unitType !== 'Unit') return unitType;
  if (roomCount === null || roomCount === undefined) return 'Unit';
  if (roomCount === 0) return 'Studio';
  return `${roomCount} Bedroom`;
}

/* ── Build a unit card HTML string ─────────────────────────────────────────── */
function buildUnitCard(unit) {
  const price    = fmtCurrency(unit.rent_amount);
  const type     = unit.unit_type || unitTypeLabel(unit.room_count, null);
  const city     = unit.property?.city || '';
  const propName = unit.property?.name || '';
  const href     = `unit.php?id=${unit.id}`;

  const imgHtml  = unit.cover_image
    ? `<img src="${escHtml(unit.cover_image)}" alt="${escHtml(type)} at ${escHtml(propName)}" loading="lazy">`
    : `<div class="unit-card-img-placeholder">
        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="3" y="8" width="18" height="13" rx="2"/>
          <path d="M7 8V5a1 1 0 0 1 1-1h8a1 1 0 0 1 1 1v3"/>
        </svg>
      </div>`;

  const metaFloor  = unit.floor_number != null
    ? `<span class="unit-meta-item"><svg class="unit-meta-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M9 21V9l3-6 3 6v12M9 12h6"/></svg>Floor ${unit.floor_number}</span>` : '';
  const metaSize   = unit.size_sqft
    ? `<span class="unit-meta-item"><svg class="unit-meta-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 3l7.5 7.5M3 3v6M3 3h6M21 21l-7.5-7.5M21 21v-6M21 21h-6"/></svg>${unit.size_sqft.toLocaleString()} sqft</span>` : '';
  const metaRooms  = unit.room_count != null
    ? `<span class="unit-meta-item"><svg class="unit-meta-icon" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 7v13M21 7v13M3 14h18M5 7h14a2 2 0 0 1 2 2v5H3V9a2 2 0 0 1 2-2Z"/></svg>${unit.room_count === 0 ? 'Studio' : plural(unit.room_count, 'Bed')}</span>` : '';

  const amenities  = (unit.amenities || []).slice(0, 3);
  const extraCount = (unit.amenities || []).length - amenities.length;
  const amenHtml   = amenities.map(a =>
    `<span class="amenity-tag">${escHtml(a.name || a)}</span>`
  ).join('') + (extraCount > 0 ? `<span class="amenity-tag amenity-more">+${extraCount}</span>` : '');

  return `
    <article class="unit-card" onclick="window.location.href='${href}'" tabindex="0" role="link" aria-label="${escHtml(type)} in ${escHtml(propName)}">
      <div class="unit-card-img">
        ${imgHtml}
        <div class="unit-card-badge">
          <span class="unit-card-badge-dot"></span>Available
        </div>
        <div class="unit-card-price">${price} <span>/ mo</span></div>
      </div>
      <div class="unit-card-body">
        <div class="unit-card-type">${escHtml(type)}</div>
        <div class="unit-card-name">${escHtml(propName)} · Unit ${escHtml(unit.unit_number)}</div>
        <div class="unit-card-meta">
          ${metaRooms}${metaFloor}${metaSize}
        </div>
        ${amenHtml ? `<div class="unit-card-amenities">${amenHtml}</div>` : ''}
      </div>
      <div class="unit-card-footer">
        <div class="unit-card-location">
          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/></svg>
          ${escHtml(city)}
        </div>
        <a href="${href}" class="btn-view-unit">
          View
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        </a>
      </div>
    </article>`;
}

/* ── Render skeleton cards ─────────────────────────────────────────────────── */
function buildSkeletons(count = 6) {
  return Array.from({ length: count }, () => `
    <div class="unit-card unit-card-skeleton">
      <div class="unit-card-img"></div>
      <div class="unit-card-body" style="gap:10px">
        <div class="skeleton" style="height:12px;width:50%"></div>
        <div class="skeleton" style="height:18px;width:80%"></div>
        <div class="skeleton" style="height:12px;width:60%"></div>
        <div class="skeleton" style="height:12px;width:40%"></div>
      </div>
    </div>`).join('');
}

/* ── Escape HTML ────────────────────────────────────────────────────────────── */
function escHtml(str) {
  if (!str) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

/* ── Sticky nav scroll behavior ─────────────────────────────────────────────── */
function initStickyNav() {
  const nav = document.querySelector('.site-nav');
  if (!nav) return;
  const toggle = () => nav.classList.toggle('scrolled', window.scrollY > 40);
  toggle();
  window.addEventListener('scroll', toggle, { passive: true });
}

/* ── Build pagination ────────────────────────────────────────────────────────── */
function buildPagination(container, currentPage, totalPages, onPageClick) {
  if (!container || totalPages <= 1) {
    if (container) container.innerHTML = '';
    return;
  }

  const range = [];
  const delta = 2;
  for (let i = 1; i <= totalPages; i++) {
    if (i === 1 || i === totalPages || (i >= currentPage - delta && i <= currentPage + delta)) {
      range.push(i);
    } else if (range[range.length - 1] !== '…') {
      range.push('…');
    }
  }

  const btns = range.map(p =>
    p === '…'
      ? `<span class="page-btn" disabled>…</span>`
      : `<button class="page-btn ${p === currentPage ? 'active' : ''}" data-page="${p}">${p}</button>`
  );

  container.innerHTML = `
    <button class="page-btn" data-page="${currentPage - 1}" ${currentPage <= 1 ? 'disabled' : ''}>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
    </button>
    ${btns.join('')}
    <button class="page-btn" data-page="${currentPage + 1}" ${currentPage >= totalPages ? 'disabled' : ''}>
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
    </button>`;

  container.querySelectorAll('[data-page]').forEach(btn => {
    btn.addEventListener('click', () => {
      const p = parseInt(btn.dataset.page);
      if (!isNaN(p) && p >= 1 && p <= totalPages) onPageClick(p);
    });
  });
}

/* ── Toast ───────────────────────────────────────────────────────────────────── */
function showToast(message, duration = 3500) {
  let container = document.querySelector('.toast-container');
  if (!container) {
    container = document.createElement('div');
    container.className = 'toast-container';
    document.body.appendChild(container);
  }
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.textContent = message;
  container.appendChild(toast);
  setTimeout(() => toast.remove(), duration);
}

document.addEventListener('DOMContentLoaded', initStickyNav);
