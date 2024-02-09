<template>
    <section class="py-50" id="createMeet">
        <div class="container">
            <h3 class="heading__text ta-c mb-50">
                Создание встречи
            </h3>
            <form action="html/leaderMeetView.html" class="d-f fd-c g-30">
                <div>
                    <input
                        v-model="data.title"
                        :class="{ 'is-invalid': error.title }"
                        class="form-control" 
                        type="text" 
                        name="title"
                        placeholder="Название встречи (обязательноe)" 
                        required
                    />
                    <p class="valid__feedback">Пример успеха</p>
                    <p
                        v-for="error in error.title"
                        class="invalid__feedback" 
                    >{{ error }}</p>
                </div>
                <div>
                    <input
                        v-model="data.description"
                        :class="{ 'is-invalid': error.description }"
                        class="form-control" 
                        type="text"
                        name="description" 
                        placeholder="Описание встречи" 
                    />
                    <p class="valid__feedback">Пример успеха</p>
                    <p
                        v-for="error in error.description"
                        class="invalid__feedback" 
                    >{{ error }}</p>
                </div>
                <div>
                    <input 
                        v-model="data.login"
                        :class="{ 'is-invalid': error.login }"
                        class="form-control" 
                        type="email" 
                        name="login"
                        placeholder="E-mail (обязательное)"
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
                        class="form-control" 
                        type="password"
                        name="password" 
                        placeholder="Пароль, для дальнейшей авторизации" 
                    />
                    <p class="valid__feedback">Пример успеха</p>
                    <p
                        v-for="error in error.password"
                        class="invalid__feedback" 
                    >{{ error }}</p>
                </div>
                <ul class="d-f fd-c g-30">
                    <li
                        v-for="date, index in data.dates"
                        class="d-f g-20 fw-w" 
                    >
                        <div class="d-f fd-c mw-150">
                            <label class="ta-c" for="date">Дата проведения</label>
                            <input 
                                @change="data.dates[index]=(new Date($event.target.value)).getTime()"
                                :value="getDate(date)"
                                :min="getDate(Date.now())"
                                :max="getDate(getFutureDate(Date.now(), 30))"
                                :class="{ 'is-invalid': error[`date${index}`] }"
                                class="form-control" 
                                id="date"
                                name="date1" 
                                type="date" 
                            />
                            <p class="valid__feedback">Пример успеха</p>
                            <p
                                v-for="error in error[`date${index}`]"
                                class="invalid__feedback" 
                            >{{ error }}</p>
                        </div>
                        <div class="d-f ai-c g-10">
                            <a
                                :class="{ 'not-available': data.dates.length < 2 }"
                                @click.prevent="data.dates.splice(index, 1)"
                                href="#"
                            >
                                ➖
                            </a>
                            <a 
                                :class="{ 'not-available': data.dates.length > 4 }"
                                @click.prevent="pushNewDate(index)"
                                href="#" 
                            >
                                ➕
                            </a>
                        </div>
                    </li>
                </ul>
                <div class="d-f g-30 fw-w">
                    <div class="d-f fd-c">
                        <label class="ta-c" for="start">Начало</label>
                        <input
                            v-model="data.start"
                            :class="{ 'is-invalid': error.start }"
                            class="form-control" 
                            id="start" 
                            name="start"
                            type="time" 
                        />
                        <p class="valid__feedback">Пример успеха</p>
                        <p 
                            v-for="error in error.start"
                            class="invalid__feedback" 
                        >{{ error }}</p>
                    </div>
                    <div class="d-f fd-c">
                        <label class="ta-c" for="end">Конец</label>
                        <input
                            v-model="data.end"
                            :class="{ 'is-invalid': error.end }"
                            class="form-control" 
                            id="end" 
                            name="end"
                            type="time" 
                        />
                        <p class="valid__feedback">Пример успеха</p>
                        <p
                            v-for="error in error.end"
                            class="invalid__feedback" 
                        >{{ error }}</p>
                    </div>
                </div>
                <div class="d-f g-10 fw-w">
                    <input
                        v-model="data.confirm"
                        :class="{ 'is-invalid': error.confirm }"
                        class="form-control checkbox" 
                        id="agree"
                        type="checkbox"
                        required
                    />
                    <div>
                        <label for="agree">Согласие с политикой конфиденциальности</label>
                    </div>
                    <p class="valid__feedback w-100">Пример успеха</p>
                    <p
                        v-for="error in error.confirm"
                        class="invalid__feedback w-100" 
                    >{{ error }}</p>
                </div>
                <input
                    @click.prevent="fetchCreateMeet"
                    type="submit" 
                    value="Создать" 
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
                title: '',
                description: '',
                login: '',
                password: '',
                dates: [
                    Date.now(),
                ],
                start: '09:00',
                end: '18:00',
                confirm: 0,
            },
            error: {},
        }
    },
    methods: {
        getDate(date) {
            if (date) {
                return `${(new Date(date)).getFullYear()}-${(String((new Date(date)).getMonth() + 1)).padStart(2, '0')}-${String((new Date(date)).getDate()).padStart(2, '0')}`;
            } else {
                return '';
            }
        },
        getFutureDate(date, days) {
            let currentDate = new Date(date);
            currentDate.setDate(currentDate.getDate() + days);
            return currentDate.getTime();
        },
        pushNewDate(index) {
            this.data.dates.splice(index + 1, 0, this.getFutureDate(this.data.dates[index], 1));
        },
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
        async fetchCreateMeet() {
            try {
                this.clearError();
                this.data.dates = this.data.dates.map(this.getDate);

                let requestOptions = {
                    method: 'POST',
                    body: JSON.stringify(this.data),
                    headers: {
                        'Content-Type': 'application/json',
                    }
                }

                let response = await fetch(`${localStorage.getItem('homeUrlAPI')}/api/meet`, requestOptions);
                let data = await response.json();

                if (response.status > 199 && response.status < 301) {
                    this.$router.push(`/${data.data.meet.hash}/${data.data.meet.leaderHash}`);                    
                } else {
                    throw Error(JSON.stringify(data.error));
                }
            } catch (e) {
                let error = JSON.parse(e.message);
                this.downloadErrors(error.errors);
            }
        }
    }
}
</script>

<style>

</style>