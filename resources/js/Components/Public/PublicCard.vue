<script setup>
import { ref, computed, h, onMounted } from 'vue';
import { NButton, NIcon, NAvatar } from 'naive-ui';
import * as IconNamespace from '@vicons/ionicons5';
import ServicesSection from './Sections/ServicesSection.vue';
import ProductsSection from './Sections/ProductsSection.vue';
import TestimonialsSection from './Sections/TestimonialsSection.vue';
import GallerySection from './Sections/GallerySection.vue';
import TextSection from './Sections/TextSection.vue';
import AppointmentsSection from './Sections/AppointmentsSection.vue';
import '../../../css/public-card.css';
import PowerBy from "@/Components/Shared/PowerBy.vue";
import { SOCIAL_ICONS, normalizeLinks } from '@/Components/Shared/SocialIcons.js';

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

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
        download_qr_png: 'Download QR',
        download_qr_svg: 'Download SVG',
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
        download_qr_png: 'تحميل QR',
        download_qr_svg: 'تحميل SVG',
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
const textColor = computed(() => props.card.theme?.config?.colors?.text || '#1f2937');
const cardBackgroundColor = computed(() => props.card.theme?.config?.colors?.card_bg || '#ffffff');
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

    // Use normalizeLinks to handle both old format {platform: url} and new format {links: [{platform, url}]}
    const links = normalizeLinks(content);

    // Return links with their platform as key (for icon lookup) and filter out empty URLs
    return links
        .filter(link => link.url && link.url.trim())
        .map(link => ({ key: link.platform, url: link.url }));
});

const getShareUrl = () => props.card?.full_url || window.location.href;

const getQrUrl = (format = 'png') => {
    const data = props.card?.full_url || window.location.href;
    const size = 150;
    if (format === 'png' && props.card?.qr_code_url) return props.card.qr_code_url;
    const params = new URLSearchParams({ size: `${size}x${size}`, data });
    if (format === 'svg') params.append('format', 'svg');
    return `https://api.qrserver.com/v1/create-qr-code/?${params.toString()}`;
};

const downloadQr = async (format = 'png') => {
    const url = getQrUrl(format);
    const name = (t(props.card.title) || 'card').toString().replace(/\s+/g, '_');
    const filename = `${name}_qr.${format === 'svg' ? 'svg' : 'png'}`;

    try {
        const response = await fetch(url);
        const blob = await response.blob();
        const blobUrl = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = blobUrl;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        a.remove();
        setTimeout(() => URL.revokeObjectURL(blobUrl), 500);
    } catch {
        window.open(url, '_blank');
    }
};

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

const isClosedHours = (value) => {
    if (value === null || value === undefined) return true;
    if (typeof value === 'string') return value.trim() === '' || value.trim().toLowerCase() === 'closed';
    if (typeof value === 'object') {
        const start = value.start || value.from || '';
        const end = value.end || value.to || '';
        return !start && !end;
    }
    return false;
};

const formatHoursValue = (value) => {
    const closedLabel = ut('closed') || 'Closed';
    if (isClosedHours(value)) return closedLabel;
    if (typeof value === 'string') {
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

const cardCoverUrl = computed(() => {
    return props.card.theme?.config?.images?.header?.url || props.card.cover_image_url;
});

const cardProfileUrl = computed(() => {
    return props.card.theme?.config?.images?.logo?.url || props.card.profile_image_url;
});

const themeStyles = computed(() => ({
    '--primary': primaryColor.value,
    '--secondary': secondaryColor.value,
    '--background': backgroundColor.value,
    '--text': textColor.value,
    '--card-bg': cardBackgroundColor.value,
    '--heading-font': headingFont.value,
    '--body-font': bodyFont.value,
    '--primary-soft': primaryColor.value.length === 7 ? primaryColor.value + '10' : primaryColor.value,
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
                        <img v-if="cardCoverUrl" :src="cardCoverUrl" class="cover-image" />
                        <div v-else class="cover-placeholder" />

                        <div class="avatar-wrap">
                            <div class="avatar-ring">
                                <NAvatar
                                    v-if="cardProfileUrl"
                                    :round="true"
                                    :size="100"
                                    :src="cardProfileUrl"
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

    		    <span class="social-icon" v-html="SOCIAL_ICONS[item.key] || ''" />
                    </a>
                </div>

                <!-- Contact cards (email + phone + address + website) -->
                <div v-if="contactFields.length" class="contact-stack">
                    <!-- Email (full width) -->
                    <template v-for="field in contactFields.filter(f => f.type === 'email')" :key="field.type">
                        <a class="info-card info-card--hover" :href="field.href">
                            <span class="info-icon block w-[30px] mb-1"><component :is="renderIcon(field.icon)" /></span>
                            <span class="info-text" :class="{ 'phone-ltr': field.type === 'phone' }">{{ field.value }}</span>
                        </a>
                    </template>

                    <!-- Phone + Website (2 columns) -->
                    <div v-if="contactFields.some(f => f.type === 'phone' || f.type === 'website')" class="two-col">
                        <template v-for="field in contactFields.filter(f => f.type === 'phone' || f.type === 'website')" :key="field.type">
                            <a v-if="field.href" class="info-card info-card--hover" :class="{ 'info-card--clickable': field.type === 'website' }" :href="field.href" :target="field.type === 'website' ? '_blank' : undefined" :rel="field.type === 'website' ? 'noopener noreferrer' : undefined">
                                <span class="info-icon block w-[30px] mb-1"><component :is="renderIcon(field.icon)" /></span>
                                <span class="info-text" :class="{ 'phone-ltr': field.type === 'phone' }">{{ field.value }}</span>
                            </a>
                        </template>
                    </div>

                    <!-- Address (full width) -->
                    <template v-for="field in contactFields.filter(f => f.type === 'address')" :key="field.type">
                        <div class="info-card info-card--hover">
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
                                :src="getQrUrl('png')"
                                class="qr-img cursor-pointer"
                                alt="QR"
                                @click="downloadQr('png')"
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
                    <div class="hours-title my-4">{{ ut('hours') }}</div>
                    <div class="hours-list">
                        <template v-for="section in parsedSections" :key="section.id">
                            <template v-if="section.section_type === 'hours'">
                                <div v-for="day in days" :key="day" class="hour-row" :class="{ 'hour-row--closed': isClosedHours(sc(section)[day]) }">
                                    <div class="hour-icon"><component :is="renderIcon('CalendarCheck')" /></div>
                                    <div class="hour-text">
                                        <div class="hour-day">{{ formatDayLabel(day) }}</div>
                                        <div class="hour-time">{{ formatHoursValue(sc(section)[day]) }}</div>
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
                    <PowerBy :title="ut('powered_by')" />
                </footer>
            </div>

        </div>
    </div>
</template>
