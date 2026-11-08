<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import AppModal from '../components/AppModal.vue';

const router = useRouter();
const shops = ref([]);
const owners = ref([]);
const loading = ref(true);
const search = ref('');
const modal = ref(false);
const editing = ref(false);
const saving = ref(false);
const form = reactive({ id: null, name: '', slug: '', location: '', phone: '', owner_id: '', is_active: true });

const visibleShops = computed(() => shops.value.filter((shop) => `${shop.name} ${shop.location || ''} ${shop.owner?.name || ''}`.toLowerCase().includes(search.value.toLowerCase())));
const availableOwners = computed(() => owners.value.filter((owner) => !owner.shop || owner.shop.id === form.id));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/admin/shops');
        shops.value = data.shops;
        owners.value = data.owners;
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        loading.value = false;
    }
}

function openCreate() {
    Object.assign(form, { id: null, name: '', slug: '', location: '', phone: '', owner_id: '', is_active: true });
    editing.value = false;
    modal.value = true;
}

function openEdit(shop) {
    Object.assign(form, { ...shop, owner_id: shop.owner_id || '' });
    editing.value = true;
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        const payload = { ...form, owner_id: form.owner_id || null };
        if (editing.value) await api.put(`/api/admin/shops/${form.id}`, payload);
        else await api.post('/api/admin/shops', payload);
        notify(editing.value ? 'Shop details updated.' : 'Shop created.');
        modal.value = false;
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(shop) {
    if (!window.confirm(`Delete ${shop.name}? Its product assignments will be removed too.`)) return;
    try {
        await api.delete(`/api/admin/shops/${shop.id}`);
        notify('Shop deleted.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    }
}

onMounted(load);
</script>

<template>
    <div class="page-heading">
        <div><div class="eyebrow">Platform</div><h1>Shops</h1><p>Set up stores, connect an owner, and manage the products each shop sells.</p></div>
        <button class="button button-primary" @click="openCreate"><span>＋</span> Add shop</button>
    </div>

    <section class="card section-card">
        <div class="section-head">
            <div><h2>All shops <span class="muted">({{ shops.length }})</span></h2><p>Storefronts on the platform</p></div>
            <div class="toolbar"><div class="searchbox"><span class="search-icon">⌕</span><input v-model="search" class="input" type="search" placeholder="Search shops"></div></div>
        </div>
        <div v-if="loading" class="empty-state"><div class="spinner"></div>Loading shops</div>
        <div v-else-if="visibleShops.length" class="table-wrap">
            <table>
                <thead><tr><th>Shop</th><th>Owner</th><th>Listings</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="shop in visibleShops" :key="shop.id">
                        <td><div class="table-primary">{{ shop.name }}</div><div class="table-sub">{{ shop.location || 'Location not set' }} · /shop/{{ shop.slug }}</div></td>
                        <td>{{ shop.owner?.name || 'Unassigned' }}<div v-if="shop.owner" class="table-sub">{{ shop.owner.email }}</div></td>
                        <td>{{ shop.products_count }}</td>
                        <td><span class="status" :class="shop.is_active ? 'status-active' : 'status-muted'">{{ shop.is_active ? 'Active' : 'Paused' }}</span></td>
                        <td><div class="table-actions">
                            <button class="button-quiet" @click="router.push(`/admin/shops/${shop.id}/catalog`)">Listings</button>
                            <button class="button-quiet" @click="openEdit(shop)">Edit</button>
                            <button class="button-quiet" style="color:#a95a50" @click="remove(shop)">Delete</button>
                        </div></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div v-else class="empty-state"><div class="empty-mark">▦</div><h3>{{ shops.length ? 'No shops match that search' : 'No shops added yet' }}</h3><p>{{ shops.length ? 'Try another shop name or owner.' : 'Add a shop, then assign an owner and choose its products.' }}</p><button v-if="!shops.length" class="button button-secondary" @click="openCreate">Add your first shop</button></div>
    </section>

    <AppModal v-if="modal" :title="editing ? 'Edit shop' : 'Add a shop'" subtitle="Shop details are shown on its customer storefront." @close="modal = false">
        <form @submit.prevent="save">
            <div class="form-grid">
                <div class="field field-full"><label for="shop-name">Shop name</label><input id="shop-name" v-model.trim="form.name" class="input" required maxlength="160" placeholder="Example: Green Basket"></div>
                <div class="field"><label for="shop-slug">Store URL name</label><input id="shop-slug" v-model.trim="form.slug" class="input" :required="editing" maxlength="180" placeholder="green-basket"><span class="field-hint">{{ editing ? 'Use letters, numbers, and hyphens.' : 'Leave empty to make one from the shop name.' }}</span></div>
                <div class="field"><label for="shop-owner">Shop owner</label><select id="shop-owner" v-model="form.owner_id" class="select"><option value="">Assign later</option><option v-for="owner in availableOwners" :key="owner.id" :value="owner.id">{{ owner.name }} · {{ owner.email }}</option></select><span class="field-hint">An owner can manage one shop at a time.</span></div>
                <div class="field"><label for="shop-location">Location</label><input id="shop-location" v-model.trim="form.location" class="input" placeholder="Dhanmondi, Dhaka"></div>
                <div class="field"><label for="shop-phone">Phone</label><input id="shop-phone" v-model.trim="form.phone" class="input" placeholder="+880 1xxx xxxxxx"></div>
                <label v-if="editing" class="checkline field-full"><input v-model="form.is_active" type="checkbox"> Storefront is active</label>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving">{{ saving ? 'Saving…' : editing ? 'Save changes' : 'Create shop' }}</button></div>
        </form>
    </AppModal>
</template>
