<script setup>
import { ref, computed, h } from 'vue';
import { NButton, NIcon, NAvatar } from 'naive-ui';
import * as IconNamespace from '@vicons/ionicons5';

const props = defineProps({
    card: { type: Object, required: true },
    sections: { type: Array, default: () => [] }
});

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

const primaryColor = computed(() => props.card.theme?.config?.primary || '#1a1a1a');
const secondaryColor = computed(() => props.card.theme?.config?.colors?.secondary || '#6b7280');
const backgroundColor = computed(() => props.card.theme?.config?.colors?.background || '#ffffff');
const cardBackgroundColor = computed(() => props.card.theme?.config?.colors?.card_background || '#ffffff');
const headingFont = computed(() => props.card.theme?.config?.fonts?.heading || 'Inter');
const bodyFont = computed(() => props.card.theme?.config?.fonts?.body || 'Inter');

const contactSection = computed(() => parsedSections.value.find(s => s.section_type === 'contact'));
const email = computed(() => contactSection.value?.content?.email);
const phone = computed(() => contactSection.value?.content?.phone);

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
        'Share': 'ShareSocialOutline'
    };
    
    if (iconMap[iconName] && icons[iconMap[iconName]]) return icons[iconMap[iconName]];
    if (icons[iconName]) return icons[iconName];
    if (icons[iconName + 'Outline']) return icons[iconName + 'Outline'];
    
    return icons.DocumentTextOutline;
};

const renderIcon = (name) => () => h(getIconComponent(name));

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
    <div class="card-viewer" :style="themeStyles">
        <div class="content-wrapper">
            <!-- Header -->
            <header class="header-section">
                <div class="avatar-wrapper">
                    <NAvatar
                        v-if="card.theme?.config?.images?.logo?.url"
                        round
                        :size="100"
                        :src="card.theme.config.images.logo.url"
                        class="logo-avatar"
                    />
                    <div v-else class="initials-avatar">
                        {{ card.title.charAt(0) }}
                    </div>
                </div>
                
                <h1 class="card-title">{{ card.title }}</h1>
                <p class="card-subtitle">{{ card.subtitle }}</p>

                <div class="action-buttons">
                    <NButton v-if="phone" circle quaternary size="large" tag="a" :href="`tel:${phone}`">
                        <template #icon><NIcon><component :is="renderIcon('Phone')" /></NIcon></template>
                    </NButton>
                    <NButton v-if="email" circle quaternary size="large" tag="a" :href="`mailto:${email}`">
                        <template #icon><NIcon><component :is="renderIcon('Mail')" /></NIcon></template>
                    </NButton>
                    <NButton circle quaternary size="large" @click="shareCard">
                        <template #icon><NIcon><component :is="renderIcon('Share')" /></NIcon></template>
                    </NButton>
                </div>
            </header>

            <!-- Main Sections -->
            <main class="sections-container">
                <div v-for="section in parsedSections" :key="section.id" class="section-block">
                    <div class="section-label">
                        <NIcon :color="primaryColor" :size="16">
                            <component :is="renderIcon(section.section_type.charAt(0).toUpperCase() + section.section_type.slice(1))" />
                        </NIcon>
                        <span>{{ section.title }}</span>
                    </div>

                    <!-- Image Support for any section -->
                    <div v-if="section.image_url" class="section-image-wrapper">
                        <img :src="section.image_url" :alt="section.title" class="section-main-image" />
                    </div>

                    <!-- Contact -->
                    <div v-if="section.section_type === 'contact'" class="glass-box contact-info">
                        <div v-if="section.content.email" class="data-row">
                            <span class="row-label">Email</span>
                            <a :href="`mailto:${section.content.email}`" class="row-value">{{ section.content.email }}</a>
                        </div>
                        <div v-if="section.content.phone" class="data-row">
                            <span class="row-label">Phone</span>
                            <a :href="`tel:${section.content.phone}`" class="row-value">{{ section.content.phone }}</a>
                        </div>
                    </div>

                    <!-- Products/Services -->
                    <div v-else-if="['services', 'products'].includes(section.section_type)" class="items-list">
                        <div v-for="(item, idx) in section.content.items" :key="idx" class="glass-box item-row">
                            <div v-if="item.image_url" class="item-thumbnail">
                                <img :src="item.image_url" :alt="item.name" />
                            </div>
                            <div class="item-detail">
                                <span class="item-title">{{ item.name }}</span>
                                <p v-if="item.description" class="item-description">{{ item.description }}</p>
                            </div>
                            <div v-if="item.price" class="item-price-tag">{{ item.price }}</div>
                        </div>
                    </div>

                    <!-- About -->
                    <div v-else-if="section.section_type === 'about'" class="glass-box about-content">
                        <p>{{ section.content.text }}</p>
                    </div>

                    <!-- External Links -->
                    <div v-else-if="section.section_type === 'links'" class="links-list">
                        <NButton
                            v-for="(link, idx) in section.content.items" 
                            :key="idx"
                            block
                            secondary
                            class="link-button"
                            tag="a"
                            :href="link.url"
                            target="_blank"
                        >
                            {{ link.label || link.url }}
                        </NButton>
                    </div>

                    <!-- Social Media -->
                    <div v-else-if="section.section_type === 'social'" class="social-grid">
                        <NButton 
                            v-for="(url, platform) in section.content" 
                            :key="platform"
                            v-show="url"
                            quaternary 
                            circle
                            size="large"
                            tag="a"
                            :href="url"
                            target="_blank"
                        >
                            <template #icon>
                                <NIcon><component :is="renderIcon('Logo' + platform.charAt(0).toUpperCase() + platform.slice(1))" /></NIcon>
                            </template>
                        </NButton>
                    </div>
                </div>
            </main>

            <footer class="viewer-footer">
                <span>Powered by <strong>Qard</strong></span>
            </footer>
        </div>
    </div>
</template>

<style scoped>
.card-viewer {
    min-height: 100vh;
    background-color: var(--background);
    color: #111827;
    padding: 60px 24px;
    font-family: var(--body-font), system-ui, sans-serif;
}

.content-wrapper {
    max-width: 440px;
    margin: 0 auto;
}

.header-section {
    text-align: center;
    margin-bottom: 56px;
}

.avatar-wrapper {
    margin-bottom: 24px;
    display: flex;
    justify-content: center;
}

.logo-avatar {
    border: 1px solid var(--border);
    box-shadow: 0 4px 20px -4px rgba(0,0,0,0.08);
}

.initials-avatar {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    font-weight: 700;
    color: var(--primary);
    background-color: var(--primary-soft);
}

.card-title {
    font-family: var(--heading-font), sans-serif;
    font-size: 32px;
    font-weight: 800;
    letter-spacing: -0.03em;
    margin: 0 0 4px 0;
    color: #111827;
}

.card-subtitle {
    font-size: 17px;
    color: var(--secondary);
    font-weight: 400;
}

.action-buttons {
    display: flex;
    justify-content: center;
    gap: 16px;
    margin-top: 28px;
}

.sections-container {
    display: flex;
    flex-direction: column;
    gap: 40px;
}

.section-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    font-size: 12px;
    font-weight: 700;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.glass-box {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: 20px;
    padding: 24px;
    transition: all 0.2s ease;
}

.glass-box:hover {
    border-color: #e5e7eb;
    box-shadow: 0 4px 12px rgba(0,0,0,0.02);
}

.section-image-wrapper {
    margin-bottom: 24px;
    border-radius: 20px;
    overflow: hidden;
    line-height: 0;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.section-main-image {
    width: 100%;
    height: auto;
    object-fit: cover;
    max-height: 350px;
}

.data-row {
    display: flex;
    flex-direction: column;
}

.row-label {
    font-size: 11px;
    font-weight: 600;
    color: #9ca3af;
    margin-bottom: 2px;
}

.row-value {
    font-size: 16px;
    font-weight: 500;
    color: #111827;
    text-decoration: none;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.item-card, .item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px 20px;
    gap: 16px;
}

.item-thumbnail {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
    border: 1px solid var(--border);
}

.item-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-detail {
    flex: 1;
}

.item-title {
    font-weight: 600;
    font-size: 16px;
    display: block;
}

.item-description {
    font-size: 14px;
    color: #6b7280;
    margin: 4px 0 0 0;
    line-height: 1.4;
}

.item-price-tag {
    font-weight: 700;
    font-size: 15px;
    color: var(--primary);
}

.link-button {
    height: 56px;
    border-radius: 16px;
    font-weight: 600;
    font-size: 16px;
    border: 1px solid var(--border) !important;
}

.about-content p {
    font-size: 16px;
    line-height: 1.6;
    color: #374151;
    margin: 0;
}

.social-grid {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
}

.viewer-footer {
    margin-top: 80px;
    padding-bottom: 40px;
    text-align: center;
    font-size: 14px;
    color: #9ca3af;
}

@media (max-width: 480px) {
    .card-viewer {
        padding: 40px 20px;
    }
    
    .card-title {
        font-size: 28px;
    }
}
</style>
