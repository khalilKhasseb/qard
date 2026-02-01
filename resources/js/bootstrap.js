import axios from 'axios';
window.axios = axios;

// Configure axios for Laravel + Sanctum
// CSRF is handled automatically via XSRF-TOKEN cookie (set by Laravel)
axios.defaults.baseURL = '/';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.headers.common['Accept'] = 'application/json';

// Response interceptor to handle 419 (CSRF token mismatch) errors for non-Inertia requests
axios.interceptors.response.use(
    (response) => response,
    async (error) => {
        const originalRequest = error.config;

        // Check if this is an Inertia request (has X-Inertia header)
        const isInertiaRequest = originalRequest?.headers?.['X-Inertia'] ||
                                  originalRequest?.headers?.['x-inertia'];

        // Handle 419 CSRF token mismatch - but NOT for Inertia requests
        // Inertia handles its own CSRF via the XSRF-TOKEN cookie automatically
        if (error.response?.status === 419 && !originalRequest._retry && !isInertiaRequest) {
            originalRequest._retry = true;

            try {
                // Fetch fresh CSRF cookie from Sanctum
                await axios.get('/sanctum/csrf-cookie');

                // Retry the original request - axios will automatically use the new XSRF-TOKEN cookie
                return axios(originalRequest);
            } catch (refreshError) {
                console.error('CSRF token refresh failed');
                return Promise.reject(refreshError);
            }
        }

        return Promise.reject(error);
    }
);

// Export helper for manual CSRF refresh if needed
window.refreshCsrfToken = async () => {
    try {
        await axios.get('/sanctum/csrf-cookie');
        return true;
    } catch (e) {
        console.error('Failed to refresh CSRF token:', e);
        return false;
    }
};
