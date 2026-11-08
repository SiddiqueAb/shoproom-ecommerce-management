<script setup>
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import { formatPrice } from '../format';
import AppModal from '../components/AppModal.vue';
import ProductThumb from '../components/ProductThumb.vue';

const shop = ref(null);
const listings = ref([]);
const loading = ref(true);
const loadError = ref('');
const modal = ref(false);
const saving = ref(false);
const form = reactive({ product_id: '', price: '', description: '', thumbnail_url: '', is_active: true });

async function load() {
    loading.value = true;
    loadError.value = '';
    try {
        const { data } = await api.get('/api/owner/listings');
        shop.value = data.shop;
        listings.value = data.listings;
    } catch (error) {
        loadError.value = error?.response?.data?.message || errorMessage(error);
    } finally {
        loading.value = false;
    }
}

function edit(product) {
    Object.assign(form, {
        product_id: product.id,
        price: product.pivot.price,
        description: product.pivot.description || '',
        thumbnail_url: product.pivot.thumbnail_url || '',
        is_active: product.pivot.is_active,
    });
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        await api.put(`/api/owner/listings/${form.product_id}`, {
            price: form.price,
            description: form.description || null,
            thumbnail_url: form.thumbnail_url || null,
            is_active: form.is_active,
        });
        modal.value = false;
        notify('Product details updated for your shop.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(product) {
    if (!window.confirm(`Remove ${product.name} from your shop? It will stay in the shared catalog.`)) return;
    try {
        await api.delete(`/api/owner/listings/${product.id}`);
        notify('Product removed from your shop.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    }
}

onMounted(load);
</script>

<template>
    <div v-if="loading" class="loading-screen"><div><div class="spinner"></div>Loading your listings</div></div>
    <template v-else-if="shop">
        <div class="page-heading">
            <div><div class="eyebrow">{{ shop.name }}</div><h1>My products</h1><p>Set the price, details and customer visibility for this shop.</p></div>
            <div class="toolbar"><a :href="`/shop/${shop.slug}`" target="_blank" class="button button-secondary">View shop ↗</a><RouterLink to="/owner/catalog" class="button button-primary"><span>＋</span> Add products</RouterLink></div>
        </div>

        <section class="card section-card">
            <div class="section-head"><div><h2>Shop listings <span class="muted">({{ listings.length }})</span></h2><p>These products are assigned to {{ shop.name }}</p></div></div>
            <div v-if="listings.length" class="table-wrap">
                <table>
                    <thead><tr><th>Product</th><th>Price</th><th>Thumbnail</th><th>Customer view</th><th></th></tr></thead>
                    <tbody><tr v-for="product in listings" :key="product.id">
                        <td><div class="product-cell"><ProductThumb :src="product.pivot.thumbnail_url" :name="product.name"/><div><div class="table-primary">{{ product.name }}</div><div class="table-sub">{{ product.category || 'Uncategorised' }} · {{ product.sku || 'No SKU' }}</div></div></div></td>
                        <td>{{ formatPrice(product.pivot.price) }}</td>
                        <td>{{ product.pivot.thumbnail_url ? 'Set' : 'Not set' }}</td>
                        <td><span class="status" :class="product.pivot.is_active ? 'status-active' : 'status-muted'">{{ product.pivot.is_active ? 'Visible' : 'Hidden' }}</span></td>
                        <td><div class="table-actions"><button class="button-quiet" @click="edit(product)">Edit</button><button class="button-quiet" style="color:#a95a50" @click="remove(product)">Remove</button></div></td>
                    </tr></tbody>
                </table>
            </div>
            <div v-else class="empty-state"><div class="empty-mark">▦</div><h3>Your shop is ready for products</h3><p>Choose an existing product from the shared catalog, or add a new product of your own.</p><RouterLink to="/owner/catalog" class="button button-secondary">Browse catalog</RouterLink></div>
        </section>
    </template>
    <div v-else class="card empty-state"><div class="empty-mark">▦</div><h3>Shop assignment needed</h3><p>{{ loadError }}</p><p>Once an administrator assigns a shop to your account, you can manage its product listings here.</p></div>

    <AppModal v-if="modal" title="Edit shop listing" subtitle="These details apply only to your customer storefront." @close="modal = false">
        <form @submit.prevent="save">
            <div class="form-grid">
                <div class="field field-full"><label>Product</label><input class="input" :value="listings.find((product) => product.id === Number(form.product_id))?.name" disabled></div>
                <div class="field"><label for="owner-price">Price (BDT)</label><input id="owner-price" v-model="form.price" class="input" type="number" min="0.01" step="0.01" required></div>
                <div class="field"><label for="owner-thumbnail">Thumbnail URL</label><input id="owner-thumbnail" v-model.trim="form.thumbnail_url" class="input" type="url" maxlength="2048" placeholder="https://example.com/image.jpg"></div>
                <div class="field field-full"><label for="owner-description">Shop description</label><textarea id="owner-description" v-model.trim="form.description" class="textarea" maxlength="5000" placeholder="Details customers should see"></textarea></div>
                <label class="checkline field-full"><input v-model="form.is_active" type="checkbox"> Visible in customer shop</label>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving">{{ saving ? 'Saving…' : 'Save changes' }}</button></div>
        </form>
    </AppModal>
</template>
