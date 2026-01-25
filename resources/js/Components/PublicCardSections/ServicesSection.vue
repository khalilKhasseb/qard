<script setup>
const props = defineProps({
    content: { type: [Array, Object], required: true },
    title: { type: String, default: '' }
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title my-4">{{ title }}</h2>
        <div class="services-grid">
            <div v-for="(service, idx) in content" :key="idx" class="service-card">
                <h3 class="service-name">{{ service.name }}</h3>
                <p v-if="service.description" class="service-desc">{{ service.description }}</p>
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

.services-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.service-card {
    background: #f4f5fa;
    border-radius: 16px;
    padding: 16px;
}

.service-name {
    font-size: 16px;
    font-weight: 600;
    color: #2e385c;
    margin: 0 0 8px 0;
}

.service-desc {
    font-size: 14px;
    color: #666b7f;
    margin: 0;
    line-height: 1.5;
}
</style>
