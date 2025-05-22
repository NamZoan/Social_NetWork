<template>
    <li class="nav-item s-nav dropdown message-drop-li">
        <a href="#" class="nav-link nav-links message-drop drop-w-tooltip" data-toggle="dropdown"
            data-placement="bottom" data-title="Messages" role="button" aria-haspopup="true" aria-expanded="false">
            <img :src="'/images/web/icons/navbar/message.png'" class="message-dropdown" alt="navbar icon">
            <span class="badge badge-pill badge-primary">{{ messageNotifications.length }}</span>
        </a>

        <ul class="dropdown-menu notify-drop dropdown-menu-right nav-drop shadow-sm">
            <div class="notify-drop-title">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Messages</div>
                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                        <a href="#" class="notify-right-icon" @click.prevent="markAllAsRead">
                            Mark All as Read
                        </a>
                    </div>
                </div>
            </div>

            <div class="drop-content">
                
                <li v-for="noti in messageNotifications" :key="noti.id">
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <div class="notify-img">
                            <template v-if="noti.conversation_type === 'group'">
                                <img :src="'/images/client/group/conversation/' + noti.group_avatar || '/images/web/groups/default-group.jpg'"
                                    alt="group image" class="group-avatar">
                            </template>
                            <template v-else>
                                <img :src="'/images/client/avatar/' + noti.sender_avatar || '/images/web/users/avatar.jpg'"
                                    alt="sender avatar" class="user-avatar">
                            </template>
                        </div>
                    </div>
                    <div class="col-md-10 col-sm-10 col-xs-10">
                        <template v-if="noti.conversation_type === 'group'">
                            <div class="d-flex align-items-center">
                                <i class='bx bx-group text-primary mr-1'></i>
                                <a :href="noti.action_url" class="notification-user">{{ noti.group_name }}</a>
                            </div>
                            <div class="sender-info">
                                <small class="text-muted">
                                    {{ noti.sender_name }}
                                </small>
                            </div>
                        </template>
                        <template v-else>
                            <div class="d-flex align-items-center">
                                <a :href="noti.action_url" class="notification-user">{{ noti.sender_name+ ' : ' + noti.message }}</a>
                            </div>
                        </template>
                        <a :href="noti.action_url" class="notify-right-icon">
                            <i class='bx bx-radio-circle-marked'></i>
                        </a>
                        <small class="text-muted">{{ new Date(noti.created_at).toLocaleString() }}</small>
                    </div>
                </li>
                <li v-if="!messageNotifications.length" class="text-center text-muted py-2">
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

const messageNotifications = ref([]);
const echoInstance = ref(null);
const retryCount = ref(0);
const maxRetries = 3;

const fetchMessageNotifications = async () => {
    try {
        const res = await fetch('/notifications');
        const data = await res.json();
        messageNotifications.value = data.notifications.filter(n => n.type === 'message');
    } catch (e) {
        console.error('Error fetching notifications:', e);
        messageNotifications.value = [];
    }
};
console.log('messageNotifications', messageNotifications.value);
const markAllAsRead = async () => {
    try {
        await fetch('/notifications/mark-all-read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        });
        await fetchMessageNotifications();
    } catch (error) {
        console.error('Error marking notifications as read:', error);
    }
};

const setupEchoListener = () => {
    if (!window.Echo) {
        console.error('Echo is not defined');
        return;
    }

    if (!window.userId) {
        console.error('User ID is not set');
        return;
    }

    console.log('Setting up Echo listener for user:', window.userId);

    try {
        echoInstance.value = window.Echo.private(`user.${window.userId}`)
            .listen('.message.sent', (e) => {
                console.log('Received message event:', e);

                if (e.notification) {
                    const notificationData = {
                        id: e.notification.id,
                        type: e.notification.type,
                        message: e.notification.data?.message || e.notification.message,
                        sender_name: e.message?.sender?.name || 'Unknown',
                        sender_avatar: e.message?.sender?.avatar || '/images/web/users/avatar.jpg',
                        action_url: e.notification.data?.action_url || e.notification.action_url,
                        created_at: e.notification.created_at,
                        conversation_type: e.message?.conversation_type || 'individual',
                        group_name: e.message?.conversation_name || null,
                        group_avatar: e.message?.conversation_avatar || null
                    };

                    if (notificationData.id && notificationData.message) {
                        console.log('Adding notification:', notificationData);
                        messageNotifications.value.unshift(notificationData);
                    } else {
                        console.warn('Invalid notification data:', notificationData);
                    }
                } else {
                    console.warn('Received event without notification data:', e);
                }
            });
    } catch (error) {
        console.error('Error setting up Echo listener:', error);
    }
};

const retrySetup = () => {
    if (retryCount.value < maxRetries) {
        retryCount.value++;
        console.log(`Retrying Echo setup (${retryCount.value}/${maxRetries})...`);
        setTimeout(setupEchoListener, 1000 * retryCount.value);
    } else {
        console.error('Failed to setup Echo after maximum retries');
    }
};

onMounted(() => {
    fetchMessageNotifications();

    if (!window.userId) {
        console.log('Waiting for user ID...');
        retrySetup();
    } else {
        setupEchoListener();
    }
});

onUnmounted(() => {
    if (echoInstance.value) {
        echoInstance.value.stopListening('.message.sent');
    }
    if (window.Echo) {
        window.Echo.leave(`user.${window.userId}`);
    }
});
</script>

<style scoped>
.group-message {
    background-color: #f8f9fa;
    border-left: 3px solid #007bff;
}

.sender-info {
    margin-top: 2px;
    margin-bottom: 4px;
}

.notification-user {
    font-weight: 600;
    color: #2c3e50;
    text-decoration: none;
}

.notification-user:hover {
    color: #007bff;
    text-decoration: none;
}

.time {
    margin-bottom: 2px;
    color: #6c757d;
}

.text-muted {
    font-size: 0.85rem;
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
</style>