<template>
    <section class="py-50" id="authorization">
        <div class="container">
            <h3 class="heading__text ta-c mb-30">Аутентификация</h3>
            <form action="./html/profileView.html" class="d-f fd-c g-30">
                <div>
                    <input 
                        v-model="data.login"
                        :class="{ 'is-invalid': error.login }"
                        type="email" 
                        name="email" 
                        placeholder="E-mail" 
                        class="form-control"
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
                        v-model="data.password"
                        :class="{ 'is-invalid': error.password }"
                        type="password" 
                        name="password" 
                        placeholder="Пароль" 
                        class="form-control"
                        required 
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
                    value="Войти" 
                    class="btn btn-primary" 
                />
            </form>
        </div>
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
            clearError(attributeName) {
                if (attributeName) {
                    this.error[attributeName] = '';
                } else {
                    for (let error in this.error) {
                        this.error[error] = '';
                    }
                }
            },
            downloadErrors(errors) {
                for (let attributeName in errors) {
                    this.error[attributeName] = errors[attributeName];
                }
            },
            async fetchLogin() {
                try {
                    this.clearError();

                    let requestOptions = {
                        method: 'POST',
                        body: JSON.stringify(this.data),
                        headers: {
                            'Content-Type': 'application/json',
                        }
                    }

                    let response = await fetch(`${localStorage.getItem('homeUrlAPI')}/api/user/login`, requestOptions);
                    let data = await response.json();

                    if (response.status > 199 && response.status < 301) {
                        localStorage.setItem('token', data.data.token);
                        this.$router.push('/profile');
                    } else {
                        throw Error(JSON.stringify(data.error));
                    }
                } catch (e) {
                    let error = JSON.parse(e.message);
                    this.downloadErrors(error.errors);

                    if (error.code == 401) {
                        this.error.login = ['Логин или пароль введен неверно'];
                    }
                }
            }
        }
    }
</script>

<style>
</style>