import { createRouter, createWebHistory } from 'vue-router';
import indexView from '@/views/indexView.vue';
import leaderMeetView from '@/views/leaderMeetView.vue';

const routes = [
    {
        path: '/',
        name: 'home',
        component: indexView
    },
    {
        path: '/:hash/:hashLeader',
        name: 'leaderMeet',
        component: leaderMeetView,
    },
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
})

export default router
