<script setup>
const props = defineProps({
    content: { type: [Array, Object], required: true },
    title: { type: String, default: '' }
});
</script>

<template>
    <div class="section-block">
        <h2 v-if="title" class="section-title">{{ title }}</h2>
        <div class="products-grid">
            <div v-for="(product, idx) in content" :key="idx" class="product-card">
                <div class="product-header">
                    <h3 class="product-name">{{ product.name }}</h3>
                    <span v-if="product.price" class="product-price">{{ product.price }}</span>
                </div>
                <p v-if="product.description" class="product-desc">{{ product.description }}</p>
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

.products-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.product-card {
    background: #f4f5fa;
    border-radius: 16px;
    padding: 16px;
}

.product-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.product-name {
    font-size: 16px;
    font-weight: 600;
    color: #2e385c;
    margin: 0;
}

.product-price {
    font-size: 16px;
    font-weight: 700;
    color: #6198f1;
}

.product-desc {
    font-size: 14px;
    color: #666b7f;
    margin: 0;
    line-height: 1.5;
}
</style>
