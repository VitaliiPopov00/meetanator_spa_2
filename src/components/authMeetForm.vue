<template>
    <section>
        <h3 class="fs-m mb-15">Введите данные, чтобы добавить себя</h3>

        <form action="./meet_update.html" class="d-f fd-c g-20">
            <div>
                <input 
                    :class="{ 'is-invalid': error.login }" 
                    v-model="data.login" 
                    class="form-control w-100" 
                    type="text" 
                    name="email" 
                    placeholder="E-mail (обязательноe)"
                    required 
                />
                <p class="valid__feedback">Пример успеха</p>
                <p 
                    v-for="error in error.login"
                    class="invalid__feedback" 
                >{{ error }}</p>
            </div>
            <div>
                <input 
                    :class="{ 'is-invalid': error.password }" 
                    v-model="data.password" 
                    class="form-control w-100" 
                    type="password" 
                    name="password" 
                    placeholder="Пароль (необязательно)"
                />
                <p class="valid__feedback">Пример успеха</p>
                <p 
                    v-for="error in error.password"
                    class="invalid__feedback" 
                >{{ error }}</p>
            </div>
            <input 
                @click.prevent="fetchLogin" 
                type="submit" 
                value="Далее" 
                class="btn btn-primary as-fs" 
            />
        </form>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                data: {
                    login: '',
                    password: '',
                },
                error: {},
            }
        },
        methods: {
            async fetchLogin() {
                try {
                    this.clearError();
                    let requestOptions = {
                        method: 'POST',
                        body: JSON.stringify(this.data),
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    }

                    let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/login`, requestOptions);
                    let data = await response.json();

                    if (response.status > 199 && response.status < 300) {
                        if (data.data.user.isLeader) {
                            this.$router.push(`/${this.$route.params.hash}/${data.data.meet.leaderHash}`);
                        } else {
                            this.$router.push(`/${this.$route.params.hash}/user/${data.data.user.id}`);
                        }
                    } else {
                        throw Error(JSON.stringify(data.error));
                    }
                } catch(e) {
                    let error = JSON.parse(e.message);

                    this.downloadErrors(error.errors);

                    if (error.code == 401) {
                        this.error.login = ['Логин или пароль введен неверно'];
                    } else if (error.code != 422) {
                        this.error.login = [error.message];
                    }
                }
            },
            downloadErrors(errors) {
                for (let attributeName in errors) {
                    this.error[attributeName] = errors[attributeName];
                }
            },
            clearError(attributeName) {
                if (attributeName) {
                    this.errors[`${attributeName}`] = '';
                } else {
                    for (let error in this.error) {
                        this.error[error] = '';
                    }
                }
            },
        }
    }
</script>

<style scoped>

</style>