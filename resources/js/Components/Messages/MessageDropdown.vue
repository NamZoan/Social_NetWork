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
                                <img :src="getAvatarUrl(noti.group_avatar, noti.sender_avatar)"
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

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (groupAvatar, userAvatar) => {
    if (groupAvatar) {
        if (groupAvatar.startsWith('http')) return groupAvatar;
        if (groupAvatar.startsWith('/')) return groupAvatar;
        return `/images/client/group/conversation/${groupAvatar}`;
    }
    
    if (!userAvatar) return '/images/web/users/avatar.jpg';
    if (userAvatar.startsWith('http')) return userAvatar;
    if (userAvatar.startsWith('/')) return userAvatar;
    return `/images/client/avatar/${userAvatar}`;
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
/* Message Dropdown Visual Improvements */
.message-drop-li .nav-link {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    padding: 0;
    transition: all 0.3s ease;
}

.message-dropdown {
    display: block;
    width: 24px;
    height: 24px;
}

.message-drop-li .badge {
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
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
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
}

.drop-content {
    max-height: 400px;
    overflow-y: auto;
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

.notification-item {
    padding: 0;
    border-bottom: 1px solid #f0f0f0;
    cursor: pointer;
    transition: all 0.3s ease;
    list-style: none;
}

.notification-item:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
    border-left: 3px solid #667eea;
}

.notification-content {
    display: flex;
    gap: 12px;
    padding: 12px 15px;
    align-items: flex-start;
}

.notify-img {
    flex-shrink: 0;
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

.notification-item:hover .notify-img img {
    border-color: #667eea;
    transform: scale(1.05);
}

.group-avatar {
    border: 2px solid #667eea !important;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.user-avatar {
    border: 2px solid #95a5a6 !important;
}

.notification-details {
    flex-grow: 1;
    min-width: 0;
}

.group-info, .individual-info {
    margin-bottom: 6px;
}

.group-name {
    font-weight: 600;
    color: #667eea;
    font-size: 14px;
}

.sender-name {
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.message-preview {
    display: flex;
    gap: 6px;
    color: #666;
    font-size: 13px;
    margin-top: 4px;
}

.message-text {
    color: #666;
    font-size: 13px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.notification-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 6px;
}

.notification-meta small {
    color: #999;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.notification-meta small::before {
    content: '⏱';
    font-size: 14px;
}

.notify-drop-footer {
    padding: 12px 20px;
    border-top: 1px solid #f0f0f0;
    background: #fafafa;
    border-radius: 0 0 12px 12px;
    text-align: center;
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
