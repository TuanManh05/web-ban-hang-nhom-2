(() => {
    const form = document.getElementById('catalogFilters');
    if (!form) {
        return;
    }

    const category = form.elements.namedItem('category_id');
    const sort = form.elements.namedItem('sort');

    const syncWithServer = () => {
        if (category instanceof HTMLSelectElement) {
            category.value = form.dataset.categoryId ?? '';
        }
        if (sort instanceof HTMLSelectElement) {
            sort.value = form.dataset.sort ?? '';
        }
    };

    window.addEventListener('pageshow', syncWithServer);
    syncWithServer();

})();
