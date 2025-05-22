<template>
    <ul class="list-group">
        <li v-for="user in users" :key="user.id" class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img :src="user.avatar ? `/images/client/avatar/${user.avatar}` : '/images/web/users/avatar.jpg'" class="rounded-circle me-2" width="40" height="40" />
                <div>
                    <Link :href="`/${user.username}`" class="fw-bold">{{ user.name }}</Link>
                    <div class="text-muted">@{{ user.username }}</div>
                    <div v-if="user.mutual_friends_count > 0" class="text-success">
                        {{ user.mutual_friends_count }} bạn chung
                    </div>
                </div>
            </div>
            <div>
                <button
                    v-if="!user.is_friend"
                    class="btn btn-primary btn-sm"
                    @click="sendFriendRequest(user.id)"
                >Kết bạn</button>
                <span v-else class="badge bg-success">Bạn bè</span>
            </div>
        </li>
        <li v-if="!users.length" class="list-group-item">
            <em>Không tìm thấy kết quả phù hợp.</em>
        </li>
    </ul>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({ users: Array });

const sendFriendRequest = async (userId) => {
    try {
        await axios.post('/send-friend-request', { user_id: userId });
        window.location.reload();
    } catch (e) {
        alert('Gửi lời mời kết bạn thất bại!');
    }
};
</script>