<template>
    <div class="message-contacts">
        <ul class="conversations">
            <li v-for="conversation in filteredConversations" 
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
            <li v-if="filteredConversations.length === 0" class="no-results">
                Không tìm thấy cuộc trò chuyện nào
            </li>
        </ul>
    </div>
</template>

<script setup>
import { defineProps, defineEmits, computed } from 'vue';
import { usePage } from "@inertiajs/vue3";

const props = defineProps({
    conversations: {
        type: Array,
        required: true
    },
    selectedConversationId: {
        type: Number,
        default: null
    },
    searchQuery: {
        type: String,
        default: ''
    }
});

const emit = defineEmits(['select-conversation']);
const page = usePage();
const currentUserId = page.props.user?.id || page.props.auth?.user?.id;

const filteredConversations = computed(() => {
    if (!props.searchQuery) {
        return props.conversations;
    }

    const query = props.searchQuery.toLowerCase();
    return props.conversations.filter(conversation => {
        const name = getConversationName(conversation).toLowerCase();
        const lastMessage = getLastMessage(conversation).toLowerCase();
        
        return name.includes(query) || lastMessage.includes(query);
    });
});

const selectConversation = (conversation) => {
    emit('select-conversation', conversation);
};

const getStatusClass = (conversation) => {
    if (conversation.conversation_type === 'group') {
        return 'group';
    }
    return isUserOnline(conversation) ? 'online' : 'offline';
};

const getConversationAvatar = (conversation) => {
    if (conversation.conversation_type === 'group') {
        return conversation.image
            ? `/images/client/group/conversation/${conversation.image}`
            : '/images/web/groups/group.webp';
    } else {
        const otherUser = conversation.members.find(m => m.id !== currentUserId);
        return otherUser?.avatar
            ? `/images/client/avatar/${otherUser.avatar}`
            : '/images/web/users/avatar.jpg';
    }
};

const getConversationName = (conversation) => {
    if (conversation.conversation_type === 'group') {
        return conversation.name;
    } else {
        const otherUser = conversation.members.find(m => m.id !== currentUserId);
        return otherUser?.name || 'Unknown User';
    }
};

const getLastMessage = (conversation) => {
    if (!conversation.messages || conversation.messages.length === 0) {
        return 'Chưa có tin nhắn nào';
    }

    const lastMessage = conversation.messages[conversation.messages.length - 1];
    
    if (conversation.conversation_type === 'group') {
        const sender = conversation.members.find(m => m.id === lastMessage.sender_id);
        const senderName = sender?.id === currentUserId ? 'Bạn' : sender?.name || 'Ai đó';
        return `${senderName}: ${lastMessage.content}`;
    } else {
        return lastMessage.content;
    }
};

const isUserOnline = (conversation) => {
    if (conversation.conversation_type === 'group') {
        return false;
    }
    const otherUser = conversation.members.find(m => m.id !== currentUserId);
    return otherUser?.is_online || false;
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

.contact-status.offline {
    background-color: #dc3545;
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

.no-results {
    padding: 20px;
    text-align: center;
    color: #666;
    font-style: italic;
}
</style>