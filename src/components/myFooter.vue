<template>
    <footer class="pt-50 pb-10">
        <div class="container">
            <div class="mb-40">
                <router-link 
                    :to="{ name: 'home' }"
                    class="logo mb-20 fw-n" 
                >
                    MeetAnator
                </router-link>
                <p>До новых наших встреч</p>
            </div>
            <ul class="footer__links pb-10 mb-15 d-f g-30 jc-c fw-w">
                <li>
                    <router-link 
                        :to="{ name: 'home', hash: '#about' }"
                    >
                        О нас
                    </router-link>
                </li>
                <li>
                    <router-link 
                        :to="{ name: 'home', hash: '#aboutSystem' }"
                    >
                        О системе
                    </router-link>
                </li>
                <li>
                    <a href="@/assets/text/soglashenie.txt"> Пользовательское соглашение </a>
                </li>
                <li>
                    <a href="@/assets/text/konfidencialnost.txt">
                        Политика конфиденциальности
                    </a>
                </li>
            </ul>
            <p class="footer__copyright ta-c">2023&copy; copyright</p>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'myFooter',
}
</script>

<style></style>