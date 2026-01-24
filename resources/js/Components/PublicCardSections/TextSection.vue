<script setup>
import { computed } from 'vue';
const props = defineProps({
    content: { type: String, required: true },
    title: { type: String, default: '' }
});

const textContent = computed(() => {
    if (typeof props.content === 'string') {
        return props.content;  // Fallback for plain strings
    }
    return props.content?.text || '';  // Access the "text" key
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title">{{ title }}</h2>
        <div class="text-content">
            <p>{{ textContent }}</p>
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

.text-content {
    background: #f4f5fa;
    border-radius: 16px;
    padding: 16px;
}

.text-content p {
    font-size: 15px;
    color: #2e385c;
    line-height: 1.6;
    margin: 0;
    white-space: pre-wrap;
}
</style>
