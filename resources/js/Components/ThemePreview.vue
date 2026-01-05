<script setup>
import { computed } from 'vue';

const props = defineProps({
    config: {
        type: Object,
        required: true
    },
    device: {
        type: String,
        default: 'desktop' // 'desktop' or 'mobile'
    }
});

const previewStyles = computed(() => {
    const config = props.config || {};
    const colors = config.colors || {};
    const fonts = config.fonts || {};
    const images = config.images || {};
    
    const styles = {
        backgroundColor: colors.background || '#ffffff',
        color: colors.text || '#1f2937',
        fontFamily: fonts.body || 'Inter',
        minHeight: '400px',
        padding: '2rem',
    };

    if (images.background?.url) {
        styles.backgroundImage = `url(${images.background.url})`;
        styles.backgroundSize = 'cover';
        styles.backgroundPosition = 'center';
    }

    return styles;
});

const cardStyles = computed(() => {
    const config = props.config || {};
    const colors = config.colors || {};
    const layout = config.layout || {};
    
    const styles = {
        backgroundColor: colors.card_bg || '#f9fafb',
        borderRadius: layout.border_radius || '12px',
        textAlign: layout.alignment || 'center',
        padding: '1.5rem',
        maxWidth: props.device === 'mobile' ? '100%' : '600px',
        margin: '0 auto',
    };

    if (layout.card_style === 'elevated') {
        styles.boxShadow = '0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)';
    } else if (layout.card_style === 'outlined') {
        styles.border = '2px solid #e5e7eb';
    }

    return styles;
});

const headingStyles = computed(() => {
    const config = props.config || {};
    const colors = config.colors || {};
    const fonts = config.fonts || {};
    
    return {
        fontFamily: fonts.heading || 'Inter',
        color: colors.primary || '#3b82f6',
    };
});

const buttonStyles = computed(() => {
    const config = props.config || {};
    const colors = config.colors || {};
    
    return {
        backgroundColor: colors.primary || '#3b82f6',
        color: '#ffffff',
        borderRadius: (config.layout?.border_radius || '12px').replace(/(\d+)/, (match) => Math.floor(parseInt(match) / 2)),
    };
});
</script>

<template>
    <div 
        class="theme-preview-container"
        :class="device === 'mobile' ? 'mobile-preview' : 'desktop-preview'"
        :style="previewStyles"
    >
        <div class="card-preview" :style="cardStyles">
            <!-- Header/Logo -->
            <div v-if="config.images?.logo?.url" class="mb-4">
                <img :src="config.images.logo.url" alt="Logo" class="h-16 mx-auto" />
            </div>

            <!-- Name -->
            <h1 class="text-3xl font-bold mb-2" :style="headingStyles">
                John Doe
            </h1>
            
            <!-- Title -->
            <p class="text-lg opacity-80 mb-4" :style="{ fontFamily: config.fonts?.body || 'Inter' }">
                Software Engineer & Designer
            </p>

            <!-- Contact Section -->
            <div class="section-preview mb-4 p-3 rounded-lg" style="background-color: rgba(0, 0, 0, 0.03)">
                <h3 class="font-semibold mb-2" :style="headingStyles">Contact</h3>
                <div class="text-sm space-y-1">
                    <p>📧 john@example.com</p>
                    <p>📞 +1 (555) 123-4567</p>
                    <p>📍 San Francisco, CA</p>
                </div>
            </div>

            <!-- Social Links Section -->
            <div class="section-preview mb-4 p-3 rounded-lg" style="background-color: rgba(0, 0, 0, 0.03)">
                <h3 class="font-semibold mb-2" :style="headingStyles">Social Links</h3>
                <div class="flex justify-center gap-3">
                    <span class="text-2xl">🔗</span>
                    <span class="text-2xl">💼</span>
                    <span class="text-2xl">🐦</span>
                </div>
            </div>

            <!-- CTA Button -->
            <button 
                class="btn-preview w-full py-3 px-6 font-semibold transition-all"
                :style="buttonStyles"
            >
                Get In Touch
            </button>

            <!-- Services Preview -->
            <div class="mt-4 grid grid-cols-2 gap-2 text-sm">
                <div class="p-2 rounded" style="background-color: rgba(0, 0, 0, 0.03)">
                    <div class="font-semibold">Web Design</div>
                </div>
                <div class="p-2 rounded" style="background-color: rgba(0, 0, 0, 0.03)">
                    <div class="font-semibold">Development</div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.theme-preview-container {
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    overflow: hidden;
}

.desktop-preview {
    width: 100%;
}

.mobile-preview {
    max-width: 375px;
    margin: 0 auto;
}

.card-preview {
    transition: all 0.3s ease;
}

.btn-preview:hover {
    opacity: 0.9;
    transform: translateY(-1px);
}
</style>
