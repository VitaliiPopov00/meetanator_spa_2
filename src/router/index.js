import { createRouter, createWebHistory } from 'vue-router';
import indexView from '@/views/indexView.vue';
import leaderMeetView from '@/views/leaderMeetView.vue';
import meetView from '@/views/meetView.vue';
import participantMeetView from '@/views/participantMeetView.vue';
import profileView from '@/views/profileView.vue';

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
    {
        path: '/:hash',
        name: 'meet',
        component: meetView,
    },
    {
        path: '/:hash/user/:userID',
        name: 'participantMeet',
        component: participantMeetView,
    },
    {
        path: '/profile',
        name: 'profile',
        component: profileView,
    },
]

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
})

export default router
