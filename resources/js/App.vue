<script setup>
import { computed, onMounted, onBeforeUnmount } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';
import { auth, loadUser, signOut } from './auth';
import { notices, notify } from './notice';
import { dateLabel, initials } from './format';

const route = useRoute();
const router = useRouter();
const isPublic = computed(() => route.meta.public === true);
const isLogin = computed(() => route.path === '/login');
const navigation = computed(() => auth.user?.role === 'admin'
    ? [
        { label: 'Overview', to: '/dashboard', glyph: '⌂' },
        { label: 'Shops', to: '/admin/shops', glyph: '▦' },
        { label: 'Product catalog', to: '/admin/products', glyph: '◇' },
        { label: 'Shop owners', to: '/admin/owners', glyph: '♙' },
    ]
    : [
        { label: 'Overview', to: '/dashboard', glyph: '⌂' },
        { label: 'My products', to: '/owner/listings', glyph: '▦' },
        { label: 'Shared catalog', to: '/owner/catalog', glyph: '◇' },
    ]);

async function logout() {
    try {
        await signOut();
        router.push('/login');
    } catch {
        notify('Could not sign out. Please try again.', 'error');
    }
}

onMounted(() => loadUser());
</script>

<template>
    <div class="notice-stack" aria-live="polite">
        <div v-for="notice in notices" :key="notice.id" class="notice" :class="{ 'notice-error': notice.tone === 'error' }">
            <span>{{ notice.tone === 'error' ? '!' : '✓' }}</span>{{ notice.message }}
        </div>
    </div>

    <div v-if="isPublic && !isLogin" class="public-page"><RouterView /></div>
    <div v-else-if="isLogin || !auth.user" class="guest-page"><RouterView /></div>
    <div v-else class="app-shell">
        <aside class="sidebar">
            <RouterLink to="/dashboard" class="brand">
                <span class="brand-mark">S.</span>
                <span><span class="brand-name">Shoproom</span><span class="brand-caption">Commerce desk</span></span>
            </RouterLink>

            <div class="nav-label">Workspace</div>
            <nav class="nav-list" aria-label="Main navigation">
                <RouterLink v-for="item in navigation" :key="item.to" :to="item.to" class="nav-link">
                    <span class="nav-glyph">{{ item.glyph }}</span>{{ item.label }}
                </RouterLink>
            </nav>

            <div class="sidebar-bottom">
                <div class="user-chip">
                    <div class="avatar">{{ initials(auth.user.name) }}</div>
                    <div style="min-width:0">
                        <div class="user-name">{{ auth.user.name }}</div>
                        <div class="user-role">{{ auth.user.role === 'admin' ? 'Administrator' : 'Shop owner' }}</div>
                    </div>
                </div>
            </div>
        </aside>

        <div class="workspace">
            <header class="topbar">
                <div class="crumb"><RouterLink to="/dashboard">Workspace</RouterLink><span> / </span><strong>{{ route.meta.title || 'Overview' }}</strong></div>
                <div class="topbar-right">
                    <span class="top-date">{{ dateLabel() }}</span>
                    <button class="signout" @click="logout">Sign out</button>
                </div>
            </header>
            <main class="main-content"><RouterView /></main>
        </div>
    </div>
</template>
