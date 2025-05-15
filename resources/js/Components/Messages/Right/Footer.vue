<template>
    <div class="col-md-12">
        <div class="message-footer">
            <div class="wrap">
                <input type="text" v-model="message" @keyup.enter="sendMessage" placeholder="Type your message..." />
                <button @click="sendMessage" :disabled="!message.trim()">
                    <i class='bx bx-send'></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversationId: {
        type: Number,
        required: true
    }
});

const emit = defineEmits(['message-sent']);
const message = ref('');

const sendMessage = async () => {
    if (!message.value.trim()) return;

    try {
        const response = await axios.post('/messages/send', {
            conversation_id: props.conversationId,
            content: message.value,
            message_type: 'text',
        });

        console.log('Response from server:', response.data);

        if (response.data.success && response.data.message) {
            const sentMessage = response.data.message;
            message.value = '';

            emit('message-sent', {
                ...sentMessage,
                conversation_id: props.conversationId,
                sender: response.data.sender || response.data.message.sender
            });
        } else {
            console.error('Invalid response format:', response.data);
        }
    } catch (error) {
        console.error('Error sending message:', error);
        message.value = '';
    }
};
</script>

<style scoped>
.message-footer {
    padding: 15px;
    background: #fff;
    border-top: 1px solid #eee;
}

.message-footer .wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.message-footer input {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
}

.message-footer button {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: #007bff;
    color: white;
    cursor: pointer;
    transition: background-color 0.3s;
}

.message-footer button:hover {
    background: #0056b3;
}

.message-footer button:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.message-footer i {
    font-size: 20px;
}
</style>