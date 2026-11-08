import { reactive } from 'vue';

export const notices = reactive([]);

export function notify(message, tone = 'success') {
    const id = Date.now() + Math.random();
    notices.push({ id, message, tone });
    window.setTimeout(() => {
        const index = notices.findIndex((notice) => notice.id === id);
        if (index !== -1) notices.splice(index, 1);
    }, 3600);
}
