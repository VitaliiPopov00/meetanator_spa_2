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
					<li 
						v-if="token"
					>
						<router-link
							:to="{ name: 'profile' }"
							class="btn btn-transparent"
						>
							Профиль
						</router-link>
					</li>
					<li 
						v-else
					>
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
    data() {
		return {
			token: localStorage.token,
		}
	},
	watch: {
		$route(to, from) {
			if (to.name == 'home' && from.name == 'profile') {
				this.token = localStorage.token;
			}

			if (to.name == 'profile' && from.name == 'home') {
				this.token = localStorage.token;
			}
		}
	}
}
</script>

<style></style>