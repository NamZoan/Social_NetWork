<template>
    <div class="message-contacts">
        <ul class="conversations">
            <li v-for="conversation in conversations" 
                :key="conversation.id" 
                class="contact"
                :class="{ 'messenger-user-active': selectedConversationId === conversation.id }"
                @click="selectConversation(conversation)">
                <div class="wrap">
                    <span class="contact-status" :class="getStatusClass(conversation)"></span>
                    <img :src="getConversationAvatar(conversation)" alt="Conversation" />
                    <div class="meta">
                        <p class="name">{{ getConversationName(conversation) }}</p>
                        <p class="preview">{{ getLastMessage(conversation) }}</p>
                    </div>
                </div>
            </li>
        </ul>
    </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    conversations: {
        type: Array,
        required: true
    },
    selectedConversationId: {
        type: Number,
        default: null
    }
});

const emit = defineEmits(['select-conversation']);

const selectConversation = (conversation) => {
    emit('select-conversation', conversation);
};

const getStatusClass = (conversation) => {
    if (conversation.conversation_type === 'group') {
        return 'group';
    }
    return 'online';
};

const getConversationAvatar = (conversation) => {
    if (conversation.conversation_type === 'group') {
        // Nếu là nhóm, lấy avatar của người đầu tiên trong nhóm
        const firstMember = conversation.members[0];
        return firstMember?.avatar 
            ? `/images/client/avatar/${firstMember.avatar}` 
            : '/images/web/users/avatar.jpg';
    } else {
        // Nếu là chat riêng, lấy avatar của người còn lại
        const otherUser = conversation.members[0];
        return otherUser?.avatar 
            ? `/images/client/avatar/${otherUser.avatar}` 
            : '/images/web/users/avatar.jpg';
    }
};

const getConversationName = (conversation) => {
    if (conversation.conversation_type === 'group') {
        // Nếu là nhóm, hiển thị tên nhóm
        return conversation.name;
    } else {
        // Nếu là chat riêng, hiển thị tên người dùng
        return conversation.members[0]?.name || 'Unknown User';
    }
};

const getLastMessage = (conversation) => {
    if (!conversation.messages || conversation.messages.length === 0) {
        return 'Chưa có tin nhắn nào';
    }
    
    const lastMessage = conversation.messages[0];
    const sender = lastMessage.sender?.name || 'Someone';
    
    if (conversation.conversation_type === 'group') {
        return `${sender}: ${lastMessage.content}`;
    }
    
    return lastMessage.content;
};
</script>

<style scoped>
@import '../../../../css/messenger.css';

.contact {
    cursor: pointer;
    transition: background-color 0.3s;
    padding: 10px;
    border-bottom: 1px solid #eee;
}

.contact:hover {
    background-color: #f5f5f5;
}

.messenger-user-active {
    background-color: #e9ecef;
}

.wrap {
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 5px;
}

.contact-status.online {
    background-color: #28a745;
}

.contact-status.group {
    background-color: #007bff;
}

.meta {
    flex: 1;
    min-width: 0;
}

.name {
    font-weight: 600;
    margin: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.preview {
    color: #666;
    margin: 0;
    font-size: 0.9em;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}
</style>