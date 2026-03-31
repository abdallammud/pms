<?php
$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($property_id <= 0) {
    echo '<script>window.location.href = "' . baseUri() . '/properties";</script>';
    exit;
}
?>
<main class="content">

    <!-- Back + Actions -->
    <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <a href="<?= baseUri() ?>/properties" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Properties
        </a>
        <div class="d-flex gap-2" id="propShowActions">
            <button class="btn btn-sm btn-primary" id="propShowEditBtn">
                <i class="bi bi-pencil me-1"></i> Edit
            </button>
            <button class="btn btn-sm btn-danger" id="propShowDeleteBtn">
                <i class="bi bi-trash me-1"></i> Delete
            </button>
        </div>
    </div>

    <div class="fade-in" id="propShowContent">
        <div id="propShowSkeleton" class="text-center py-5">
            <div class="spinner-border text-primary opacity-50" role="status"></div>
        </div>
    </div>

</main>

<style>
/* ── Hero Panel ─────────────────────────────────────────── */
.prop-hero-image {
    height: 100%;
    min-height: 280px;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}
.prop-hero-image img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
    transition: transform .4s ease;
}
.prop-hero-image:hover img { transform: scale(1.03); }
.prop-hero-placeholder {
    width: 100%; height: 100%; min-height: 280px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #e8edf5 0%, #c9d4e2 100%);
    color: #8fa3bf; font-size: 5rem; border-radius: 12px;
}
.prop-hero-badge {
    position: absolute; top: 14px; left: 14px;
    background: rgba(29,51,84,.82);
    color: #fff; font-size: .7rem; font-weight: 700;
    padding: 4px 12px; border-radius: 20px; letter-spacing: .04em;
    backdrop-filter: blur(4px);
}

/* ── Details Card ───────────────────────────────────────── */
.prop-details-card {
    background: #fff;
    border-radius: 14px;
    padding: 28px 28px 22px;
    height: 100%;
    box-shadow: 0 2px 12px rgba(29,51,84,.07);
    display: flex; flex-direction: column; gap: 14px;
}
.prop-details-card .prop-name {
    font-size: 1.55rem; font-weight: 800; color: #1d3354; line-height: 1.2;
}
.prop-detail-divider {
    border: 0; border-top: 1px solid rgba(29,51,84,.07); margin: 2px 0;
}
.prop-detail-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px 16px;
}
.prop-detail-row {
    display: flex; align-items: flex-start; gap: 10px;
    font-size: .875rem; color: #555;
}
.prop-detail-row.full-width {
    grid-column: 1 / -1;
}
.prop-detail-row .prop-detail-icon {
    width: 32px; height: 32px; border-radius: 8px;
    background: rgba(29,51,84,.07);
    display: flex; align-items: center; justify-content: center;
    color: #1d3354; font-size: 1rem; flex-shrink: 0;
}
.prop-detail-row .prop-detail-label { font-size: .68rem; color: #aaa; text-transform: uppercase; letter-spacing: .05em; }
.prop-detail-row .prop-detail-val { font-weight: 600; color: #222; font-size: .875rem; }
.prop-description-box {
    background: rgba(29,51,84,.04);
    border-left: 3px solid #1d3354;
    border-radius: 0 8px 8px 0;
    padding: 10px 14px;
    font-size: .84rem;
    color: #445;
    line-height: 1.65;
}

/* ── Stat Cards ─────────────────────────────────────────── */
.prop-kpi-card {
    border-radius: 14px;
    background: #fff;
    border: 1px solid rgba(29,51,84,.08);
    box-shadow: 0 2px 10px rgba(29,51,84,.06);
    transition: transform .2s, box-shadow .2s;
}
.prop-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 28px rgba(29,51,84,.12);
}
.prop-kpi-card .card-body {
    padding: 22px 24px 20px;
}
/* icon + number side by side */
.prop-kpi-card .kpi-top {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 10px;
}
.prop-kpi-card .kpi-icon-wrap {
    width: 56px; height: 56px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.9rem; flex-shrink: 0;
}
.prop-kpi-card .kpi-number {
    font-size: 2.4rem; font-weight: 800; line-height: 1; color: #1d3354;
}
/* label below */
.prop-kpi-card .kpi-label {
    font-size: .72rem; text-transform: uppercase;
    letter-spacing: .07em; font-weight: 700;
    color: #94a3b8;
}
/* accent left border */
.prop-kpi-card { border-left: 4px solid transparent; }
.kpi-accent-blue   { border-left-color: #1d3354; }
.kpi-accent-orange { border-left-color: #f97316; }
.kpi-accent-green  { border-left-color: #16a34a; }
.kpi-accent-teal   { border-left-color: #0891b2; }

/* icon bg tints */
.kpi-icon-blue   { background: rgba(29,51,84,.08);  color: #1d3354; }
.kpi-icon-orange { background: rgba(249,115,22,.1); color: #f97316; }
.kpi-icon-green  { background: rgba(22,163,74,.1);  color: #16a34a; }
.kpi-icon-teal   { background: rgba(8,145,178,.1);  color: #0891b2; }

/* ── Gallery ─────────────────────────────────────────────── */
.gallery-thumb {
    height: 120px; border-radius: 10px; overflow: hidden; position: relative; cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,.08);
    transition: transform .18s, box-shadow .18s;
}
.gallery-thumb:hover { transform: translateY(-3px); box-shadow: 0 6px 18px rgba(0,0,0,.15); }
.gallery-thumb img { width: 100%; height: 100%; object-fit: cover; }
.img-cover-badge {
    position: absolute; top: 7px; left: 7px;
    font-size: .6rem; padding: 2px 8px; font-weight: 700; letter-spacing: .04em;
    background: #1d3354; color: #fff; border-radius: 20px; z-index: 2;
}

/* ── Units Table ─────────────────────────────────────────── */
.units-table th, .units-table td {
    padding: 14px 18px !important;
    vertical-align: middle;
}
.units-table thead th {
    background: #f8f9fc;
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    font-weight: 700;
    color: #888;
    border-bottom: 2px solid #eef0f5;
}
.units-table tbody tr:hover { background: #f9fbff; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    (function () {
        var PROP_ID = <?= $property_id ?>;
        var BASE    = '<?= baseUri() ?>';

        function esc(s) {
            if (!s) return '';
            return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        function statusBadge(s) {
            var map = { occupied: 'warning', vacant: 'success', maintenance: 'danger' };
            return '<span class="badge bg-' + (map[s] || 'secondary') + '">'
                 + s.charAt(0).toUpperCase() + s.slice(1) + '</span>';
        }

        function imgUrl(path) {
            return path ? BASE + '/' + path : null;
        }

        function renderShow(d) {
            var p      = d.property;
            var st     = d.stats;
            var images = d.images || [];
            var units  = d.units  || [];

            /* ── Hero image ──────────────────────────────── */
            var coverImg = images.find(function (i) { return i.is_cover == 1; });
            var heroHtml = coverImg
                ? '<div class="prop-hero-image h-100">'
                    + (p.type_name ? '<span class="prop-hero-badge"><i class="bi bi-building me-1"></i>' + esc(p.type_name) + '</span>' : '')
                    + '<img src="' + imgUrl(coverImg.image_path) + '" alt="cover">'
                  + '</div>'
                : '<div class="prop-hero-placeholder"><i class="bi bi-building"></i></div>';

            /* ── Details ─────────────────────────────────── */
            function detailRow(icon, label, val, full) {
                return '<div class="prop-detail-row' + (full ? ' full-width' : '') + '">'
                    + '<div class="prop-detail-icon"><i class="bi bi-' + icon + '"></i></div>'
                    + '<div><div class="prop-detail-label">' + label + '</div>'
                    + '<div class="prop-detail-val">' + esc(val) + '</div></div>'
                    + '</div>';
            }

            var gridRows = '';
            if (p.type_name)    gridRows += detailRow('building',       'Property Type',    p.type_name);
            if (p.address)      gridRows += detailRow('geo-alt',        'Street Address',   p.address, true);
            if (p.city)         gridRows += detailRow('pin-map',        'City',             p.city);
            if (p.district)     gridRows += detailRow('map',            'District',         p.district);
            if (p.region)       gridRows += detailRow('compass',        'Region',           p.region);
            if (p.owner_name)   gridRows += detailRow('person-badge',   'Owner',            p.owner_name);
            if (p.manager_name) gridRows += detailRow('person-gear',    'Property Manager', p.manager_name);
            if (p.phone)        gridRows += detailRow('telephone',      'Phone',            p.phone);
            if (p.created_at) {
                var addedOn = new Date(p.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
                gridRows += detailRow('calendar3', 'Added On', addedOn);
            }

            /* ── KPI Cards ───────────────────────────────── */
            var totalU  = parseInt(st.total_units)   || 0;
            var occupied = parseInt(st.occupied)     || 0;
            var vacant   = parseInt(st.vacant)       || 0;
            var leases   = parseInt(st.active_leases)|| 0;
            var occPct   = totalU > 0 ? Math.round((occupied / totalU) * 100) : 0;

            function kpiCard(accentCls, iconCls, icon, number, label) {
                return '<div class="col-6 col-md-3">'
                    + '<div class="prop-kpi-card ' + accentCls + '">'
                    + '<div class="card-body">'
                    + '<div class="kpi-top">'
                    + '<div class="kpi-icon-wrap ' + iconCls + '"><i class="bi bi-' + icon + '"></i></div>'
                    + '<div class="kpi-number">' + number + '</div>'
                    + '</div>'
                    + '<div class="kpi-label">' + label + '</div>'
                    + '</div></div></div>';
            }

            var statsHtml =
                kpiCard('kpi-accent-blue',   'kpi-icon-blue',   'door-open',         totalU,   'Total Units') +
                kpiCard('kpi-accent-orange', 'kpi-icon-orange', 'person-fill',        occupied, 'Occupied') +
                kpiCard('kpi-accent-green',  'kpi-icon-green',  'door-closed',        vacant,   'Vacant') +
                kpiCard('kpi-accent-teal',   'kpi-icon-teal',   'file-earmark-text',  leases,   'Active Leases');

            /* ── Gallery ─────────────────────────────────── */
            var galleryHtml = '';
            if (images.length > 0) {
                galleryHtml = images.map(function (img, idx) {
                    return '<div class="col-6 col-md-3 col-lg-2">'
                        + '<div class="gallery-thumb" onclick="openLightbox(' + idx + ', propShowImages)">'
                        + (img.is_cover == 1 ? '<span class="img-cover-badge">Cover</span>' : '')
                        + '<img src="' + imgUrl(img.image_path) + '" alt="">'
                        + '</div></div>';
                }).join('');
            } else {
                galleryHtml = '<div class="col-12 text-muted small py-2">'
                    + '<i class="bi bi-images me-1"></i>No images uploaded yet.</div>';
            }

            /* ── Units rows ──────────────────────────────── */
            var unitsRows = units.length === 0
                ? '<tr><td colspan="4" class="text-center text-muted py-4">No units found.</td></tr>'
                : units.map(function (u) {
                    return '<tr>'
                        + '<td class="fw-semibold">' + esc(u.unit_number) + '</td>'
                        + '<td>' + esc(u.unit_type || '—') + '</td>'
                        + '<td>' + (u.tenant_name ? esc(u.tenant_name) : '<span class="text-muted">—</span>') + '</td>'
                        + '<td>' + statusBadge(u.status) + '</td>'
                        + '</tr>';
                }).join('');

            /* ── Assemble HTML ───────────────────────────── */
            var html = ''

                /* Row 1: Details left + Cover right */
                + '<div class="row g-3 mb-3">'

                + '<div class="col-lg-6 order-2 order-lg-1">'
                + '<div class="prop-details-card">'
                + '<div>'
                + '<div class="prop-name">' + esc(p.name) + '</div>'
                + '</div>'
                + (p.description ? '<div class="prop-description-box"><i class="bi bi-card-text me-2 opacity-50"></i>' + esc(p.description) + '</div>' : '')
                + (gridRows ? '<hr class="prop-detail-divider"><div class="prop-detail-grid">' + gridRows + '</div>' : '')
                + '</div>'
                + '</div>'

                + '<div class="col-lg-6 order-1 order-lg-2">'
                + heroHtml
                + '</div>'

                + '</div>'

                /* Row 2: KPI cards */
                + '<div class="row g-3 mb-3">' + statsHtml + '</div>'

                /* Row 3: Gallery */
                + '<div class="card border-0 shadow-sm mb-3">'
                + '<div class="card-header bg-white border-bottom-0 pt-3 pb-2 fw-bold d-flex align-items-center gap-2">'
                + '<i class="bi bi-images text-primary"></i> Gallery'
                + '<span class="badge bg-secondary ms-2">' + images.length + '</span>'
                + '</div>'
                + '<div class="card-body pt-0"><div class="row g-2">' + galleryHtml + '</div></div>'
                + '</div>'

                /* Row 4: Units */
                + '<div class="card border-0 shadow-sm mb-4">'
                + '<div class="card-header bg-white border-bottom-0 pt-3 pb-2 fw-bold d-flex align-items-center gap-2">'
                + '<i class="bi bi-door-open text-primary"></i> Units'
                + '<span class="badge bg-secondary ms-2">' + totalU + '</span>'
                + '<a href="' + BASE + '/units" class="btn btn-outline-primary btn-sm ms-auto px-3">'
                + '<i class="bi bi-grid me-1"></i>View All Units</a>'
                + '</div>'
                + '<div class="card-body p-0">'
                + '<div class="table-responsive">'
                + '<table class="table mb-0 units-table">'
                + '<thead><tr>'
                + '<th>Unit #</th><th>Type</th><th>Tenant</th><th>Status</th>'
                + '</tr></thead>'
                + '<tbody>' + unitsRows + '</tbody>'
                + '</table></div></div></div>';

            document.getElementById('propShowSkeleton').remove();
            document.getElementById('propShowContent').insertAdjacentHTML('beforeend', html);

            /* Wire buttons */
            document.getElementById('propShowEditBtn').setAttribute(
                'onclick', 'editProperty(' + p.id + ')');
            document.getElementById('propShowDeleteBtn').setAttribute(
                'onclick', 'deletePropertyAndRedirect(' + p.id + ')');
        }

        /* ── Fetch ───────────────────────────────────────── */
        $.getJSON(BASE + '/app/property_controller.php?action=get_property_show&id=' + PROP_ID)
            .done(function (d) {
                if (d.error) {
                    document.getElementById('propShowSkeleton').innerHTML =
                        '<div class="alert alert-warning">Property not found or you do not have access.</div>';
                    return;
                }
                window.propShowImages = d.images || [];
                renderShow(d);
            })
            .fail(function () {
                document.getElementById('propShowSkeleton').innerHTML =
                    '<div class="alert alert-danger">Failed to load property.</div>';
            });
    })();

    /* ── Delete + redirect ───────────────────────────────── */
    function deletePropertyAndRedirect(id) {
        swal({ title: 'Delete Property?', text: 'This will also remove all associated images.',
               icon: 'warning', buttons: true, dangerMode: true })
            .then(function (ok) {
                if (!ok) return;
                $.post(base_url + '/app/property_controller.php?action=delete_property',
                    { id: id }, function (r) {
                        if (r.error) { swal('Error', r.msg, 'error'); return; }
                        window.location.href = base_url + '/properties';
                    }, 'json');
            });
    }

    /* ── Lightbox ────────────────────────────────────────── */
    function openLightbox(idx, images) {
        if (!images || !images.length) return;
        var BASE = '<?= baseUri() ?>';
        var html = '<div id="lbOverlay" style="position:fixed;inset:0;background:rgba(0,0,0,.92);'
            + 'z-index:9999;display:flex;align-items:center;justify-content:center;" '
            + 'onclick="document.getElementById(\'lbOverlay\').remove()">'
            + '<img src="' + BASE + '/' + images[idx].image_path + '" '
            + 'style="max-width:90vw;max-height:90vh;border-radius:10px;box-shadow:0 20px 60px rgba(0,0,0,.5);" '
            + 'onclick="event.stopPropagation()">'
            + '<button onclick="document.getElementById(\'lbOverlay\').remove()" '
            + 'style="position:absolute;top:18px;right:18px;background:rgba(255,255,255,.12);'
            + 'border:none;color:#fff;font-size:1.5rem;border-radius:50%;width:42px;height:42px;cursor:pointer;">'
            + '&times;</button></div>';
        document.body.insertAdjacentHTML('beforeend', html);
    }
});
</script>
