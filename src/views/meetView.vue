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
                    <div class="mb-25">
                        <h3 class="heading__text">{{ meet.title }}</h3>

                        <div 
                            v-if="meet.block" 
                            class="d-f g-10"
                        >
                            <span class="mark mark-danger">заблокирована</span>
                            <div class="info">
                                <p class="info__link">❔</p>
                                <div class="info__description bg-d p-10 br-10">Встреча заблокирована, добавление новых пользователей невозможно, изменение существующих интервалов невозможно</div>
                            </div>
                        </div>
                    </div>


                    <availables-meet 
                        :meet="meet" 
                        :onlyView="true" 
                    />

                    <file-view-meet 
                        :files="meet" 
                        @update="fetchGetMeetInfo" 
                    />

                    <auth-meet-form 
                        v-if="!meet.block" 
                    />
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
    import fileViewMeet from '@/components/fileViewMeet.vue';
    import authMeetForm from '@/components/authMeetForm.vue';

    export default {
        components: {
            availablesMeet,
            fileViewMeet,
            authMeetForm,
        },
        data() {
            return {
                meet: null,
                error: null
            }
        },
        methods: {
            async fetchGetMeetInfo() {
                try {
                    let requestOptions = {
                        method: 'GET',
                    }
        
                    let response = await fetch(`${localStorage.getItem('homeUrlAPI')}/api/meet/${this.$route.params.hash}`, requestOptions);
                    let data = await response.json();
        
                    if (response.status > 199 && response.status < 301) {
                        this.meet = data.data.meet;
                        this.userID = this.meet.leader.id;
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
                return this.meet ? `url('${localStorage.getItem('homeUrlAPI')}/api/img/${this.$route.params.hash}/${this.meet.img}')` : '';
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