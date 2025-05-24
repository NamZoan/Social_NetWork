<template>
    <li class="nav-item s-nav dropdown message-drop-li">
        <a href="#" class="nav-link nav-links message-drop drop-w-tooltip" data-toggle="dropdown"
            data-placement="bottom" data-title="Messages" role="button" aria-haspopup="true" aria-expanded="false">
            <img :src="'/images/web/icons/navbar/message.png'" class="message-dropdown" alt="navbar icon">
            <span v-if="messageNotifications.length > 0" class="badge badge-pill badge-primary">
                {{ messageNotifications.length }}
            </span>
        </a>

        <ul class="dropdown-menu notify-drop dropdown-menu-right nav-drop shadow-sm">
            <div class="notify-drop-title">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Tin nhắn mới</div>
                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                        <a v-if="messageNotifications.length > 0" href="#" class="notify-right-icon" @click.prevent="markAllAsRead">
                            Đánh dấu đã đọc
                        </a>
                    </div>
                </div>
            </div>

            <div class="drop-content">
                <template v-if="messageNotifications.length > 0">
                    <li v-for="noti in messageNotifications" :key="noti.id" class="notification-item">
                        <div class="notification-content" @click="handleNotificationClick(noti)">
                            <div class="notify-img">
                                <img :src="noti.group_avatar ? `/images/client/group/conversation/${noti.group_avatar}` : 
                                    (noti.sender_avatar ? `/images/client/avatar/${noti.sender_avatar}` : '/images/web/users/avatar.jpg')"
                                    :class="{'group-avatar': noti.conversation_type === 'group', 'user-avatar': noti.conversation_type === 'individual'}"
                                    alt="avatar">
                            </div>
                            <div class="notification-details">
                                <template v-if="noti.conversation_type === 'group'">
                                    <div class="group-info">
                                        <div class="d-flex align-items-center">
                                            <i class='bx bx-group text-primary mr-1'></i>
                                            <span class="group-name">{{ noti.group_name }}</span>
                                        </div>
                                        <div class="message-preview">
                                            <span class="sender-name">{{ noti.sender_name }}</span>
                                            <span class="message-text">{{ noti.message }}</span>
                                        </div>
                                    </div>
                                </template>
                                <template v-else>
                                    <div class="individual-info">
                                        <div class="sender-name">{{ noti.sender_name }}</div>
                                        <div class="message-text">{{ noti.message }}</div>
                                    </div>
                                </template>
                                <div class="notification-meta">
                                    <small class="text-muted">{{ formatTime(noti.created_at) }}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
                <li v-else class="text-center text-muted py-2">
                    Không có tin nhắn mới
                </li>
            </div>
            <div class="notify-drop-footer text-center">
                <a href="/messages">Xem tất cả</a>
            </div>
        </ul>
    </li>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import axios from 'axios';

const messageNotifications = ref([]);
const echoInstance = ref(null);

const fetchMessageNotifications = async () => {
    try {
        const response = await axios.get('/message-notifications');
        if (response.data.error) {
            console.error('Server error:', response.data.error);
            return;
        }
        messageNotifications.value = response.data.notifications || [];
    } catch (error) {
        console.error('Error fetching notifications:', error);
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

const handleNotificationClick = async (notification) => {
    try {
        const response = await axios.post('/notifications/mark-as-read', {
            notification_id: notification.id
        });

        if (response.data.success) {
            await fetchMessageNotifications();
            if (notification.action_url && notification.action_url !== '#') {
                router.visit(notification.action_url);
            }
        }
    } catch (error) {
        console.error('Error handling notification click:', error);
    }
};

const markAllAsRead = async () => {
    try {
        const response = await axios.post('/notifications/mark-as-read');
        if (response.data.success) {
            await fetchMessageNotifications();
        }
    } catch (error) {
        console.error('Error marking all as read:', error);
    }
};

const setupEchoListener = () => {
    if (!window.Echo || !window.userId) {
        console.warn('Echo or userId not available');
        return;
    }

    echoInstance.value = window.Echo.private(`user.${window.userId}`)
        .listen('.message.sent', (e) => {
            console.log('Received message event:', e);
            
            if (!e.notification || !e.message) {
                console.warn('Invalid event data:', e);
                return;
            }

            const notificationData = {
                id: e.notification.id,
                type: 'message',
                message: e.message.content,
                sender_name: e.message.sender?.name || 'Unknown',
                sender_avatar: e.message.sender?.avatar || null,
                action_url: e.conversation ? `/messages?conversation=${e.conversation.id}` : '#',
                created_at: new Date().toISOString(),
                conversation_type: e.conversation?.conversation_type || 'individual',
                group_name: e.conversation?.name || null,
                group_avatar: e.conversation?.image || null,
                is_read: false
            };

            console.log('Adding notification:', notificationData);
            messageNotifications.value.unshift(notificationData);
        });
};

onMounted(() => {
    fetchMessageNotifications();
    setupEchoListener();
});

onUnmounted(() => {
    if (echoInstance.value) {
        echoInstance.value.stopListening('.message.sent');
    }
});
</script>

<style scoped>
.notification-item {
    padding: 10px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background-color 0.2s;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-content {
    display: flex;
    gap: 10px;
}

.notify-img {
    flex-shrink: 0;
}

.notify-img img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.group-avatar {
    border: 2px solid #007bff;
}

.user-avatar {
    border: 2px solid #6c757d;
}

.notification-details {
    flex-grow: 1;
    min-width: 0;
}

.group-info, .individual-info {
    margin-bottom: 4px;
}

.group-name {
    font-weight: 500;
    color: #007bff;
}

.sender-name {
    font-weight: 500;
    color: #2c3e50;
}

.message-preview {
    display: flex;
    gap: 4px;
    color: #666;
    font-size: 0.9em;
}

.message-text {
    color: #666;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notification-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
}

.notify-drop-footer {
    padding: 10px;
    border-top: 1px solid #eee;
}

.notify-drop-footer a {
    color: #007bff;
    text-decoration: none;
}

.notify-drop-footer a:hover {
    color: #0056b3;
    text-decoration: underline;
}


.notify-right-icon {
    color: #007bff;
    text-decoration: none;
    font-size: 0.9em;
}

.notify-right-icon:hover {
    color: #0056b3;
    text-decoration: underline;
}
</style>