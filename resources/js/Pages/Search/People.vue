<template>
    <ul class="list-group">
        <li v-for="user in users" :key="user.id" class="list-group-item d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <img :src="getAvatarUrl(user.avatar)" class="rounded-circle me-2" width="40" height="40" />
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
                    v-if="!user.is_friend && !sentRequests.has(user.id)"
                    class="btn btn-primary btn-sm"
                    @click="sendFriendRequest(user.id)"
                    :disabled="loadingStates[user.id]"
                >
                    <span v-if="loadingStates[user.id]" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    {{ loadingStates[user.id] ? 'Đang gửi...' : 'Kết bạn' }}
                </button>
                <span v-else-if="sentRequests.has(user.id)" class="badge bg-info">Đã gửi lời mời</span>
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
import { ref, reactive } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => []
    }
});

const loadingStates = reactive({});
const sentRequests = reactive(new Set());

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

const sendFriendRequest = async (userId) => {
    loadingStates[userId] = true;
    try {
        await axios.post('/send-friend-request', { user_id: userId });
        sentRequests.add(userId);
    } catch (error) {
        const message = error.response?.data?.message || 'Gửi lời mời kết bạn thất bại!';
        alert(message);
    } finally {
        loadingStates[userId] = false;
    }
};
</script>
