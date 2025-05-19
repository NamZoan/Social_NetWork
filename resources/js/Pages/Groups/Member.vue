<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div class="container mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="mb-0">Thành viên nhóm</h4>
                <span class="badge bg-primary fs-6">{{ members.length }} thành viên</span>
            </div>
            <ul class="list-group">
                <li v-for="member in members" :key="member.id" class="list-group-item d-flex align-items-center py-3">
                    <img
                        :src="member.avatar ? `/images/client/avatar/${member.avatar}` : '/images/web/users/avatar.jpg'"
                        alt="avatar"
                        class="rounded-circle me-3"
                        style="width:48px;height:48px;object-fit:cover;border:2px solid #e3e3e3;"
                    >
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center">
                            <span class="fw-bold">{{ member.name }}</span>
                            <span v-if="member.pivot.role === 'admin'" class="badge bg-info ms-2">Admin</span>
                        </div>
                        <small class="text-muted">{{ member.email }}</small>
                    </div>
                    <button
                        v-if="isAdmin && member.id !== user_auth.id"
                        class="btn btn-outline-danger btn-sm ms-3"
                        title="Xóa thành viên"
                        @click="removeMember(member.id)"
                    >
                        <i class="bx bx-user-x"></i>
                    </button>
                </li>
            </ul>
        </div>
    </GroupDetail>
</template>

<script setup>
import GroupDetail from './GroupDetail.vue';
import { defineProps } from 'vue';
import axios from 'axios';

const props = defineProps({
    group: { type: Object, required: true },
    user_auth: { type: Object, required: true },
    isMember: { type: Boolean, required: true },
    isAdmin: { type: Boolean, required: true },
    members: { type: Array, required: true }
});

const removeMember = async (memberId) => {
    if (!confirm('Bạn có chắc muốn xóa thành viên này?')) return;
    try {
        await axios.delete(`/groups/${props.group.id}/members/${memberId}`);
        window.location.reload();
    } catch (e) {
        alert('Xóa thành viên thất bại!');
    }
};
</script>

<style scoped>
.fw-bold {
    font-weight: 600;
}
.list-group-item {
    transition: background 0.2s;
}
.list-group-item:hover {
    background: #f8f9fa;
}
</style>
