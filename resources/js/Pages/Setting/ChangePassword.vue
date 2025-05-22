<template>
    <Index>
        <div class="content">
            <div class="settings-form p-4">
                <h2>Đổi mật khẩu</h2>
                <form @submit.prevent="submitForm" class="mt-4 settings-form">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mật khẩu hiện tại</label>
                            <input type="password" class="form-control" v-model="form.current_password" />
                            <span v-if="form.errors.current_password" class="text-danger small">{{ form.errors.current_password }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Mật khẩu mới</label>
                            <input type="password" class="form-control" v-model="form.new_password" />
                            <span v-if="form.errors.new_password" class="text-danger small">{{ form.errors.new_password }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label>Xác nhận mật khẩu mới</label>
                            <input type="password" class="form-control" v-model="form.new_password_confirmation" />
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Đổi mật khẩu
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Index>
</template>

<script setup>
import Index from "./Index.vue";
import { useForm } from "@inertiajs/vue3";

const form = useForm({
    current_password: "",
    new_password: "",
    new_password_confirmation: "",
});

const submitForm = () => {
    form.post("/user/update-password", {
        preserveScroll: true,
    });
};
</script>

<style scoped>

@import '../../../css/forms.css';
@import '../../../css/settings.css';

</style>
