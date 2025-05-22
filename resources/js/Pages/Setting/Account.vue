<template>
    <Index>
        <div class="content">
            <div class="settings-form p-4">
                <h2>Your Account</h2>
                <form @submit.prevent="submitForm" class="mt-4 settings-form">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsName">Name</label>
                            <input type="text" class="form-control" id="settingsName" v-model="form.name" placeholder="Name" />
                            <span v-if="form.errors.name" class="text-danger small">{{ form.errors.name }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsUsername">Username</label>
                            <input type="text" class="form-control" id="settingsUsername" v-model="form.username" placeholder="Username" />
                            <span v-if="form.errors.username" class="text-danger small">{{ form.errors.username }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsEmail">Email</label>
                            <input type="email" class="form-control" id="settingsEmail" v-model="form.email" placeholder="Email" />
                            <span v-if="form.errors.email" class="text-danger small">{{ form.errors.email }}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsPhone">Phone</label>
                            <input type="text" class="form-control" id="settingsPhone" v-model="form.phone" placeholder="Phone" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="settingsBirthday">Birthday</label>
                            <input type="date" class="form-control" id="settingsBirthday" v-model="form.birthday" placeholder="Birthday" />
                        </div>
                    </div>
                    <div class="col-md-6 text-right">
                        <button type="submit" class="btn btn-primary btn-sm">
                            Save Changes
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
import { defineProps } from "vue";

const props = defineProps({
    user: Object,
});
console.log("User data:", props.user);

function convertBirthday(birthday) {
    if (!birthday) return "";
    const [day, month, year] = birthday.split("-");
    if (!day || !month || !year) return "";
    return `${year}-${month.padStart(2, "0")}-${day.padStart(2, "0")}`;
}

function toDDMMYYYY(dateStr) {
    if (!dateStr) return "";
    const [year, month, day] = dateStr.split("-");
    if (!year || !month || !day) return "";
    return `${day}-${month}-${year}`;
}

const form = useForm({
    name: props.user.name || "",
    username: props.user.username || "",
    email: props.user.email || "",
    phone: props.user.phone || "",
    birthday: convertBirthday(props.user.birthday),
});

const submitForm = () => {
    const data = { ...form.data() };
    data.birthday = toDDMMYYYY(data.birthday);
    form.post("/user/update", {
        data,
        preserveScroll: true,
    });
};
</script>
<style scoped>

@import '../../../css/forms.css';
@import '../../../css/settings.css';

</style>
