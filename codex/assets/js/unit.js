/**
 * PropSpace · Unit Detail Page
 * Gallery, lightbox, and similar units rendering.
 */

(function () {
  'use strict';

  const unitId = new URLSearchParams(window.location.search).get('id');

  if (!unitId) {
    window.location.href = 'listings.php';
    return;
  }

  /* ── Element refs ─────────────────────────────────────────────────────────── */
  const titleEl        = document.getElementById('unit-title');
  const typeEl         = document.getElementById('unit-type-badge');
  const priceEl        = document.getElementById('unit-price');
  const specFloor      = document.getElementById('spec-floor');
  const specSize       = document.getElementById('spec-size');
  const specRooms      = document.getElementById('spec-rooms');
  const specType       = document.getElementById('spec-type');
  const amenitiesEl    = document.getElementById('unit-amenities');
  const descEl         = document.getElementById('unit-description');
  const propNameEl     = document.getElementById('property-name');
  const propAddressEl  = document.getElementById('property-address');
  const propImgEl      = document.getElementById('property-img');
  const propTypeEl     = document.getElementById('property-type');
  const galleryMain    = document.getElementById('gallery-main-img');
  const galleryCount   = document.getElementById('gallery-count');
  const thumbsEl       = document.getElementById('gallery-thumbs');
  const similarEl      = document.getElementById('similar-units');
  const lightbox       = document.getElementById('lightbox');
  const lightboxImg    = document.getElementById('lightbox-img');

  /* ── Gallery state ────────────────────────────────────────────────────────── */
  let images      = [];
  let activeIndex = 0;

  function setActiveImage(idx) {
    if (!images.length) return;
    activeIndex = Math.max(0, Math.min(idx, images.length - 1));
    const url = images[activeIndex];
    if (galleryMain) {
      galleryMain.src = url;
      galleryMain.alt = `Photo ${activeIndex + 1}`;
    }
    if (thumbsEl) {
      thumbsEl.querySelectorAll('.gallery-thumb').forEach((t, i) => {
        t.classList.toggle('active', i === activeIndex);
      });
    }
  }

  /* ── Lightbox ─────────────────────────────────────────────────────────────── */
  function openLightbox(idx) {
    if (!lightbox || !lightboxImg || !images.length) return;
    lightboxImg.src = images[idx];
    lightbox.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function closeLightbox() {
    if (!lightbox) return;
    lightbox.classList.remove('open');
    document.body.style.overflow = '';
  }

  if (lightbox) {
    document.getElementById('lightbox-close')?.addEventListener('click', closeLightbox);
    lightbox.addEventListener('click', (e) => { if (e.target === lightbox) closeLightbox(); });
    document.addEventListener('keydown', (e) => {
      if (!lightbox.classList.contains('open')) return;
      if (e.key === 'Escape') closeLightbox();
      if (e.key === 'ArrowRight') openLightbox(Math.min(activeIndex + 1, images.length - 1));
      if (e.key === 'ArrowLeft')  openLightbox(Math.max(activeIndex - 1, 0));
    });
  }

  if (galleryMain) {
    galleryMain.addEventListener('click', () => openLightbox(activeIndex));
  }

  /* ── Build gallery ────────────────────────────────────────────────────────── */
  function buildGallery(imgs, coverImage) {
    const allImages = imgs.length ? imgs.map(i => i.url) : (coverImage ? [coverImage] : []);
    images = allImages;

    if (!allImages.length) {
      // Placeholder
      if (galleryMain) {
        galleryMain.src = 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&q=80&auto=format&fit=crop';
      }
      if (galleryCount) galleryCount.style.display = 'none';
      return;
    }

    // Main image
    if (galleryMain) galleryMain.src = allImages[0];
    if (galleryCount) galleryCount.textContent = `1 / ${allImages.length}`;

    // Thumbnails
    if (thumbsEl) {
      thumbsEl.innerHTML = allImages.map((url, i) => `
        <div class="gallery-thumb ${i === 0 ? 'active' : ''}" data-index="${i}">
          <img src="${escHtml(url)}" alt="Photo ${i + 1}" loading="lazy">
        </div>`).join('');

      thumbsEl.querySelectorAll('.gallery-thumb').forEach(thumb => {
        thumb.addEventListener('click', () => {
          const idx = parseInt(thumb.dataset.index);
          setActiveImage(idx);
          if (galleryCount) galleryCount.textContent = `${idx + 1} / ${allImages.length}`;
        });
      });
    }
  }

  /* ── Amenity icon map ─────────────────────────────────────────────────────── */
  function amenityIcon(name) {
    const n = (name || '').toLowerCase();
    if (n.includes('wifi') || n.includes('internet'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12.55a11 11 0 0 1 14.08 0"/><path d="M1.42 9a16 16 0 0 1 21.16 0"/><path d="M8.53 16.11a6 6 0 0 1 6.95 0"/><circle cx="12" cy="20" r="1"/></svg>`;
    if (n.includes('park'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8Z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>`;
    if (n.includes('gym') || n.includes('fitness'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8h12M6 16h12M4 12h16M2 12h2M20 12h2"/></svg>`;
    if (n.includes('pool') || n.includes('swim'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 12h20M2 17c1.5-1 3-1 4.5 0s3 1 4.5 0 3-1 4.5 0 3 1 4.5 0M4 7a4 4 0 0 1 8 0"/></svg>`;
    if (n.includes('security') || n.includes('guard'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/></svg>`;
    if (n.includes('water'))
      return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 12 6 14.5 6 16a6 6 0 0 0 12 0c0-1.5-.5-4-6-14Z"/></svg>`;
    // Default
    return `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>`;
  }

  /* ── Render unit ──────────────────────────────────────────────────────────── */
  function renderUnit(unit) {
    document.title = `Unit ${unit.unit_number} · ${unit.property?.name || ''} — PropSpace`;

    const type = unit.unit_type || unitTypeLabel(unit.room_count, null);

    if (typeEl)     typeEl.textContent     = type;
    if (titleEl)    titleEl.textContent    = `${unit.property?.name || ''} · Unit ${unit.unit_number}`;
    const locationText = document.getElementById('unit-location-text');
    if (locationText) locationText.textContent = [unit.property?.address, unit.property?.city].filter(Boolean).join(', ');
    if (priceEl)    priceEl.textContent    = fmtCurrency(unit.rent_amount);

    // Specs
    if (specType)  specType.textContent  = type;
    if (specFloor) specFloor.textContent = unit.floor_number != null ? `Floor ${unit.floor_number}` : '—';
    if (specSize)  specSize.textContent  = unit.size_sqft    != null ? `${unit.size_sqft.toLocaleString()} sqft` : '—';
    if (specRooms) specRooms.textContent = unit.room_count   != null ? (unit.room_count === 0 ? 'Studio' : plural(unit.room_count, 'Bed')) : '—';

    // Amenities
    if (amenitiesEl) {
      if (unit.amenities?.length) {
        amenitiesEl.innerHTML = unit.amenities.map(a =>
          `<span class="detail-amenity">${amenityIcon(a.name)}${escHtml(a.name)}</span>`
        ).join('');
      } else {
        amenitiesEl.innerHTML = '<p style="color:var(--text-muted);font-size:.85rem">No amenities listed.</p>';
      }
    }

    // Description
    if (descEl && unit.property?.description) {
      descEl.textContent = unit.property.description;
    } else if (descEl) {
      descEl.closest('.detail-section')?.remove();
    }

    // Property card
    if (propNameEl)    propNameEl.textContent    = unit.property?.name    || '';
    if (propAddressEl) propAddressEl.textContent = [unit.property?.address, unit.property?.city].filter(Boolean).join(', ');
    if (propTypeEl)    propTypeEl.textContent    = unit.property?.type    || '';
    if (propImgEl && unit.property?.cover_image) {
      propImgEl.src = unit.property.cover_image;
    }

    // Gallery
    buildGallery(unit.images || [], unit.cover_image);

    // Similar units
    if (similarEl && unit.similar?.length) {
      similarEl.innerHTML = unit.similar.map(u => buildUnitCard({
        ...u,
        property: { name: unit.property?.name, city: unit.property?.city }
      })).join('');
    } else if (similarEl) {
      similarEl.closest('.detail-section')?.remove();
    }

    // Show content, hide loader
    document.getElementById('unit-loader')?.remove();
    document.getElementById('unit-content')?.removeAttribute('hidden');
  }

  /* ── Fetch unit data ──────────────────────────────────────────────────────── */
  async function loadUnit() {
    try {
      const res  = await fetch(`${API}/unit.php?id=${unitId}`);
      const data = await res.json();

      if (data.error) {
        showErrorState(data.message || 'Unit not available.');
        return;
      }

      renderUnit(data);
    } catch (e) {
      showErrorState('Failed to load unit details. Please try again.');
    }
  }

  function showErrorState(msg) {
    document.getElementById('unit-loader')?.remove();
    const err = document.getElementById('unit-error');
    if (err) {
      err.innerHTML = `
        <div class="empty-state">
          <div class="empty-state-icon">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
          </div>
          <h3>Unit not available</h3>
          <p>${escHtml(msg)}</p>
          <a href="listings.php" class="btn btn-outline-navy">Browse listings</a>
        </div>`;
      err.removeAttribute('hidden');
    }
  }

  loadUnit();
})();
