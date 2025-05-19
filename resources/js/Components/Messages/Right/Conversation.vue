<template>
    <div class="col-md-12">
        <div class="messages">
            <ul class="messages-content" ref="messageContainer">
                <div v-if="isLoading" class="text-center p-3">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </div>
                <template v-else>
                    <li v-for="message in messages" :key="message.id"
                        :class="['message', message.sender_id === currentUser.id ? 'message-reply' : 'message-receive']">
                        <!-- Hiển thị ảnh đại diện với tooltip -->
                        <img :src="getAvatarUrl(message.sender)" :alt="message.sender.name"
                            :title="message.sender.name" />
                        <div class="message-content">
                            <div class="d-flex justify-content-between align-items-center">
                                <button v-if="message.sender_id === currentUser.id"
                                    class="btn btn-link btn-sm text-danger p-0 ms-2" title="Xóa tin nhắn"
                                    @click="deleteMessage(message.id)">
                                    <i class="bx bx-trash"></i>
                                </button>
                                <p>{{ message.content }}</p>

                            </div>
                            <small class="time">{{ formatDateTime(message.sent_at) }}</small>
                            <!-- Nút xóa chỉ hiện với tin nhắn của mình -->

                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversationId: {
        type: Number,
        required: true
    },
    currentUser: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['message-sent']);
const messages = ref([]);
const isLoading = ref(false);
const messageContainer = ref(null);
const channels = ref([]);

// Hàm định dạng ngày giờ
const formatDateTime = (time) => {
    if (!time) return 'Unknown time';
    return new Intl.DateTimeFormat('en-US', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true,
    }).format(new Date(time));
};

// Hàm tải tin nhắn
const loadMessages = async () => {
    if (!props.conversationId) return;

    isLoading.value = true;
    try {
        const response = await axios.get(`/messages/${props.conversationId}`);
        messages.value = response.data.sort((a, b) =>
            new Date(a.sent_at) - new Date(b.sent_at)
        );
        scrollToBottom();
    } catch (error) {
        console.error('Error loading messages:', error);
    } finally {
        isLoading.value = false;
    }
};

// Hàm cuộn xuống cuối danh sách tin nhắn
const scrollToBottom = () => {
    if (messageContainer.value) {
        const lastMessage = messageContainer.value.querySelector('.message:last-child');
        if (lastMessage) {
            lastMessage.scrollIntoView({ behavior: 'smooth' });
        }
    }
};

// Hàm xử lý tin nhắn mới
const handleNewMessage = (message) => {
    if (message.conversation_id === props.conversationId) {
        const messageExists = messages.value.some(m => m.id === message.id);
        if (!messageExists) {
            messages.value.push({
                ...message,
                sender: message.sender || {
                    id: message.sender_id,
                    name: message.sender?.name || 'Unknown',
                    avatar: message.sender?.avatar || null
                }
            });
            scrollToBottom();
        }
    }
};

// Thiết lập WebSocket
const setupWebSocket = () => {
    cleanup();
    try {
        const conversationChannel = window.Echo.private(`conversation.${props.conversationId}`);
        conversationChannel.listen('.message.sent', (e) => {
            if (e.message) handleNewMessage(e.message);
        });

        const userChannel = window.Echo.private(`user.${props.currentUser.id}`);
        userChannel.listen('.message.sent', (e) => {
            if (e.message) handleNewMessage(e.message);
        });

        channels.value = [conversationChannel, userChannel];
    } catch (error) {
        console.error('Error setting up WebSocket listeners:', error);
    }
};

// Dọn dẹp WebSocket
const cleanup = () => {
    channels.value.forEach(channel => channel?.stopListening('.message.sent'));
    channels.value = [];
};

// Lấy URL avatar
const getAvatarUrl = (sender) => {
    return sender?.avatar
        ? `/images/client/avatar/${sender.avatar}`
        : '/images/web/users/avatar.jpg';
};

// Hàm xóa tin nhắn
const deleteMessage = async (messageId) => {
    if (!confirm('Bạn có chắc muốn xóa tin nhắn này?')) return;
    try {
        await axios.delete(`/messages/${messageId}`);
        // Xóa tin nhắn khỏi danh sách trên giao diện
        const idx = messages.value.findIndex(m => m.id === messageId);
        if (idx !== -1) messages.value.splice(idx, 1);
    } catch (e) {
        alert('Xóa tin nhắn thất bại!');
    }
};

// Lifecycle hooks
onMounted(() => {
    loadMessages();
    setupWebSocket();
});

onUnmounted(() => {
    cleanup();
});

// Watchers
watch(() => props.conversationId, (newId, oldId) => {
    if (newId !== oldId) {
        cleanup();
        loadMessages();
        setupWebSocket();
    }
});
</script>

<style scoped>
.messages {
    height: calc(100vh - 200px);
    overflow-y: auto;
    padding: 20px;
    background-color: #f9f9f9;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.messages-content {
    list-style: none;
    padding: 0;
    margin: 0;
}

.message {
    display: flex;
    align-items: flex-start;
    margin-bottom: 20px;
}

.message img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 10px;
}



.message-content p {
    background: #cacaca;
    padding: 5px 10px;
    border-radius: 20px;
    margin: 0;
    font-size: 14px;
    line-height: 1.5;
    word-wrap: break-word;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.message-content .time {
    display: block;
    margin-top: 5px;
    font-size: 10px;
    color: #888;
    text-align: right;
}

.message-reply {
    flex-direction: row-reverse;
}

.message-reply img {
    margin-right: 0;
    margin-left: 10px;
}

.message-reply .message-content p {
    background: #007bff;
    color: white;
}
</style>
