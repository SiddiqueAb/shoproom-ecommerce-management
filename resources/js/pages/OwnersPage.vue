<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import api, { errorMessage } from '../api';
import { notify } from '../notice';
import AppModal from '../components/AppModal.vue';

const owners = ref([]);
const shops = ref([]);
const loading = ref(true);
const modal = ref(false);
const editing = ref(false);
const saving = ref(false);
const search = ref('');
const form = reactive({ id: null, name: '', email: '', password: '', shop_id: '', is_active: true });
const filteredOwners = computed(() => owners.value.filter((owner) => `${owner.name} ${owner.email} ${owner.shop?.name || ''}`.toLowerCase().includes(search.value.toLowerCase())));
const availableShops = computed(() => shops.value.filter((shop) => !shop.owner_id || shop.owner_id === form.id));

async function load() {
    loading.value = true;
    try {
        const { data } = await api.get('/api/admin/owners');
        owners.value = data.owners;
        shops.value = data.shops;
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        loading.value = false;
    }
}

function createOwner() {
    Object.assign(form, { id: null, name: '', email: '', password: '', shop_id: '', is_active: true });
    editing.value = false;
    modal.value = true;
}

function editOwner(owner) {
    Object.assign(form, { id: owner.id, name: owner.name, email: owner.email, password: '', shop_id: owner.shop?.id || '', is_active: owner.is_active });
    editing.value = true;
    modal.value = true;
}

async function save() {
    saving.value = true;
    try {
        const payload = { ...form, shop_id: form.shop_id || null };
        if (editing.value) await api.put(`/api/admin/owners/${form.id}`, payload);
        else await api.post('/api/admin/owners', payload);
        modal.value = false;
        notify(editing.value ? 'Owner account updated.' : 'Owner account created.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    } finally {
        saving.value = false;
    }
}

async function remove(owner) {
    if (!window.confirm(`Delete the account for ${owner.name}?`)) return;
    try {
        await api.delete(`/api/admin/owners/${owner.id}`);
        notify('Owner account deleted.');
        await load();
    } catch (error) {
        notify(errorMessage(error), 'error');
    }
}

onMounted(load);
</script>

<template>
    <div class="page-heading">
        <div><div class="eyebrow">Platform</div><h1>Shop owners</h1><p>Create owner logins and assign them to a shop.</p></div>
        <button class="button button-primary" @click="createOwner"><span>＋</span> Add owner</button>
    </div>

    <section class="card section-card">
        <div class="section-head">
            <div><h2>Owner accounts <span class="muted">({{ owners.length }})</span></h2><p>Each owner can be assigned to one shop</p></div>
            <div class="toolbar"><div class="searchbox"><span class="search-icon">⌕</span><input v-model="search" class="input" type="search" placeholder="Search owners"></div></div>
        </div>
        <div v-if="loading" class="empty-state"><div class="spinner"></div>Loading accounts</div>
        <div v-else-if="filteredOwners.length" class="table-wrap">
            <table>
                <thead><tr><th>Owner</th><th>Assigned shop</th><th>Created</th><th>Status</th><th></th></tr></thead>
                <tbody><tr v-for="owner in filteredOwners" :key="owner.id">
                    <td><div class="table-primary">{{ owner.name }}</div><div class="table-sub">{{ owner.email }}</div></td>
                    <td>{{ owner.shop?.name || 'Not assigned' }}</td>
                    <td>{{ new Date(owner.created_at).toLocaleDateString() }}</td>
                    <td><span class="status" :class="owner.is_active ? 'status-active' : 'status-muted'">{{ owner.is_active ? 'Active' : 'Disabled' }}</span></td>
                    <td><div class="table-actions"><button class="button-quiet" @click="editOwner(owner)">Edit</button><button class="button-quiet" style="color:#a95a50" @click="remove(owner)">Delete</button></div></td>
                </tr></tbody>
            </table>
        </div>
        <div v-else class="empty-state"><div class="empty-mark">♙</div><h3>{{ owners.length ? 'No accounts match that search' : 'No owner accounts yet' }}</h3><p>{{ owners.length ? 'Try another name or email address.' : 'Create an account and assign the owner to a shop.' }}</p><button v-if="!owners.length" class="button button-secondary" @click="createOwner">Add the first owner</button></div>
    </section>

    <AppModal v-if="modal" :title="editing ? 'Edit owner account' : 'Add a shop owner'" subtitle="The owner can sign in and manage listings for the assigned shop." @close="modal = false">
        <form @submit.prevent="save">
            <div class="form-grid">
                <div class="field field-full"><label for="owner-name">Name</label><input id="owner-name" v-model.trim="form.name" class="input" required maxlength="160" placeholder="Full name"></div>
                <div class="field field-full"><label for="owner-email">Email address</label><input id="owner-email" v-model.trim="form.email" class="input" type="email" required maxlength="255" placeholder="owner@example.com"></div>
                <div class="field field-full"><label for="owner-password">{{ editing ? 'New password' : 'Temporary password' }}</label><input id="owner-password" v-model="form.password" class="input" type="password" :required="!editing" minlength="8" autocomplete="new-password" :placeholder="editing ? 'Leave blank to keep current password' : 'At least 8 characters'"></div>
                <div class="field field-full"><label for="owner-shop">Shop</label><select id="owner-shop" v-model="form.shop_id" class="select"><option value="">No shop assigned</option><option v-for="shop in availableShops" :key="shop.id" :value="shop.id">{{ shop.name }}</option></select></div>
                <label v-if="editing" class="checkline field-full"><input v-model="form.is_active" type="checkbox"> Account is active</label>
            </div>
            <div class="form-actions"><button type="button" class="button button-secondary" @click="modal = false">Cancel</button><button class="button button-primary" :disabled="saving">{{ saving ? 'Saving…' : editing ? 'Save changes' : 'Create account' }}</button></div>
        </form>
    </AppModal>
</template>
