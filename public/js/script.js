
document.addEventListener('DOMContentLoaded', function () {
    let logoImg = document.querySelector('.logo-img');
    if (logoImg) {
        let currentSrc = logoImg.src;
        let pathParts = currentSrc.split('/');
        pathParts.pop();
        let imageBasePath = pathParts.join('/') + '/';

        if (settings.theme === 'light') {
            logoImg.src = imageBasePath + 'logo.png';
        } else {
            logoImg.src = imageBasePath + 'logo-white.png';
        }
    }

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