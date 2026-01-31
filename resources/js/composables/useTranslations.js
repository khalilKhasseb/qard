import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Translation composable for Vue components
 * Provides t() function for key-based lookups with interpolation support
 *
 * Usage:
 *   const { t, locale, direction, isRTL } = useTranslations();
 *   t('common.nav.dashboard') // => "Dashboard"
 *   t('dashboard.subscription.remaining', { count: 5 }) // => "5 remaining"
 */
export function useTranslations() {
    const page = usePage();

    // Current locale code (e.g., 'en', 'ar', 'he')
    const locale = computed(() => page.props.currentLanguage || 'en');

    // Text direction ('ltr' or 'rtl')
    const direction = computed(() => page.props.currentDirection || 'ltr');

    // Convenience boolean for RTL checks
    const isRTL = computed(() => direction.value === 'rtl');

    /**
     * Translate a key with optional replacements
     *
     * @param {string} key - Dot-notation key (e.g., 'common.nav.dashboard')
     * @param {Object} replacements - Key-value pairs for :placeholder replacement
     * @returns {string} - Translated string or the key if not found
     */
    const t = (key, replacements = {}) => {
        const keys = key.split('.');
        let value = page.props.translations || {};

        // Traverse the translations object
        for (const k of keys) {
            value = value?.[k];
            if (value === undefined) {
                // Key not found, return the original key for debugging
                return key;
            }
        }

        // If value is not a string (nested object), return key
        if (typeof value !== 'string') {
            return key;
        }

        // Handle :placeholder interpolation (Laravel-style)
        return value.replace(/:(\w+)/g, (match, placeholder) => {
            return replacements[placeholder] !== undefined
                ? String(replacements[placeholder])
                : match;
        });
    };

    /**
     * Check if a translation key exists
     *
     * @param {string} key - Dot-notation key
     * @returns {boolean}
     */
    const has = (key) => {
        const keys = key.split('.');
        let value = page.props.translations || {};

        for (const k of keys) {
            value = value?.[k];
            if (value === undefined) {
                return false;
            }
        }

        return typeof value === 'string';
    };

    /**
     * Get a translation with a fallback value
     *
     * @param {string} key - Dot-notation key
     * @param {string} fallback - Fallback value if key not found
     * @param {Object} replacements - Key-value pairs for :placeholder replacement
     * @returns {string}
     */
    const tOr = (key, fallback, replacements = {}) => {
        if (has(key)) {
            return t(key, replacements);
        }
        return fallback;
    };

    /**
     * Simple pluralization helper
     * Uses count to pick between singular and plural keys
     *
     * @param {string} singularKey - Key for singular form
     * @param {string} pluralKey - Key for plural form
     * @param {number} count - The count to determine which form to use
     * @param {Object} replacements - Additional replacements (count is added automatically)
     * @returns {string}
     */
    const tPlural = (singularKey, pluralKey, count, replacements = {}) => {
        const key = count === 1 ? singularKey : pluralKey;
        return t(key, { count, ...replacements });
    };

    return {
        t,
        has,
        tOr,
        tPlural,
        locale,
        direction,
        isRTL,
    };
}

export default useTranslations;
