<template>
    <ul class="list-group">
        <li v-for="group in groups" :key="group.id" class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img :src="group.cover_photo_url ? `/images/client/group/thumbnail/${group.cover_photo_url}` : '/images/web/groups/group.webp'" class="rounded me-2" width="40" height="40" />
                <div>
                    <Link :href="`groups/${group.id}`" class="fw-bold">{{ group.name }}</Link>
                </div>
            </div>
            <div>
                <button
                    v-if="group.is_joined"
                    class="btn btn-outline-danger btn-sm"
                    @click="leaveGroup(group.id)"
                >Rời nhóm</button>
                <button
                    v-else
                    class="btn btn-outline-primary btn-sm"
                    @click="joinGroup(group.id)"
                >Tham gia</button>
            </div>
        </li>
        <li v-if="!groups.length" class="list-group-item">
            <em>Không tìm thấy kết quả phù hợp.</em>
        </li>
    </ul>
</template>

<script setup>
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ groups: Array });

const joinGroup = async (groupId) => {
    try {
        await axios.post(`/groups/${groupId}/join`);
        window.location.reload();
    } catch (e) {
        alert('Tham gia nhóm thất bại!');
    }
};

const leaveGroup = async (groupId) => {
    try {
        await axios.post(`/groups/${groupId}/leave`);
        window.location.reload();
    } catch (e) {
        alert('Rời nhóm thất bại!');
    }
};
</script>