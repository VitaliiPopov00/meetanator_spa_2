import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import components from '@/components';

if (!localStorage.getItem('homeUrlAPI')) {
    localStorage.setItem('homeUrlAPI', 'http://krsmcfz-m2.wsr.ru');
}

const app = createApp(App);

components.forEach(component => {
    app.component(component.name, component);
})

app.use(router);
app.mount('#app');
