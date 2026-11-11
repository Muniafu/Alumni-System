// Password show/hide — delegated, works for any field.
// Markup contract: <button class="password-eye" data-toggle-password for="INPUT_ID">
document.addEventListener('click', function (e) {
    var btn = e.target.closest('[data-toggle-password]');
    if (!btn) return;

    var input = document.getElementById(btn.getAttribute('for'));
    if (!input) return;

    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';

    btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    var icon = btn.querySelector('i');
    if (icon) icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
});