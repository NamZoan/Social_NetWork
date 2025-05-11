<template>
    <Index>
        <div class="content">
            <div class="settings-form p-4">
                <h2>Your Account</h2>
                <form @submit.prevent="updateAccount" class="mt-4 settings-form">
                    <div class="form-group">
                        <label for="settingsName">Họ Tên</label>
                        <input
                            type="text"
                            class="form-control"
                            id="settingsName"
                            v-model="form.name"
                            :disabled="!isEditing"
                            placeholder="Họ Tên"
                        />
                    </div>
                    <div class="form-group">
                        <label for="settingsUsername">Username</label>
                        <input
                            type="text"
                            class="form-control"
                            id="settingsUsername"
                            v-model="form.username"
                            :disabled="!isEditing"
                            placeholder="Username"
                        />
                    </div>
                    <div class="form-group">
                        <label for="settingsEmail">Email</label>
                        <input
                            type="email"
                            class="form-control"
                            id="settingsEmail"
                            v-model="form.email"
                            :disabled="!isEditing"
                            placeholder="Email"
                        />
                    </div>
                    <div class="form-group">
                        <label for="settingsPhone">Phone</label>
                        <input
                            type="text"
                            class="form-control"
                            id="settingsPhone"
                            v-model="form.phone"
                            :disabled="!isEditing"
                            placeholder="Phone"
                        />
                    </div>
                    <div class="form-group">
                        <label for="settingsBirthday">Birthday</label>
                        <input
                            type="date"
                            class="form-control"
                            id="settingsBirthday"
                            v-model="form.birthday"
                            :disabled="!isEditing"
                        />
                    </div>
                    <div class="text-right">
                        <button
                            type="button"
                            class="btn btn-secondary btn-sm"
                            v-if="!isEditing"
                            @click="isEditing = true"
                        >
                            Edit
                        </button>
                        <button
                            type="submit"
                            class="btn btn-primary btn-sm"
                            v-if="isEditing"
                        >
                            Save Changes
                        </button>
                        <button
                            type="button"
                            class="btn btn-danger btn-sm"
                            v-if="isEditing"
                            @click="cancelEdit"
                        >
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </Index>
</template>

<script setup lang="ts">
import { ref } from "vue";
import axios from "axios";
import Index from "./Index.vue";

const form = ref({
    name: "",
    username: "",
    email: "",
    phone: "",
    birthday: "",
});

const originalForm = ref({ ...form.value }); // Lưu bản sao dữ liệu gốc
const isEditing = ref(false); // Trạng thái chỉnh sửa

const updateAccount = async () => {
    try {
        const response = await axios.put("/api/user/update", form.value);
        alert(response.data.message);
        originalForm.value = { ...form.value }; // Cập nhật dữ liệu gốc sau khi lưu
        isEditing.value = false; // Thoát chế độ chỉnh sửa
    } catch (error) {
        console.error(error);
        alert("Đã xảy ra lỗi khi cập nhật thông tin tài khoản.");
    }
};

const cancelEdit = () => {
    form.value = { ...originalForm.value }; // Khôi phục dữ liệu gốc
    isEditing.value = false; // Thoát chế độ chỉnh sửa
};
</script>

<style scoped>
@import '../../../css/forms.css';
@import '../../../css/settings.css';
</style>