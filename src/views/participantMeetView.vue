<template>
    <main
        :style="{ 'background-image': meetImg }"
        class="py-50 bg-d" 
    >
        <transition name="page">
            <section 
                v-if="meet && !error"
            >
                <div class="container p-20 br-10 bg-white">
                    <h3 class="heading__text mb-30">
                        Редактирование интервалов
                    </h3>

                    <availables-meet 
                        :meet="meet" 
                        :userID="$route.params.userID" 
                        @update="fetchGetMeetInfo"
                    />

                    <router-link 
                        :to="{ name: 'meet', params: { hash: $route.params.hash } } " 
                        class="btn btn-primary"
                    >Назад</router-link>
                </div>
            </section>
            <section
                v-else-if="error"
            >
                <div class="container mt-10 py-10 bg-white br-10">
                    <h3 class="heading__text mb-25 ta-c">{{ error.code }}</h3>
                    <h3 class="heading__text mb-25 ta-c">{{ error.message }}</h3>
                </div>
            </section>
        </transition>
    </main>
</template>

<script>
    import availablesMeet from '@/components/availablesMeet.vue';
    export default {
        components: {
            availablesMeet,
        },
        data() {
            return {
                error: null,
                meet: null,
            }
        },
        methods: {
            async fetchGetMeetInfo() {
                try {
                    let requestOptions = {
                        method: 'GET',
                    }
        
                    let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}`, requestOptions);
                    let data = await response.json();
        
                    if (response.status > 199 && response.status < 301) {
                        this.meet = data.data.meet;
                    } else {
                        throw Error(JSON.stringify(data.error));
                    }
                } catch (e) {
                    this.error = JSON.parse(e.message);
                }
            },
        },
        computed: {
            meetImg() {
                return this.meet ? `url('${localStorage.homeUrlAPI}/api/img/${this.$route.params.hash}/${this.meet.img}')` : '';
            }
        },
        mounted() {
            this.fetchGetMeetInfo();
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