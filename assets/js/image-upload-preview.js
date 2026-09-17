(function () {
    'use strict';

    document.querySelectorAll('[data-image-input]').forEach(function (input) {
        var preview = document.getElementById(input.dataset.previewTarget);
        if (!preview) {
            return;
        }

        input.addEventListener('change', function () {
            preview.replaceChildren();
            Array.from(input.files || []).slice(0, 5).forEach(function (file) {
                var figure = document.createElement('figure');
                var image = document.createElement('img');
                var caption = document.createElement('figcaption');

                image.alt = 'Xem trước ' + file.name;
                image.src = URL.createObjectURL(file);
                image.addEventListener('load', function () {
                    URL.revokeObjectURL(image.src);
                }, { once: true });
                caption.textContent = file.name;
                figure.append(image, caption);
                preview.appendChild(figure);
            });
        });
    });
})();
