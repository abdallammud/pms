
document.addEventListener('DOMContentLoaded', function () {
    tinymce.init({
        selector: '.tinymce',
        height: 260
    });

    // PIKADAY ON ALL datepicker inouts
    document.querySelectorAll('.datepicker').forEach(function (input) {
        let picker = new Pikaday({
            field: input,
            format: 'YYYY-MM-DD',
            toString: function (date) {
                return date.toISOString().split('T')[0];
            },
            parse: function (dateString) {
                return new Date(dateString);
            }
        });
    });
});