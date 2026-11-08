<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import AppModal from '../components/AppModal.vue';

const products = ref([]);
const loading = ref(true);
const modal = ref(false);
const editing = ref(false);
const saving = ref(false);
const search = ref('');
const form = reactive({ id: null, name: '', sku: '', category: '', description: '' });
const visibleProducts = computed(() => products.value.filter((product) => `${product.name} ${product.sku || ''} ${product.category || ''} ${product.creator?.name || ''}`.toLowerCase().includes(search.value.toLowerCase())));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/admin/products');
        products.value = data.products;
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        loading.value = false;
    }
}

function createProduct() {
    Object.assign(form, { id: null, name: '', sku: '', category: '', description: '' });
    editing.value = false;
    modal.value = true;
}

function editProduct(product) {
    Object.assign(form, product);
    editing.value = true;
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        const payload = { ...form, sku: form.sku || null, category: form.category || null, description: form.description || null };
        if (editing.value) await api.put(`/api/admin/products/${form.id}`, payload);
        else await api.post('/api/admin/products', payload);
        modal.value = false;
        notify(editing.value ? 'Catalog product updated.' : 'Product added to the shared catalog.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(product) {
    const details = product.shops_count ? ` It is currently assigned to ${product.shops_count} shop(s).` : '';
    if (!window.confirm(`Delete ${product.name} from the shared catalog?${details} The product will be removed from every shop.`)) return;
    try {
        await api.delete(`/api/admin/products/${product.id}`);
        notify('Product deleted from the shared catalog.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    }
}

onMounted(load);
</script>

<template>
    <div class="page-heading">
        <div><div class="eyebrow">Shared catalog</div><h1>Product catalog</h1><p>Every product here can be listed by any shop. Shop prices and details stay separate.</p></div>
        <button class="button button-primary" @click="createProduct"><span>＋</span> Add product</button>
    </div>

    <div class="notice-bar">Adding a product here makes it available in the shared catalog. It appears to customers only after it is assigned to an active shop.</div>

    <section class="card section-card">
        <div class="section-head">
            <div><h2>Catalog products <span class="muted">({{ products.length }})</span></h2><p>Products created by administrators and shop owners</p></div>
            <div class="toolbar"><div class="searchbox"><span class="search-icon">⌕</span><input v-model="search" class="input" type="search" placeholder="Search products"></div></div>
        </div>
        <div v-if="loading" class="empty-state"><div class="spinner"></div>Loading catalog</div>
        <div v-else-if="visibleProducts.length" class="table-wrap">
            <table>
                <thead><tr><th>Product</th><th>Category</th><th>Added by</th><th>Shops</th><th></th></tr></thead>
                <tbody><tr v-for="product in visibleProducts" :key="product.id">
                    <td><div class="table-primary">{{ product.name }}</div><div class="table-sub">{{ product.sku || 'No SKU' }}</div></td>
                    <td>{{ product.category || '—' }}</td>
                    <td>{{ product.creator?.name || 'Administrator' }}</td>
                    <td><span class="status" :class="product.shops_count ? 'status-active' : 'status-warning'">{{ product.shops_count }} {{ product.shops_count === 1 ? 'shop' : 'shops' }}</span></td>
                    <td><div class="table-actions"><button class="button-quiet" @click="editProduct(product)">Edit</button><button class="button-quiet" style="color:#a95a50" @click="remove(product)">Delete</button></div></td>
                </tr></tbody>
            </table>
        </div>
        <div v-else class="empty-state"><div class="empty-mark">◇</div><h3>{{ products.length ? 'No products match that search' : 'The shared catalog is empty' }}</h3><p>{{ products.length ? 'Try another product name or category.' : 'Add a product so shop owners can start listing it.' }}</p><button v-if="!products.length" class="button button-secondary" @click="createProduct">Add a product</button></div>
    </section>

    <AppModal v-if="modal" :title="editing ? 'Edit catalog product' : 'Add a catalog product'" subtitle="Shared product information is separate from each shop's price and listing details." @close="modal = false">
        <form @submit.prevent="save">
            <div class="form-grid">
                <div class="field field-full"><label for="product-name">Product name</label><input id="product-name" v-model.trim="form.name" class="input" required maxlength="180" placeholder="Example: Miniket rice"></div>
                <div class="field"><label for="product-sku">SKU</label><input id="product-sku" v-model.trim="form.sku" class="input" maxlength="100" placeholder="Optional product code"></div>
                <div class="field"><label for="product-category">Category</label><input id="product-category" v-model.trim="form.category" class="input" maxlength="120" placeholder="Groceries"></div>
                <div class="field field-full"><label for="product-description">Catalog description</label><textarea id="product-description" v-model.trim="form.description" class="textarea" maxlength="5000" placeholder="A short description shared with shop owners"></textarea></div>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving">{{ saving ? 'Saving…' : editing ? 'Save changes' : 'Add to catalog' }}</button></div>
        </form>
    </AppModal>
</template>
