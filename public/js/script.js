
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
});