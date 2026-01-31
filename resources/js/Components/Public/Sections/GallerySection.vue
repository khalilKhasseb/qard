<script setup>
const props = defineProps({
    content: { type: [Array, Object], required: true },
    title: { type: String, default: '' }
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title my-4">{{ title }}</h2>
        <div class="gallery-grid">
            <div v-for="(item, idx) in (Array.isArray(content) ? content : (content.items || content.images || []))" :key="idx" class="gallery-item">
                <img v-if="(typeof item === 'string' ? item : (item.url || item.image_url))" :src="(typeof item === 'string' ? item : (item.url || item.image_url))" :alt="item.caption || ''" class="gallery-img" />
                <div v-else class="w-full h-40 flex items-center justify-center text-gray-300">No image</div>
                <p v-if="item.caption" class="gallery-caption">{{ item.caption }}</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.section-block {
    margin-top: 24px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #2e385c;
    text-align: center;
    margin-bottom: 16px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.gallery-item {
    background: #f4f5fa;
    border-radius: 16px;
    overflow: hidden;
}

.gallery-img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}

.gallery-caption {
    font-size: 13px;
    color: #666b7f;
    padding: 12px;
    margin: 0;
}
</style>
