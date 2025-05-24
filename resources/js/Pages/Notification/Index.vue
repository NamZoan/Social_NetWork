<template>
    <App>
        <div class="row newsfeed-right-side-content mt-3">
            <Left />
            <div class="col-md-9 second-section" id="page-content-wrapper">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Thông Báo</h1>
                    <button class="btn btn-primary" @click="markAllAsRead" :disabled="isLoading">
                        {{ isLoading ? 'Đang xử lý...' : 'Đánh dấu tất cả đã đọc' }}
                    </button>
                </div>

                <!-- Loading state -->
                <div v-if="isLoading" class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>

                <!-- Notifications list -->
                <div v-else class="notifications-list">
                    <div v-if="notifications.length == 0" class="text-center py-4 text-muted">
                        Không có thông báo nào
                    </div>


                    <div v-else v-for="notification in notifications" :key="notification.id" class="notification-item"
                        :class="{ 'unread': !notification.is_read }" @click="markAsRead(notification.id)">
                        <div class="d-flex align-items-center p-3">
                            <img :src="notification.sender_avatar ? '/images/client/avatar/' + notification.sender_avatar : '/images/web/users/avatar.jpg'"
                                class="notification-avatar mr-3" alt="Avatar">


                            <div class="notification-content flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>{{ notification.sender_name }}</strong>
                                    <small class="text-muted">{{ formatTime(notification.created_at) }}</small>
                                </div>
                                <p class="mb-0">
                                    <template v-if="notification.type === 'comment'">
                                        đã bình luận: "{{ notification.comment_content }}"
                                    </template>
                                    <template v-else-if="notification.type === 'reaction'">
                                        đã bày tỏ cảm xúc {{ notification.reaction_type }} với bài viết của bạn
                                    </template>
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Pagination -->
                    <!-- Pagination -->
                    <div class="mt-6 flex justify-center gap-2">
                        <Link v-if="pagination.prev_page_url" :href="`?page=${pagination.current_page - 1}`"
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                        &laquo; Trước
                        </Link>

                        <span class="px-3 py-1 border rounded bg-gray-200">
                            Trang {{ pagination.current_page }} / {{ pagination.last_page }}
                        </span>

                        <Link v-if="pagination.next_page_url" :href="`?page=${pagination.current_page + 1}`"
                            class="px-3 py-1 border rounded hover:bg-gray-100">
                        Sau &raquo;
                        </Link>
                    </div>

                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import App from "../../Layouts/App.vue";
import Left from "../../Components/Left.vue";
import { ref } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        type: Object,
        required: true
    },
});

console.log('data', props.notifications);

const notifications = ref(props.notifications.data);
const pagination = props.notifications;



const markAsRead = async (notificationId) => {
    try {
        await axios.post('/notifications/mark-as-read', {
            notification_id: notificationId
        });

        // Update local state
        const notification = notifications.value.find(n => n.id === notificationId);
        if (notification) {
            notification.is_read = true;
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
    }
};

const markAllAsRead = async () => {
    try {
        isLoading.value = true;
        await axios.post('/notifications/mark-all-read');

        // Update all notifications to read
        notifications.value.forEach(notification => {
            notification.is_read = true;
        });
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
    } finally {
        isLoading.value = false;
    }
};

const formatTime = (time) => {
    return new Date(time).toLocaleString();
};
</script>

<style scoped>
.notifications-list {
    max-height: 800px;
    overflow-y: auto;
}

.notification-item {
    border-bottom: 1px solid #e5e7eb;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #f3f4f6;
}

.notification-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
}

.notification-content {
    font-size: 0.9rem;
}

.notification-content strong {
    color: #1a1a1a;
}

.notification-content p {
    color: #4b5563;
}

/* Pagination styles */
:deep(.pagination) {
    margin-bottom: 0;
}

:deep(.page-item.active .page-link) {
    background-color: #1877f2;
    border-color: #1877f2;
}

:deep(.page-link) {
    color: #1877f2;
}

:deep(.page-link:hover) {
    color: #1877f2;
    background-color: #e7f3ff;
    border-color: #1877f2;
}

:deep(.page-item.disabled .page-link) {
    color: #6c757d;
}
</style>
