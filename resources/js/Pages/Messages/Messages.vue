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
import { ref } from 'vue';
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
    console.log('Message sent event received in Messages.vue:', message);

    if (selectedConversation.value && message.conversation_id === selectedConversation.value.id) {
        // Update the conversation's last message
        const conversationIndex = props.conversations.findIndex(c => c.id === message.conversation_id);
        if (conversationIndex !== -1) {
            props.conversations[conversationIndex].messages = [message, ...(props.conversations[conversationIndex].messages || [])];
        }

        // Forward the message to the conversation component
        if (conversationRef.value?.handleMessageSent) {
            conversationRef.value.handleMessageSent(message);
        }
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
    
    // Reset conversation ref to force re-mount
    conversationRef.value = null;
    
    // Small delay to ensure proper cleanup and mount
    setTimeout(() => {
        conversationRef.value = null;
    }, 0);
};

</script>

<style scoped>
@import '../../../css/messenger.css';
</style>
