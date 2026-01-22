<script setup>
const props = defineProps({
    content: { type: Object, required: true },
    title: { type: String, default: '' }
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title">{{ title }}</h2>
        <div class="appointment-card">
            <p v-if="content.instructions" class="appointment-instructions">{{ content.instructions }}</p>
            <a v-if="content.booking_url" :href="content.booking_url" target="_blank" rel="noopener noreferrer" class="appointment-btn">
                Book Appointment
            </a>
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

.appointment-card {
    background: #f4f5fa;
    border-radius: 16px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    align-items: center;
}

.appointment-instructions {
    font-size: 14px;
    color: #666b7f;
    text-align: center;
    margin: 0;
    line-height: 1.5;
}

.appointment-btn {
    background: #6198f1;
    color: white;
    padding: 12px 24px;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 600;
    display: inline-block;
    transition: background 0.2s;
}

.appointment-btn:hover {
    background: #5088e0;
}
</style>
