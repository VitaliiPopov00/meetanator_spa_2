<template>
    <section class="mb-50">
        <h3 class="mb-15 fs-m">
            Отправьте приглашение новым участникам встречи
        </h3>

        <form class="d-f fd-c g-30">
            <div 
                v-for="email, index in emails"
                :key="index"
                class="d-f ai-c g-10" 
            >
                <div>
                    <input 
                        :class="{ 'is-invalid': error[`email${index}`] }"
                        :value="email"
                        @input="emails[index] = $event.target.value"
                        type="email" 
                        name="email" 
                        class="form-control" 
                        placeholder="Введите email участника"
                    />
                    <p class="valid__feedback">Пример успеха</p>
                    <p 
                        v-for="error in error[`email${index}`]"
                        class="invalid__feedback" 
                    >{{ error }}</p>
                </div>
                <div class="d-f g-10">
                    <p 
                        @click="emails.splice(index, 1)" 
                        :class="{ 'not-available': emails.length < 2 }"
                        alt="Удалить" 
                        class="h-p"
                    >➖</p>
                    <p 
                        @click="emails.push('')" 
                        class="h-p"
                    >➕</p>
                </div>
            </div>

            <input 
                @click.prevent="fetchSendInvite" 
                type="submit" 
                value="Отправить" 
                class="btn btn-primary" 
            />
        </form>
    </section>
</template>

<script>
    export default {
        data() {
            return {
                emails: [
                    '',
                ],
                error: {},
            }
        },
        methods: {
            async fetchSendInvite() {
                try {
                    this.clearError();
                    let requestOptions = {
                        method: 'POST',
                        headers: {
                            'Content-type': 'application/json',
                        },
                        body: JSON.stringify(this.emails),
                    }

                    let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/${this.$route.params.hashLeader}/invite`, requestOptions);

                    if (response.status > 199 && response.status < 300) {
                        this.emails.length = 1;
                        this.emails[0] = '';
                    } else {
                        let data = await response.json();
                        throw Error(JSON.stringify(data.error));
                    }
                } catch (e) {
                    let error = JSON.parse(e.message);
                    this.error = error.errors; 
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