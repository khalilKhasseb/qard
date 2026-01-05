import '../css/app.css';
import './bootstrap';

import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { NConfigProvider, NMessageProvider, NDialogProvider, NNotificationProvider, darkTheme, lightTheme } from 'naive-ui';
import naive from 'naive-ui';
import PublicCard from './Components/PublicCard.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Create a custom theme based on the card's primary color
const createTheme = (primaryColor) => {
    return {
        name: 'custom',
        common: {
            primaryColor: primaryColor || '#8b5cf6',
            primaryColorHover: primaryColor ? `${primaryColor}99` : '#8b5cf699',
            primaryColorPressed: primaryColor ? `${primaryColor}99` : '#8b5cf699',
            primaryColorSuppl: primaryColor ? `${primaryColor}33` : '#8b5cf633',
        }
    };
};

// Check if this is a Blade template with PublicCard (has data-card attribute)
const appEl = document.getElementById('app');
const hasCardData = appEl && (appEl.dataset.card || appEl.dataset.sections);

if (hasCardData) {
    // This is a public card page with Blade template - mount PublicCard directly
    const cardData = appEl.dataset.card ? JSON.parse(appEl.dataset.card) : null;
    const sectionsData = appEl.dataset.sections ? JSON.parse(appEl.dataset.sections) : [];
    const primaryColor = cardData?.theme?.config?.primary || '#8b5cf6';
    const customTheme = createTheme(primaryColor);

    const app = createApp({
        render: () => h(NConfigProvider, { theme: customTheme }, {
            default: () => h(NMessageProvider, null, {
                default: () => h(NDialogProvider, null, {
                    default: () => h(NNotificationProvider, null, {
                        default: () => h(PublicCard, { card: cardData, sections: sectionsData })
                    })
                })
            })
        })
    });
    
    app.use(ZiggyVue);
    app.component('PublicCard', PublicCard);
    app.mount('#app');
} else {
    // This is an Inertia page - initialize Inertia
    import('./inertia-app').catch(err => console.error('Failed to load Inertia app:', err));
}
