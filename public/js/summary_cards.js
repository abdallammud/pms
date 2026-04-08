/**
 * Summary Cards JS Helper
 */

function loadSummaryStats(url, containerSelector) {
    const $container = $(containerSelector);
    if (!$container.length) return;

    // Show loading placeholders if needed, or just fade out current values
    $container.find('.card-stat-value').css('opacity', '0.5');

    $.ajax({
        url: (typeof base_url !== 'undefined' ? base_url : '') + (url.startsWith('/') ? '' : '/') + url,
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            if (res.error) {
                console.error("Stats Error:", res.msg);
                return;
            }

            // Loop through the data and update values
            // The API should return an object like { total_units: 50, occupied: 45, ... }
            // We map these to the .card-stat-value elements by order or data-key
            const $values = $container.find('.card-stat-value');
            if (res.stats && Array.isArray(res.stats)) {
                res.stats.forEach((stat, index) => {
                    if ($values[index]) {
                        // Animate counter if possible, or just update
                        const $val = $($values[index]);
                        $val.fadeOut(100, function () {
                            $(this).text(stat).fadeIn(100).css('opacity', '1');
                        });
                    }
                });
            }
        },
        error: function () {
            console.error("Failed to load stats from " + url);
        }
    });
}
