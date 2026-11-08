<script setup>
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { signIn } from '../auth';
import { fieldErrors, errorMessage } from '../api';
import { notify } from '../notice';

const router = useRouter();
const form = reactive({ email: '', password: '' });
const errors = ref({});
const busy = ref(false);

function useDemo(email) {
    form.email = email;
    form.password = 'password';
    errors.value = {};
}

async function submit() {
    errors.value = {};
    busy.value = true;
    try {
        await signIn(form);
        router.push('/dashboard');
    } catch (error) {
        errors.value = fieldErrors(error);
        notify(errorMessage(error), 'error');
    } finally {
        busy.value = false;
    }
}
</script>

<template>
    <section class="auth-page">
        <aside class="auth-aside">
            <RouterLink to="/login" class="brand">
                <span class="brand-mark">S.</span>
                <span><span class="brand-name">Shoproom</span><span class="brand-caption">Commerce desk</span></span>
            </RouterLink>
            <div class="auth-copy">
                <div class="eyebrow">A simpler way to run a shop</div>
                <h1>Your shop,<br>all in one place.</h1>
                <p>Keep your product list tidy, set your own prices, and share a catalog with the shops around you.</p>
            </div>
            <div class="auth-foot">Built for independent shops and the people who run them.</div>
        </aside>

        <main class="auth-main">
            <form class="auth-form-wrap" @submit.prevent="submit">
                <div class="eyebrow">Welcome back</div>
                <h2>Sign in to Shoproom</h2>
                <p>Use the account details your administrator gave you.</p>

                <div class="field">
                    <label for="email">Email address</label>
                    <input id="email" v-model.trim="form.email" class="input" type="email" autocomplete="username" placeholder="you@yourshop.com" required>
                    <p v-if="errors.email" class="form-error">{{ errors.email[0] }}</p>
                </div>
                <div class="field">
                    <label for="password">Password</label>
                    <input id="password" v-model="form.password" class="input" type="password" autocomplete="current-password" placeholder="Enter your password" required>
                    <p v-if="errors.password" class="form-error">{{ errors.password[0] }}</p>
                </div>
                <button class="button button-primary" type="submit" :disabled="busy">{{ busy ? 'Signing in…' : 'Sign in' }} <span aria-hidden="true">→</span></button>

                <div class="demo-details">
                    <div class="demo-label">Sample accounts for a fresh install</div>
                    <div class="demo-accounts">
                        <button type="button" class="demo-account" @click="useDemo('admin@example.com')"><strong>Administrator</strong>admin@example.com</button>
                        <button type="button" class="demo-account" @click="useDemo('owner@example.com')"><strong>Shop owner</strong>owner@example.com</button>
                    </div>
                    <div class="field-hint" style="margin-top:8px">Both use <code>password</code>. Run the database seeder to create these accounts.</div>
                </div>
            </form>
        </main>
    </section>
</template>
