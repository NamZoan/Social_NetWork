<template>
    <div class="row ht-100v flex-row-reverse no-gutters">
        <div class="col-md-12 d-flex justify-content-center align-items-center">
            <div class="signup-form">
                <div v-if="statusMessage" class="alert alert-success">
                    {{ statusMessage }}
                </div>

                <div class="auth-logo text-center mb-5">
                    <div class="row">
                        <div class="col-md-2">
                            <img :src="'/images/web/logo-64x64.png'" class="logo-img" alt="Logo">
                        </div>
                        <div class="col-md-10">
                            <p>Argon Mang Xa Hoi</p>
                            <span>He Thong Thiet Ke</span>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submitForm">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <p>Nhập email để nhận mã OTP đặt lại mật khẩu.</p>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="form-control"
                                    placeholder="Nhập email"
                                />
                                <span v-if="form.errors.email" class="text-danger small">
                                    {{ form.errors.email }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <span class="go-login">
                                Đã có tài khoản?
                                <Link href="/dang-nhap">Đăng nhập</Link>
                            </span>
                        </div>

                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up" :disabled="form.processing">
                                    Gửi mã OTP
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

const form = useForm({
    email: '',
});

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || '');

const submitForm = () => {
    form.post('/forgot-password', {
        preserveScroll: true,
    });
};
</script>

<style scoped>
@import '../../../css/forms.css';
@import '../../../css/auth.css';
</style>
