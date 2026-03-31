<!-- Main Content -->
<main class="content">
    <!-- Page Header -->
    <div class="d-flex mt-3 align-items-center justify-content-between mb-3">
        <h5 class="page-title">Properties</h5>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
            <i class="bi bi-plus me-1"></i> Add Property
        </button>
    </div>

    <!-- Search / Filter Bar -->
    <div class="page-content fade-in">
        <div class="prop-filter-bar mb-4">
            <div class="prop-filter-inner">

                <!-- Search -->
                <div class="prop-filter-search">
                    <i class="bi bi-search prop-filter-search-icon"></i>
                    <input type="text" id="propertySearch" class="prop-filter-search-input"
                        placeholder="Search by name, city, owner…">
                </div>

                <!-- Divider -->
                <div class="prop-filter-divider"></div>

                <!-- Type filter -->
                <div class="prop-filter-select-wrap">
                    <i class="bi bi-building prop-filter-sel-icon"></i>
                    <select id="propertyTypeFilter" class="prop-filter-select">
                        <option value="">All Types</option>
                    </select>
                    <i class="bi bi-chevron-down prop-filter-chevron"></i>
                </div>

                <!-- Status filter -->
                <div class="prop-filter-select-wrap">
                    <i class="bi bi-funnel prop-filter-sel-icon"></i>
                    <select id="propertyStatusFilter" class="prop-filter-select">
                        <option value="">All Status</option>
                        <option value="occupied">Has Occupied</option>
                        <option value="vacant">Has Vacant</option>
                    </select>
                    <i class="bi bi-chevron-down prop-filter-chevron"></i>
                </div>

                <!-- Results count -->
                <div class="prop-filter-count">
                    <span id="propertyCount"></span>
                </div>

            </div>
        </div>

        <!-- Cards Grid -->
        <div id="propertiesGrid" class="row g-4">
            <!-- Injected by JS -->
        </div>

        <!-- Empty State -->
        <div id="propertiesEmpty" class="text-center py-5 d-none">
            <i class="bi bi-building display-4 text-muted opacity-50"></i>
            <p class="mt-3 text-muted">No properties found.</p>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addPropertyModal">
                <i class="bi bi-plus me-1"></i> Add First Property
            </button>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3" id="propertiesPagination" style="display:none!important">
            <small class="text-muted" id="paginationInfo"></small>
            <nav>
                <ul class="pagination pagination-sm mb-0" id="paginationLinks"></ul>
            </nav>
        </div>
    </div>
</main>

<style>
/* ── Filter Bar ──────────────────────────────────────────── */
.prop-filter-bar {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 12px rgba(29,51,84,.08);
    border: 1px solid rgba(29,51,84,.07);
    border-top: 3px solid #1d3354;
    overflow: hidden;
}
.prop-filter-inner {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    gap: 0;
    flex-wrap: wrap;
}

/* Search field */
.prop-filter-search {
    position: relative;
    flex: 1 1 220px;
    min-width: 180px;
}
.prop-filter-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: .9rem;
    pointer-events: none;
}
.prop-filter-search-input {
    width: 100%;
    border: none;
    outline: none;
    background: transparent;
    padding: 8px 12px 8px 34px;
    font-size: .875rem;
    color: #333;
}
.prop-filter-search-input::placeholder { color: #b0bec5; }

/* Vertical divider */
.prop-filter-divider {
    width: 1px;
    height: 28px;
    background: rgba(29,51,84,.1);
    margin: 0 12px;
    flex-shrink: 0;
}

/* Select wrapper */
.prop-filter-select-wrap {
    position: relative;
    display: flex;
    align-items: center;
    flex: 0 0 auto;
    min-width: 150px;
}
.prop-filter-sel-icon {
    position: absolute;
    left: 10px;
    color: #94a3b8;
    font-size: .85rem;
    pointer-events: none;
    z-index: 1;
}
.prop-filter-chevron {
    position: absolute;
    right: 8px;
    color: #94a3b8;
    font-size: .75rem;
    pointer-events: none;
}
.prop-filter-select {
    border: none;
    outline: none;
    background: transparent;
    padding: 8px 28px 8px 30px;
    font-size: .875rem;
    color: #444;
    appearance: none;
    -webkit-appearance: none;
    cursor: pointer;
    width: 100%;
}
.prop-filter-select:focus { background: rgba(29,51,84,.04); border-radius: 8px; }

/* Results count */
.prop-filter-count {
    margin-left: auto;
    padding-left: 12px;
    flex-shrink: 0;
}
#propertyCount {
    display: inline-block;
    background: rgba(29,51,84,.07);
    color: #1d3354;
    font-size: .72rem;
    font-weight: 700;
    padding: 4px 12px;
    border-radius: 20px;
    letter-spacing: .03em;
    white-space: nowrap;
}

/* ── Property Cards ──────────────────────────────────────── */
.prop-card {
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 12px;
    overflow: hidden;
    transition: box-shadow .2s, transform .2s;
    background: #fff;
    height: 100%;
}
.prop-card:hover {
    box-shadow: 0 6px 24px rgba(29,51,84,.12);
    transform: translateY(-2px);
}
.prop-card-img {
    height: 175px;
    object-fit: cover;
    width: 100%;
    background: #f0f4f8;
}
.prop-card-img-placeholder {
    height: 175px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e8edf5 0%, #d4dde9 100%);
    color: #8fa3bf;
    font-size: 3rem;
}
.prop-stat-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}
.prop-stat-total  { background: #e8edf5; color: #1d3354; }
.prop-stat-occ    { background: #fff3cd; color: #856404; }
.prop-stat-vac    { background: #d1fae5; color: #065f46; }
.prop-type-badge  { font-size: 0.7rem; font-weight: 600; letter-spacing:.3px; }
.prop-card-actions { border-top: 1px solid rgba(0,0,0,.06); }
</style>
