<template>
    <header class="py-20">
		<div class="container">
			<nav class="d-f ai-c jc-sb fw-w g-20">
                <router-link 
                    :to="{ name: 'home' }"
                    class="logo fw-b" 
                >
                    MeetAnator
                </router-link>
				<ul class="d-f jc-sb ai-c g-30">
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
				</ul>
				<ul class="d-f jc-sb ai-c g-30">
					<li >
                        <router-link 
                            :to="{ name: 'home', hash: '#authorization' }"
                            class="btn btn-transparent"    
                        >
                            Войти
                        </router-link>
					</li>
					<li>
                        <router-link 
                            :to="{ name: 'home', hash: '#createMeet' }"
                            class="btn btn-primary" 
                        >
                            Начать
                        </router-link>
					</li>
				</ul>
			</nav>
		</div>
	</header>
</template>

<script>
export default {
    name: 'myHeader',
}
</script>

<style></style>