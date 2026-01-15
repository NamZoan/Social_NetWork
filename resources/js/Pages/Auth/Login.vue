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
                            <p>Argon Mạng Xã Hội</p>
                            <span>Hệ Thống Thiết Kế</span>
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
                                    placeholder="Địa chỉ email"
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
                                    placeholder="Mật khẩu"
                                />
                                <span v-if="form.errors.password" class="text-danger small">
                                    {{ form.errors.password }}
                                </span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <a href="/forgot-password">Quên mật khẩu?</a>
                        </div>

                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up" :disabled="form.processing">
                                    Đăng nhập
                                </button>
                            </div>
                        </div>

                        <div class="col-md-12 text-center mt-5">
                            <span class="go-login">Chưa là thành viên?
                                <Link href="/dang-ky">Đăng ký</Link>
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

<style scoped>
@import '../../../css/forms.css';
@import '../../../css/auth.css';
</style>
