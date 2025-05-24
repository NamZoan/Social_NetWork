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
                            <img :src="noti.sender_avatar ? `/images/client/avatar/${noti.sender_avatar}` : '/images/web/users/avatar.jpg'"
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
        notifications.value = response.data.notifications;
    } catch (error) {
        console.error('Error fetching notifications:', error);
    }
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
.reaction-icon {
    width: 20px;
    height: 20px;
    vertical-align: middle;
    margin: 0 2px;
}

.notification-type {
    color: #65676b;
    margin: 0 4px;
}

.notify-right-icon {
    color: #1877f2;
    text-decoration: none;
}

.notify-right-icon:hover {
    color: #166fe5;
    text-decoration: none;
}

.time {
    margin: 4px 0 0;
    font-size: 0.8em;
    color: #65676b;
}
</style>