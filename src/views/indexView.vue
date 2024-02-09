<template>
    <main>
        <section class="slogan container d-f jc-sb ai-c fw-w">
            <div class="slogan__main d-f jc-sb fd-c g-30">
                <h3 class="slogan__main__header heading__text">
                    Доверь свою встречу <span>нам!</span>
                </h3>
                <p class="fs-m">
                    MeetAnator - сервис по планированию ваших встреч
                </p>
                <router-link 
                    :to="{ name: 'home', hash: '#createMeet' }"
                    class="btn btn-primary" 
                >
                    Начать
                </router-link>
            </div>
            <div class="main__img">
                <img src="@/assets/img/slogan.svg" alt="slogan" />
            </div>
        </section>
        <section class="about py-50" id="about">
            <div class="container">
                <h3 class="heading__text ta-c mb-50">О нас</h3>
                <ul class="about__list d-f jc-c fw-w g-30">
                    <li class="about__list__item ta-c fs-b c-p pb-10 fw-n">
                        &gt;4 <span class="fs-n fw-s">пользователей</span>
                    </li>
                    <li class="about__list__item ta-c fs-b c-p pb-10 fw-n">
                        146% <span class="fs-n fw-s">удовлетворенных пользователей</span>
                    </li>
                    <li class="about__list__item ta-c fs-b c-p pb-10 fw-n">
                        &gt;4 <span class="fs-n fw-s">дней на рынке</span>
                    </li>
                    <li class="about__list__item ta-c fs-b c-p pb-10 fw-n">
                        &gt;100 млн. рублей<span class="fs-n fw-s">привлеченных инвестиции</span>
                    </li>
                </ul>
            </div>
        </section>
        <section class="py-50" id="aboutSystem">
            <div class="container">
                <h3 class="heading__text ta-c mb-50">О системе</h3>
                <div>
                    <video src="@/assets/video/about.mp4" autoplay loop muted></video>
                </div>
            </div>
        </section>

        <create-meet-form />

        <auth-leader-form />
    </main>
</template>

<script>
import createMeetForm from '@/components/createMeetForm.vue';
import authLeaderForm from '@/components/authLeaderForm.vue';
export default {
    components: {
        createMeetForm,
        authLeaderForm
    },
}
</script>
