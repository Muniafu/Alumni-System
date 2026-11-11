import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// Escape user-derived strings before inserting them via innerHTML
// (notification messages/titles contain user names -> stored XSS).
function escapeHtml(str) {
    return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// The navbar badge element (layouts/navigation.blade.php) uses
// id="notificationBadge"; keep the older ids as fallbacks.
function findBadge() {
    return document.getElementById('notificationBadge')
        || document.getElementById('unread-count')
        || document.querySelector('.notification-count');
}

document.addEventListener('DOMContentLoaded', function () {

    // Single mark-as-read handler (the duplicate inline script in
    // layouts/app.blade.php is removed — it fired TWO fetches per click).
    document.addEventListener('click', function (e) {
        const item = e.target.closest('.notification-item, .mark-as-read');
        if (!item) return;

        e.preventDefault();

        const id = item.dataset.id;
        const url = item.getAttribute('href');

        if (!id) return;

        fetch(`/notifications/mark-as-read/${id}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(() => {
            // Decrease the unread badge
            const badge = findBadge();
            if (badge) {
                let n = parseInt(badge.textContent) - 1;
                if (isNaN(n) || n <= 0) {
                    badge.remove();
                } else {
                    badge.textContent = n;
                }
            }

            // Navbar dropdown items: visually mark read.
            // Standalone notification items: remove from the list.
            if (item.classList.contains('dropdown-item')) {
                item.classList.remove('fw-bold', 'bg-light');
            } else if (item.classList.contains('notification-item')) {
                item.remove();
            }

            // Redirect if the notification had a URL
            if (url && url !== '#') {
                window.location.href = url;
            }
        });
    });

    // Mark all notifications as read
    document.getElementById('mark-all-read')?.addEventListener('click', function () {
        fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        }).then(() => {
            const list = document.getElementById('notifications-list');
            if (list) {
                list.innerHTML = '<div class="px-4 py-3 text-center small" style="color: var(--text-muted);">No new notifications</div>';
            }

            const badge = findBadge();
            if (badge) badge.remove();
        });
    });

    // Real-time notifications. Echo is initialized ONCE in bootstrap.js,
    // only when a Pusher key is configured — guard on its existence.
    const userId = document.querySelector('meta[name="user-id"]')?.getAttribute('content');
    if (userId && window.Echo) {
        window.Echo.private(`App.Models.User.${userId}`)
            .notification((notification) => {
                // Play notification sound
                const audio = document.getElementById('notificationSound');
                if (audio) {
                    audio.currentTime = 0;
                    audio.play().catch(e => console.log('Audio play failed:', e));
                }

                // Update unread badge
                const badge = findBadge();
                if (badge) {
                    badge.textContent = (parseInt(badge.textContent) || 0) + 1;
                    badge.classList.remove('hidden');
                } else {
                    const bell = document.querySelector('#navbarNotifications, .fa-bell, .bi-bell');
                    if (bell) {
                        bell.insertAdjacentHTML('afterend',
                            '<span id="notificationBadge" class="badge bg-danger ms-1">1</span>');
                    }
                }

                // Prepend to the navbar dropdown (UL -> wrap in <li>) or
                // to a #notifications-list container. Token-styled, escaped.
                const list = document.getElementById('notifications-list')
                    || document.querySelector('#navbarNotifications + .dropdown-menu');

                if (list) {
                    const anchor = document.createElement('a');
                    anchor.href = notification.url || '#';
                    anchor.className = 'dropdown-item d-flex justify-content-between align-items-start fw-bold mark-as-read';
                    anchor.dataset.id = notification.id;
                    anchor.innerHTML =
                        '<div class="me-2">' + escapeHtml(notification.message || 'Notification') +
                        '<br><small style="color: var(--text-muted);">Just now</small></div>';

                    let node = anchor;
                    if (list.tagName === 'UL') {
                        node = document.createElement('li');
                        node.appendChild(anchor);
                    }

                    const divider = list.querySelector('hr.dropdown-divider');
                    if (divider) {
                        list.insertBefore(node, divider);
                    } else {
                        list.prepend(node);
                    }
                }

                // Show toast popup
                showToastNotification(notification.message, notification.url);
            });
    }

    // Select2 for messaging recipients — only when jQuery + the plugin
    // are actually loaded (they were assumed before).
    const recipients = document.querySelector('#recipients');
    if (recipients && window.jQuery && window.jQuery.fn.select2) {
        window.jQuery('#recipients').select2({
            placeholder: 'Select recipients',
            allowClear: true,
            width: '100%'
        });
    }
});

// Toast notification (token-styled — the old Tailwind classes were not
// reliably loaded, and raw-color styles broke dark mode).
function showToastNotification(message, url = '#') {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.innerHTML = '<p class="mb-0 small" style="color: var(--text-strong);">' + escapeHtml(message) + '</p>';

    document.body.appendChild(toast);

    // Auto-remove after 5s
    setTimeout(() => {
        toast.classList.add('toast-out');
        setTimeout(() => toast.remove(), 300);
    }, 5000);

    // Click to visit URL
    toast.addEventListener('click', () => {
        if (url && url !== '#') {
            window.location.href = url;
        }
    });
}
window.showToastNotification = showToastNotification;
