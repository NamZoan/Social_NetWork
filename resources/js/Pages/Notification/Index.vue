<template>
    <App>
        <div class="row newsfeed-right-side-content mt-3">
            <Left />
            <div class="col-md-9 second-section" id="page-content-wrapper">
                <div class="card notifications-card shadow-sm">
                    <div class="d-flex justify-content-between align-items-center mb-3 header-row">
                        <div>
                            <p class="eyebrow">Trung tâm thông báo</p>
                            <h1 class="title mb-0">Thông báo</h1>
                            <p class="subtitle mb-0" v-if="notifications.length">
                                {{ unreadCount }} chưa đọc · {{ notifications.length }} tổng số
                            </p>
                        </div>
                        <button class="btn btn-primary btn-soft" @click="markAllAsRead" :disabled="isLoading">
                            <i class="bx bx-check-double mr-1"></i>
                            {{ isLoading ? 'Đang xử lý...' : 'Đánh dấu đã đọc' }}
                        </button>
                    </div>

                    <div class="filter-bar d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <span class="status-dot bg-success"></span>
                            <span class="text-muted small">Nhấp để mở và đánh dấu đọc</span>
                        </div>
                        <span class="badge badge-light text-muted">
                            Cập nhật lúc {{ formatTime(new Date().toISOString()) }}
                        </span>
                    </div>

                    <!-- Loading state -->
                    <div v-if="isLoading" class="text-center py-4">
                        <div class="loader-circle mb-2"></div>
                        <p class="text-muted mb-0">Đang tải thông báo...</p>
                    </div>

                    <!-- Notifications list -->
                    <div v-else class="notifications-list">
                        <div v-if="notifications.length == 0" class="text-center py-4 text-muted empty-state">
                            <i class="bx bx-bell-off display-4 d-block mb-2"></i>
                            <p class="mb-0">Không có thông báo nào</p>
                        </div>

                        <template v-else>
                            <div v-for="notification in notifications" :key="notification.id" class="notification-item"
                                :class="{ 'unread': !notification.is_read }" @click="markAsRead(notification.id)">
                                <div class="d-flex align-items-start p-3">
                                    <div class="avatar-wrapper mr-3">
                                        <img :src="getAvatarUrl(notification.sender_avatar)"
                                            class="notification-avatar" alt="Avatar">
                                        <span v-if="!notification.is_read" class="status-dot"></span>
                                    </div>

                                    <div class="notification-content flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div class="d-flex align-items-center gap-2">
                                                <strong>{{ notification.sender_name }}</strong>
                                                <span class="pill" :class="typeClass(notification.type)">
                                                    {{ typeLabel(notification.type) }}
                                                </span>
                                            </div>
                                            <small class="text-muted">{{ formatTime(notification.created_at) }}</small>
                                        </div>
                                        <p class="mb-1">
                                            {{ notificationMessage(notification) }}
                                        </p>
                                    </div>

                                    <button class="btn btn-link text-muted fs-5 p-0 ml-3" title="Đánh dấu đã đọc"
                                        @click.stop="markAsRead(notification.id)">
                                        <i class="bx bx-check"></i>
                                    </button>
                                </div>
                            </div>
                        </template>

                        <!-- Pagination -->
                        <div class="mt-4 flex justify-center gap-2 pagination-bar">
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
        </div>
    </App>
</template>

<script setup>
import App from "../../Layouts/App.vue";
import Left from "../../Components/Left.vue";
import { computed, ref } from 'vue';
import axios from 'axios';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    notifications: {
        type: Object,
        required: true
    },
});

const notifications = ref(props.notifications.data);
const pagination = props.notifications;
const isLoading = ref(false);

const unreadCount = computed(() => notifications.value.filter((n) => !n.is_read).length);

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

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

const notificationMessage = (notification) => {
    if (notification.type === 'comment') {
        return `Đã bình luận: "${notification.comment_content || ''}"`;
    }
    if (notification.type === 'reaction') {
        return `Đã bày tỏ cảm xúc ${notification.reaction_type || ''} với bài viết của bạn`;
    }
    return notification.message || 'Bạn có thông báo mới';
};

const typeLabel = (type) => {
    const map = {
        comment: 'Bình luận',
        reaction: 'Cảm xúc',
    };
    return map[type] || 'Khác';
};

const typeClass = (type) => {
    const map = {
        comment: 'pill-blue',
        reaction: 'pill-green',
    };
    return map[type] || 'pill-gray';
};
</script>

<style scoped>
.notifications-card {
    background: linear-gradient(180deg, #f9fbff 0%, #ffffff 30%);
    border: 1px solid #e7ebf3;
    border-radius: 14px;
    padding: 20px;
}

.title {
    font-size: 28px;
    font-weight: 700;
    color: #0f172a;
}

.eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 12px;
    color: #64748b;
    margin-bottom: 6px;
}

.subtitle {
    color: #64748b;
    font-size: 14px;
}

.btn-soft {
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(24, 119, 242, 0.15);
    padding: 10px 16px;
    font-weight: 600;
}

.filter-bar {
    padding: 10px 14px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
}

.notifications-list {
    max-height: 800px;
    overflow-y: auto;
}

.notification-item {
    border: 1px solid #ecf0f5;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 6px 20px rgba(15, 23, 42, 0.04);
    cursor: pointer;
    transition: background-color 0.2s, transform 0.2s, box-shadow 0.2s;
    margin-bottom: 12px;
}

.notification-item:hover {
    background-color: #f8fbff;
    transform: translateY(-1px);
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
}

.notification-item.unread {
    background-color: #eef2ff;
    border-color: #dbeafe;
}

.avatar-wrapper {
    position: relative;
}

.notification-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #fff;
    box-shadow: 0 4px 14px rgba(0, 0, 0, 0.08);
}

.notification-content {
    font-size: 0.95rem;
}

.notification-content strong {
    color: #1a1a1a;
}

.notification-content p {
    color: #4b5563;
}

.status-dot {
    position: absolute;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #22c55e;
    border: 2px solid #fff;
    bottom: 0;
    right: -2px;
}

.pill {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.pill-blue {
    background: #e0edff;
    color: #1d4ed8;
}

.pill-green {
    background: #dcfce7;
    color: #15803d;
}

.pill-gray {
    background: #e5e7eb;
    color: #374151;
}

.loader-circle {
    width: 40px;
    height: 40px;
    border: 4px solid #e5e7eb;
    border-top-color: #1877f2;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin: 0 auto;
}

@keyframes spin {
    to {
        transform: rotate(360deg);
    }
}

.empty-state {
    border: 1px dashed #d6d9de;
    border-radius: 12px;
    background: #f8fafc;
}

.pagination-bar .hover\:bg-gray-100:hover {
    background: #e5e7eb;
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
