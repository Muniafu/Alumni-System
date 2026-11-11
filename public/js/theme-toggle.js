// Theme toggle — pairs with the FOUC-guard script in <head> that sets
// data-bs-theme BEFORE first paint. This only wires the button.
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('themeToggle');
    if (!btn) return;

    var icon = btn.querySelector('i');

    var sync = function () {
        var theme = document.documentElement.getAttribute('data-bs-theme');
        icon.className = (theme === 'dark') ? 'bi bi-sun' : 'bi bi-moon-stars';
    };

    sync();

    btn.addEventListener('click', function () {
        var html = document.documentElement;
        var next = html.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        html.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        sync();
    });
});
