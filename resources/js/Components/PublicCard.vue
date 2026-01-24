<script setup>
import { ref, computed, h, onMounted } from 'vue';
import { NButton, NIcon, NAvatar } from 'naive-ui';
import * as IconNamespace from '@vicons/ionicons5';
import ServicesSection from './PublicCardSections/ServicesSection.vue';
import ProductsSection from './PublicCardSections/ProductsSection.vue';
import TestimonialsSection from './PublicCardSections/TestimonialsSection.vue';
import GallerySection from './PublicCardSections/GallerySection.vue';
import TextSection from './PublicCardSections/TextSection.vue';
import AppointmentsSection from './PublicCardSections/AppointmentsSection.vue';
import '../../css/public-card.css';

const FIGMA_ICONS = {
    location: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21.7183 8.19024C20.5352 3.2322 15.9944 1 12.0056 1H11.9944C8.01689 1 3.46477 3.22146 2.28167 8.17951C0.963353 13.7171 4.52393 18.4068 7.74647 21.358C8.94084 22.4527 10.4732 23 12.0056 23C13.538 23 15.0704 22.4527 16.2535 21.358C19.4761 18.4068 23.0366 13.7278 21.7183 8.19024ZM12.0056 13.5668C10.0451 13.5668 8.45633 12.0537 8.45633 10.1863C8.45633 8.31903 10.0451 6.80585 12.0056 6.80585C13.9662 6.80585 15.5549 8.31903 15.5549 10.1863C15.5549 12.0537 13.9662 13.5668 12.0056 13.5668Z" fill="#FF575A"/></svg>`,
    facebook: `<svg viewBox="0 0 24.0005 23.9122" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 0C5.37264 0 0 5.37264 0 12C0 17.6275 3.87456 22.3498 9.10128 23.6467V15.6672H6.62688V12H9.10128V10.4198C9.10128 6.33552 10.9498 4.4424 14.9597 4.4424C15.72 4.4424 17.0318 4.59168 17.5685 4.74048V8.06448C17.2853 8.03472 16.7933 8.01984 16.1822 8.01984C14.2147 8.01984 13.4544 8.76528 13.4544 10.703V12H17.3741L16.7006 15.6672H13.4544V23.9122C19.3963 23.1946 24.0005 18.1354 24.0005 12C24 5.37264 18.6274 0 12 0Z" fill="#0866FF"/></svg>`,
    instagram: `<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill="#E1306C" d="M7 2h10a5 5 0 0 1 5 5v10a5 5 0 0 1-5 5H7a5 5 0 0 1-5-5V7a5 5 0 0 1 5-5Z"/><path fill="#fff" d="M12 8.2a3.8 3.8 0 1 0 0 7.6 3.8 3.8 0 0 0 0-7.6Zm0 6.2a2.4 2.4 0 1 1 0-4.8 2.4 2.4 0 0 1 0 4.8ZM16.6 7.8a.9.9 0 1 0 0-1.8.9.9 0 0 0 0 1.8Z"/></svg>`,
    whatsapp: `<svg viewBox="0 0 23.8859 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0 24L1.687 17.837C0.645998 16.033 0.0989998 13.988 0.0999998 11.891C0.103 5.335 5.43799 0 11.993 0C15.174 0.001 18.16 1.24 20.406 3.488C22.6509 5.736 23.8869 8.724 23.8859 11.902C23.8829 18.459 18.548 23.794 11.993 23.794C10.003 23.793 8.04198 23.294 6.30499 22.346L0 24ZM6.59698 20.193C8.27298 21.188 9.87298 21.784 11.989 21.785C17.437 21.785 21.875 17.351 21.878 11.9C21.88 6.438 17.463 2.01 11.997 2.008C6.54498 2.008 2.11 6.442 2.108 11.892C2.107 14.117 2.75899 15.783 3.85399 17.526L2.85499 21.174L6.59698 20.193ZM17.984 14.729C17.91 14.605 17.712 14.531 17.414 14.382C17.117 14.233 15.656 13.514 15.383 13.415C15.111 13.316 14.913 13.266 14.714 13.564C14.516 13.861 13.946 14.531 13.773 14.729C13.6 14.927 13.426 14.952 13.129 14.803C12.832 14.654 11.874 14.341 10.739 13.328C9.85598 12.54 9.25898 11.567 9.08598 11.269C8.91298 10.972 9.06798 10.811 9.21598 10.663C9.34998 10.53 9.51298 10.316 9.66198 10.142C9.81298 9.97 9.86198 9.846 9.96198 9.647C10.061 9.449 10.012 9.275 9.93698 9.126C9.86198 8.978 9.26798 7.515 9.02098 6.92C8.77898 6.341 8.53398 6.419 8.35198 6.41L7.78198 6.4C7.58398 6.4 7.26198 6.474 6.98998 6.772C6.71798 7.07 5.94999 7.788 5.94999 9.251C5.94999 10.714 7.01498 12.127 7.16298 12.325C7.31198 12.523 9.25798 15.525 12.239 16.812C12.948 17.118 13.502 17.301 13.933 17.438C14.645 17.664 15.293 17.632 15.805 17.556C16.376 17.471 17.563 16.837 17.811 16.143C18.059 15.448 18.059 14.853 17.984 14.729Z" fill="#25D366"/></svg>`,
};

const props = defineProps({
    card: { type: Object, required: true },
    sections: { type: Array, default: () => [] },
    languages: { type: Array, default: () => [] }
});

const currentLanguage = ref(props.card.primary_language || 'en');

const fallbackTranslations = {
    en: {
        available_in: 'Available in',
        share: 'Share',
        powered_by: 'Powered by',
        qr_code: 'QR Code',
        download: 'Download',
        view_website: 'View Website',
        book_appointment: 'Book Appointment',
        hours: 'Business Hours',
        am: 'AM',
        pm: 'PM',
        closed: 'Closed',
        monday: 'Monday',
        tuesday: 'Tuesday',
        wednesday: 'Wednesday',
        thursday: 'Thursday',
        friday: 'Friday',
        saturday: 'Saturday',
        sunday: 'Sunday',
    },
    ar: {
        available_in: 'متوفر بـ',
        share: 'مشاركة',
        powered_by: 'مشغل بواسطة',
        qr_code: 'رمز QR',
        download: 'تنزيل',
        view_website: 'عرض الموقع',
        book_appointment: 'حجز موعد',
        hours: 'ساعات العمل',
        am: 'ص',
        pm: 'م',
        closed: 'مغلق',
        monday: 'الاثنين',
        tuesday: 'الثلاثاء',
        wednesday: 'الأربعاء',
        thursday: 'الخميس',
        friday: 'الجمعة',
        saturday: 'السبت',
        sunday: 'الأحد',
    }
};

const labelsByCode = computed(() => {
    const map = {};
    props.languages.forEach((lang) => {
        map[lang.code] = lang.labels || {};
    });
    return map;
});

const ut = (key) => {
    const labels = labelsByCode.value[currentLanguage.value] || {};
    const fallback = labelsByCode.value.en || {};
    return labels[key] ?? fallback[key] ?? fallbackTranslations[currentLanguage.value]?.[key] ?? fallbackTranslations.en[key];
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

    // Gallery: prefer language-specific nested content if present, else top-level
    if (section.section_type === 'gallery') {
        if (section.content[currentLanguage.value] !== undefined) {
            return section.content[currentLanguage.value];
        }
        if (section.content[props.card.primary_language] !== undefined) {
            return section.content[props.card.primary_language];
        }
        // If top-level already uses items/images, return it
        if (section.content.items || section.content.images) {
            return section.content;
        }
        // If the first key looks like a language and contains items, return that
        const firstKey = Object.keys(section.content)[0];
        if (firstKey && props.languages.some(l => l.code === firstKey) && section.content[firstKey] && (section.content[firstKey].items || section.content[firstKey].images)) {
            return section.content[firstKey];
        }

        // Fallback to top-level
        return section.content;
    }

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
const address = computed(() => sc(contactSection.value || {})?.address);
const website = computed(() => sc(contactSection.value || {})?.website);

// Get all contact fields that have values
const contactFields = computed(() => {
    const fields = [];
    const content = sc(contactSection.value || {});
    if (!content) return fields;
    
    if (content.email) fields.push({ type: 'email', value: content.email, icon: 'Mail', href: `mailto:${content.email}` });
    if (content.phone) fields.push({ type: 'phone', value: content.phone, icon: 'Call', href: `tel:${content.phone}` });
    if (content.address) fields.push({ type: 'address', value: content.address, icon: 'Location', href: null });
    if (content.website) {
        const websiteUrl = content.website.startsWith('http') ? content.website : `https://${content.website}`;
        fields.push({ type: 'website', value: ut('view_website'), icon: 'Globe', href: websiteUrl });
    }
    
    return fields;
});

const socialSection = computed(() => parsedSections.value.find(s => s.section_type === 'social'));
const socialLinks = computed(() => {
    const content = sc(socialSection.value || {});
    if (!content || typeof content !== 'object') return [];
    const supported = ['location', 'facebook', 'instagram', 'whatsapp'];
    return supported
        .map((k) => ({ key: k, url: content[k] }))
        .filter((x) => !!x.url);
});

const getShareUrl = () => props.card?.full_url || window.location.href;

const shareCard = async () => {
    const url = getShareUrl();
    const title = t(props.card.title) || '';

    if (navigator.share) {
        try {
            await navigator.share({ title, url });
            return;
        } catch {
            // fallthrough to clipboard
        }
    }

    try {
        await navigator.clipboard.writeText(url);
        // keep minimal UX; avoid alert loops on mobile
    } catch {
        // ignore
    }
};

const escapeVCardText = (value) => {
    if (!value) return '';
    return String(value)
        .replace(/\\/g, '\\\\')
        .replace(/\n/g, '\\n')
        .replace(/;/g, '\\;')
        .replace(/,/g, '\\,');
};

const buildVCard = () => {
    const name = (t(props.card.title) || '').trim();
    const orgOrRole = (t(props.card.subtitle) || '').trim();
    const emailVal = email.value;
    const phoneVal = phone.value;
    const telVal = sc(contactSection.value || {})?.telephone;
    const addrVal = sc(contactSection.value || {})?.address;
    const url = getShareUrl();

    return [
        'BEGIN:VCARD',
        'VERSION:3.0',
        `FN:${escapeVCardText(name)}`,
        `N:${escapeVCardText(name)};;;;`,
        orgOrRole ? `TITLE:${escapeVCardText(orgOrRole)}` : null,
        phoneVal ? `TEL;TYPE=CELL:${escapeVCardText(phoneVal)}` : null,
        telVal ? `TEL;TYPE=WORK:${escapeVCardText(telVal)}` : null,
        emailVal ? `EMAIL;TYPE=INTERNET:${escapeVCardText(emailVal)}` : null,
        addrVal ? `ADR;TYPE=WORK:;;${escapeVCardText(addrVal)};;;;` : null,
        url ? `URL:${escapeVCardText(url)}` : null,
        'END:VCARD',
    ].filter(Boolean).join('\r\n');
};

const downloadContactCard = () => {
    const vcf = buildVCard();
    const blob = new Blob([vcf], { type: 'text/vcard;charset=utf-8' });
    const blobUrl = URL.createObjectURL(blob);
    const filename = `${(t(props.card.title) || 'contact').toString().replace(/\s+/g, '_')}.vcf`;

    const a = document.createElement('a');
    a.href = blobUrl;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    a.remove();
    setTimeout(() => URL.revokeObjectURL(blobUrl), 500);
};

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

const formatHoursValue = (value) => {
    if (!value) return '';
    if (typeof value === 'string') {
        const closedLabel = ut('closed') || 'Closed';
        if (value.trim().toLowerCase() === 'closed') return closedLabel;
        return value
            .replace(/\bAM\b/gi, ut('am') || 'AM')
            .replace(/\bPM\b/gi, ut('pm') || 'PM');
    }
    if (typeof value === 'object') {
        const start = value.start || value.from || '';
        const end = value.end || value.to || '';
        return [start, end]
            .filter(Boolean)
            .join(' - ')
            .replace(/\bAM\b/gi, ut('am') || 'AM')
            .replace(/\bPM\b/gi, ut('pm') || 'PM');
    }
    return String(value);
};

const formatDayLabel = (day) => {
    if (!day) return '';
    const key = String(day).toLowerCase();
    return ut(key) || day;
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

onMounted(() => {
   console.log('Parsed => ',parsedSections.value);});
</script>

<template>
    <div class="card-viewer" :style="themeStyles" :dir="selectedLangData.direction" :lang="currentLanguage">
        <div class="card-container mt-2">
            <div class="ui" :class="{ 'is-rtl': selectedLangData.direction === 'rtl' }">
                <!-- Header (cover + avatar) -->
                <div class="header">
                    <div class="cover">
                        <img v-if="card.cover_image_url" :src="card.cover_image_url" class="cover-image" />
                        <div v-else class="cover-placeholder" />

                        <div class="avatar-wrap">
                            <div class="avatar-ring">
                                <NAvatar
                                    v-if="card.profile_image_url"
                                    :round="true"
                                    :size="100"
                                    :src="card.profile_image_url"
                                    class="profile-avatar"
                                />
                                <div v-else class="initials-avatar">
                                    {{ (t(card.title) || 'C').charAt(0) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="header-text">
                        <h1 class="name">{{ t(card.title) }}</h1>
                        <p class="subtitle">{{ t(card.subtitle) }}</p>
                    </div>

                    <div v-if="availableLanguages.length > 1" class="language-switcher">
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
                </div>

                <!-- Social icons (from section content) -->
                <div class="social-row">
                    <a
                        v-for="item in socialLinks"
                        :key="item.key"
                        v-if="socialLinks.length"
                        class="social-btn"
                        :href="item.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="item.key"
                    >
                    
    		    <span class="social-icon" v-html="FIGMA_ICONS[item.key] || ''" />
                    </a>
                </div>

                <!-- Contact cards (email + phone + address + website) -->
                <div v-if="contactFields.length" class="contact-stack">
                    <!-- Email (full width) -->
                    <template v-for="field in contactFields.filter(f => f.type === 'email')" :key="field.type">
                        <a class="info-card " :href="field.href">
                            <span class="info-icon block w-[30px] mb-1"><component :is="renderIcon(field.icon)" /></span>
                            <span class="info-text">{{ field.value }}</span>
                        </a>
                    </template>

                    <!-- Phone + Website (2 columns) -->
                    <div v-if="contactFields.some(f => f.type === 'phone' || f.type === 'website')" class="two-col">
                        <template v-for="field in contactFields.filter(f => f.type === 'phone' || f.type === 'website')" :key="field.type">
                            <a v-if="field.href" class="info-card" :class="{ 'info-card--clickable': field.type === 'website' }" :href="field.href" :target="field.type === 'website' ? '_blank' : undefined" :rel="field.type === 'website' ? 'noopener noreferrer' : undefined">
                                <span class="info-icon block w-[30px] mb-1"><component :is="renderIcon(field.icon)" /></span>
                                <span class="info-text">{{ field.value }}</span>
                            </a>
                        </template>
                    </div>

                    <!-- Address (full width) -->
                    <template v-for="field in contactFields.filter(f => f.type === 'address')" :key="field.type">
                        <div class="info-card">
                            <span class="info-icon block w-[30px] mb-1"><component :is="renderIcon(field.icon)" /></span>
                            <span class="info-text info-text--small">{{ field.value }}</span>
                        </div>
                    </template>
                </div>

                <!-- QR + Actions row -->
                <div class="qr-actions">
                    <div class="qr-left">
                        <div class="qr-box">
                            <img
                                :src="card.qr_code_url || `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(card.full_url)}`"
                                class="qr-img"
                                alt="QR"
                            />
                        </div>
                        <div class="qr-label">{{ ut('qr_code') }}</div>
                    </div>

                    <div class="qr-right">
                        <button class="primary-btn" type="button" @click="shareCard">
                            <span class="btn-icon block w-[30px] mb-1"><component :is="renderIcon('Share')" /></span>
                            <span class="btn-text">{{ ut('share') }}</span>
                        </button>
                        <button class="primary-btn" type="button" @click="downloadContactCard">
                            <span class="btn-icon block w-[30px] mb-1"><component :is="renderIcon('Download')" /></span>
                            <span class="btn-text">{{ ut('download') }}</span>
                        </button>
                    </div>
                </div>

                <!-- Hours section (Figma style) -->
                <template v-if="parsedSections.length > 0 && Array.from(parsedSections).find(el => el.section_type === 'hours')">
                <div class="hours">
                    <div class="hours-title">{{ ut('hours') }}</div>
                    <div class="hours-list">
                        <template v-for="section in parsedSections" :key="section.id">
                            <template v-if="section.section_type === 'hours' && hasContent(section)">
                                <div v-for="(time, day) in sc(section)" :key="day" class="hour-row">
                                    <div class="hour-icon"><component :is="renderIcon('CalendarCheck')" /></div>
                                    <div class="hour-text">
                                        <div class="hour-day">{{ formatDayLabel(day) }}</div>
                                        <div class="hour-time">{{ formatHoursValue(time) }}</div>
                                    </div>
                                </div>
                            </template>
                        </template>
                    </div>
                </div>
                </template>

                <!-- Dynamic sections (services, products, testimonials, etc.) -->
                <template v-for="section in parsedSections" :key="section.id">
                    
                    <template v-if="hasContent(section)">
                        <!-- Services Section -->
                        <ServicesSection
                            v-if="section.section_type === 'services'"
                            :content="sc(section)"
                            :title="t(section.title)"
                        />
                        
                        <!-- Products Section -->
                        <ProductsSection
                            v-if="section.section_type === 'products'"
                            :content="sc(section)"
                            :title="t(section.title)"
                        />
                        
                        <!-- Testimonials Section -->
                        <TestimonialsSection
                            v-if="section.section_type === 'testimonials'"
                            :content="sc(section)"
                            :title="t(section.title)"
                        />
                        
                        <!-- Gallery Section -->
                        <GallerySection
                            v-if="section.section_type === 'gallery'"
                            :content="sc(section)"
                            :title="t(section.title)"
                        />
                        
                        <!-- Text/About Section -->
                        <TextSection
                            v-if="section.section_type === 'text' || section.section_type === 'about'"
                            :content="sc(section)"
                            :title="t(section.title)"
                        />
                        
                        <!-- Appointments Section -->
                        <AppointmentsSection
                            v-if="section.section_type === 'appointments'"
                            :content="sc(section)"
                            :title="t(section.title)"
                            :button-label="ut('book_appointment')"
                        />
                    </template>
                </template>

                <footer class="viewer-footer">
                    <a href="https://birdie.ps/" target="_blank" class="inline-block ">{{ ut('powered_by') }} <strong>

                        <svg class="inline-block transition-colors duration-300 ease-in-out hover:fill-red-400 hover:text-red-500" width="30px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 189.8 94.7" xml:space="preserve">
<path fill="currentcolor" d="M166,61.2c1.3,1.1,3.2,1,4.4-0.3c1.1-1.3,1-3.2-0.3-4.4c-1.3-1.1-3.2-1-4.4,0.3c0,0,0,0,0,0
	c-0.1,0.1-0.2,0.2-0.3,0.4C164.5,58.4,164.8,60.2,166,61.2 M105,61.2c1.3,1.1,3.2,1,4.4-0.3c1.1-1.3,1-3.2-0.3-4.4s-3.2-1-4.4,0.3
	c0,0,0,0,0,0c-0.1,0.1-0.2,0.2-0.3,0.4C103.5,58.4,103.8,60.2,105,61.2 M184.8,81.6c-2.3,1.2-5,1.4-7.5,0.7c3.4-1.7,6.6-3.7,9.6-6
	c1.3-1,3.3-3,2.9-5.7c-0.3-2-2-3.6-4.4-4.4c-3.8-1.2-8-0.2-10.9,2.6c-3.1,3-5.7,8.3-4.7,12.4c-1.1,0.8-2.3,1.4-3.6,1.7
	c-0.2,0-0.5,0.1-0.8,0.1h0c-0.1,0-0.2,0-0.4,0h-0.3c-0.4,0-0.8-0.1-1.2-0.2c-0.2-0.1-0.4-0.2-0.6-0.3c-0.5-0.5-0.8-1.2-0.7-1.9
	c0-0.1,0-0.3,0.1-0.4l4.8-12.4c0.4-1.1-0.2-2.4-1.3-2.8c-1.1-0.4-2.3,0.1-2.8,1.2v0l-5.4,13.9c0,0.1-0.1,0.2-0.1,0.4
	c-0.7,1.5-2.2,2.5-3.9,2.5c-0.9,0.1-1.8-0.3-2.4-1.1c-0.1-0.1-0.2-0.3-0.2-0.4c-0.1-0.2-0.1-0.4-0.2-0.6l9.8-25.7
	c0.4-1.1-0.1-2.4-1.2-2.8s-2.4,0.1-2.8,1.2c0,0,0,0,0,0.1l0,0l-5.9,15.5c-0.1,0.1-0.1,0.2-0.2,0.3c-0.3-0.4-0.6-0.9-0.9-1.3
	c-0.4-0.4-0.7-0.8-1.1-1.1c-1.8-1.5-4.1-2.2-6.4-2.2c-3,0-5.9,1.2-8.1,3.2c-0.1,0.1-0.2,0.2-0.2,0.2c-0.1,0-0.1,0.1-0.2,0.2
	c-1.5,1.4-2.6,3.1-3.3,5c0,0,0,0,0,0.1c-0.1,0.2-0.2,0.5-0.2,0.8c-0.1,0.5-0.2,0.9-0.3,1.4c-0.1,0.4-0.1,0.9-0.1,1.3
	c0,1.3,0.1,2.5,0.6,3.7c-0.2,0.3-0.5,0.7-0.8,0.9c-0.8,0.8-1.9,1.3-3.1,1.4h-0.3c-0.4,0-0.8-0.1-1.2-0.2c-0.5-0.2-0.8-0.5-1.1-0.8
	c-0.5-0.9-0.5-2.1,0-3c0.1-0.3,0.8-2.2,1.5-3.9c0.5-1.3,0.9-2.5,1-2.8c0-0.1,0.1-0.3,0.2-0.6c0.2-0.6,0.1-1.2-0.1-1.8
	c-0.1-0.2-0.3-0.5-0.5-0.7c-0.4-0.3-0.8-0.6-1.3-0.8c-0.2-0.1-0.4-0.1-0.5-0.2c-0.3-0.1-0.7-0.2-1-0.4c0,0,0,0,0,0
	c-0.6-0.3-3.6-1.7-3.6-4.3v-0.3c0-0.3-0.1-0.6-0.4-0.8c0,0,0,0,0,0h0c0,0,0,0-0.1,0h0c0,0,0,0,0,0c-0.1,0-0.2-0.1-0.4-0.1h-2
	c-0.5,0-0.9,0.2-1.2,0.6c-0.1,0.1-0.2,0.3-0.3,0.5c-0.4,1.1-1,2.5-1.5,3.9c-2,5-4.3,11.1-5,12.8l-0.1,0.2c-0.6,1.8-2.2,2.9-4.1,2.9
	c-0.9,0.1-1.8-0.3-2.4-1.1c-0.3-0.5-0.4-1-0.4-1.5c0-0.5,0.1-1,0.3-1.5c0.4-1.1,1.3-3.4,2.3-5.9c0.9-2.3,1.9-4.8,2.5-6.5
	c0.2-0.3,0.1-0.8-0.3-1c-0.1-0.1-0.3-0.1-0.4-0.1h-2.3c-0.2,0-0.3,0-0.4,0.1h0c-0.5,0.1-0.9,0.4-1.3,0.8c-1.1,1.3-2.4,2.3-4,3.1
	c-1.5,0.7-3.1,1.2-4.7,1.4c-0.2-0.6-0.5-1.2-0.9-1.7c0,0,0,0,0-0.1c-1.8-2.8-4.9-4.4-8.2-4.3c-1.6,0-3.2,0.4-4.7,1l4.1-10.7
	c0.4-1.1-0.1-2.4-1.2-2.8s-2.4,0.1-2.8,1.2c0,0,0,0,0,0.1l0,0l-9.6,25.1c-0.7,1.7-1.5,3.4-2.6,4.9c-0.1,0.1-0.2,0.3-0.3,0.4
	c-0.7,1-0.5,2.3,0.4,3c1,0.7,2.3,0.5,3-0.4l0,0c0.1-0.2,0.2-0.3,0.3-0.5c0.8-1.1,1.5-2.3,2.1-3.5v0c0.1-0.1,0.1-0.2,0.2-0.3
	c0.1,0.1,0.1,0.2,0.2,0.3l0.3,0.4c0.1,0.2,0.3,0.3,0.4,0.5c1.9,2.2,4.6,3.4,7.5,3.3c4.2-0.1,8-2.2,10.4-5.6c1.1-1.5,1.8-3.3,2.1-5.1
	c0.1-0.5,0.1-0.9,0.1-1.4c0.4,0,0.8-0.1,1.2-0.2c1.7-0.3,3.3-0.9,4.7-1.6c0,0.1-0.1,0.2-0.1,0.4c-0.7,1.8-1.3,3.3-1.6,4.1
	c-1,2.2-0.8,4.7,0.4,6.7c0.5,0.7,1.1,1.3,1.8,1.7c1.2,0.7,2.5,1.1,3.9,1.1c3.5,0,6.6-2.2,7.8-5.5c0.2-0.4,0.5-1.4,1-2.6
	c1-2.5,2.4-6.3,3.8-9.7c0.1-0.3,0.2-0.6,0.4-0.9c1.1,1.4,2.6,2.5,4.2,3.2c0.1,0,0.2,0.1,0.2,0.1c0.2,0.1,0.4,0.1,0.5,0.2
	c-0.8,2-2,5.3-2.1,5.7c-0.6,1.4-0.8,2.9-0.5,4.4c0.2,0.8,0.5,1.6,1,2.4c1.2,1.7,3.1,2.8,5.2,2.8c0.2,0,0.3,0,0.4,0
	c0.2,0,0.5,0,0.7,0c0.1,0,0.1,0,0.2,0h0.1c1-0.1,2-0.4,2.9-0.9c0.9-0.5,1.8-1.1,2.5-1.8c1.4,1.4,3.3,2.3,5.2,2.6
	c0.6,0.1,1.2,0.1,1.7,0.1c2.9,0,5.6-1,7.8-2.9c0.2-0.1,0.3-0.2,0.4-0.4c0.1,0.2,0.2,0.3,0.3,0.4c0.8,1.1,1.9,2,3.2,2.4
	c0.1,0,0.1,0,0.2,0.1c0.1,0,0.2,0,0.2,0.1c0.1,0,0.2,0,0.2,0.1c0.1,0,0.1,0,0.2,0c0.1,0,0.2,0,0.2,0c0.1,0,0.1,0,0.2,0
	c0.1,0,0.2,0,0.2,0c0.1,0,0.2,0,0.3,0c0.1,0,0.1,0,0.2,0c0.2,0,0.3,0,0.5,0c2.2,0,4.3-0.8,5.9-2.4c0.2,0.3,0.5,0.5,0.7,0.7
	c1.2,1,2.6,1.6,4.2,1.6c0.2,0,0.3,0,0.4,0c0.2,0,0.5,0,0.7,0c0.1,0,0.1,0,0.2,0h0.1c0.4,0,0.9-0.1,1.3-0.2l0,0l0.2-0.1
	c0.3-0.1,0.5-0.2,0.8-0.3c0.9-0.3,2.3-0.9,4-1.6c0,0,0.2,0,0.4,0c2.3,1.5,5,2.2,7.8,2.2c2.3,0,4.5-0.5,6.6-1.5
	c1.1-0.5,1.6-1.8,1-2.9C187.2,81.5,185.9,81.1,184.8,81.6 M88.8,75.6c-0.6,4.1-4,7.2-8.1,7.4c-0.5,0-1-0.1-1.5-0.2
	c-1.1-0.3-2-0.9-2.7-1.7c-1.1-1.4-1.5-3.1-1.2-4.8v0c0-0.3,0.1-0.6,0.2-0.9l0.6-1.7c0,0,0,0,0-0.1c0.1-0.1,0.1-0.2,0.2-0.3
	c0,0,0-0.1,0.1-0.1c0.5-0.8,1.1-1.5,1.8-2.2c1.4-1.3,3.3-2.1,5.2-2.1c0.8,0,1.6,0.2,2.4,0.5c0.7,0.3,1.4,0.8,1.9,1.4
	C88.7,72.1,89.2,73.9,88.8,75.6 M147.6,75.6c-0.4,2.6-2,4.9-4.2,6.3c0,0-0.1,0-0.1,0c0,0,0,0,0,0v0c-1.1,0.7-2.5,1-3.8,1
	c-0.6,0-1.2-0.1-1.8-0.3c-1-0.3-1.8-0.9-2.5-1.6c-0.7-0.8-1.1-1.8-1.2-2.8v0c-0.1-0.7-0.1-1.3,0-2c0.2-1,0.5-2,1-2.9
	c0.7-1.2,1.6-2.2,2.7-3c1.3-0.9,2.8-1.4,4.4-1.5c1,0,2,0.2,2.9,0.7c0.5,0.3,1,0.7,1.4,1.2c0.2,0.2,0.4,0.5,0.5,0.8
	c0.5,0.9,0.8,2,0.8,3.1C147.7,74.9,147.7,75.3,147.6,75.6 M177.2,71.8c1.3-1.3,3-2,4.9-2c0.6,0,1.3,0.1,1.9,0.3
	c1,0.3,1.6,0.8,1.6,1.2c0.1,0.3-0.3,1-1.3,1.8c-3.4,2.5-7,4.7-10.7,6.4C173.5,77.4,174.7,74.2,177.2,71.8 M44.2,71.7
	c2.6-4.3,3-9.6,1-15.8l8.5-2.6c1.1-0.3,1.8-1.6,1.4-2.7c-0.1-0.2-0.2-0.4-0.3-0.6c-0.3-0.4-6.7-9.2-14.8-9.4
	c-4-0.1-7.6,1.8-10.7,5.7c-0.8,0.9-0.6,2.3,0.3,3.1c0.9,0.8,2.3,0.6,3.1-0.3l0,0c2.2-2.8,4.6-4.1,7.2-4.1c3.7,0.1,7.2,3,9.4,5.2
	l-7.6,2.3c-1.1,0.3-1.8,1.6-1.4,2.7c0,0,0,0,0,0c0,0.1,0,0.1,0.1,0.2c2.3,5.8,2.3,10.5,0.1,14.2C36.7,75.8,27,77,26.7,77
	c-3.4,0.4-6.8,1.2-10.1,2.4c-7.3,2.6-13,6.8-16.3,11.9c-0.6,1-0.4,2.4,0.7,3c1,0.6,2.4,0.4,3-0.7l0,0c6.8-10.7,23-12.4,23.2-12.4h0
	C27.7,81.3,39.2,80,44.2,71.7 M34.8,28.7c-1.3,0-2.5,0.1-3.8,0.2c2.1-9.1,1.5-18.6-1.6-27.5C29.3,1.2,29.2,1,29,0.8
	c0,0-0.1-0.1-0.1-0.1c0,0,0,0,0,0c0,0-0.1-0.1-0.1-0.1c0,0-0.1-0.1-0.1-0.1c0,0,0,0-0.1-0.1c0,0-0.1-0.1-0.1-0.1l0,0
	c0,0-0.1-0.1-0.1-0.1l-0.1,0c0,0-0.1,0-0.1-0.1l-0.1,0c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0s-0.1,0-0.1,0l-0.1,0
	c0,0-0.1,0-0.1,0h-0.2l-0.1,0c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0c0,0-0.1,0-0.1,0s-0.1,0-0.1,0c0,0,0,0-0.1,0c0,0-0.1,0-0.1,0.1
	c-0.2,0.1-0.4,0.2-0.6,0.4c-4.8,4.5-8.7,9.8-11.3,15.8c-0.5,1.1,0,2.4,1.1,2.9c1.1,0.5,2.4,0,2.9-1.1c2-4.4,4.6-8.4,7.9-11.9
	c2,7.3,2.1,15,0.3,22.4c-0.1,0.4-0.2,0.9-0.3,1.3C13.8,33.7,5.3,45.1,5.3,58.1v0c0,0,0,0.1,0,0.1c0,0,0,0,0,0.1c0,0,0,0.1,0,0.1
	c0,0,0,0,0,0.1c0,0,0,0.1,0,0.1c0,0,0,0,0,0.1c0,0,0,0.1,0.1,0.1l0,0.1c0,0,0,0.1,0.1,0.1l0,0c0,0,0,0,0,0.1s0,0,0,0.1l0.1,0.1
	c0.1,0.1,0.2,0.3,0.4,0.4l0,0c0,0,0.1,0.1,0.2,0.1c0.2,0.1,0.3,0.2,0.5,0.3l0,0c0.1,0,0.1,0,0.2,0l0,0c0.5,0.1,1.1,0,1.5-0.3
	c10-6,17.4-15.4,21.1-26.5c13.6-2.8,26.9,6,29.6,19.5s-6,26.9-19.5,29.6c-1.7,0.3-3.3,0.5-5,0.5c-1.2,0-2.2,1-2.2,2.2
	c0,1.2,1,2.2,2.2,2.2c16.3,0,29.4-13.2,29.4-29.5C64.2,41.9,51,28.7,34.8,28.7 M10,53.8c1.4-8.2,6.8-15.1,14.4-18.5
	C21.4,42.6,16.4,49,10,53.8 M27.8,70c1,0.7,2.3,0.4,3-0.6c2.2-3.4,3-6.5,2.2-9.4c-0.6-2.3-2.1-4.2-4.1-5.4c-0.5-0.3-1.1-0.3-1.6-0.2
	c-5.4,1.6-14.1,11.8-15,13c-0.8,0.9-0.6,2.3,0.3,3.1c0.9,0.8,2.3,0.6,3.1-0.3l0,0c3-3.6,8.6-9.5,12-11.2c0.6,0.6,1,1.3,1.2,2.1
	c0.4,1.6-0.2,3.6-1.7,5.9C26.5,68,26.8,69.3,27.8,70"></path>
</svg>
                    </strong></a>
                </footer>
            </div>

        </div>
    </div>
</template>
