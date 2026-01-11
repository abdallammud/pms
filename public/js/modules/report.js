$(document).ready(function () {
    // Report selection handler
    $('.report-item').on('click', function (e) {
        e.preventDefault();

        // UI updates
        $('.report-item').removeClass('active');
        $(this).addClass('active');

        const reportType = $(this).data('report');
        const reportTitle = $(this).text().trim();

        $('#report_type').val(reportType);
        $('#selected-report-title').text(reportTitle + ' Filters');

        // Show filter card, hide empty state
        $('#report-empty-state').hide();
        $('#filter-card').fadeIn();

        // Handle dynamic filters based on report type
        renderDynamicFilters(reportType);
    });

    function renderDynamicFilters(type) {
        const container = $('#dynamic-filters');
        container.empty();

        switch (type) {
            case 'rent_collection':
            case 'outstanding_balance':
            case 'unit_occupancy':
            case 'maintenance_report':
            case 'maintenance_expense':
            case 'income_expense':
                // Add property filter
                container.append(`
                    <div class="col-lg-3 mb-3">
                        <label>Property (Optional)</label>
                        <select name="property_id" class="form-control select2">
                            <option value="">All Properties</option>
                            <!-- Options will be populated -->
                        </select>
                    </div>
                `);
                break;

            case 'tenant_report':
                container.append(`
                    <div class="col-lg-3 mb-3">
                        <label>Status</label>
                        <select name="tenant_status" class="form-control select2">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="all">All</option>
                        </select>
                    </div>
                `);
                break;
        }

        // Re-init select2 and datepicker if any
        if ($.fn.select2) {
            $('.select2').select2({
                theme: 'bootstrap4'
            });
        }
        if ($.fn.datepicker) {
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
        }

        // Populate property dropdown if it exists
        if ($('select[name="property_id"]').length > 0) {
            fetchPropertiesForFilter();
        }
    }

    function fetchPropertiesForFilter() {
        $.ajax({
            url: 'app/property_controller.php?action=get_all_properties',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                const select = $('select[name="property_id"]');
                select.empty().append('<option value="">All Properties</option>');
                if (response && response.length > 0) {
                    response.forEach(prop => {
                        select.append(`<option value="${prop.id}">${prop.name}</option>`);
                    });
                }
            }
        });
    }

    // Auto-select first report on load
    $('.report-item').first().click();
});
