<template>
    <ul class="d-f fd-c g-30 mb-50">
        <li 
            v-for="meet in meets"
            class="d-f fd-c g-10 card__meet__proile" 
        >
            <p>Встреча:
                <span class="c-p">{{ meet.title }}</span>
                <span 
                    v-if="meet.delete" 
                    class="mark mark-danger ml-5"
                >удалена</span>
                <span 
                    v-if="meet.block" 
                    class="mark mark-danger ml-5"
                >заблокирована</span></p>
            <p 
                v-if="!meet.delete"
            >
                Ссылка для приглашения участников и просмотра встречи:
                <router-link 
                    :to="{ name: 'meet', params: { hash: meet.hash } }"
                    class="c-p d-b"
                >{{ getHost() }}/{{ meet.hash }}</router-link>
            </p>
            <div 
                v-if="!meet.delete"
                class="d-f g-20 fw-w" 
            >
                <router-link 
                    :to="{ name: 'leaderMeet', params: { hash: meet.hash, hashLeader: meet.hashLeader } }"
                    class="btn btn-primary"
                >Перейти</router-link>
                <a 
                    @click.prevent="fetchDeleteMeet(meet.hash, meet.hashLeader)"
                    href="#" 
                    class="btn btn-danger" 
                >Удалить</a>
            </div>
        </li>
    </ul>
</template>

<script>
export default {
    props: {
        meets: {
            type: Array,
            required: true,
        },
    },
    methods: {
        getHost() {
            return `${window.location.origin}`;
        },
        async fetchDeleteMeet(hash, hashLeader) {
            let requestOptions = {
                method: "DELETE",
                redirect: "follow",
            };

            try {
                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${hash}/${hashLeader}/delete`, requestOptions
                );

                if (response.status == 200 || response.status == 204) {
                    this.$emit('update');
                } else {
                    let data = await response.json();
                    throw Error(JSON.stringify(data.error));
                }
            } catch (error) {
                console.log(JSON.parse(error.message));
            }
        }
    }
}
</script>

<style scoped></style>