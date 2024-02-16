<template>
    <section class="mb-50">
        <h3 class="mb-10 fs-m">Файлы для встречи</h3>
        <table
            v-if="files.files.length || ($route.params.hashLeader && files.img)"
        >
            <tr
                v-if="files.img && $route.params.hashLeader"
            >
                <td class="ta-l py-10 px-20">
                    {{ files.img }}
                </td>
                <td class="py-10 px-20">
                    <a 
                        @click.prevent="fetchDeleteFile(files.img)"
                        href="#"
                    >
                        ➖
                    </a>
                </td>
            </tr>
            <tr
                v-if="files.files.length"
                v-for="file in files.files"
            >
                <td class="ta-l py-10 px-20">
                    {{ file }}
                </td>
                <td class="py-10 px-20">
                    <a
                        v-if="$route.params.hashLeader" 
                        @click.prevent="fetchDeleteFile(file)"
                        href="#"
                    >
                        ➖
                    </a>
                    <a 
                        v-else
                        :href="downloadFile(file)"
                    >
                        <img src="@/assets/img/download.svg" alt="Установить">
                    </a>
                </td>
            </tr>
        </table>
        <p
            v-else-if="$route.params.hashLeader"
        >
            Вы ничего не добавили
        </p>
        <p
            v-else
        >
            Лидер ничего не добавил, для получения информации обратитесь к нему
        </p>
    </section>
</template>


<script>
export default {
    name: 'fileViewMeet',
    props: {
        files: {
            type: Object,
            required: true,
        },
    },
    methods: {
        downloadFile(filename) {
            return `${localStorage.homeUrlAPI}/api/file/${this.$route.params.hash}/${filename}`;
        },
        async fetchDeleteFile(filename) {
            try {
                let requestOptions = {
                    method: 'DELETE',
                }

                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${this.$route.params.hash}/${this.$route.params.hashLeader}/file/${filename}`, requestOptions);
                
                if (response.status > 199 && response.status < 300) {
                    this.$emit('update');
                } else {
                    let data = await response.json();
                    throw Error(JSON.stringify(data.error));
                }
            } catch (e) {
                console.log(JSON.parse(e.message));
            }
        }
    }
}
</script>


<style>

</style>