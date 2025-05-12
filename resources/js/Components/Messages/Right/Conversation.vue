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
const channels = ref([]);

const emit = defineEmits(['message-sent']);

const loadMessages = async () => {
    if (!props.conversationId) return;
    
    isLoading.value = true;
    try {
        const response = await axios.get(`/messages/${props.conversationId}`);
        messages.value = response.data.sort((a, b) => 
            new Date(a.sent_at) - new Date(b.sent_at)
        );
        scrollToBottom();
        console.log('Messages loaded:', messages.value.length);
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
    console.log('New message received in Conversation:', message);
    
    if (message.conversation_id === props.conversationId) {
        const messageExists = messages.value.some(m => m.id === message.id);
        if (!messageExists) {
            console.log('Adding new message to conversation:', message);
            messages.value.push(message);
            scrollToBottom();
        }
    }
};

const handleMessageSent = (message) => {
    console.log('Message sent event received in Conversation:', message);
    handleNewMessage(message);
};

const setupWebSocket = () => {
    if (!window.Echo) {
        console.error('Echo is not initialized');
        return;
    }

    console.log('Setting up WebSocket listeners for conversation:', props.conversationId);
    
    try {
        // Cleanup existing channels
        cleanup();

        // Setup conversation channel
        const conversationChannel = window.Echo.private(`conversation.${props.conversationId}`);
        conversationChannel.listen('.message.sent', (data) => {
            console.log('Received message on conversation channel:', data);
            if (data.message) {
                handleNewMessage(data.message);
            }
        });

        // Setup user channel
        const userChannel = window.Echo.private(`user.${props.currentUser.id}`);
        userChannel.listen('.message.sent', (data) => {
            console.log('Received message on user channel:', data);
            if (data.message) {
                handleNewMessage(data.message);
            }
        });

        // Store channels for cleanup
        channels.value = [conversationChannel, userChannel];
        console.log('WebSocket listeners setup completed');
    } catch (error) {
        console.error('Error setting up WebSocket listeners:', error);
    }
};

const cleanup = () => {
    try {
        if (channels.value.length > 0) {
            channels.value.forEach(channel => {
                if (channel) {
                    channel.stopListening('.message.sent');
                }
            });
            channels.value = [];
        }
        console.log('WebSocket cleanup completed');
    } catch (error) {
        console.error('Error during WebSocket cleanup:', error);
    }
};

onMounted(() => {
    loadMessages();
    setupWebSocket();
});

onUnmounted(() => {
    cleanup();
});

// Watch for conversation changes
watch(() => props.conversationId, (newId, oldId) => {
    if (newId !== oldId) {
        console.log('Conversation changed from', oldId, 'to', newId);
        cleanup();
        loadMessages();
        setupWebSocket();
    }
});

// Expose functions
defineExpose({
    loadMessages,
    handleMessageSent,
    cleanup
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
