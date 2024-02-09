<template>
    <section class="mb-50">
        <form action="#" method="POST" enctype=multipart/form-data>
            <h3 class="mb-15 fs-m">
                Обновите визуальное и информационное содержание вашей встречи
            </h3>
            <div class="d-f fd-c g-30 mb-30">
                <div class="d-f fd-c">
                    <label 
                        :class="{ 'is-invalid': error.upload_img }"
                        class="form-control-label-file form-control"
                    >
                        <span>Выберите изображение</span>
                        <br>
                        <input
                            ref="img"
                            type="file" 
                            name="file" 
                            id="file" 
                            accept="image/png, image/jpeg, image/jpg" 
                            class="d-n"
                        >
                    </label>
                    <p class="valid__feedback">Пример успеха</p>
                    <p 
                        v-for="error in error.upload_img"
                        class="invalid__feedback"
                    >{{ error }}</p>
                </div>
                <div class="d-f fd-c">
                    <label 
                        :class="{ 'is-invalid': error.upload_files }"
                        class="form-control-label-file form-control"
                    >
                        <span>Выберите pdf (до 2-х файлов)</span>
                        <br>
                        <input
                            ref="files"
                            type="file" 
                            name="file1" 
                            accept=".pdf" 
                            id="file1" 
                            class="d-n"
                            multiple
                        >
                    </label>
                    <p class="valid__feedback">Пример успеха</p>
                    <p 
                        v-for="error in error.upload_files"
                        class="invalid__feedback" 
                    >{{ error }}</p>
                </div>
            </div>
            <input 
                @click.prevent="fetchUpdateMeetFile"
                type="submit" 
                value="Сохранить" 
                class="btn btn-primary" 
            />
        </form>
    </section>
</template>


<script>
export default {
    data() {
        return {
            error: {},
        }
    },
    methods: {
        async fetchUpdateMeetFile() {
            try {
                this.clearError();
                let hash = this.$route.params.hash;
                let hashLeader = this.$route.params.hashLeader;
                let requestOptions = {
                    method: "POST",
                    body: this.getDataForFetch(),
                }

                let response = await fetch(`${localStorage.homeUrlAPI}/api/meet/${hash}/${hashLeader}/file`, requestOptions);
                let data = await response.json();

                if (response.status > 199 && response.status < 300) {
                    this.$emit('update');
                } else {
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
                for (let error in this.errors) {
                    this.errors[error] = '';
                }
            }
        },
        getDataForFetch() {
            let data = new FormData();
            let img = this.$refs.img.files[0];

            if (img) { data.append('upload_img', img); }

            for (let i = 0; i < this.$refs.files.files.length; i++) {
                data.append('upload_files[]', this.$refs.files.files[i]);
            }

            return data;
        },
        
    }
}
</script>


<style>

</style>