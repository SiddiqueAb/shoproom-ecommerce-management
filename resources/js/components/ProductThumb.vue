<script setup>
defineProps({ src: { type: String, default: '' }, name: { type: String, default: 'Product' }, large: Boolean });
</script>

<template>
    <div class="product-thumb" :class="{ large }">
        <img v-if="src" :src="src" :alt="name" loading="lazy" @error="$event.target.remove()">
        <span>{{ name.slice(0, 1) || 'P' }}</span>
    </div>
</template>
