/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF-TOKEN" cookie.
 */

import _ from 'lodash';
import axios from 'axios';

window._ = _;

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo is initialized HERE and nowhere else. It is created just once, and
 * ONLY when a Pusher key is configured — with BROADCAST_DRIVER=log there is
 * nothing to connect to, and an unconditional instance spams the console
 * with failed websocket attempts.
 * (app.js used to create a SECOND instance via process.env — undefined in
 * the browser under Vite — crashing the whole script and silently killing
 * every notification handler.)
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

if (import.meta.env.VITE_PUSHER_APP_KEY) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: import.meta.env.VITE_PUSHER_APP_KEY,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
        forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    });
}
