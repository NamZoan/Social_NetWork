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
        console.log('Sending message:', message.value); // Debug log
        const response = await axios.post('/messages/send', {
            conversation_id: props.conversationId,
            content: message.value
        });

        console.log('Message sent response:', response.data); // Debug log

        if (response.data.success && response.data.message) {
            // Emit event with the new message
            emit('message-sent', response.data.message);
            console.log('Message sent event emitted:', response.data.message);

            // Clear input after successful send
            message.value = '';
        } else {
            console.error('Invalid response format:', response.data);
            alert('Lỗi: Không thể gửi tin nhắn.');
        }
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Lỗi khi gửi tin nhắn. Vui lòng thử lại.');
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