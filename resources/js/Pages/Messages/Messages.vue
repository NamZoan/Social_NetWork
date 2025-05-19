<template>
    <App>
        <div class="row message-right-side-content">
            <div class="col-md-12">
                <div id="message-frame">
                    <div class="message-sidepanel">
                        <div class="message-profile">
                            <div class="wrap">
                                <img :src="user.avatar ? `/images/client/avatar/${user.avatar}` : '/images/web/users/avatar.jpg'"
                                    loading="lazy" class="online conv-img" alt="Conversation user" />
                                <p>{{ user.name }}</p>
                            </div>
                        </div>

                        <SearchBar />
                        <NewMessage />
                        <NewGroupMessage />

                        <ContactItem :conversations="conversations" :selected-conversation-id="selectedConversation?.id"
                            @select-conversation="selectConversation" />

                    </div>
                    <div class="content">
                        <div class="row">
                            <Header :conversation="selectedConversation" />
                            <Conversation v-if="selectedConversation" ref="conversationRef"
                                :conversation-id="selectedConversation.id" :current-user="user"
                                @message-sent="handleMessageSent" />
                            <Footer v-if="selectedConversation" :conversation-id="selectedConversation.id"
                                @message-sent="handleMessageSent" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import ContactItem from "../../Components/Messages/Left/ContactItem.vue";
import App from "../../Layouts/App.vue";
import SearchBar from "../../Components/Messages/Left/SearchBar.vue";
import Conversation from "../../Components/Messages/Right/Conversation.vue";
import Footer from "../../Components/Messages/Right/Footer.vue";
import Header from "../../Components/Messages/Right/Header.vue";
import NewMessage from '../../Components/Messages/Left/NewMessage.vue';
import NewGroupMessage from '../../Components/Messages/Left/NewGroupMessage.vue';

const props = defineProps({
    conversations: {
        type: Array,
        required: true
    },
    user: {
        type: Object,
        required: true
    }
});

const selectedConversation = ref(null);
const conversationRef = ref(null);

const handleMessageSent = (message) => {
    console.log('Parent handling message:', message);

    if (selectedConversation.value && message.conversation_id === selectedConversation.value.id) {
        // Update the conversation's last message
        const conversationIndex = props.conversations.findIndex(c => c.id === message.conversation_id);
        if (conversationIndex !== -1) {
            if (!props.conversations[conversationIndex].messages) {
                props.conversations[conversationIndex].messages = [];
            }
            // Kiểm tra tin nhắn đã tồn tại chưa
            const messageExists = props.conversations[conversationIndex].messages.some(m => m.id === message.id);
            if (!messageExists) {
                props.conversations[conversationIndex].messages.unshift(message);
                console.log('Updated conversation messages');
            }
        }

        // Forward the message to the conversation component
        nextTick(() => {
            if (conversationRef.value?.handleMessageSent) {
                console.log('Forwarding message to conversation component');
                conversationRef.value.handleMessageSent(message);
            } else {
                console.log('Conversation ref not ready, retrying...');
                // Retry up to 3 times with increasing delays
                let retryCount = 0;
                const maxRetries = 3;
                const retry = () => {
                    if (retryCount < maxRetries) {
                        setTimeout(() => {
                            if (conversationRef.value?.handleMessageSent) {
                                console.log('Retry successful, forwarding message');
                                conversationRef.value.handleMessageSent(message);
                            } else {
                                retryCount++;
                                retry();
                            }
                        }, 100 * (retryCount + 1));
                    } else {
                        console.error('Failed to forward message after multiple retries');
                    }
                };
                retry();
            }
        });
    } else {
        console.log('Message not for current conversation');
    }
};

const selectConversation = (conversation) => {
    // Reset conversation state before switching
    if (selectedConversation.value) {
        // Cleanup old conversation if needed
        if (conversationRef.value?.cleanup) {
            conversationRef.value.cleanup();
        }
    }

    // Set new conversation
    selectedConversation.value = conversation;

    // Reset conversation ref
    conversationRef.value = null;

    // Đợi một chút để đảm bảo component được mount lại
    nextTick(() => {
        if (conversationRef.value?.loadMessages) {
            conversationRef.value.loadMessages();
        }
    });
};

const handleConversationDeleted = (conversationId) => {
    conversations.value = conversations.value.filter(c => c.id !== conversationId);
    if (selectedConversation.value && selectedConversation.value.id === conversationId) {
        selectedConversation.value = null;
    }
};

</script>

<style scoped>
@import '../../../css/messenger.css';
</style>
