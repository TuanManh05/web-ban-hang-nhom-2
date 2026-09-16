(function () {
    'use strict';

    var MIN_CHARS = 2;
    var DEBOUNCE_MS = 350;

    var form = document.querySelector('.header-search');
    if (!form) {
        return;
    }

    var input = form.querySelector('input[name="q"]');
    var panel = form.querySelector('.search-suggest-panel');
    if (!input || !panel) {
        return;
    }

    var suggestUrl = form.getAttribute('data-suggest-url');
    if (!suggestUrl) {
        return;
    }

    var debounceTimer = null;
    var activeController = null;
    var requestSeq = 0;

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = String(text == null ? '' : text);
        return div.innerHTML;
    }

    function openPanel() {
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function closePanel() {
        panel.hidden = true;
        panel.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
    }

    function renderState(message, isError) {
        panel.innerHTML = '<div class="search-suggest-state' + (isError ? ' search-suggest-error' : '') + '">'
            + escapeHtml(message) + '</div>';
        openPanel();
    }

    function renderResults(items, keyword) {
        if (!items || items.length === 0) {
            renderState('Không tìm thấy sản phẩm phù hợp với "' + keyword + '".', false);
            return;
        }

        var html = items.map(function (item) {
            return ''
                + '<a class="search-suggest-item" href="' + escapeHtml(item.url) + '" role="option">'
                + '<img src="' + escapeHtml(item.image) + '" alt="" loading="lazy">'
                + '<span class="search-suggest-info">'
                + '<span class="search-suggest-name">' + escapeHtml(item.name) + '</span>'
                + '<span class="search-suggest-price">' + escapeHtml(item.price_formatted) + '</span>'
                + '</span>'
                + '</a>';
        }).join('');

        panel.innerHTML = html;
        openPanel();
    }

    function fetchSuggestions(keyword) {
        // Huỷ request cũ (nếu còn) để tránh kết quả trả về không đúng thứ tự
        if (activeController && typeof activeController.abort === 'function') {
            activeController.abort();
        }

        var currentSeq = ++requestSeq;
        var hasAbortController = typeof window.AbortController === 'function';
        activeController = hasAbortController ? new AbortController() : null;

        renderState('Đang tìm kiếm…', false);

        var fetchOptions = { headers: { 'Accept': 'application/json' } };
        if (activeController) {
            fetchOptions.signal = activeController.signal;
        }

        fetch(suggestUrl + '?q=' + encodeURIComponent(keyword), fetchOptions)
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('HTTP ' + response.status);
                }
                return response.json();
            })
            .then(function (payload) {
                // Bỏ qua nếu người dùng đã gõ tiếp và có request mới hơn
                if (currentSeq !== requestSeq) {
                    return;
                }
                if (!payload || payload.success !== true) {
                    throw new Error('Payload không hợp lệ');
                }
                renderResults(payload.data, keyword);
            })
            .catch(function (error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                if (currentSeq !== requestSeq) {
                    return;
                }
                // Lỗi API không được làm hỏng thanh tìm kiếm: chỉ hiển thị
                // thông báo nhẹ nhàng, ô nhập và nút tìm kiếm vẫn hoạt động bình thường.
                renderState('Không thể tải gợi ý lúc này. Bạn vẫn có thể nhấn "Tìm kiếm".', true);
            });
    }

    input.addEventListener('input', function () {
        var keyword = input.value.trim();

        window.clearTimeout(debounceTimer);

        if (keyword.length < MIN_CHARS) {
            if (activeController && typeof activeController.abort === 'function') {
                activeController.abort();
            }
            requestSeq++;
            closePanel();
            return;
        }

        debounceTimer = window.setTimeout(function () {
            fetchSuggestions(keyword);
        }, DEBOUNCE_MS);
    });

    input.addEventListener('focus', function () {
        if (input.value.trim().length >= MIN_CHARS && panel.innerHTML.trim() !== '') {
            openPanel();
        }
    });

    input.addEventListener('keydown', function (evt) {
        if (evt.key === 'Escape') {
            closePanel();
        }
    });

    // Đóng khung gợi ý khi bấm ra ngoài ô tìm kiếm
    document.addEventListener('click', function (evt) {
        if (!form.contains(evt.target)) {
            closePanel();
        }
    });

    // Đóng khung gợi ý khi mất focus khỏi toàn bộ form (vd. Tab ra ngoài),
    // trễ một chút để vẫn kịp xử lý click vào gợi ý trước khi đóng.
    form.addEventListener('focusout', function () {
        window.setTimeout(function () {
            if (!form.contains(document.activeElement)) {
                closePanel();
            }
        }, 120);
    });
})();
