<script setup>
defineProps({ title: String, subtitle: String, wide: Boolean });
defineEmits(['close']);
</script>

<template>
    <div class="modal-backdrop" @click.self="$emit('close')" @keydown.esc="$emit('close')">
        <section class="modal" :class="{ 'modal-wide': wide }" role="dialog" aria-modal="true" :aria-label="title">
            <header class="modal-head">
                <div>
                    <h2>{{ title }}</h2>
                    <p v-if="subtitle">{{ subtitle }}</p>
                </div>
                <button class="modal-close" aria-label="Close" @click="$emit('close')">×</button>
            </header>
            <div class="modal-body"><slot /></div>
        </section>
    </div>
</template>
