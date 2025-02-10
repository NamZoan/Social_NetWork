<template>
    <div class="row ht-100v flex-row-reverse no-gutters">
        <div class="col-md-12 d-flex justify-content-center align-items-center">
            <div class="signup-form">
                <div class="auth-logo text-center mb-5">
                    <div class="row">
                        <div class="col-md-2">
                            <img :src="'/images/web/logo-64x64.png'" class="logo-img" alt="Logo" />
                        </div>
                        <div class="col-md-10">
                            <p>Argon Social Network</p>
                            <span>Design System</span>
                        </div>
                    </div>
                </div>
                <form @submit.prevent="submitForm">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" class="form-control" v-model="form.name" placeholder="Họ tên" />
                                <span v-if="form.errors.name" class="text-danger small">{{
                                    form.errors.name
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" class="form-control" v-model="form.email" placeholder="Email" />
                                <span v-if="form.errors.email" class="text-danger small">{{
                                    form.errors.email
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="text" class="form-control" v-model="form.phone"
                                    placeholder="Số Điện Thoại" />
                                <span v-if="form.errors.phone" class="text-danger small">{{
                                    form.errors.phone
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select v-model="form.day" class="form-control">
                                    <option value="">- Ngày -</option>
                                    <option v-for="day in 31" :key="day" :value="day">
                                        {{ day }}
                                    </option>
                                </select>
                                <span v-if="form.errors.day" class="text-danger small">{{
                                    form.errors.day
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select v-model="form.month" class="form-control">
                                    <option value="">- Tháng -</option>
                                    <option v-for="month in 12" :key="month" :value="month">
                                        {{ month }}
                                    </option>
                                </select>
                                <span v-if="form.errors.month" class="text-danger small">{{
                                    form.errors.month
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <select v-model="form.year" class="form-control">
                                    <option value="">- Năm -</option>
                                    <option v-for="year in years" :key="year" :value="year">
                                        {{ year }}
                                    </option>
                                </select>
                                <span v-if="form.errors.year" class="text-danger small">{{
                                    form.errors.year
                                }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="password" class="form-control" v-model="form.password"
                                    placeholder="Mật Khẩu" />
                                <span v-if="form.errors.password" class="text-danger small">{{ form.errors.password }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <input type="password" class="form-control" v-model="form.confirmPassword"
                                    placeholder="Xác Nhận Mật Khẩu" />
                                <span v-if="form.errors.confirmPassword" class="text-danger small">{{
                                    form.errors.confirmPassword }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <span class="go-login">Already a member?
                                <Link href="/dang-nhap">Sign In</Link>
                            </span>
                        </div>
                        <div class="col-md-6 text-right">
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary sign-up">
                                    Sign Up
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
import { Link, useForm } from "@inertiajs/vue3";
import '../../../css/forms.css';
import '../../../css/auth.css';


const years = Array.from({ length: 100 }, (_, i) => new Date().getFullYear() - i);

const form = useForm({
    name: "",
    email: "",
    phone: "",
    day: "",
    month: "",
    year: "",
    password: "",
    confirmPassword: "",
    errors: {}
});




const submitForm = () => {

    form.post("/dang-ky", {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
        },
        onError: (errors) => {
            form.errors = errors;
            console.log("Lỗi từ server:", errors);

        }
    });
};
</script>

<style scoped></style>
