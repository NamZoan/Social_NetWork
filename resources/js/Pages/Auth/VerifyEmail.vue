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
                            <p>Argon Mạng Xã Hội</p>
                        </div>
                    </div>
                </div>

                <form @submit.prevent="submit">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <p>
                                Chúng tôi đã gửi mã xác thực 6 chữ số tới
                                <strong>{{ email }}</strong>.
                                Vui lòng nhập mã để kích hoạt tài khoản.
                            </p>
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
                                    placeholder="Nhập mã xác thực"
                                    @input="sanitizeCode"
                                />
                                <span v-if="form.errors.code" class="text-danger small">
                                    {{ form.errors.code }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <button
                                    type="button"
                                    class="btn btn-light"
                                    @click="resend"
                                    :disabled="resendForm.processing"
                                >
                                    Gửi lại mã
                                </button>
                            </div>
                        </div>

                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up" :disabled="form.processing">
                                    Xác thực
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12 text-center mt-4">
                            <span class="go-login">
                                Đã có tài khoản?
                                <Link href="/dang-nhap">Đăng nhập</Link>
                            </span>
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

defineProps({
    email: String,
});

const page = usePage();
const statusMessage = computed(() => page.props.flash?.status || '');

const form = useForm({
    code: '',
});

const resendForm = useForm({});

const sanitizeCode = () => {
    form.code = form.code.replace(/\D/g, '').slice(0, 6);
};

    const submit = () => {
        form.post('/email/verify-otp', {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('code');
            },
            onError: () => {
                form.code = '';
            },
        });
    };

const resend = () => {
    resendForm.post('/email/verification-notification', {
    });
};
</script>

<style scoped>
@import '../../../css/forms.css';
@import '../../../css/auth.css';
</style>
