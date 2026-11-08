import axios from 'axios';

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

const api = axios.create({
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        ...(token ? { 'X-CSRF-TOKEN': token } : {}),
    },
});

export function errorMessage(error) {
    return error?.response?.data?.message || 'Something went wrong. Please try again.';
}

export function fieldErrors(error) {
    return error?.response?.data?.errors || {};
}

export default api;
