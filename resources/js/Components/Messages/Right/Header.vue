<template>
    <div class="col-md-12">
        <div class="message-header d-flex justify-content-between align-items-center">
            <div class="wrap d-flex align-items-center">
                <span class="contact-status online"></span>
                <img :src="getOtherUserAvatar" alt="Conversation user" />
                <div class="meta">
                    <p class="name">{{ getOtherUserName }}</p>
                    <p class="preview">Active now</p>
                </div>
            </div>
            <!-- Dấu ba chấm -->
            <div class="dropdown">
                <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bx bx-dots-horizontal-rounded" style="font-size: 24px;"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li>
                        <button class="dropdown-item text-danger" @click="deleteConversation">
                            <i class="bx bx-trash me-2"></i> Xóa cuộc trò chuyện
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversation: {
        type: Object,
        required: false,
        default: () => ({
            members: []
        })
    }
});

const emit = defineEmits(['conversation-deleted']);

const getOtherUserAvatar = computed(() => {
    if (!props.conversation) return '/images/web/users/avatar.jpg';
    if (props.conversation.conversation_type === 'group') {
        return props.conversation.image
           ? `/images/client/group/conversation/${props.conversation.image}`
            : '/images/web/groups/group.webp';
    }
    // Cá nhân
    const otherUser = props.conversation.members[0];
    return otherUser?.avatar
        ? `/images/client/avatar/${otherUser.avatar}`
        : '/images/web/users/avatar.jpg';
});

const getOtherUserName = computed(() => {
    if (!props.conversation) return 'Unknown';
    if (props.conversation.conversation_type === 'group') {
        return props.conversation.name;
    }
    return props.conversation.members[0]?.name || 'Unknown User';
});

const deleteConversation = async () => {
    if (!confirm('Bạn có chắc chắn muốn xóa cuộc trò chuyện này?')) return;

    try {
        const response = await axios.delete(`/conversations/${props.conversation.id}`);
        if (response.data.success) {
            alert(response.data.message);
            emit('conversation-deleted'); // Phát sự kiện để thông báo cuộc trò chuyện đã bị xóa
        } else {
            alert('Không thể xóa cuộc trò chuyện: ' + response.data.message);
        }
    } catch (error) {
        console.error('Error deleting conversation:', error);
        alert('Có lỗi xảy ra khi xóa cuộc trò chuyện.');
    }
};
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
