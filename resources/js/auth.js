import { reactive } from 'vue';
import api from './api';

export const auth = reactive({ user: null, ready: false });

let loadPromise;

export function loadUser() {
    if (auth.ready) return Promise.resolve(auth.user);
    if (!loadPromise) {
        loadPromise = api.get('/api/me')
            .then(({ data }) => (auth.user = data.user))
            .catch(() => (auth.user = null))
            .finally(() => {
                auth.ready = true;
                loadPromise = null;
            });
    }

    return loadPromise;
}

export async function signIn(credentials) {
    const { data } = await api.post('/api/login', credentials);
    auth.user = data.user;
    auth.ready = true;
    return auth.user;
}

export async function signOut() {
    await api.post('/api/logout');
    auth.user = null;
}

export function homeFor(user = auth.user) {
    return user?.role === 'admin' ? '/dashboard' : '/dashboard';
}
