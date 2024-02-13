<template>
    <main>
        <div class="container mt-50">
            <h3 class="heading__text mb-20">Личный кабинет</h3>
            <transition name="page">
                <div
                    v-if="meets.length"
                >
                    <h3 class="fs-m mb-15">Ваши встречи</h3>
                    <leader-meet-list 
                        :meets="meets" 
                        @update="fetchLeaderInfo"
                    />
                </div>
            </transition>
            <a 
                        @click="fetchLogout"
                        href="#" 
                        class="btn btn-primary" 
            >Выход</a>
        </div>
    </main>
</template>

<script>
    import leaderMeetList from '@/components/leaderMeetList.vue';

    export default {
        data() {
            return {
                meets: [],
            }
        },
        components: {
            leaderMeetList,
        },
        methods: {
            async fetchLeaderInfo() {
                let requestOptions = {
                    method: 'GET',
                    redirect: 'follow',
                    headers: {
                        'Authorization': `Bearer ${localStorage.token}`
                    },
                };

                try {
                    let response = await fetch(`${localStorage.homeUrlAPI}/api/user/profile`, requestOptions)
                    let data = await response.json();

                    if (response.status == 200 || response.status == 204) {
                        this.meets = data.data.user.meets;
                    } else {
                        throw Error(JSON.stringify(data.error));
                    }
                } catch (error) {
                    console.log(JSON.parse(error.message));
                }
            },
            async fetchLogout() {
                let requestOptions = {
                    method: 'GET',
                    redirect: 'follow',
                    headers: {
                        'Authorization': 'Bearer ' + localStorage.getItem('token')
                    },
                };

                try {
                    let response = await fetch(`${localStorage.homeUrlAPI}/api/user/logout`, requestOptions)

                    if (response.status == 200 || response.status == 204) {
                        localStorage.removeItem('token');
                        this.$router.push('/');
                    } else {
                        let data = await response.json();
                        throw Error(JSON.stringify(data.error));
                    }
                } catch (error) {
                    console.log(JSON.parse(error.message));
                }
            }
        },
        mounted() {
            this.fetchLeaderInfo();
        }
    }
</script>

<style scoped>
    .page-enter-active,
    .page-leave-active {
        transition: all 0.5s ease;
    }

    .page-enter-from,
    .page-leave-to {
        transform: translateY(20px);
        opacity: 0;
    }
</style>