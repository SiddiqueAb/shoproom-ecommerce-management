<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import api from '../api';
import { formatPrice } from '../format';

const route = useRoute();
const shop = ref(null);
const products = ref([]);
const loading = ref(true);
const missing = ref(false);
const search = ref('');
const visibleProducts = computed(() => products.value.filter((product) => `${product.name} ${product.category || ''} ${product.description || ''}`.toLowerCase().includes(search.value.toLowerCase())));

onMounted(async () => {
    try {
        const { data } = await api.get(`/api/store/${encodeURIComponent(route.params.slug)}`);
        shop.value = data.shop;
        products.value = data.products;
    } catch {
        missing.value = true;
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="store-page">
        <header class="store-nav"><div class="store-brand">Shop<span>room</span></div><small>{{ shop?.location || 'Local shop' }} <span v-if="shop?.phone">· {{ shop.phone }}</span></small></header>
        <div v-if="loading" class="loading-screen"><div><div class="spinner"></div>Opening shop</div></div>
        <template v-else-if="shop && !missing">
            <section class="store-hero"><div class="eyebrow">Independent shop</div><h1>{{ shop.name }}</h1><p>Thoughtfully chosen goods, available from your local shop.</p></section>
            <main class="store-content">
                <div class="store-content-head">
                    <div><h2>Products</h2><p>{{ products.length }} {{ products.length === 1 ? 'item' : 'items' }} available</p></div>
                    <div class="searchbox"><span class="search-icon">⌕</span><input v-model="search" class="input" type="search" placeholder="Find a product"></div>
                </div>
                <div v-if="visibleProducts.length" class="store-product-grid">
                    <article v-for="product in visibleProducts" :key="product.id" class="store-product">
                        <div class="store-product-image"><img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name" loading="lazy" @error="$event.target.remove()"><span>{{ product.name.slice(0, 1) }}</span></div>
                        <div class="store-product-info"><div class="category">{{ product.category || 'Shop pick' }}</div><h3>{{ product.name }}</h3><p>{{ product.description || 'Ask in store for more details.' }}</p><div class="store-price">{{ formatPrice(product.price) }}</div></div>
                    </article>
                </div>
                <div v-else class="empty-state"><div class="empty-mark">◇</div><h3>{{ products.length ? 'No products found' : 'No products available just yet' }}</h3><p>{{ products.length ? 'Try a different search.' : 'Please check back soon.' }}</p></div>
            </main>
            <footer class="store-footer">A local storefront powered by Shoproom</footer>
        </template>
        <main v-else class="store-content"><div class="empty-state"><div class="empty-mark">?</div><h3>We could not find that shop</h3><p>The shop may have moved or its storefront is currently unavailable.</p><a href="/login" class="button button-secondary">Go to sign in</a></div></main>
    </div>
</template>
