/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.crossDomain = true;
window.axios.defaults.withXSRFToken = true;


/*
window.axios.interceptors.response.use(response => response, async err => {
  const status = get(err, 'response.status')

  if (status === 419) {
    // Refresh our session token
    await window.axios.get('/csrf-token')

    // Return a new request using the original request's configuration
    return window.axios(err.response.config)
  }

  return Promise.reject(err)
})
*/

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
