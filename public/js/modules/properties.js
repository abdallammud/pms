// ================================================================
// Properties Module JS
// ================================================================

var epCurrentId = null;  // property ID being edited
var euCurrentId = null;  // unit ID being edited
var propAllData = [];    // full list from server
var propPage = 1;
var propPerPage = 12;

document.addEventListener('DOMContentLoaded', function () {
    loadPropertyTypes();
    loadManagers();
    loadPropertiesForUnits();
    loadUnitTypesDropdown();
    loadAmenitiesChecklist();

    // Properties listing page
    if (document.getElementById('propertiesGrid')) {
        loadPropertiesGrid();
        document.getElementById('propertySearch').addEventListener('input', debounce(filterProperties, 300));
        document.getElementById('propertyTypeFilter').addEventListener('change', filterProperties);
        document.getElementById('propertyStatusFilter').addEventListener('change', filterProperties);
    }

    // Units listing page
    if (document.getElementById('unitsTable')) {
        loadUnits();
    }

    // ── Add Property ────────────────────────────────────────────
    $(document).off('click', '#savePropertyBtn').on('click', '#savePropertyBtn', function () {
        var form = document.getElementById('addPropertyForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: base_url + '/app/property_controller.php?action=save_property',
            type: 'POST', data: new FormData(form), processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#addPropertyModal').modal('hide');
                form.reset();
                loadPropertiesGrid();
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Update Unit (edit modal info tab) ───────────────────────
    $(document).off('click', '#updateUnitBtn').on('click', '#updateUnitBtn', function () {
        var form = document.getElementById('editUnitForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        var status = document.getElementById('eu_status').value;
        var isListed = document.getElementById('eu_is_listed').checked;
        if (status === 'occupied' && isListed) {
            swal('Validation Error', 'A unit cannot be occupied and listed on the website at the same time.', 'warning');
            return;
        }

        var fd = new FormData(form);
        if (isListed) fd.set('is_listed', '1'); else fd.set('is_listed', '0');
        document.querySelectorAll('#euAmenitiesChecklist input[name="amenity_ids[]"]:checked').forEach(function (cb) {
            fd.append('amenity_ids[]', cb.value);
        });

        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: base_url + '/app/property_controller.php?action=save_unit',
            type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#editUnitModal').modal('hide');
                if ($.fn.DataTable.isDataTable('#unitsTable')) { $('#unitsTable').DataTable().ajax.reload(); }
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Edit Property (basic info tab) ──────────────────────────
    $(document).off('click', '#updatePropertyBtn').on('click', '#updatePropertyBtn', function () {
        var form = document.getElementById('editPropertyForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }
        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: base_url + '/app/property_controller.php?action=save_property',
            type: 'POST', data: new FormData(form), processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#editPropertyModal').modal('hide');
                loadPropertiesGrid();
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Add Unit ─────────────────────────────────────────────────
    $(document).off('click', '#saveUnitBtn').on('click', '#saveUnitBtn', function () {
        var form = document.getElementById('addUnitForm');
        if (!form.checkValidity()) { form.reportValidity(); return; }

        var status = document.getElementById('unit_status').value;
        var isListed = document.getElementById('unit_is_listed') && document.getElementById('unit_is_listed').checked;
        if (status === 'occupied' && isListed) {
            swal('Validation Error', 'A unit cannot be occupied and listed on the website at the same time.', 'warning');
            return;
        }

        // Sync hidden unit_type from selected type ID
        var typeSelect = document.getElementById('unit_type_id');
        if (typeSelect && typeSelect.selectedIndex > 0) {
            var selOpt = typeSelect.options[typeSelect.selectedIndex];
            document.getElementById('unit_type_hidden').value = selOpt.dataset.name || selOpt.textContent;
        }

        var fd = new FormData(form);
        fd.set('is_listed', isListed ? '1' : '0');

        var $btn = $(this).prop('disabled', true);
        $.ajax({
            url: base_url + '/app/property_controller.php?action=save_unit',
            type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                if (r.error) { swal('Error', r.msg, 'error'); return; }
                toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                $('#addUnitModal').modal('hide');
                form.reset();
                if ($.fn.DataTable.isDataTable('#unitsTable')) {
                    $('#unitsTable').DataTable().ajax.reload();
                }
            },
            error: function () { swal('Error', 'An unexpected error occurred.', 'error'); },
            complete: function () { $btn.prop('disabled', false); }
        });
    });

    // ── Modal resets ─────────────────────────────────────────────
    $(document).on('hidden.bs.modal', '#addPropertyModal', function () {
        document.getElementById('addPropertyForm').reset();
        $('#property_id').val('');
        $('#property_type_select').val('').selectpicker('refresh');
        $('#manager_select').val('').selectpicker('refresh');
    });

    $(document).on('hidden.bs.modal', '#editPropertyModal', function () {
        document.getElementById('editPropertyForm').reset();
        $('#ep_property_id').val('');
        $('#ep_type_id').val('').selectpicker('refresh');
        $('#ep_manager').val('').selectpicker('refresh');
        // Reset to first tab
        var tab = document.getElementById('ep-info-tab');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
        // Clear images gallery
        document.getElementById('epImagesGallery').innerHTML =
            '<div class="col-12 text-center text-muted py-3" id="epImagesPlaceholder"><i class="bi bi-images opacity-50"></i> No images yet</div>';
        epCurrentId = null;
    });

    $(document).on('hidden.bs.modal', '#addUnitModal', function () {
        document.getElementById('addUnitForm').reset();
        $('#unit_id').val('');
        $('#unit_property_select').val('').selectpicker('refresh');
        $('#addUnitLabel').html('<i class="bi bi-door-open me-2"></i>Add Unit');
        $('#saveUnitBtn').html('<i class="bi bi-save me-1"></i>Save Unit');
        $('#unit_listed_warning').addClass('d-none');
    });

    $(document).on('hidden.bs.modal', '#editUnitModal', function () {
        document.getElementById('editUnitForm').reset();
        $('#eu_unit_id').val('');
        $('#eu_property_id').val('').selectpicker('refresh');
        var tab = document.getElementById('eu-info-tab');
        if (tab) bootstrap.Tab.getOrCreateInstance(tab).show();
        document.getElementById('euImagesGallery').innerHTML =
            '<div class="col-12 text-center text-muted py-3" id="euImagesPlaceholder"><i class="bi bi-images opacity-50"></i> No images yet</div>';
        $('#eu_listed_warning').addClass('d-none');
        euCurrentId = null;
    });

    // ── Image upload (Edit modal Images tab) ─────────────────────
    initImageUpload();
    initUnitImageUpload();
});

// ================================================================
// Properties Card Grid
// ================================================================

function loadPropertiesGrid() {
    var grid = document.getElementById('propertiesGrid');
    var empty = document.getElementById('propertiesEmpty');
    var counter = document.getElementById('propertyCount');
    if (!grid) return;
    grid.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
    if (empty) empty.classList.add('d-none');

    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_properties',
        type: 'POST',
        data: { draw: 1, start: 0, length: 500, search: { value: '' } },
        dataType: 'json',
        success: function (res) {
            propAllData = res.data || [];
            if (counter) counter.textContent = propAllData.length + ' propert' + (propAllData.length === 1 ? 'y' : 'ies');
            filterProperties();
        },
        error: function () {
            grid.innerHTML = '<div class="col-12"><div class="alert alert-danger">Failed to load properties.</div></div>';
        }
    });
}

function filterProperties() {
    var search = (document.getElementById('propertySearch')?.value || '').toLowerCase();
    var typeEl = document.getElementById('propertyTypeFilter');
    var statEl = document.getElementById('propertyStatusFilter');
    var typeVal = typeEl ? typeEl.value : '';
    var statVal = statEl ? statEl.value : '';

    var filtered = propAllData.filter(function (p) {
        var matchSearch = !search ||
            (p.name || '').toLowerCase().includes(search) ||
            (p.city || '').toLowerCase().includes(search) ||
            (p.type || '').toLowerCase().includes(search) ||
            (p.region || '').toLowerCase().includes(search) ||
            (p.district || '').toLowerCase().includes(search);

        var matchType = !typeVal || (p.type || '') === typeVal;

        var matchStat = true;
        if (statVal === 'occupied') matchStat = p.occupied_units > 0;
        if (statVal === 'vacant') matchStat = p.vacant_units > 0;

        return matchSearch && matchType && matchStat;
    });

    renderPropertiesGrid(filtered);
}

function renderPropertiesGrid(properties) {
    var grid = document.getElementById('propertiesGrid');
    var empty = document.getElementById('propertiesEmpty');
    if (!grid) return;

    if (properties.length === 0) {
        grid.innerHTML = '';
        if (empty) empty.classList.remove('d-none');
        return;
    }
    if (empty) empty.classList.add('d-none');

    grid.innerHTML = properties.map(function (p) {
        var imgHtml = p.cover_image
            ? '<img src="' + base_url + '/' + p.cover_image + '" class="prop-card-img" alt="">'
            : '<div class="prop-card-img-placeholder"><i class="bi bi-building"></i></div>';

        var location = [p.city, p.district, p.region].filter(function (x) { return x && x.trim(); }).join(', ');

        var occupancyPct = p.units > 0 ? Math.round((p.occupied_units / p.units) * 100) : 0;
        var barColor = occupancyPct >= 80 ? 'bg-warning' : (occupancyPct >= 50 ? 'bg-primary' : 'bg-success');

        return '<div class="col-sm-6 col-lg-4 col-xl-3" style="cursor: pointer; margin-bottom: 10px;">' +
            '<div class="prop-card">' +
            '<a href="' + base_url + '/property/' + p.id + '" class="text-decoration-none text-dark">' +
            imgHtml +
            '<div class="p-3">' +
            (p.type ? '<span class="badge bg-primary-subtle text-primary prop-type-badge mb-1">' + p.type + '</span>' : '') +
            '<h6 class="fw-bold mb-1 lh-sm">' + p.name + '</h6>' +
            (location ? '<div class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>' + location + '</div>' : '') +
            '<div class="d-flex flex-wrap gap-1 mb-2">' +
            '<span class="prop-stat-pill prop-stat-total"><i class="bi bi-door-open"></i>' + p.units + ' Units</span>' +
            '<span class="prop-stat-pill prop-stat-occ"><i class="bi bi-person-fill"></i>' + p.occupied_units + ' Occ.</span>' +
            '<span class="prop-stat-pill prop-stat-vac"><i class="bi bi-door-closed"></i>' + p.vacant_units + ' Vac.</span>' +
            '</div>' +
            (p.units > 0 ? '<div class="progress mb-1" style="height:4px;" title="' + occupancyPct + '% occupancy"><div class="progress-bar ' + barColor + '" style="width:' + occupancyPct + '%"></div></div>' : '') +
            (p.manager_name && p.manager_name !== 'N/A' ? '<div class="text-muted small mt-2"><i class="bi bi-person-gear me-1"></i>' + p.manager_name + '</div>' : '') +
            '</div>' +
            '</a>' +
            '<div class="prop-card-actions d-flex gap-1 px-3 py-2">' +
            '<a href="' + base_url + '/property/' + p.id + '" class="btn btn-sm btn-outline-secondary flex-fill"><i class="bi bi-eye me-1"></i>View</a>' +
            '<button class="btn btn-sm btn-primary flex-fill" onclick="editProperty(' + p.id + ')"><i class="bi bi-pencil me-1"></i>Edit</button>' +
            '<button class="btn btn-sm btn-outline-danger" onclick="deleteProperty(' + p.id + ')"><i class="bi bi-trash"></i></button>' +
            '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

// ================================================================
// Dropdowns
// ================================================================

function loadPropertyTypes() {
    $.ajax({
        url: base_url + '/app/property_type_controller.php?action=get_active_types',
        type: 'GET', dataType: 'json',
        success: function (data) {
            // Populate type filter on listing page
            var filterSel = document.getElementById('propertyTypeFilter');
            if (filterSel) {
                filterSel.querySelectorAll('option:not(:first-child)').forEach(function (o) { o.remove(); });
                (data || []).forEach(function (t) {
                    var o = document.createElement('option');
                    o.value = t.type_name; o.textContent = t.type_name;
                    filterSel.appendChild(o);
                });
            }
            // Populate Add modal select
            ['#property_type_select', '#ep_type_id'].forEach(function (sel) {
                var el = $(sel);
                if (!el.length) return;
                el.find('option:not(:first)').remove();
                (data || []).forEach(function (t) {
                    el.append('<option value="' + t.id + '">' + t.type_name + '</option>');
                });
                el.selectpicker('refresh');
            });
        }
    });
}

function loadManagers() {
    $.ajax({
        url: base_url + '/app/user_controller.php?action=get_managers',
        type: 'GET', dataType: 'json',
        success: function (data) {
            ['#manager_select', '#ep_manager'].forEach(function (sel) {
                var el = $(sel);
                if (!el.length) return;
                el.find('option:not(:first)').remove();
                (data || []).forEach(function (u) {
                    el.append('<option value="' + u.id + '">' + u.name + '</option>');
                });
                el.selectpicker('refresh');
            });
        }
    });
}

function loadPropertiesForUnits() {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_all_properties',
        type: 'GET', dataType: 'json',
        success: function (data) {
            var select = $('#unit_property_select');
            if (!select.length) return;
            select.find('option:not(:first)').remove();
            (data || []).forEach(function (p) {
                select.append('<option value="' + p.id + '">' + p.name + '</option>');
            });
            select.selectpicker('refresh');
        }
    });
}

// ================================================================
// Edit Property
// ================================================================

function editProperty(id) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_property&id=' + id,
        type: 'GET', dataType: 'json',
        success: function (data) {
            epCurrentId = data.id;
            $('#ep_property_id').val(data.id);
            $('#ep_name').val(data.name);
            $('#ep_type_id').val(data.type_id).selectpicker('refresh');
            $('#ep_address').val(data.address);
            $('#ep_city').val(data.city);
            $('#ep_region').val(data.region || '');
            $('#ep_district').val(data.district || '');
            $('#ep_owner').val(data.owner_name);
            $('#ep_manager').val(data.manager_id).selectpicker('refresh');
            $('#ep_description').val(data.description);
            $('#editPropertyModal').modal('show');
        },
        error: function () { swal('Error', 'Could not fetch property data.', 'error'); }
    });
}

function deleteProperty(id) {
    swal({ title: 'Are you sure?', text: "This will permanently delete the property.", icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_property',
                type: 'POST', data: { id: id }, dataType: 'json',
                success: function (r) {
                    if (r.error) { swal('Error', r.msg, 'error'); return; }
                    toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    loadPropertiesGrid();
                },
                error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
            });
        });
}

// ================================================================
// Property Images (Edit modal – Images tab)
// ================================================================

function loadPropertyImages(propertyId) {
    if (!propertyId) return;
    var gallery = document.getElementById('epImagesGallery');
    if (!gallery) return;
    gallery.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_property_images&property_id=' + propertyId,
        type: 'GET', dataType: 'json',
        success: function (res) {
            renderImageGallery(res.data || []);
        },
        error: function () {
            gallery.innerHTML = '<div class="col-12 text-danger small">Failed to load images.</div>';
        }
    });
}

function renderImageGallery(images) {
    var gallery = document.getElementById('epImagesGallery');
    if (!gallery) return;

    if (images.length === 0) {
        gallery.innerHTML = '<div class="col-12 text-center text-muted py-3" id="epImagesPlaceholder"><i class="bi bi-images opacity-50"></i> No images yet. Upload some above.</div>';
        return;
    }

    gallery.innerHTML = images.map(function (img) {
        return '<div class="col-6 col-md-4 col-lg-3" id="imgWrap_' + img.id + '">' +
            '<div class="img-thumb-wrap ' + (img.is_cover == 1 ? 'is-cover' : '') + '">' +
            (img.is_cover == 1 ? '<span class="img-cover-badge"><i class="bi bi-star-fill me-1"></i>Cover</span>' : '') +
            '<img src="' + base_url + '/' + img.image_path + '" alt="">' +
            '<div class="img-thumb-actions">' +
            (img.is_cover != 1 ? '<button class="btn btn-xs btn-light py-0 px-1" title="Set as Cover" onclick="setCoverImage(' + img.id + ',' + img.property_id + ')"><i class="bi bi-star text-warning"></i></button>' : '') +
            '<button class="btn btn-xs btn-danger py-0 px-1" title="Delete" onclick="deletePropertyImage(' + img.id + ')"><i class="bi bi-trash"></i></button>' +
            '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

function setCoverImage(imageId, propertyId) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=set_cover_image',
        type: 'POST', data: { id: imageId, property_id: propertyId }, dataType: 'json',
        success: function (r) {
            if (r.error) { swal('Error', r.msg, 'error'); return; }
            loadPropertyImages(propertyId);
        }
    });
}

function deletePropertyImage(imageId) {
    swal({ title: 'Delete image?', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_property_image',
                type: 'POST', data: { id: imageId }, dataType: 'json',
                success: function (r) {
                    if (r.error) { swal('Error', r.msg, 'error'); return; }
                    loadPropertyImages(epCurrentId);
                }
            });
        });
}

function initImageUpload() {
    var input = document.getElementById('epImageInput');
    var dropArea = document.getElementById('epDropArea');
    if (!input || !dropArea) return;

    input.addEventListener('change', function () {
        uploadFiles(Array.from(this.files));
        this.value = '';
    });

    dropArea.addEventListener('dragover', function (e) { e.preventDefault(); this.classList.add('drag-over'); });
    dropArea.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
    dropArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('drag-over');
        uploadFiles(Array.from(e.dataTransfer.files));
    });
}

function uploadFiles(files) {
    if (!files || files.length === 0) return;
    if (!epCurrentId) { swal('Error', 'Property not set.', 'error'); return; }

    var progress = document.getElementById('epUploadProgress');
    var bar = document.getElementById('epProgressBar');
    var label = document.getElementById('epProgressLabel');
    var total = files.length;
    var done = 0;

    if (progress) progress.classList.remove('d-none');

    files.forEach(function (file) {
        var fd = new FormData();
        fd.append('property_id', epCurrentId);
        fd.append('image', file);

        $.ajax({
            url: base_url + '/app/property_controller.php?action=upload_property_image',
            type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                done++;
                if (bar) bar.style.width = Math.round((done / total) * 100) + '%';
                if (label) label.textContent = 'Uploaded ' + done + ' of ' + total;
                if (r.error) { swal('Upload Error', r.msg, 'error'); }
                if (done === total) {
                    setTimeout(function () {
                        if (progress) progress.classList.add('d-none');
                        if (bar) bar.style.width = '0%';
                        loadPropertyImages(epCurrentId);
                    }, 600);
                }
            },
            error: function () {
                done++;
                if (done === total && progress) progress.classList.add('d-none');
            }
        });
    });
}

// ================================================================
// Unit Types Dropdown
// ================================================================

function loadUnitTypesDropdown() {
    $.ajax({
        url: base_url + '/app/unit_type_controller.php?action=get_active_types',
        type: 'GET', dataType: 'json',
        success: function (data) {
            ['#unit_type_id', '#eu_unit_type_id'].forEach(function (sel) {
                var el = document.querySelector(sel);
                if (!el) return;
                while (el.options.length > 1) el.remove(1);
                (data || []).forEach(function (t) {
                    var o = document.createElement('option');
                    o.value = t.id; o.textContent = t.type_name; o.dataset.name = t.type_name;
                    el.appendChild(o);
                });
            });
        }
    });
}

// ================================================================
// Amenities Checklist
// ================================================================

function loadAmenitiesChecklist(selectedIds, containerId) {
    containerId = containerId || 'amenitiesChecklist';
    var container = document.getElementById(containerId);
    if (!container) return;
    container.innerHTML = '<div class="col-12 text-muted small">Loading...</div>';

    $.ajax({
        url: base_url + '/app/amenity_controller.php?action=get_active_amenities',
        type: 'GET', dataType: 'json',
        success: function (data) {
            if (!data || data.length === 0) {
                container.innerHTML = '<div class="col-12 text-muted small">No amenities defined. Add them in Settings → Amenities.</div>';
                return;
            }
            container.innerHTML = data.map(function (a) {
                var checked = selectedIds && selectedIds.indexOf(a.id) !== -1 ? 'checked' : '';
                var icon = a.icon ? '<i class="bi ' + a.icon + ' me-1"></i>' : '';
                return '<div class="col-6 col-md-4">' +
                    '<div class="form-check">' +
                    '<input class="form-check-input" type="checkbox" name="amenity_ids[]" ' +
                    'id="am_' + containerId + '_' + a.id + '" value="' + a.id + '" ' + checked + '>' +
                    '<label class="form-check-label small" for="am_' + containerId + '_' + a.id + '">' +
                    icon + a.name +
                    '</label>' +
                    '</div>' +
                    '</div>';
            }).join('');
        },
        error: function () {
            container.innerHTML = '<div class="col-12 text-muted small">Could not load amenities.</div>';
        }
    });
}

// ================================================================
// Units
// ================================================================

function loadUnits() {
    if ($.fn.DataTable.isDataTable('#unitsTable')) {
        $('#unitsTable').DataTable().destroy();
    }
    $('#unitsTable').DataTable({
        processing: true, serverSide: true, pageLength: 25,
        ajax: { url: base_url + '/app/property_controller.php?action=get_units', type: 'POST' },
        columns: [
            { data: 'unit_number' },
            { data: 'unit_type' },
            { data: 'property_name' },
            { data: 'status' },
            { data: 'actions', orderable: false }
        ],
        order: [[0, 'asc']]
    });
}

function editUnit(id) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_unit&id=' + id,
        type: 'GET', dataType: 'json',
        success: function (data) {
            if (!data) { swal('Error', 'Could not fetch unit data.', 'error'); return; }
            euCurrentId = data.id;
            $('#eu_unit_id').val(data.id);
            $('#eu_property_id').val(data.property_id).selectpicker('refresh');
            $('#eu_unit_number').val(data.unit_number);
            $('#eu_unit_type_id').val(data.unit_type_id || '');
            $('#eu_unit_type_hidden').val(data.unit_type || '');
            $('#eu_size').val(data.size_sqft);
            $('#eu_floor').val(data.floor_number !== null ? data.floor_number : '');
            $('#eu_rooms').val(data.room_count !== null ? data.room_count : '');
            $('#eu_rent').val(data.rent_amount);
            $('#eu_status').val(data.status);
            document.getElementById('eu_is_listed').checked = data.is_listed == 1;

            // Load amenities with selected IDs
            loadAmenitiesChecklist(data.amenity_ids || [], 'euAmenitiesChecklist');

            // Populate the eu_property_id with available properties
            loadPropertiesIntoSelect('#eu_property_id', data.property_id);

            $('#editUnitModal').modal('show');
        },
        error: function () { swal('Error', 'Could not fetch unit data.', 'error'); }
    });
}

function loadPropertiesIntoSelect(selector, selectedId) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_all_properties',
        type: 'GET', dataType: 'json',
        success: function (data) {
            var el = $(selector);
            el.find('option:not(:first)').remove();
            (data || []).forEach(function (p) {
                el.append('<option value="' + p.id + '">' + p.name + '</option>');
            });
            el.val(selectedId).selectpicker('refresh');
        }
    });
}

function deleteUnit(id) {
    swal({ title: 'Are you sure?', text: "You won't be able to revert this!", icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_unit',
                type: 'POST', data: { id: id }, dataType: 'json',
                success: function (r) {
                    if (r.error) { swal('Error', r.msg, 'error'); return; }
                    toaster.success(r.msg, 'Success', { top: '10%', right: '20px', hide: true, duration: 1500 });
                    if ($.fn.DataTable.isDataTable('#unitsTable')) {
                        $('#unitsTable').DataTable().ajax.reload();
                    }
                },
                error: function () { swal('Error', 'An unexpected error occurred.', 'error'); }
            });
        });
}

// ================================================================
// Unit Images (Edit Unit Modal)
// ================================================================

function loadUnitImages(unitId) {
    if (!unitId) return;
    var gallery = document.getElementById('euImagesGallery');
    if (!gallery) return;
    gallery.innerHTML = '<div class="col-12 text-center py-3"><div class="spinner-border spinner-border-sm text-primary"></div></div>';

    $.ajax({
        url: base_url + '/app/property_controller.php?action=get_unit_images&unit_id=' + unitId,
        type: 'GET', dataType: 'json',
        success: function (res) { renderUnitImageGallery(res.data || []); },
        error: function () {
            gallery.innerHTML = '<div class="col-12 text-danger small">Failed to load images.</div>';
        }
    });
}

function renderUnitImageGallery(images) {
    var gallery = document.getElementById('euImagesGallery');
    if (!gallery) return;

    if (images.length === 0) {
        gallery.innerHTML = '<div class="col-12 text-center text-muted py-3" id="euImagesPlaceholder">' +
            '<i class="bi bi-images opacity-50"></i> No images yet. Upload some above.</div>';
        return;
    }

    gallery.innerHTML = images.map(function (img) {
        return '<div class="col-6 col-md-4 col-lg-3" id="uImgWrap_' + img.id + '">' +
            '<div class="img-thumb-wrap ' + (img.is_cover == 1 ? 'is-cover' : '') + '">' +
            (img.is_cover == 1 ? '<span class="img-cover-badge"><i class="bi bi-star-fill me-1"></i>Cover</span>' : '') +
            '<img src="' + base_url + '/' + img.image_path + '" alt="">' +
            '<div class="img-thumb-actions">' +
            (img.is_cover != 1 ? '<button class="btn btn-xs btn-light py-0 px-1" title="Set Cover" onclick="setUnitCoverImage(' + img.id + ',' + img.unit_id + ')"><i class="bi bi-star text-warning"></i></button>' : '') +
            '<button class="btn btn-xs btn-danger py-0 px-1" title="Delete" onclick="deleteUnitImage(' + img.id + ')"><i class="bi bi-trash"></i></button>' +
            '</div>' +
            '</div>' +
            '</div>';
    }).join('');
}

function setUnitCoverImage(imageId, unitId) {
    $.ajax({
        url: base_url + '/app/property_controller.php?action=set_unit_cover_image',
        type: 'POST', data: { id: imageId, unit_id: unitId }, dataType: 'json',
        success: function (r) {
            if (r.error) { swal('Error', r.msg, 'error'); return; }
            loadUnitImages(unitId);
        }
    });
}

function deleteUnitImage(imageId) {
    swal({ title: 'Delete image?', icon: 'warning', buttons: true, dangerMode: true })
        .then(function (ok) {
            if (!ok) return;
            $.ajax({
                url: base_url + '/app/property_controller.php?action=delete_unit_image',
                type: 'POST', data: { id: imageId }, dataType: 'json',
                success: function (r) {
                    if (r.error) { swal('Error', r.msg, 'error'); return; }
                    loadUnitImages(euCurrentId);
                }
            });
        });
}

function initUnitImageUpload() {
    var input = document.getElementById('euImageInput');
    var dropArea = document.getElementById('euDropArea');
    if (!input || !dropArea) return;

    input.addEventListener('change', function () {
        uploadUnitFiles(Array.from(this.files));
        this.value = '';
    });
    dropArea.addEventListener('dragover', function (e) { e.preventDefault(); this.classList.add('drag-over'); });
    dropArea.addEventListener('dragleave', function () { this.classList.remove('drag-over'); });
    dropArea.addEventListener('drop', function (e) {
        e.preventDefault(); this.classList.remove('drag-over');
        uploadUnitFiles(Array.from(e.dataTransfer.files));
    });
}

function uploadUnitFiles(files) {
    if (!files || files.length === 0) return;
    if (!euCurrentId) { swal('Error', 'Unit not set.', 'error'); return; }

    var progress = document.getElementById('euUploadProgress');
    var bar = document.getElementById('euProgressBar');
    var label = document.getElementById('euProgressLabel');
    var total = files.length;
    var done = 0;
    if (progress) progress.classList.remove('d-none');

    files.forEach(function (file) {
        var fd = new FormData();
        fd.append('unit_id', euCurrentId);
        fd.append('image', file);
        $.ajax({
            url: base_url + '/app/property_controller.php?action=upload_unit_image',
            type: 'POST', data: fd, processData: false, contentType: false, dataType: 'json',
            success: function (r) {
                done++;
                if (bar) bar.style.width = Math.round((done / total) * 100) + '%';
                if (label) label.textContent = 'Uploaded ' + done + ' of ' + total;
                if (r.error) { swal('Upload Error', r.msg, 'error'); }
                if (done === total) {
                    setTimeout(function () {
                        if (progress) progress.classList.add('d-none');
                        if (bar) bar.style.width = '0%';
                        loadUnitImages(euCurrentId);
                    }, 600);
                }
            },
            error: function () {
                done++;
                if (done === total && progress) progress.classList.add('d-none');
            }
        });
    });
}

// is_listed / occupied mutual exclusion validators
function validateUnitListedStatus() {
    var status = document.getElementById('unit_status').value;
    var isListed = document.getElementById('unit_is_listed').checked;
    var warning = document.getElementById('unit_listed_warning');
    if (warning) {
        if (isListed && status === 'occupied') {
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    }
}

function validateEuListedStatus() {
    var status = document.getElementById('eu_status').value;
    var isListed = document.getElementById('eu_is_listed').checked;
    var warning = document.getElementById('eu_listed_warning');
    if (warning) {
        if (isListed && status === 'occupied') {
            warning.classList.remove('d-none');
        } else {
            warning.classList.add('d-none');
        }
    }
}

// ================================================================
// Utilities
// ================================================================

function debounce(fn, delay) {
    var t;
    return function () {
        var ctx = this, args = arguments;
        clearTimeout(t);
        t = setTimeout(function () { fn.apply(ctx, args); }, delay);
    };
}
