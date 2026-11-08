<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import { formatPrice } from '../format';
import AppModal from '../components/AppModal.vue';
import ProductThumb from '../components/ProductThumb.vue';

const route = useRoute();
const shop = ref(null);
const catalog = ref([]);
const listings = ref([]);
const loading = ref(true);
const modal = ref(false);
const adding = ref(false);
const saving = ref(false);
const form = reactive({ product_id: '', price: '', description: '', thumbnail_url: '', is_active: true });
const availableProducts = computed(() => catalog.value.filter((product) => !listings.value.some((listing) => listing.id === product.id)));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get(`/api/admin/shops/${route.params.id}/listings`);
        shop.value = data.shop;
        catalog.value = data.catalog;
        listings.value = data.listings;
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        loading.value = false;
    }
}

function openAdd() {
    Object.assign(form, { product_id: '', price: '', description: '', thumbnail_url: '', is_active: true });
    adding.value = true;
    modal.value = true;
}

function openEdit(product) {
    Object.assign(form, {
        product_id: product.id,
        price: product.pivot.price,
        description: product.pivot.description || '',
        thumbnail_url: product.pivot.thumbnail_url || '',
        is_active: product.pivot.is_active,
    });
    adding.value = false;
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        const payload = { ...form, thumbnail_url: form.thumbnail_url || null, description: form.description || null };
        if (adding.value) await api.post(`/api/admin/shops/${route.params.id}/listings`, payload);
        else await api.put(`/api/admin/shops/${route.params.id}/listings/${form.product_id}`, payload);
        modal.value = false;
        notify(adding.value ? 'Product added to this shop.' : 'Shop listing updated.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(product) {
    if (!window.confirm(`Remove ${product.name} from ${shop.value.name}?`)) return;
    try {
        await api.delete(`/api/admin/shops/${route.params.id}/listings/${product.id}`);
        notify('Product removed from this shop.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="loading-screen"><div><div class="spinner"></div>Loading shop listings</div></div>
    <template v-else-if="shop">
        <div class="page-heading">
            <div><div class="eyebrow"><RouterLink to="/admin/shops">Shops</RouterLink> / Product listings</div><h1>{{ shop.name }}</h1><p>Choose catalog products and set this shop's own price and details.</p></div>
            <div class="toolbar"><a :href="`/shop/${shop.slug}`" target="_blank" class="button button-secondary">View shop ↗</a><button class="button button-primary" :disabled="!availableProducts.length" @click="openAdd"><span>＋</span> Add product</button></div>
        </div>

        <section class="card section-card">
            <div class="section-head"><div><h2>Assigned products <span class="muted">({{ listings.length }})</span></h2><p>Only active listings appear to customers</p></div></div>
            <div v-if="listings.length" class="table-wrap">
                <table>
                    <thead><tr><th>Product</th><th>Price</th><th>Thumbnail</th><th>Status</th><th></th></tr></thead>
                    <tbody><tr v-for="product in listings" :key="product.id">
                        <td><div class="product-cell"><ProductThumb :src="product.pivot.thumbnail_url" :name="product.name"/><div><div class="table-primary">{{ product.name }}</div><div class="table-sub">{{ product.category || 'Uncategorised' }} · {{ product.sku || 'No SKU' }}</div></div></div></td>
                        <td>{{ formatPrice(product.pivot.price) }}</td>
                        <td>{{ product.pivot.thumbnail_url ? 'Set' : 'Not set' }}</td>
                        <td><span class="status" :class="product.pivot.is_active ? 'status-active' : 'status-muted'">{{ product.pivot.is_active ? 'Visible' : 'Hidden' }}</span></td>
                        <td><div class="table-actions"><button class="button-quiet" @click="openEdit(product)">Edit</button><button class="button-quiet" style="color:#a95a50" @click="remove(product)">Remove</button></div></td>
                    </tr></tbody>
                </table>
            </div>
            <div v-else class="empty-state"><div class="empty-mark">◇</div><h3>This shop has no products yet</h3><p>Add any product from the shared catalog, then set its price and shop details.</p><button class="button button-secondary" :disabled="!catalog.length" @click="openAdd">Add a catalog product</button><div v-if="!catalog.length" class="field-hint" style="margin-top:10px">The shared catalog is empty. Add products from the product catalog first.</div></div>
        </section>
    </template>
    <div v-else class="notice-bar">This shop could not be found.</div>

    <AppModal v-if="modal" :title="adding ? 'Add a product to this shop' : 'Edit shop listing'" subtitle="Price, description, thumbnail and visibility belong to this shop." @close="modal = false">
        <form @submit.prevent="save">
            <div class="form-grid">
                <div v-if="adding" class="field field-full"><label for="catalog-product">Catalog product</label><select id="catalog-product" v-model="form.product_id" class="select" required><option value="" disabled>Select a product</option><option v-for="product in availableProducts" :key="product.id" :value="product.id">{{ product.name }}{{ product.sku ? ` · ${product.sku}` : '' }}</option></select></div>
                <div v-else class="field field-full"><label>Catalog product</label><input class="input" :value="listings.find((product) => product.id === Number(form.product_id))?.name" disabled></div>
                <div class="field"><label for="listing-price">Price (BDT)</label><input id="listing-price" v-model="form.price" class="input" type="number" min="0.01" step="0.01" required placeholder="0.00"></div>
                <div class="field"><label for="listing-thumbnail">Thumbnail URL</label><input id="listing-thumbnail" v-model.trim="form.thumbnail_url" class="input" type="url" maxlength="2048" placeholder="https://example.com/image.jpg"></div>
                <div class="field field-full"><label for="listing-description">Shop description</label><textarea id="listing-description" v-model.trim="form.description" class="textarea" maxlength="5000" placeholder="Details shown in this shop"></textarea></div>
                <label v-if="!adding" class="checkline field-full"><input v-model="form.is_active" type="checkbox"> Visible in customer storefront</label>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving || (adding && !availableProducts.length)">{{ saving ? 'Saving…' : adding ? 'Add to shop' : 'Save changes' }}</button></div>
        </form>
    </AppModal>
</template>
