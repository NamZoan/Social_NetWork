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
                            <p>Nhập mã OTP và mật khẩu mới.</p>
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

                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    v-model="form.code"
                                    type="text"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    maxlength="6"
                                    class="form-control"
                                    placeholder="Nhập mã OTP"
                                    @input="sanitizeCode"
                                />
                                <span v-if="form.errors.code" class="text-danger small">
                                    {{ form.errors.code }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="form-control"
                                    placeholder="Mật khẩu mới"
                                />
                                <span v-if="form.errors.password" class="text-danger small">
                                    {{ form.errors.password }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    type="password"
                                    v-model="form.password_confirmation"
                                    class="form-control"
                                    placeholder="Xác nhận mật khẩu"
                                />
                                <span v-if="form.errors.password_confirmation" class="text-danger small">
                                    {{ form.errors.password_confirmation }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <span class="go-login">
                                Chưa nhận được mã?
                                <Link href="/forgot-password">Gửi lại mã</Link>
                            </span>
                        </div>

                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up" :disabled="form.processing">
                                    Đổi mật khẩu
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

const props = defineProps({
    email: String,
});

const form = useForm({
    email: props.email || '',
    code: '',
    password: '',
    password_confirmation: '',
});

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || '');

const sanitizeCode = () => {
    form.code = form.code.replace(/\D/g, '').slice(0, 6);
};

const submitForm = () => {
    form.post('/reset-password', {
        preserveScroll: true,
    });
};
</script>

<style scoped>
@import '../../../css/forms.css';
@import '../../../css/auth.css';
</style>
