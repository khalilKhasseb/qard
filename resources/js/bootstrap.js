import axios from 'axios';
window.axios = axios;

axios.defaults.baseURL = '/';
axios.defaults.withCredentials = true;  // ← THIS IS KEY
axios.defaults.withXSRFToken = true;    // ← AND THIS
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';
