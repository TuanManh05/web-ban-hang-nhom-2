(function () {
    'use strict';

    document.querySelectorAll('[data-editor-for]').forEach(function (editor) {
        var field = document.getElementById(editor.dataset.editorFor);
        var toolbar = document.querySelector('[data-editor-toolbar="' + editor.id + '"]');
        var form = editor.closest('form');

        if (!field || !toolbar || !form) {
            return;
        }

        toolbar.addEventListener('click', function (event) {
            var button = event.target.closest('[data-command]');
            if (!button) {
                return;
            }

            event.preventDefault();
            editor.focus();
            document.execCommand(button.dataset.command, false, button.dataset.value || null);
        });

        editor.addEventListener('paste', function (event) {
            event.preventDefault();
            var text = (event.clipboardData || window.clipboardData).getData('text/plain');
            document.execCommand('insertText', false, text);
        });

        form.addEventListener('submit', function () {
            field.value = editor.innerHTML.trim();
        });
    });
})();
