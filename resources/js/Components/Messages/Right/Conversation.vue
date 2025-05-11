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
                        <img :src="getAvatarUrl(message.sender)" :alt="message.sender.name" />
                        <p>{{ message.content }}</p>
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

const messages = ref([]);
const isLoading = ref(false);
const messageContainer = ref(null);

const emit = defineEmits(['message-sent']);

const loadMessages = async () => {
    if (!props.conversationId) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(`/messages/${props.conversationId}`);
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error('Error loading messages:', error);
    } finally {
        isLoading.value = false;
    }
};

const scrollToBottom = () => {
    if (messageContainer.value) {
        messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
};

const handleNewMessage = (message) => {
    console.log('New message received in handleNewMessage:', message); // Debug log
    if (message && message.conversation_id === props.conversationId) {
        messages.value.push(message); // Thêm tin nhắn vào cuối danh sách
        scrollToBottom(); // Cuộn xuống cuối danh sách
    } else {
        console.error('Invalid message format or conversation ID mismatch:', message);
    }
};

let channel = null;

onMounted(() => {
    loadMessages();

    if (window.Echo) {
        console.log('Setting up Echo listener for conversation:', props.conversationId); // Debug log
        channel = window.Echo.private(`conversation.${props.conversationId}`)
            .listen('.message.sent', (e) => {
                console.log('Message event received via WebSocket:', e); // Debug log
                if (e.message) {
                    handleNewMessage(e.message);
                }
            });

        // Lắng nghe kênh cá nhân của người gửi
        window.Echo.private(`user.${props.currentUser.id}`)
            .listen('.message.sent', (e) => {
                console.log('Message event received for sender:', e); // Debug log
                if (e.message) {
                    handleNewMessage(e.message);
                }
            });
    }

    // Lắng nghe sự kiện 'message-sent' từ Footer.vue
    emit('message-sent', (message) => {
        console.log('Message sent event received:', message); // Debug log
        handleNewMessage(message);
    });
});

onUnmounted(() => {
    if (channel) {
        channel.stopListening('.message.sent');
    }
});

// Cuối file <script setup> trong Conversation.vue
defineExpose({
    handleNewMessage
});




const getAvatarUrl = (sender) => {
    if (!sender) return '/images/web/users/avatar.jpg';
    return sender.avatar 
        ? `/images/client/avatar/${sender.avatar}` 
        : '/images/web/users/avatar.jpg';
};
</script>

<style scoped>
.messages {
    height: calc(100vh - 200px);
    overflow-y: auto;
    padding: 20px;
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

.message p {
    background: #f5f5f5;
    padding: 10px 15px;
    border-radius: 15px;
    max-width: 70%;
    margin: 0;
}

.message-reply {
    flex-direction: row-reverse;
}

.message-reply img {
    margin-right: 0;
    margin-left: 10px;
}

.message-reply p {
    background: #007bff;
    color: white;
}
</style>
