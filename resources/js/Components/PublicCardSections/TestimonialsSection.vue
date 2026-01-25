<script setup>
const props = defineProps({
    content: { type: [Array, Object], required: true },
    title: { type: String, default: '' }
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title my-4">{{ title }}</h2>
        <div class="testimonials-grid">
            <div v-for="(testimonial, idx) in content" :key="idx" class="testimonial-card">
                <p class="testimonial-quote">"{{ testimonial.quote }}"</p>
                <div class="testimonial-author">
                    <strong>{{ testimonial.author }}</strong>
                    <span v-if="testimonial.company"> · {{ testimonial.company }}</span>
                </div>
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

.testimonials-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.testimonial-card {
    background: #f4f5fa;
    border-radius: 16px;
    padding: 16px;
}

.testimonial-quote {
    font-size: 15px;
    color: #2e385c;
    font-style: italic;
    margin: 0 0 12px 0;
    line-height: 1.6;
}

.testimonial-author {
    font-size: 14px;
    color: #666b7f;
}
</style>
