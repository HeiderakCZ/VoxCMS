(function () {
    'use strict';

    function initThemeToggle() {
        var root = document.documentElement;
        var storageKey = 'dtiskari-color-theme';
        var button = document.querySelector('.theme-toggle');

        if (!button) {
            return;
        }

        function setTheme(theme) {
            var isDark = theme === 'dark';
            root.setAttribute('data-theme', isDark ? 'dark' : 'light');
            button.setAttribute('aria-pressed', String(isDark));
            button.querySelector('.theme-toggle-icon').textContent = isDark ? '☀' : '☾';
            button.querySelector('.theme-toggle-label').textContent = isDark ? 'Světlý režim' : 'Tmavý režim';
        }

        var savedTheme = null;
        try {
            savedTheme = window.localStorage.getItem(storageKey);
        } catch (error) {
            // Continue with the system preference when browser storage is unavailable.
        }

        var initialTheme = savedTheme === 'dark' || savedTheme === 'light'
            ? savedTheme
            : (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        setTheme(initialTheme);

        button.addEventListener('click', function () {
            var nextTheme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            setTheme(nextTheme);
            try {
                window.localStorage.setItem(storageKey, nextTheme);
            } catch (error) {
                // The control still works for the current page without browser storage.
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initThemeToggle);
    } else {
        initThemeToggle();
    }
}());
