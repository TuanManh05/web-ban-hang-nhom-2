document.addEventListener('DOMContentLoaded', () => {
    const mainImage = document.getElementById('productMainImage');
    const thumbnails = document.querySelectorAll('[data-gallery-image]');

    if (!mainImage || thumbnails.length === 0) {
        return;
    }

    thumbnails.forEach((thumbnail) => {
        thumbnail.addEventListener('click', () => {
            mainImage.src = thumbnail.dataset.galleryImage;
            thumbnails.forEach((item) => {
                item.classList.remove('active');
                item.setAttribute('aria-pressed', 'false');
            });
            thumbnail.classList.add('active');
            thumbnail.setAttribute('aria-pressed', 'true');
        });
    });
});
