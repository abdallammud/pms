<?php
$page_title = 'Unit Detail — PropSpace';
$page_desc  = 'View full details, photos and amenities for this available rental unit.';
$active_nav = 'listings';
require_once __DIR__ . '/includes/header.php';
?>

<div style="padding-top:88px"></div>

<!-- ── Lightbox ───────────────────────────────────────────────────────────────── -->
<div class="lightbox-overlay" id="lightbox" role="dialog" aria-label="Image viewer" aria-modal="true">
  <button class="lightbox-close" id="lightbox-close" aria-label="Close">
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
  </button>
  <img id="lightbox-img" src="" alt="Unit photo" class="lightbox-img">
</div>

<!-- ── Loading state ─────────────────────────────────────────────────────────── -->
<div id="unit-loader" class="section">
  <div class="container">
    <div style="height:24px;width:120px;margin-bottom:32px" class="skeleton"></div>
    <div class="unit-detail-layout">
      <div>
        <div style="height:460px;border-radius:var(--r-lg)" class="skeleton"></div>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:4px;margin-top:4px">
          <div style="height:90px" class="skeleton"></div>
          <div style="height:90px" class="skeleton"></div>
          <div style="height:90px" class="skeleton"></div>
          <div style="height:90px" class="skeleton"></div>
        </div>
        <div style="margin-top:32px;display:flex;flex-direction:column;gap:12px">
          <div style="height:16px;width:40%" class="skeleton"></div>
          <div style="height:36px;width:75%" class="skeleton"></div>
          <div style="height:16px;width:55%" class="skeleton"></div>
        </div>
      </div>
      <div style="background:var(--surface);border:1px solid var(--border);border-radius:var(--r-xl);overflow:hidden">
        <div style="height:140px;background:var(--navy);opacity:.4"></div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:12px">
          <div style="height:14px;width:60%" class="skeleton"></div>
          <div style="height:14px;width:80%" class="skeleton"></div>
          <div style="height:14px;width:50%" class="skeleton"></div>
          <div style="height:48px;margin-top:8px;border-radius:var(--r)" class="skeleton"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Error state ────────────────────────────────────────────────────────────── -->
<div id="unit-error" hidden class="section">
  <div class="container"></div>
</div>

<!-- ── Unit content ───────────────────────────────────────────────────────────── -->
<div id="unit-content" hidden>
  <section class="section" style="padding-top:32px">
    <div class="container">

      <!-- Back link -->
      <a href="listings.php" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
        Back to listings
      </a>

      <div class="unit-detail-layout">

        <!-- ── Left: gallery + content ──────────────────────────────────────── -->
        <div class="unit-detail-content">

          <!-- Gallery -->
          <div class="unit-gallery">
            <div class="gallery-main">
              <img id="gallery-main-img" src="" alt="Unit photo" style="width:100%;height:100%;object-fit:cover">
              <div class="gallery-count" id="gallery-count"></div>
            </div>
            <div class="gallery-thumbs" id="gallery-thumbs"></div>
          </div>

          <!-- Header -->
          <div class="detail-header">
            <span class="detail-type-badge" id="unit-type-badge"></span>
            <h1 class="detail-title" id="unit-title"></h1>
            <div class="detail-location" id="unit-location">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7Zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5Z"/></svg>
              <span id="unit-location-text"></span>
            </div>
          </div>

          <!-- Description -->
          <div class="detail-section" id="description-section">
            <h3 class="detail-section-title">About this property</h3>
            <p class="detail-description" id="unit-description"></p>
          </div>

          <!-- Property info -->
          <div class="detail-section">
            <h3 class="detail-section-title">Property</h3>
            <div class="property-card-mini">
              <div class="property-card-mini-img">
                <img id="property-img" src="" alt="Property" onerror="this.parentElement.innerHTML='<svg width=\'28\' height=\'28\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#9ca3af\' stroke-width=\'1.5\'><path d=\'M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z\'/></svg>'">
              </div>
              <div class="property-card-mini-info">
                <div class="property-card-mini-name" id="property-name"></div>
                <div class="property-card-mini-sub" id="property-address"></div>
                <div style="margin-top:4px">
                  <span style="font-size:.72rem;font-weight:600;color:var(--gold);text-transform:uppercase;letter-spacing:.08em" id="property-type"></span>
                </div>
              </div>
            </div>
          </div>

          <!-- Similar units -->
          <div class="detail-section" id="similar-section">
            <h3 class="detail-section-title">Other units in this property</h3>
            <div class="similar-grid" id="similar-units"></div>
          </div>

        </div>

        <!-- ── Right: sticky sidebar ─────────────────────────────────────────── -->
        <aside>
          <div class="detail-sidebar-card">

            <!-- Price -->
            <div class="detail-price-header">
              <div class="detail-price-label">Monthly Rent</div>
              <div class="detail-price-amount" id="unit-price">—</div>
              <div class="detail-price-period">per month</div>
              <div class="detail-availability">
                <svg width="8" height="8" viewBox="0 0 10 10"><circle cx="5" cy="5" r="5" fill="#4ade80"/></svg>
                Available Now
              </div>
            </div>

            <div class="detail-sidebar-body">
              <!-- Specs -->
              <div class="detail-specs">
                <div class="detail-spec">
                  <span class="detail-spec-label">Type</span>
                  <span class="detail-spec-value" id="spec-type">—</span>
                </div>
                <div class="detail-spec">
                  <span class="detail-spec-label">Bedrooms</span>
                  <span class="detail-spec-value" id="spec-rooms">—</span>
                </div>
                <div class="detail-spec">
                  <span class="detail-spec-label">Floor</span>
                  <span class="detail-spec-value" id="spec-floor">—</span>
                </div>
                <div class="detail-spec">
                  <span class="detail-spec-label">Size</span>
                  <span class="detail-spec-value" id="spec-size">—</span>
                </div>
              </div>

              <!-- Amenities -->
              <div class="detail-amenities">
                <div class="detail-amenities-heading">Amenities</div>
                <div class="detail-amenity-list" id="unit-amenities"></div>
              </div>

              <!-- CTA -->
              <a
                href="#enquire"
                class="btn btn-primary detail-cta"
                onclick="document.getElementById('enquire').scrollIntoView({behavior:'smooth'});return false"
              >
                Enquire About This Unit
              </a>

              <div style="margin-top:12px;text-align:center">
                <button
                  onclick="copyShareLink()"
                  style="background:none;border:none;cursor:pointer;font-size:.8rem;color:var(--text-muted);font-weight:500;display:inline-flex;align-items:center;gap:5px"
                >
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                  Share this listing
                </button>
              </div>
            </div>
          </div>
        </aside>
      </div>

      <!-- ── Enquiry section ─────────────────────────────────────────────────── -->
      <div class="detail-section" id="enquire" style="max-width:600px">
        <h3 class="detail-section-title">Send an Enquiry</h3>
        <p style="color:var(--text-muted);font-size:.9rem;margin-bottom:24px">
          Interested? Fill in your details and the property manager will get back to you shortly.
        </p>
        <form id="enquiry-form" onsubmit="handleEnquiry(event)" style="display:flex;flex-direction:column;gap:16px">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
            <div>
              <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Full Name *</label>
              <input type="text" name="name" required placeholder="Jane Doe"
                style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--r);font-size:.875rem;color:var(--text);background:var(--surface)"
                onfocus="this.style.borderColor='var(--navy)'"
                onblur="this.style.borderColor='var(--border)'">
            </div>
            <div>
              <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Phone *</label>
              <input type="tel" name="phone" required placeholder="+254 700 000 000"
                style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--r);font-size:.875rem;color:var(--text);background:var(--surface)"
                onfocus="this.style.borderColor='var(--navy)'"
                onblur="this.style.borderColor='var(--border)'">
            </div>
          </div>
          <div>
            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Email</label>
            <input type="email" name="email" placeholder="you@example.com"
              style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--r);font-size:.875rem;color:var(--text);background:var(--surface)"
              onfocus="this.style.borderColor='var(--navy)'"
              onblur="this.style.borderColor='var(--border)'">
          </div>
          <div>
            <label style="font-size:.8rem;font-weight:600;color:var(--text-muted);display:block;margin-bottom:6px">Message</label>
            <textarea name="message" rows="4" placeholder="I'm interested in this unit and would like to arrange a viewing…"
              style="width:100%;padding:10px 14px;border:1px solid var(--border);border-radius:var(--r);font-size:.875rem;color:var(--text);background:var(--surface);resize:vertical"
              onfocus="this.style.borderColor='var(--navy)'"
              onblur="this.style.borderColor='var(--border)'"></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="align-self:flex-start;padding:12px 32px">
            Send Enquiry
          </button>
          <p id="enquiry-msg" style="font-size:.875rem;display:none"></p>
        </form>
      </div>

    </div>
  </section>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<script src="assets/js/unit.js"></script>
<script>
function copyShareLink() {
  navigator.clipboard.writeText(window.location.href)
    .then(() => showToast('Link copied to clipboard'))
    .catch(() => showToast('Copy the URL from your browser bar'));
}

function handleEnquiry(e) {
  e.preventDefault();
  const btn = e.target.querySelector('button[type=submit]');
  const msg = document.getElementById('enquiry-msg');
  btn.disabled = true;
  btn.textContent = 'Sending…';
  // Simulate send (replace with real API call if needed)
  setTimeout(() => {
    e.target.reset();
    msg.style.display = 'block';
    msg.style.color = 'var(--available)';
    msg.textContent = '✓ Your enquiry has been sent. We\'ll be in touch shortly.';
    btn.disabled = false;
    btn.textContent = 'Send Enquiry';
  }, 1200);
}

// Update page title when unit loads
document.addEventListener('DOMContentLoaded', () => {
  const unitId = new URLSearchParams(window.location.search).get('id');
  if (!unitId) {
    document.getElementById('unit-loader').innerHTML =
      '<div class="container"><div class="empty-state"><h3>No unit specified</h3><a href="listings.php" class="btn btn-outline-navy">Browse listings</a></div></div>';
  }
});
</script>
