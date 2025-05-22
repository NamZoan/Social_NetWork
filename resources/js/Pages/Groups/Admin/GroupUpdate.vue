<template>
    <GroupDetail  :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
    <div class="container mt-4">
        <h2>Cập nhật nhóm</h2>
        <form @submit.prevent="submit">
            <div class="form-group mb-2">
                <label>Tên nhóm</label>
                <input v-model="form.name" class="form-control" required />
            </div>
            <div class="form-group mb-2">
                <label>Mô tả</label>
                <textarea v-model="form.description" class="form-control"></textarea>
            </div>
            <div class="form-group mb-2">
                <label>Trạng thái nhóm</label>
                <select v-model="form.privacy_setting" class="form-control">
                    <option :value="true">Công khai</option>
                    <option :value="false">Riêng tư</option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label>Cho phép duyệt bài</label>
                <select v-model="form.post_approval_required" class="form-control">
                    <option :value="false">Không</option>
                    <option :value="true">Có</option>
                </select>
            </div>
            <div class="form-group mb-2">
                <label>Ảnh nhóm</label>
                <input type="file" @change="onImageChange" class="form-control" />
                <!-- Luôn hiển thị ảnh cũ nếu chưa chọn ảnh mới -->
                <img
                    v-if="previewImage"
                    :src="previewImage"
                    alt="preview"
                    style="max-width:100px;margin-top:10px;"
                />
                <img
                    v-else-if="props.group.cover_photo_url"
                    :src="`/images/client/group/thumbnail/${props.group.cover_photo_url}`"
                    alt="old"
                    style="max-width:100px;margin-top:10px;"
                />
            </div>
            <button class="btn btn-primary" type="submit" :disabled="form.processing">Lưu thay đổi</button>
        </form>
    </div>
</GroupDetail>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import GroupDetail from './../GroupDetail.vue';
const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    user_auth: {
        type: Object,
        required: true
    },
    isMember: {
        type: Boolean,
        required: true
    },
    isAdmin: {
        type: Boolean,
        required: true
    }
});

const form = useForm({
    name: props.group.name,
    description: props.group.description,
    privacy_setting: Boolean(props.group.privacy_setting),
    post_approval_required: Boolean(props.group.post_approval_required),
    cover_photo_url: null
});

const previewImage = ref(
    props.group.cover_photo_url
        ? `/images/client/group/thumbnail/${props.group.cover_photo_url}`
        : ''
);

const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.cover_photo_url = file;
        previewImage.value = URL.createObjectURL(file);
    } else {
        // Nếu bỏ chọn file, hiển thị lại ảnh cũ
        previewImage.value = props.group.cover_photo_url
            ? `/images/client/group/thumbnail/${props.group.cover_photo_url}`
            : '';
    }
};

const submit = () => {
    form.post(`/groups/${props.group.id}/update`, {
        forceFormData: true
    });
};
</script>

