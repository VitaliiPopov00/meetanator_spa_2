<template>
    <main
        :style="{ 'background-image': meetImg }"
        class="bg-d"
    >
        <section 
            v-if="meet && !error"
            class="py-50"
        >
            <div class="container bg-white p-20 br-10">
                <div class="mb-25">
                    <h3 class="heading__text">Управление встречей</h3>

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

                <ul class="mb-50 d-f fd-c g-20">
                    <li>
                        <span class="fs-m d-b c-p fw-n">ID</span>
                        <p class="ml-5">{{ $route.params.hash }}</p>
                    </li>
                    <li>
                        <span class="fs-m d-b c-p fw-n">Название</span>
                        <p class="ml-5">{{ meet.title }}</p>
                    </li>
                    <li>
                        <span class="fs-m d-b c-p fw-n">Описание</span>
                        <p class="ml-5">{{ meet.description }}</p>
                    </li>
                    <li>
                        <span class="fs-m d-b c-p fw-n">Лидер</span>
                        <p class="ml-5">{{ meet.leader.login }}</p>
                    </li>
                    <li>
                        <span class="fs-m d-b c-p fw-n">Ссылки на встречу:</span>
                        <div class="d-f fd-c g-15 ml-5">
                            <div>
                                <p class="mb-5">Для редактирования встречи:
                                <router-link
                                    :to="{ path: `/${$route.params.hash}/${$route.params.hashLeader}` }"
                                    class="c-p"
                                >{{ getHost() }}/{{ $route.params.hash }}/{{ $route.params.hashLeader }}</router-link></p>
                                <p class="mark mark-info">не передавайте ее никому, а также сохраните ее, если вы не
                                зарегистрированы</p>
                            </div>
                            <div>
                                <p class="mb-5">Для просмотра и приглашения участников:
                                <router-link
                                    :to="{ path: `/${$route.params.hash}` }"
                                    class="c-p mb-10"
                                >http://localhost/{{ $route.params.hash }}</router-link></p>
                                <figure class="qr-code__meet__leader">
                                    <img src="@/assets/img/qrcode.png" alt="QR-CODE" class="qr-code__meet__leader__img">
                                    <figcaption>QR-Code для встречи</figcaption>
                                </figure>
                            </div>
                        </div>
                    </li>
                </ul>

                <availables-meet 
                    :meet="meet"
                    :userID="userID"
                    @update="fetchGetMeetInfo"
                />

                <file-view-meet 
                    :files="meet"
                    @update="fetchGetMeetInfo"
                />

                <file-meet-form 
                    @update="fetchGetMeetInfo"
                />

                <invite-form />

                <div 
                    v-if="!meet.block"
                    class="mb-25"
                >
                    <a 
                        @click="fetchBlockMeet"
                        href="#" 
                        class="btn btn-danger"
                    >Заблокировать встречу </a>
                </div>
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
    </main>
</template>


<script>
import fileMeetForm from '@/components/fileMeetForm.vue';
import inviteForm from '@/components/inviteForm.vue';

export default {
    data() {
        return {
            meet: null,
            error: null,
            userID: null,
        }
    },
    components: {
        fileMeetForm,
        inviteForm,
    },
    methods: {
        getHost() {
            return window.location.origin;
        },
        async fetchCheckAccess() {
            try {
                let requestOptions = {
                    method: 'GET',
                }

                let response = await fetch(`${localStorage.getItem('homeUrlAPI')}/api/meet/${this.$route.params.hash}/${this.$route.params.hashLeader}`, requestOptions);
                let data = await response.json();

                if (response.status > 199 && response.status < 301) {
                    this.fetchGetMeetInfo();
                } else {
                    throw Error(JSON.stringify(data.error));
                }
            } catch (e) {
                this.error = JSON.parse(e.message);
            }
        },
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
        async fetchBlockMeet() {
            try {
                let requestOptions = {
                    method: 'DELETE',
                }

                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/${this.$route.params.hashLeader}`, requestOptions);
                
                if (response.status > 199 && response.status < 300) {
                    this.fetchGetMeetInfo();
                } else {
                    let data = await response.json();
                    throw Error(JSON.stringify(data.error));
                }
            } catch(e) {
                this.error = JSON.parse(e.message);
            }
        }
    },
    computed: {
        meetImg() {
            return this.meet ? `url('${localStorage.getItem('homeUrlAPI')}/api/img/${this.$route.params.hash}/${this.meet.img}')` : '';
        }
    },
    mounted() {
        this.fetchCheckAccess();
    }
}
</script>


<style>

</style>