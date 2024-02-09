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

                <section class="mb-50">
                    <h3 class="mb-15 fs-m">
                        Отправьте приглашение новым участникам встречи
                    </h3>
            
                    <form class="d-f fd-c g-30">
                        <div class="d-f ai-c g-10">
                            <div>
                                <input 
                                    type="email" 
                                    name="email" 
                                    class="form-control" 
                                    placeholder="Введите email участника"
                                />
                                <p class="valid__feedback">Пример успеха</p>
                                <p 
                                    class="invalid__feedback" 
                                >Пример ошибки</p>
                            </div>
                            <div class="d-f g-10">
                                <p class="h-p">➖</p>
                                <p class="h-p">➕</p>
                            </div>
                        </div>
            
                        <input 
                            type="submit" 
                            value="Отправить" 
                            class="btn btn-primary" 
                        />
                    </form>
                </section>

                <div class="mb-25">
                    <a href="#" class="btn btn-danger">Заблокировать встречу </a>
                </div>
            </div>
        </section>
    </main>
</template>


<script>
import fileMeetForm from '@/components/fileMeetForm.vue';
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