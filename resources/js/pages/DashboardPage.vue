<script setup>
import { onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import api from '../api';
import { auth } from '../auth';
import { formatPrice } from '../format';
import ProductThumb from '../components/ProductThumb.vue';

const busy = ref(true);
const data = ref(null);
const loadError = ref('');
const isAdmin = auth.user?.role === 'admin';

onMounted(async () => {
    try {
        const { data: result } = await api.get(isAdmin ? '/api/admin/overview' : '/api/owner/dashboard');
        data.value = result;
    } catch (error) {
        loadError.value = error?.response?.data?.message || 'We could not load this overview.';
    } finally {
        busy.value = false;
    }
});
</script>

<template>
    <div v-if="busy" class="loading-screen"><div><div class="spinner"></div>Loading your workspace</div></div>
    <template v-else-if="data">
        <div class="page-heading">
            <div>
                <div class="eyebrow">{{ isAdmin ? 'Administrator' : 'Shop owner' }} workspace</div>
                <h1>{{ isAdmin ? 'Overview' : `Hello, ${auth.user.name.split(' ')[0]}` }}</h1>
                <p>{{ isAdmin ? 'A quick look at the shops and products on the platform.' : 'Here is what is happening in your shop today.' }}</p>
            </div>
            <RouterLink v-if="!isAdmin && data.shop" :to="`/shop/${data.shop.slug}`" target="_blank" class="button button-secondary">View customer shop <span>↗</span></RouterLink>
        </div>

        <div class="stat-grid">
            <div v-for="stat in (isAdmin ? [
                { label: 'Shops', value: data.shops, note: 'Registered stores' },
                { label: 'Shop owners', value: data.owners, note: 'Owner accounts' },
                { label: 'Catalog products', value: data.products, note: 'Shared across shops' },
                { label: 'Active listings', value: data.listings, note: 'Visible to customers' },
            ] : [
                { label: 'Shop listings', value: data.listings, note: 'Products in your store' },
                { label: 'Available listings', value: data.active_listings, note: 'Currently visible' },
                { label: 'Shared products', value: data.catalog_products, note: 'Available to browse' },
            ])" :key="stat.label" class="card stat-card">
                <div class="stat-label">{{ stat.label }}<span class="stat-dot"></span></div>
                <div class="stat-value">{{ stat.value }}</div>
                <div class="stat-note">{{ stat.note }}</div>
            </div>
        </div>

        <div v-if="!isAdmin && !data.shop" class="notice-bar">{{ loadError || 'This account has not been assigned to a shop yet. Ask an administrator to set up your shop.' }}</div>

        <div class="panel-grid">
            <section class="card section-card">
                <div class="section-head">
                    <div><h2>{{ isAdmin ? 'Recently added shops' : 'Recently updated products' }}</h2><p>{{ isAdmin ? 'Your latest shop accounts' : 'Changes to your shop listings' }}</p></div>
                    <RouterLink v-if="isAdmin" to="/admin/shops" class="button-quiet">All shops →</RouterLink>
                    <RouterLink v-else to="/owner/listings" class="button-quiet">Manage products →</RouterLink>
                </div>

                <div v-if="isAdmin && data.recent_shops.length" class="table-wrap">
                    <table>
                        <thead><tr><th>Shop</th><th>Owner</th><th>Added</th></tr></thead>
                        <tbody><tr v-for="shop in data.recent_shops" :key="shop.id">
                            <td><div class="table-primary">{{ shop.name }}</div><div class="table-sub">/shop/{{ shop.slug }}</div></td>
                            <td>{{ shop.owner?.name || 'Unassigned' }}</td>
                            <td>{{ new Date(shop.created_at).toLocaleDateString() }}</td>
                        </tr></tbody>
                    </table>
                </div>
                <div v-else-if="!isAdmin && data.recent_listings?.length" class="table-wrap">
                    <table>
                        <thead><tr><th>Product</th><th>Price</th><th>Availability</th></tr></thead>
                        <tbody><tr v-for="product in data.recent_listings" :key="product.id">
                            <td><div class="product-cell"><ProductThumb :src="product.pivot.thumbnail_url" :name="product.name"/><div><div class="table-primary">{{ product.name }}</div><div class="table-sub">{{ product.category || 'Uncategorised' }}</div></div></div></td>
                            <td>{{ formatPrice(product.pivot.price) }}</td>
                            <td><span class="status" :class="product.pivot.is_active ? 'status-active' : 'status-muted'">{{ product.pivot.is_active ? 'Visible' : 'Hidden' }}</span></td>
                        </tr></tbody>
                    </table>
                </div>
                <div v-else class="empty-state"><div class="empty-mark">◇</div><h3>{{ isAdmin ? 'No shops yet' : 'No products listed' }}</h3><p>{{ isAdmin ? 'Add a shop and assign an owner to get started.' : 'Browse the shared catalog to choose products for your shop.' }}</p></div>
            </section>

            <aside class="card quick-card">
                <div class="eyebrow">{{ isAdmin ? 'Get started' : 'Your storefront' }}</div>
                <h2>{{ isAdmin ? 'Keep things moving' : (data.shop?.name || 'Shop not assigned') }}</h2>
                <p>{{ isAdmin ? 'Add a shop, invite its owner, or update the shared catalog.' : 'Customers can browse the products you have made visible.' }}</p>
                <template v-if="isAdmin">
                    <RouterLink to="/admin/shops" class="quick-link"><span>Manage shops</span><span>→</span></RouterLink>
                    <RouterLink to="/admin/owners" class="quick-link"><span>Create an owner account</span><span>→</span></RouterLink>
                    <RouterLink to="/admin/products" class="quick-link"><span>Review product catalog</span><span>→</span></RouterLink>
                </template>
                <template v-else-if="data.shop">
                    <div class="shop-url"><code>/shop/{{ data.shop.slug }}</code><a class="inline-link" :href="`/shop/${data.shop.slug}`" target="_blank">Open ↗</a></div>
                    <RouterLink to="/owner/catalog" class="quick-link"><span>Browse shared catalog</span><span>→</span></RouterLink>
                    <RouterLink to="/owner/listings" class="quick-link"><span>Manage my products</span><span>→</span></RouterLink>
                </template>
                <template v-else>
                    <RouterLink to="/owner/catalog" class="quick-link"><span>Browse shared catalog</span><span>→</span></RouterLink>
                </template>
            </aside>
        </div>
    </template>
    <div v-else class="notice-bar">{{ loadError }}</div>
</template>
