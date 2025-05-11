<template>
    <div class="col-md-12">
        <div class="message-header">
            <div class="wrap">
                <span class="contact-status online"></span>
                <img :src="getOtherUserAvatar" alt="Conversation user" />
                <div class="meta">
                    <p class="name">{{ getOtherUserName }}</p>
                    <p class="preview">Active now</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    conversation: {
        type: Object,
        required: true
    }
});

const getOtherUserAvatar = computed(() => {
    if (!props.conversation?.members?.[0]) return '/images/web/users/avatar.jpg';
    return props.conversation.members[0].avatar 
        ? `/images/client/avatar/${props.conversation.members[0].avatar}` 
        : '/images/web/users/avatar.jpg';
});

const getOtherUserName = computed(() => {
    return props.conversation?.members?.[0]?.name || 'Unknown User';
});
</script>

<style scoped>
.message-header {
    padding: 15px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.message-header .wrap {
    display: flex;
    align-items: center;
}

.message-header img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
}

.message-header .meta {
    flex: 1;
}

.message-header .name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.message-header .preview {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.contact-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 10px;
}

.contact-status.online {
    background: #2ecc71;
}
</style>
