<template>
    <div class="row ht-100v flex-row-reverse no-gutters">
        <div class="col-md-12 d-flex justify-content-center align-items-center">
            <div class="signup-form">
                <!-- Hiển thị thông báo đăng nhập thành công -->
                <div v-if="successMessage" class="alert alert-success">
                    {{ successMessage }}
                </div>

                <div class="auth-logo text-center mb-5">
                    <div class="row">
                        <div class="col-md-2">
                            <img :src="'/images/web/logo-64x64.png'" class="logo-img" alt="Logo">
                        </div>
                        <div class="col-md-10">
                            <p>Argon Social Network</p>
                            <span>Design System</span>
                        </div>
                    </div>
                </div>

                <!-- Form đăng nhập -->
                <form @submit.prevent="submitForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    type="email"
                                    v-model="form.email"
                                    class="form-control"
                                    placeholder="Email Address"
                                />
                                <span v-if="form.errors.email" class="text-danger small">
                                    {{ form.errors.email }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <input
                                    type="password"
                                    v-model="form.password"
                                    class="form-control"
                                    placeholder="Password"
                                />
                                <span v-if="form.errors.password" class="text-danger small">
                                    {{ form.errors.password }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <a href="/forgot-password">Forgot password?</a>
                        </div>

                        <div class="col-md-6">
                            <label class="custom-control material-checkbox">
                                <input type="checkbox" class="material-control-input">
                                <span class="material-control-indicator"></span>
                                <span class="material-control-description">Remember Me</span>
                            </label>
                        </div>

                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up" :disabled="form.processing">
                                    Sign In
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12 text-center mt-5">
                            <span class="go-login">Not yet a member?
                                <Link href="/dang-ky">Sign Up</Link>
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
import { Link, usePage, useForm } from '@inertiajs/vue3';

// Khởi tạo form đăng nhập
const form = useForm({
  email: "",
  password: "",
});

const page = usePage();
const successMessage = computed(() => page.props.flash?.success || "");

// Xử lý đăng nhập
const submitForm = () => {
  form.post("/dang-nhap", {
    preserveScroll: true,
    onError: (errors) => {
      console.log("Lỗi đăng nhập:", errors);
    },
  });
};
</script>

<style scoped></style>
