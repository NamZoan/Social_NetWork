<template>
    <li class="nav-item s-nav dropdown notification">
        <a href="#" class="nav-link nav-links rm-drop-mobile drop-w-tooltip" data-toggle="dropdown"
            data-placement="bottom" data-title="Notifications" role="button" aria-haspopup="true" aria-expanded="false">
            <img :src="'/images/web/icons/navbar/notification.png'" class="notification-bell" alt="navbar icon">
            <span v-if="notifications.length > 0" class="badge badge-pill badge-primary">{{ notifications.length
                }}</span>
        </a>
        <ul class="dropdown-menu notify-drop dropdown-menu-right nav-drop shadow-sm">
            <div class="notify-drop-title">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Thông Báo </div>
                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                        <a href="#" class="notify-right-icon" @click.prevent="markAllAsRead">
                            Đánh dấu đã đọc
                        </a>
                    </div>
                </div>
            </div>
            <div class="drop-content">
                <li v-for="noti in notifications" :key="noti.id">
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <div class="notify-img">
                            <img :src="getAvatarUrl(noti.sender_avatar)"
                                alt="notification user image">
                        </div>
                    </div>
                    <div class="col-md-10 col-sm-10 col-xs-10">
                        <template v-if="noti.type === 'reaction'">
                            <a :href="noti.action_url" class="notification-user">{{ noti.sender_name }}</a>
                            <span class="notification-type">đã thả</span>
                            <img :src="`/images/web/icons/reactions/reactions_${noti.reaction_type}.png`"
                                class="reaction-icon" alt="reaction">
                            <span class="notification-type">vào bài viết của bạn</span>
                        </template>
                        <template v-else-if="noti.type === 'comment'">
                            <a :href="noti.action_url" class="notification-user">{{ noti.sender_name }}</a>
                            <span class="notification-type">đã bình luận vào bài viết của bạn: {{ noti.comment_content
                                }}</span>
                        </template>
                        <a :href="noti.action_url" class="notify-right-icon">
                            <i class='bx bx-radio-circle-marked'></i>
                        </a>
                        <p class="time">
                            <span class="badge badge-pill badge-primary">
                                <i :class="getNotificationIcon(noti.type)"></i>
                            </span>
                            {{ formatTime(noti.created_at) }}
                        </p>
                    </div>
                </li>
                <li v-if="!notifications.length" class="text-center text-muted py-2">
                    Không có thông báo nào
                </li>
            </div>
            <div class="notify-drop-footer text-center">
                <a href="#">Xem thêm</a>
            </div>
        </ul>
    </li>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const notifications = ref([]);
const echoInstance = ref(null);

const fetchNotifications = async () => {
    try {
        const response = await axios.get('/notifications');
        notifications.value = response.data.notifications || [];
    } catch (error) {
        console.error('Error fetching notifications:', error);
        notifications.value = [];
    }
};

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

const markAllAsRead = async () => {
    try {
        await axios.post('/notifications/mark-all-read');
        await fetchNotifications();
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
};

const getNotificationIcon = (type) => {
    switch (type) {
        case 'reaction':
            return 'bx bxs-like';
        case 'comment':
            return 'bx bxs-comment';
        default:
            return 'bx bx-bell';
    }
};

const formatTime = (time) => {
    const date = new Date(time);
    const now = new Date();
    const diff = now - date;

    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (minutes < 60) return `${minutes}m`;
    if (hours < 24) return `${hours}h`;
    return `${days}d`;
};

const setupEchoListener = () => {
    if (!window.Echo || !window.userId) return;

    echoInstance.value = window.Echo.private(`user.${window.userId}`)
        .listen('.reaction.added', (e) => {
            notifications.value.unshift(e);
        })
        .listen('.comment.added', (e) => {
            notifications.value.unshift(e);
        });
};

onMounted(() => {
    fetchNotifications();
    setupEchoListener();
});

onUnmounted(() => {
    if (echoInstance.value) {
        echoInstance.value.stopListening('.reaction.added');
        echoInstance.value.stopListening('.comment.added');
    }
});
</script>

<style scoped>
/* Notification Dropdown Visual Improvements */
.notification .nav-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    transition: all 0.3s ease;
}

.notification-bell {
    display: block;
    width: 24px;
    height: 24px;
}

.notification .badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
    border: 2px solid white;
    font-size: 10px;
    min-width: 18px;
    height: 18px;
    padding: 2px 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: bellRing 2s infinite;
}

@keyframes bellRing {
    0%, 100% { transform: rotate(0deg); }
    10%, 30% { transform: rotate(-10deg); }
    20%, 40% { transform: rotate(10deg); }
    50% { transform: rotate(0deg); }
}

.notify-drop {
    min-width: 380px;
    max-width: 380px;
    border-radius: 12px;
    border: none;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    animation: dropdownSlide 0.3s ease;
}

@keyframes dropdownSlide {
    from {
        opacity: 0;
        transform: translateY(-15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.notify-drop-title {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
    font-weight: 600;
}

.notify-drop-title .fs-8 {
    font-size: 16px;
    font-weight: 600;
}

.notify-right-icon {
    color: white !important;
    font-size: 13px;
    background: rgba(255, 255, 255, 0.2);
    padding: 4px 10px;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.notify-right-icon:hover {
    background: rgba(255, 255, 255, 0.3);
    text-decoration: none !important;
    transform: scale(1.05);
    color: white !important;
}

.drop-content {
    max-height: 400px;
    overflow-y: auto;
    padding: 0;
}

.drop-content::-webkit-scrollbar {
    width: 6px;
}

.drop-content::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.drop-content::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.drop-content::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.drop-content > li {
    list-style: none;
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    transition: all 0.3s ease;
    display: flex;
    gap: 12px;
}

.drop-content > li:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-left: 3px solid #667eea;
    padding-left: 12px;
}

.drop-content > li:last-child {
    border-bottom: none;
}

.notify-img {
    position: relative;
}

.notify-img img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #f0f0f0;
    transition: all 0.3s ease;
}

.drop-content > li:hover .notify-img img {
    border-color: #667eea;
    transform: scale(1.05);
}

.notification-user {
    font-weight: 600;
    color: #2c3e50;
    text-decoration: none;
    transition: color 0.3s ease;
}

.notification-user:hover {
    color: #667eea;
    text-decoration: none;
}

.notification-type {
    color: #666;
    margin: 0 2px;
    font-size: 14px;
}

.reaction-icon {
    width: 20px;
    height: 20px;
    vertical-align: middle;
    margin: 0 4px;
    transition: transform 0.3s ease;
}

.drop-content > li:hover .reaction-icon {
    transform: scale(1.2);
}

.time {
    margin: 6px 0 0;
    font-size: 12px;
    color: #999;
    display: flex;
    align-items: center;
    gap: 6px;
}

.time .badge {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    padding: 4px 8px;
    font-size: 11px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.notify-drop-footer {
    padding: 12px 20px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    border-radius: 0 0 12px 12px;
}

.notify-drop-footer a {
    color: #667eea;
    font-weight: 600;
    font-size: 14px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}

.notify-drop-footer a:hover {
    color: #3b82f6;
    gap: 10px;
    text-decoration: none;
}

.notify-drop-footer a::after {
    content: '→';
    font-size: 16px;
    transition: transform 0.3s ease;
}

.notify-drop-footer a:hover::after {
    transform: translateX(4px);
}

/* Empty State */
.drop-content .text-center {
    padding: 40px 20px;
    color: #999;
}

/* Responsive */
@media (max-width: 576px) {
    .notify-drop {
        min-width: 320px;
        max-width: 320px;
    }
}
</style>
