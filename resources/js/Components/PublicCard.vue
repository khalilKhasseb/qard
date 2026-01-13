<script setup>
import { ref, computed, h } from 'vue';
import { NButton, NIcon, NAvatar } from 'naive-ui';
import * as IconNamespace from '@vicons/ionicons5';

const props = defineProps({
    card: { type: Object, required: true },
    sections: { type: Array, default: () => [] },
    languages: { type: Array, default: () => [] }
});

const currentLanguage = ref(props.card.primary_language || 'en');

const uiTranslations = {
    en: {
        available_in: 'Available in',
        call: 'Call',
        email: 'Email',
        share: 'Share',
        scan_to_connect: 'Scan to connect',
        powered_by: 'Powered by',
        phone: 'Phone',
        address: 'Address',
        appointments: 'Book Appointment',
        contact: 'Contact Info',
        social: 'Social Links',
        services: 'Services',
        products: 'Products',
        testimonials: 'Testimonials',
        hours: 'Business Hours',
        gallery: 'Gallery',
        text: 'About',
        image: 'Image',
        video: 'Video',
        link: 'Link',
    },
    ar: {
        available_in: 'متوفر بـ',
        call: 'اتصال',
        email: 'بريد إلكتروني',
        share: 'مشاركة',
        scan_to_connect: 'امسح الرمز للتواصل',
        powered_by: 'مشغل بواسطة',
        phone: 'الهاتف',
        address: 'العنوان',
        appointments: 'حجز موعد',
        contact: 'معلومات الاتصال',
        social: 'روابط التواصل',
        services: 'الخدمات',
        products: 'المنتجات',
        testimonials: 'التوصيات',
        hours: 'ساعات العمل',
        gallery: 'معرض الصور',
        text: 'حول',
        image: 'صورة',
        video: 'فيديو',
        link: 'رابط',
    }
};

const ut = (key) => {
    return uiTranslations[currentLanguage.value]?.[key] || uiTranslations['en'][key];
};

const selectedLangData = computed(() => {
    return props.languages.find(l => l.code === currentLanguage.value) || { direction: 'ltr' };
});

const t = (field) => {
    if (!field || typeof field !== 'object') return field;
    return field[currentLanguage.value] || field[props.card.primary_language] || Object.values(field)[0] || '';
};

// Get section content for current language with fallback
const sc = (section) => {
    if (!section.content || typeof section.content !== 'object') return {};

    // Check if it's language-nested
    if (section.content[currentLanguage.value] !== undefined) {
        return section.content[currentLanguage.value];
    }

    // Fallback to primary language
    if (section.content[props.card.primary_language] !== undefined) {
        return section.content[props.card.primary_language];
    }

    // Fallback to first available language key
    const firstKey = Object.keys(section.content)[0];
    if (firstKey && section.content[firstKey] !== undefined) {
        // Double check if firstKey is a language code
        if (props.languages.some(l => l.code === firstKey)) {
            return section.content[firstKey];
        }
    }

    // Not language-nested (old data)
    return section.content;
};

// Robust section data handling
const parsedSections = computed(() => {
    return props.sections.map(section => {
        let content = section.content;
        if (typeof content === 'string') {
            try {
                content = JSON.parse(content);
            } catch (e) {
                content = {};
            }
        }
        return { ...section, content };
    });
});

const availableLanguages = computed(() => {
    if (props.languages.length <= 1) return [];

    return props.languages.filter(lang => {
        if (lang.code === props.card.primary_language) return true;

        if (props.card.title && props.card.title[lang.code]) return true;
        if (props.card.subtitle && props.card.subtitle[lang.code]) return true;

        return parsedSections.value.some(section => {
            const content = section.content[lang.code];
            if (!content) return false;
            if (typeof content === 'string') return content.trim() !== '';
            if (Array.isArray(content)) return content.length > 0;
            if (typeof content === 'object') return Object.values(content).some(v => v !== null && v !== '');
            return false;
        });
    });
});

const switchLanguage = (code) => {
    currentLanguage.value = code;
};

const primaryColor = computed(() => props.card.theme?.config?.colors?.primary || '#1a1a1a');
const secondaryColor = computed(() => props.card.theme?.config?.colors?.secondary || '#6b7280');
const backgroundColor = computed(() => props.card.theme?.config?.colors?.background || '#ffffff');
const cardBackgroundColor = computed(() => props.card.theme?.config?.colors?.card_background || '#ffffff');
const headingFont = computed(() => props.card.theme?.config?.fonts?.heading || 'Inter');
const bodyFont = computed(() => props.card.theme?.config?.fonts?.body || 'Inter');

const contactSection = computed(() => parsedSections.value.find(s => s.section_type === 'contact'));
const email = computed(() => sc(contactSection.value || {})?.email);
const phone = computed(() => sc(contactSection.value || {})?.phone);

const getIconComponent = (iconName) => {
    const icons = IconNamespace;
    const lowerName = iconName.toLowerCase();

    // Hardcoded mapping for social platforms
    if (lowerName.includes('facebook')) return icons.LogoFacebook;
    if (lowerName.includes('twitter') || lowerName.includes('x')) return icons.LogoTwitter;
    if (lowerName.includes('instagram')) return icons.LogoInstagram;
    if (lowerName.includes('linkedin')) return icons.LogoLinkedin;
    if (lowerName.includes('github')) return icons.LogoGithub;
    if (lowerName.includes('whatsapp')) return icons.LogoWhatsapp;
    if (lowerName.includes('youtube')) return icons.LogoYoutube;
    if (lowerName.includes('tiktok')) return icons.LogoTiktok;

    const iconMap = {
        'Phone': 'PhonePortraitOutline',
        'Link': 'LinkOutline',
        'Briefcase': 'BriefcaseOutline',
        'Bag': 'BagHandleOutline',
        'Star': 'StarOutline',
        'Time': 'TimeOutline',
        'CalendarCheck': 'CalendarClearOutline',
        'Image': 'ImageOutline',
        'InformationCircle': 'InformationCircleOutline',
        'List': 'ListOutline',
        'Code': 'CodeOutline',
        'Mail': 'MailOutline',
        'Location': 'LocationOutline',
        'Share': 'ShareSocialOutline',
        'QrCode': 'QrCodeOutline'
    };

    if (iconMap[iconName] && icons[iconMap[iconName]]) return icons[iconMap[iconName]];
    if (icons[iconName]) return icons[iconName];
    if (icons[iconName + 'Outline']) return icons[iconName + 'Outline'];

    return icons.DocumentTextOutline;
};

const renderIcon = (name) => {
    // Handle special cases for icons
    let iconToLookup = name;
    if (name === 'Qr_code') iconToLookup = 'QrCode';
    return () => h(getIconComponent(iconToLookup));
};

const hasContent = (section) => {
    if (section.section_type === 'qr_code') return true;
    const content = sc(section);
    if (!content) return false;
    if (typeof content === 'string') return content.trim() !== '';
    if (Array.isArray(content)) return content.length > 0;
    if (typeof content === 'object') return Object.values(content).some(v => v !== null && v !== '');
    return false;
};

const shareCard = async () => {
    if (navigator.share) {
        try { await navigator.share({ title: props.card.title, url: window.location.href }); } catch {}
    } else {
        navigator.clipboard.writeText(window.location.href);
        alert('Link copied to clipboard');
    }
};

const themeStyles = computed(() => ({
    '--primary': primaryColor.value,
    '--secondary': secondaryColor.value,
    '--background': backgroundColor.value,
    '--card-bg': cardBackgroundColor.value,
    '--heading-font': headingFont.value,
    '--body-font': bodyFont.value,
    '--primary-soft': primaryColor.value + '10',
    '--border': '#f3f4f6'
}));
</script>

<template>
    <div class="card-viewer" :style="themeStyles" :dir="selectedLangData.direction" :lang="currentLanguage">
        <div class="card-container mt-2">
            <!-- Facebook style Header -->
            <div class="cover-wrapper">
                <img v-if="card.cover_image_url" :src="card.cover_image_url" class="cover-image" />
                <div v-else class="cover-placeholder"></div>

                <div class="profile-image-container">
                    <div class="profile-image-ring">
                        <NAvatar
                            v-if="card.profile_image_url"
                            :round="true"
                            :size="120"
                            :src="card.profile_image_url"
                            class="profile-avatar"
                        />
                        <div v-else class="initials-avatar-large">
                            {{ (t(card.title) || 'C').charAt(0) }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="bio-section">
                <h1 class="card-title">{{ t(card.title) }}</h1>
                <p class="card-subtitle">{{ t(card.subtitle) }}</p>

                <!-- Language Selector (Inline) -->
                <div v-if="availableLanguages.length > 1" class="language-switcher-inline">
                    <span class="available-text">{{ ut('available_in') }}</span>
                    <div class="lang-buttons">
                        <button
                            v-for="lang in availableLanguages"
                            :key="lang.code"
                            :class="{ 'active-lang': currentLanguage === lang.code }"
                            @click="switchLanguage(lang.code)"
                            class="lang-inline-btn"
                        >
                            {{ lang.code.toUpperCase() }}
                        </button>
                    </div>
                </div>

                <div class="action-buttons">
                    <NButton v-if="phone" round type="primary" size="large" tag="a" :href="`tel:${phone}`" class="flex-1">
                        <template #icon><NIcon><component :is="renderIcon('Phone')" /></NIcon></template>
                        {{ ut('call') }}
                    </NButton>
                    <NButton v-if="email" round quaternary size="large" tag="a" :href="`mailto:${email}`" class="flex-1">
                        <template #icon><NIcon><component :is="renderIcon('Mail')" /></NIcon></template>
                        {{ ut('email') }}
                    </NButton>
                    <NButton circle quaternary size="large" @click="shareCard">
                        <template #icon><NIcon><component :is="renderIcon('Share')" /></NIcon></template>
                    </NButton>
                </div>
            </div>

            <!-- Main Sections -->
            <main class="sections-container">
                <div v-for="section in parsedSections" :key="section.id" class="section-block">
                    <!-- Only show section if it has content for current language -->
                    <template v-if="hasContent(section)">
                        <div class="section-label">
                            <NIcon :color="primaryColor" :size="16">
                                <component :is="renderIcon(section.section_type.charAt(0).toUpperCase() + section.section_type.slice(1))" />
                            </NIcon>
                            <span>{{ section.title || ut(section.section_type) }}</span>
                        </div>

                        <!-- Image Support for any section -->
                        <div v-if="section.image_url" class="section-image-wrapper">
                            <img :src="section.image_url" :alt="section.title" class="section-main-image" />
                        </div>

                        <!-- Contact -->
                        <div v-if="section.section_type === 'contact'" class="glass-box contact-info space-y-3">
                            <div v-if="sc(section).email" class="data-row">
                                <span class="row-label">{{ ut('email') }}</span>
                                <a :href="`mailto:${sc(section).email}`" class="row-value">{{ sc(section).email }}</a>
                            </div>
                            <div v-if="sc(section).phone" class="data-row">
                                <span class="row-label">{{ ut('phone') }}</span>
                                <a :href="`tel:${sc(section).phone}`" class="row-value">{{ sc(section).phone }}</a>
                            </div>
                            <div v-if="sc(section).address" class="data-row">
                                <span class="row-label">{{ ut('address') }}</span>
                                <p class="row-value">{{ sc(section).address }}</p>
                            </div>
                        </div>

                        <!-- Social Links -->
                        <div v-else-if="section.section_type === 'social'" class="flex flex-wrap gap-3">
                            <template v-for="(url, platform) in sc(section)" :key="platform">
                                <NButton v-if="url" circle quaternary size="large" tag="a" :href="url" target="_blank">
                                    <template #icon><NIcon><component :is="renderIcon(platform.charAt(0).toUpperCase() + platform.slice(1))" /></NIcon></template>
                                </NButton>
                            </template>
                        </div>

                        <!-- QR Code Section -->
                        <div v-else-if="section.section_type === 'qr_code'" class="glass-box qr-code-box">
                            <div class="flex flex-col items-center py-4">
                                <div class="qr-wrapper bg-white p-3 rounded-2xl shadow-sm border border-gray-100">
                                    <img :src="card.qr_code_url || `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(card.full_url)}`"
                                         class="w-40 h-40" />
                                </div>
                                <p class="mt-4 text-xs font-medium text-gray-400 uppercase tracking-widest">{{ ut('scan_to_connect') }}</p>
                            </div>
                        </div>

                        <!-- Hours -->
                        <div v-else-if="section.section_type === 'hours'" class="glass-box space-y-2">
                            <div v-for="(time, day) in sc(section)" :key="day" class="flex justify-between text-sm">
                                <span class="capitalize font-medium text-gray-500">{{ day }}</span>
                                <span class="text-gray-900">{{ time || '-' }}</span>
                            </div>
                        </div>

                        <!-- Appointments -->
                        <div v-else-if="section.section_type === 'appointments'" class="glass-box space-y-4">
                            <p v-if="sc(section).instructions" class="text-sm text-gray-600 whitespace-pre-wrap">{{ sc(section).instructions }}</p>
                            <NButton v-if="sc(section).booking_url" block type="primary" tag="a" :href="sc(section).booking_url" target="_blank" round>
                                {{ ut('appointments') }}
                            </NButton>
                        </div>

                        <!-- Services / Products -->
                        <div v-else-if="['services', 'products'].includes(section.section_type)" class="space-y-4">
                            <div v-for="(item, idx) in sc(section)" :key="idx" class="glass-box flex gap-4">
                                <div v-if="item.image_url" class="w-20 h-20 rounded-lg overflow-hidden flex-shrink-0">
                                    <img :src="item.image_url" class="w-full h-full object-cover">
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between items-start">
                                        <h4 class="font-bold text-gray-900">{{ item.name }}</h4>
                                        <span v-if="item.price" class="text-sm font-bold text-primary">{{ item.price }}</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ item.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Testimonials -->
                        <div v-else-if="section.section_type === 'testimonials'" class="space-y-4">
                            <div v-for="(item, idx) in sc(section)" :key="idx" class="glass-box italic text-gray-700 relative">
                                <span class="absolute top-2 left-2 text-4xl text-gray-200 font-serif">"</span>
                                <p class="relative z-10 pt-4">{{ item.quote }}</p>
                                <div class="mt-4 not-italic">
                                    <p class="font-bold text-gray-900">{{ item.author }}</p>
                                    <p class="text-xs text-gray-500">{{ item.company }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Gallery -->
                        <div v-else-if="section.section_type === 'gallery'" class="grid grid-cols-2 gap-3">
                            <div v-for="(item, idx) in sc(section)" :key="idx" class="group relative rounded-xl overflow-hidden aspect-square border border-gray-100">
                                <img :src="item.url" class="w-full h-full object-cover transition transform group-hover:scale-110">
                                <div v-if="item.caption" class="absolute bottom-0 left-0 right-0 bg-black/50 p-2 text-[10px] text-white backdrop-blur-sm opacity-0 group-hover:opacity-100 transition">
                                    {{ item.caption }}
                                </div>
                            </div>
                        </div>

                        <!-- Text based sections -->
                        <div v-else-if="['text', 'about', 'video', 'link', 'links', 'image'].includes(section.section_type)" class="glass-box text-content">
                            <p v-if="['link', 'links'].includes(section.section_type)">
                                <a :href="sc(section)" target="_blank" class="row-value break-all text-primary hover:underline">{{ sc(section) }}</a>
                            </p>
                            <div v-else-if="section.section_type === 'image'" class="image-section">
                                <img :src="sc(section)" class="w-full h-auto rounded-lg" :alt="section.title" />
                            </div>
                            <p v-else class="whitespace-pre-wrap">{{ sc(section) }}</p>
                        </div>

                        <!-- Fallback -->
                        <div v-else class="glass-box">
                             <p>{{ sc(section) }}</p>
                        </div>
                    </template>
                </div>
            </main>

            <footer class="viewer-footer">
                <span>{{ ut('powered_by') }} <strong>Qard</strong></span>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.card-viewer {
    min-height: 100vh;
    background-color: var(--background);
    color: #111827;
    font-family: var(--body-font), system-ui, sans-serif;
    padding-bottom: 40px;
}

.card-container {
    max-width: 500px;
    margin: 0 auto;
    background: var(--card-bg);
    min-height: 100vh;
    box-shadow: 0 0 40px rgba(0,0,0,0.05);
}

.language-switcher-inline {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.available-text {
    font-size: 12px;
    color: #9ca3af;
    font-weight: 500;
}

.lang-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.lang-inline-btn {
    background: white;
    border: 1px solid #e5e7eb;
    padding: 4px 12px;
    border-radius: 9999px;
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    cursor: pointer;
    transition: all 0.2s;
}

.lang-inline-btn:hover {
    border-color: var(--primary);
    color: var(--primary);
}

.lang-inline-btn.active-lang {
    background: var(--primary);
    border-color: var(--primary);
    color: white;
}

.cover-wrapper {
    position: relative;
    height: 200px;
}

.cover-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cover-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    opacity: 0.15;
}

.profile-image-container {
    position: absolute;
    bottom: -60px;
    left: 0;
    right: 0;
    display: flex;
    justify-content: center;
}

.profile-image-ring {
    background: var(--card-bg);
    padding: 6px;
    border-radius: 50%;
    box-shadow: 0 8px 24px rgba(0,0,0,0.12);
}

.profile-avatar {
    border: 1px solid rgba(0,0,0,0.05);
}

.initials-avatar-large {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: var(--primary-soft);
    color: var(--primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    font-weight: 800;
}

.bio-section {
    padding: 80px 24px 32px;
    text-align: center;
}

.card-title {
    font-family: var(--heading-font), sans-serif;
    font-size: 28px;
    font-weight: 800;
    margin: 0 0 8px 0;
    color: #111827;
    letter-spacing: -0.02em;
}

.card-subtitle {
    font-size: 16px;
    color: var(--secondary);
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.sections-container {
    padding: 0 24px 40px;
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.section-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 12px;
    font-size: 11px;
    font-weight: 800;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.1em;
}

.glass-box {
    background: rgba(255, 255, 255, 0.5);
    border: 1px solid var(--border);
    border-radius: 24px;
    padding: 20px;
    transition: transform 0.2s ease;
}

.qr-code-box {
    background: var(--primary-soft);
    border: 1px dashed var(--primary);
}

.section-image-wrapper {
    margin-bottom: 16px;
    border-radius: 20px;
    overflow: hidden;
}

.section-main-image {
    width: 100%;
    height: auto;
    max-height: 400px;
    object-fit: cover;
}

.row-label {
    display: block;
    font-size: 10px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    margin-bottom: 2px;
}

.row-value {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    text-decoration: none;
}

.viewer-footer {
    text-align: center;
    padding: 40px 0;
    font-size: 13px;
    color: #9ca3af;
}

@media (max-width: 500px) {
    .card-container {
        box-shadow: none;
    }
}
</style>
