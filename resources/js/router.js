import { createRouter, createWebHistory } from 'vue-router';
import { auth, homeFor, loadUser } from './auth';
import LoginPage from './pages/LoginPage.vue';
import DashboardPage from './pages/DashboardPage.vue';
import ShopsPage from './pages/ShopsPage.vue';
import OwnersPage from './pages/OwnersPage.vue';
import ProductsPage from './pages/ProductsPage.vue';
import ShopCatalogPage from './pages/ShopCatalogPage.vue';
import OwnerListingsPage from './pages/OwnerListingsPage.vue';
import OwnerCatalogPage from './pages/OwnerCatalogPage.vue';
import StorefrontPage from './pages/StorefrontPage.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/dashboard' },
        { path: '/login', component: LoginPage, meta: { public: true, title: 'Sign in' } },
        { path: '/dashboard', component: DashboardPage, meta: { title: 'Overview' } },
        { path: '/admin/shops', component: ShopsPage, meta: { role: 'admin', title: 'Shops' } },
        { path: '/admin/shops/:id/catalog', component: ShopCatalogPage, meta: { role: 'admin', title: 'Shop listings' } },
        { path: '/admin/products', component: ProductsPage, meta: { role: 'admin', title: 'Product catalog' } },
        { path: '/admin/owners', component: OwnersPage, meta: { role: 'admin', title: 'Shop owners' } },
        { path: '/owner/listings', component: OwnerListingsPage, meta: { role: 'owner', title: 'My products' } },
        { path: '/owner/catalog', component: OwnerCatalogPage, meta: { role: 'owner', title: 'Shared catalog' } },
        { path: '/shop/:slug', component: StorefrontPage, meta: { public: true, title: 'Shop' } },
        { path: '/:pathMatch(.*)*', redirect: '/' },
    ],
});

router.beforeEach(async (to) => {
    if (to.meta.public) {
        if (to.path === '/login') {
            await loadUser();
            if (auth.user) return homeFor();
        }
        return true;
    }

    await loadUser();
    if (!auth.user) return '/login';
    if (to.meta.role && auth.user.role !== to.meta.role) {
        return auth.user.role === 'admin' ? '/dashboard' : '/dashboard';
    }

    return true;
});

export default router;
