<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import AppModal from '../components/AppModal.vue';

const shop = ref(null);
const products = ref([]);
const loading = ref(true);
const search = ref('');
const modal = ref(false);
const mode = ref('add');
const saving = ref(false);
const form = reactive({ product_id: '', name: '', sku: '', category: '', catalog_description: '', price: '', description: '', thumbnail_url: '' });
const filteredProducts = computed(() => products.value.filter((product) => `${product.name} ${product.sku || ''} ${product.category || ''} ${product.creator?.name || ''}`.toLowerCase().includes(search.value.toLowerCase())));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/owner/catalog');
        shop.value = data.shop;
        products.value = data.products;
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        loading.value = false;
    }
}

function addProduct(product) {
    mode.value = 'add';
    Object.assign(form, {
        product_id: product.id,
        name: product.name,
        sku: product.sku || '',
        category: product.category || '',
        catalog_description: product.description || '',
        price: '',
        description: product.description || '',
        thumbnail_url: '',
    });
    modal.value = true;
}

function createProduct() {
    mode.value = 'create';
    Object.assign(form, { product_id: '', name: '', sku: '', category: '', catalog_description: '', price: '', description: '', thumbnail_url: '' });
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        if (mode.value === 'add') {
            await api.post('/api/owner/listings', {
                product_id: form.product_id,
                price: form.price,
                description: form.description || null,
                thumbnail_url: form.thumbnail_url || null,
            });
            notify('Product added to your shop.');
        } else {
            await api.post('/api/owner/products', {
                name: form.name,
                sku: form.sku || null,
                category: form.category || null,
                catalog_description: form.catalog_description || null,
                price: form.price,
                description: form.description || null,
                thumbnail_url: form.thumbnail_url || null,
            });
            notify('Product added to the shared catalog and your shop.');
        }
        modal.value = false;
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="page-heading">
        <div><div class="eyebrow">{{ shop?.name || 'Shop owner' }}</div><h1>Shared catalog</h1><p>Pick a product for your shop or add one that other owners can use too.</p></div>
        <button class="button button-primary" :disabled="!shop" @click="createProduct"><span>＋</span> Create a product</button>
    </div>

    <div v-if="!shop && !loading" class="notice-bar">This account has not been assigned to a shop yet. Ask an administrator to assign one before adding products.</div>

    <section class="card section-card">
        <div class="section-head">
            <div><h2>Catalog <span class="muted">({{ products.length }})</span></h2><p>Products shared by the shops on this platform</p></div>
            <div class="toolbar"><div class="searchbox"><span class="search-icon">⌕</span><input v-model="search" class="input" type="search" placeholder="Search catalog"></div></div>
        </div>
        <div v-if="loading" class="empty-state"><div class="spinner"></div>Loading shared catalog</div>
        <div v-else-if="filteredProducts.length" class="product-grid" style="padding:16px">
            <article v-for="product in filteredProducts" :key="product.id" class="card product-card">
                <div class="product-card-image"><span>{{ product.name.slice(0, 1) }}</span></div>
                <div class="product-card-body">
                    <div class="category">{{ product.category || 'Uncategorised' }}</div>
                    <h3>{{ product.name }}</h3>
                    <p>{{ product.description || 'No catalog description has been added.' }}</p>
                    <div class="field-hint" style="margin-top:7px">{{ product.creator?.name ? `Added by ${product.creator.name}` : 'Added by administrator' }}</div>
                    <div class="product-card-foot">
                        <span v-if="product.already_listed" class="status status-active">In your shop</span>
                        <span v-else class="muted" style="font-size:10px">{{ product.sku || 'No SKU' }}</span>
                        <button v-if="!product.already_listed" class="button button-secondary button-small" :disabled="!shop" @click="addProduct(product)">Add to shop</button>
                    </div>
                </div>
            </article>
        </div>
        <div v-else class="empty-state"><div class="empty-mark">◇</div><h3>{{ products.length ? 'Nothing found' : 'No shared products yet' }}</h3><p>{{ products.length ? 'Try another search.' : 'Create the first product for your shop. It will be available to other owners too.' }}</p><button v-if="!products.length && shop" class="button button-secondary" @click="createProduct">Create a product</button></div>
    </section>

    <AppModal v-if="modal" :title="mode === 'add' ? 'Add to your shop' : 'Create a product'" :subtitle="mode === 'add' ? 'This product stays in the shared catalog. Set the details your customers will see.' : 'Your product will be added to the shared catalog and listed in your shop.'" :wide="mode === 'create'" @close="modal = false">
        <form @submit.prevent="save">
            <div v-if="mode === 'add'" class="form-grid">
                <div class="field field-full"><label>Catalog product</label><input class="input" :value="form.name" disabled></div>
                <div class="field"><label for="catalog-price">Your price (BDT)</label><input id="catalog-price" v-model="form.price" class="input" type="number" min="0.01" step="0.01" required placeholder="0.00"></div>
                <div class="field"><label for="catalog-thumbnail">Your thumbnail URL</label><input id="catalog-thumbnail" v-model.trim="form.thumbnail_url" class="input" type="url" maxlength="2048" placeholder="https://example.com/image.jpg"></div>
                <div class="field field-full"><label for="catalog-shop-description">Your product details</label><textarea id="catalog-shop-description" v-model.trim="form.description" class="textarea" maxlength="5000" placeholder="Details for your customers"></textarea></div>
            </div>
            <div v-else class="form-grid">
                <div class="field field-full"><label for="new-product-name">Product name</label><input id="new-product-name" v-model.trim="form.name" class="input" maxlength="180" required placeholder="Example: Fresh farm eggs"></div>
                <div class="field"><label for="new-product-sku">SKU</label><input id="new-product-sku" v-model.trim="form.sku" class="input" maxlength="100" placeholder="Optional"></div>
                <div class="field"><label for="new-product-category">Category</label><input id="new-product-category" v-model.trim="form.category" class="input" maxlength="120" placeholder="Groceries"></div>
                <div class="field field-full"><label for="new-product-catalog-description">Shared catalog description</label><textarea id="new-product-catalog-description" v-model.trim="form.catalog_description" class="textarea" maxlength="5000" placeholder="A short note for other shop owners"></textarea></div>
                <div class="field"><label for="new-product-price">Your price (BDT)</label><input id="new-product-price" v-model="form.price" class="input" type="number" min="0.01" step="0.01" required placeholder="0.00"></div>
                <div class="field"><label for="new-product-thumbnail">Your thumbnail URL</label><input id="new-product-thumbnail" v-model.trim="form.thumbnail_url" class="input" type="url" maxlength="2048" placeholder="https://example.com/image.jpg"></div>
                <div class="field field-full"><label for="new-product-details">Your shop details</label><textarea id="new-product-details" v-model.trim="form.description" class="textarea" maxlength="5000" placeholder="Product details for your customers"></textarea></div>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving">{{ saving ? 'Saving…' : mode === 'add' ? 'Add to my shop' : 'Create and list product' }}</button></div>
        </form>
    </AppModal>
</template>
