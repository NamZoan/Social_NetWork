<template>
    <div class="conversation-container">
        <div class="messages" ref="messagesContainer" @scroll="handleScroll">
            <!-- Load More Button (appears at top when scrolled up) -->
            <div v-if="canLoadMore && !isLoadingMore" class="load-more-container">
                <button @click="loadMoreMessages" class="btn-load-more">
                    <i class="bx bx-chevron-up"></i>
                    Tải tin nhắn cũ hơn
                </button>
            </div>

            <!-- Loading More State -->
            <div v-if="isLoadingMore" class="loading-more">
                <div class="spinner-border spinner-border-sm" role="status"></div>
                <span class="ms-2">Đang tải...</span>
            </div>

            <div class="messages-content" ref="messageContainer">
                <!-- Initial Loading State -->
                <div v-if="isLoading" class="loading-container">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Đang tải tin nhắn...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="!messages.length" class="empty-state">
                    <i class="bx bx-message-dots empty-icon"></i>
                    <p class="text-muted">Chưa có tin nhắn nào. Hãy bắt đầu cuộc trò chuyện!</p>
                </div>

                <!-- Messages List -->
                <template v-else>
                    <div
                        v-for="(message, index) in messages"
                        :key="`msg-${message.id}`"
                        :class="[
                            'message',
                            message.sender_id === currentUser.id ? 'message-sent' : 'message-received',
                            shouldShowAvatar(index) ? 'show-avatar' : 'hide-avatar'
                        ]"
                    >
                        <!-- Avatar -->
                        <div class="avatar-container" v-if="shouldShowAvatar(index)">
                            <img
                                :src="getAvatarUrl(message.sender)"
                                :alt="message.sender?.name || 'User'"
                                class="avatar"
                                loading="lazy"
                                @error="handleAvatarError"
                            />
                        </div>
                        <div v-else class="avatar-spacer"></div>

                        <div class="message-bubble">
                            <!-- Message Content -->
                            <div class="message-content">
                                <!-- Multiple Images Message -->
                                <div v-if="message.message_type === 'images'" class="message-images-container">
                                    <div
                                        class="images-gallery"
                                        :style="getGalleryLayout(getImagesArray(message.attachment_url).length)"
                                    >
                                        <div
                                            v-for="(imageData, index) in getImagesArray(message.attachment_url)"
                                            :key="index"
                                            class="gallery-image-item"
                                            @click="openImageModal(message, index)"
                                        >
                                            <img
                                                :src="getImageUrl(imageData.url)"
                                                :alt="imageData.caption || 'Image'"
                                                class="gallery-image"
                                                loading="lazy"
                                            />
                                            <div v-if="imageData.caption" class="gallery-caption">
                                                {{ imageData.caption }}
                                            </div>
                                        </div>
                                    </div>
                                    <div v-if="message.content && !message.content.includes('hình ảnh')" class="images-text-caption">
                                        {{ message.content }}
                                    </div>
                                </div>

                                <!-- Single Image Message -->
                                <div v-else-if="message.message_type === 'image'" class="message-image-container">
                                    <img
                                        :src="getImageUrl(message.attachment_url)"
                                        :alt="message.content || 'Image'"
                                        class="message-image"
                                        @click="openImageModal(message)"
                                        @error="handleImageError"
                                        loading="lazy"
                                    />
                                    <div v-if="message.content && message.content !== 'Đã gửi một hình ảnh'" class="image-caption">
                                        {{ message.content }}
                                    </div>
                                </div>

                                <!-- Text Message -->
                                <div v-else class="message-text">
                                    {{ message.content }}
                                </div>
                            </div>

                            <!-- Message Actions -->
                            <div class="message-actions">
                                <small class="message-time">
                                    {{ formatDateTime(message.sent_at) }}
                                </small>

                                <!-- Delete Button -->
                                <button
                                    v-if="message.sender_id === currentUser.id && !message.deleting"
                                    @click="confirmDeleteMessage(message)"
                                    class="btn-delete"
                                    title="Xóa tin nhắn"
                                >
                                    <i class="bx bx-trash"></i>
                                </button>

                                <!-- Deleting State -->
                                <span v-else-if="message.deleting" class="deleting-indicator">
                                    <i class="bx bx-loader-alt bx-spin"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Scroll to Bottom Button -->
        <button
            v-if="showScrollToBottom"
            @click="scrollToBottom(true)"
            class="scroll-to-bottom"
            title="Cuộn xuống cuối"
        >
            <i class="bx bx-down-arrow-alt"></i>
        </button>

        <!-- Image Modal -->
        <div v-if="selectedImageModal" class="image-modal-overlay" @click="closeImageModal">
            <div class="image-modal" @click.stop>
                <button class="modal-close-btn" @click="closeImageModal">
                    <i class="bx bx-x"></i>
                </button>

                <!-- Navigation arrows for multiple images -->
                <button
                    v-if="modalImages.length > 1 && currentImageIndex > 0"
                    class="modal-nav-btn modal-nav-prev"
                    @click="previousImage"
                >
                    <i class="bx bx-chevron-left"></i>
                </button>
                <button
                    v-if="modalImages.length > 1 && currentImageIndex < modalImages.length - 1"
                    class="modal-nav-btn modal-nav-next"
                    @click="nextImage"
                >
                    <i class="bx bx-chevron-right"></i>
                </button>

                <!-- Image counter -->
                <div v-if="modalImages.length > 1" class="modal-counter">
                    {{ currentImageIndex + 1 }} / {{ modalImages.length }}
                </div>

                <!-- Modal image -->
                <img
                    :src="getImageUrl(currentModalImage.url)"
                    :alt="currentModalImage.caption || 'Image'"
                    class="modal-image"
                />

                <!-- Modal caption -->
                <div v-if="currentModalImage.caption" class="modal-caption">
                    {{ currentModalImage.caption }}
                </div>

                <!-- Modal info -->
                <div class="modal-info">
                    <span class="modal-sender">{{ selectedImageModal.sender?.name }}</span>
                    <span class="modal-time">{{ formatDateTime(selectedImageModal.sent_at) }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick, computed } from 'vue';
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

const emit = defineEmits(['message-sent', 'error']);

// Reactive data
const messages = ref([]);
const isLoading = ref(false);
const isLoadingMore = ref(false);
const canLoadMore = ref(true);
const messagesContainer = ref(null);
const messageContainer = ref(null);
const channels = ref([]);
const showScrollToBottom = ref(false);
const selectedImageModal = ref(null);
const modalImages = ref([]);
const currentImageIndex = ref(0);

// Utility functions
const formatDateTime = (time) => {
    if (!time) return 'Unknown time';

    const messageDate = new Date(time);
    const now = new Date();
    const diffInMs = now - messageDate;
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

    if (diffInMinutes < 1) return 'Vừa xong';
    if (diffInMinutes < 60) return `${diffInMinutes} phút trước`;
    if (diffInHours < 24) return `${diffInHours} giờ trước`;
    if (diffInDays < 7) return `${diffInDays} ngày trước`;

    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(messageDate);
};

const getAvatarUrl = (sender) => {
    if (!sender?.avatar) {
        return '/images/web/users/avatar.jpg';
    }

    const avatar = sender.avatar;
    if (avatar.startsWith('http')) {
        return avatar;
    }

    return `/images/client/avatar/${avatar}`;
};


const getImageUrl = (attachmentUrl) => {
    if (!attachmentUrl) return '/images/placeholder.jpg';

    // Nếu là URL đầy đủ (http/https), trả về trực tiếp
    if (attachmentUrl.startsWith('http')) {
        return attachmentUrl;
    }

    // Nếu đã bắt đầu bằng dấu /, trả về trực tiếp
    if (attachmentUrl.startsWith('/')) {
        return attachmentUrl;
    }

    // Nếu là đường dẫn tương đối như 'images/client/message/filename.jpg'
    // Thêm dấu / ở đầu để tạo URL đầy đủ
    return '/' + attachmentUrl;
};
const getImagesArray = (attachmentUrl) => {
    if (!attachmentUrl) return [];

    try {
        const parsed = JSON.parse(attachmentUrl);
        return Array.isArray(parsed) ? parsed : [];
    } catch (e) {
        // Nếu không phải JSON, trả về mảng với một ảnh
        return [{ url: attachmentUrl, caption: '' }];
    }
};

const currentModalImage = computed(() => {
    return modalImages.value[currentImageIndex.value] || { url: '', caption: '' };
});

const getGalleryLayout = (imageCount) => {
    if (imageCount === 1) {
        return { gridTemplateColumns: '1fr' };
    } else if (imageCount === 2) {
        return { gridTemplateColumns: '1fr 1fr' };
    } else if (imageCount === 3) {
        return { gridTemplateColumns: '1fr 1fr 1fr' };
    } else if (imageCount === 4) {
        return { gridTemplateColumns: '1fr 1fr', gridTemplateRows: '1fr 1fr' };
    } else {
        return { gridTemplateColumns: '1fr 1fr 1fr' };
    }
};

const handleAvatarError = (event) => {
    event.target.src = '/images/web/users/avatar.jpg';
};

// const handleImageError = (event) => {
//     event.target.src = '/images/placeholder.jpg';
//     event.target.alt = 'Ảnh không tải được';
// };

const shouldShowAvatar = (index) => {
    if (index === 0) return true;

    const currentMessage = messages.value[index];
    const prevMessage = messages.value[index - 1];

    if (currentMessage.sender_id !== prevMessage.sender_id) return true;

    const timeDiff = new Date(currentMessage.sent_at) - new Date(prevMessage.sent_at);
    return timeDiff > 5 * 60 * 1000; // 5 minutes
};

// Image modal functions
const openImageModal = (message, imageIndex = 0) => {
    selectedImageModal.value = message;
    modalImages.value = getImagesArray(message.attachment_url);
    currentImageIndex.value = imageIndex;
    document.body.style.overflow = 'hidden'; // Prevent background scrolling
};

const closeImageModal = () => {
    selectedImageModal.value = null;
    modalImages.value = [];
    currentImageIndex.value = 0;
    document.body.style.overflow = 'auto'; // Restore scrolling
};

const previousImage = () => {
    if (currentImageIndex.value > 0) {
        currentImageIndex.value--;
    }
};

const nextImage = () => {
    if (currentImageIndex.value < modalImages.value.length - 1) {
        currentImageIndex.value++;
    }
};

// Message operations
const loadMessages = async () => {
    if (!props.conversationId) return;

    isLoading.value = true;
    try {
        const response = await axios.get(`/messages/${props.conversationId}/data`);
        const loadedMessages = Array.isArray(response.data) ? response.data : response.data.messages || [];

        messages.value = loadedMessages.map(msg => ({
            ...msg,
            deleting: false,
            sender: msg.sender || {
                id: msg.sender_id,
                name: 'Unknown User',
                avatar: null
            }
        }));

        canLoadMore.value = response.data.has_more || false;

        await nextTick();
        scrollToBottom(false);

    } catch (error) {
        console.error('Error loading messages:', error);
        emit('error', 'Không thể tải tin nhắn. Vui lòng thử lại.');
    } finally {
        isLoading.value = false;
    }
};

const loadMoreMessages = async () => {
    if (!props.conversationId || isLoadingMore.value || !canLoadMore.value) return;

    const oldestMessage = messages.value[0];
    if (!oldestMessage) return;

    isLoadingMore.value = true;

    // Lưu vị trí scroll hiện tại
    const currentScrollHeight = messagesContainer.value.scrollHeight;
    const currentScrollTop = messagesContainer.value.scrollTop;

    try {
        const response = await axios.get(`/messages/${props.conversationId}/data`, {
            params: {
                before_id: oldestMessage.id,
                limit: 10
            }
        });

        const olderMessages = Array.isArray(response.data) ? response.data : response.data.messages || [];

        if (olderMessages.length > 0) {
            const formattedMessages = olderMessages.map(msg => ({
                ...msg,
                deleting: false,
                sender: msg.sender || {
                    id: msg.sender_id,
                    name: 'Unknown User',
                    avatar: null
                }
            }));

            // Thêm tin nhắn cũ vào đầu mảng
            messages.value.unshift(...formattedMessages);

            await nextTick();

            // Giữ nguyên vị trí scroll
            const newScrollHeight = messagesContainer.value.scrollHeight;
            const scrollDiff = newScrollHeight - currentScrollHeight;
            messagesContainer.value.scrollTop = currentScrollTop + scrollDiff;
        }

        canLoadMore.value = response.data.has_more || false;

    } catch (error) {
        console.error('Error loading more messages:', error);
        emit('error', 'Không thể tải thêm tin nhắn.');
    } finally {
        isLoadingMore.value = false;
    }
};

const handleNewMessage = (message) => {
    if (message.conversation_id !== props.conversationId) return;

    const messageExists = messages.value.some(m => m.id === message.id);
    if (messageExists) return;

    const formattedMessage = {
        ...message,
        deleting: false,
        sender: message.sender || {
            id: message.sender_id,
            name: 'Unknown User',
            avatar: null
        },
        sent_at: message.sent_at || new Date().toISOString()
    };

    messages.value.push(formattedMessage);

    // Auto scroll to bottom if user is near bottom
    if (isNearBottom()) {
        scrollToBottom(true);
    } else {
        showScrollToBottom.value = true;
    }

    emit('message-sent', formattedMessage);
};

const confirmDeleteMessage = async (message) => {
    if (!confirm('Bạn có chắc muốn xóa tin nhắn này không?')) return;

    message.deleting = true;

    try {
        await axios.delete(`/messages/${message.id}`);

        const index = messages.value.findIndex(m => m.id === message.id);
        if (index !== -1) {
            messages.value.splice(index, 1);
        }

    } catch (error) {
        console.error('Error deleting message:', error);
        message.deleting = false;
        emit('error', 'Không thể xóa tin nhắn. Vui lòng thử lại.');
    }
};

// Scroll functions
const scrollToBottom = (smooth = true) => {
    nextTick(() => {
        if (messagesContainer.value) {
            messagesContainer.value.scrollTo({
                top: messagesContainer.value.scrollHeight,
                behavior: smooth ? 'smooth' : 'auto'
            });
            showScrollToBottom.value = false;
        }
    });
};

const isNearBottom = () => {
    if (!messagesContainer.value) return true;

    const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;
    return scrollHeight - scrollTop - clientHeight < 100;
};

const handleScroll = () => {
    if (!messagesContainer.value) return;

    const { scrollTop, scrollHeight, clientHeight } = messagesContainer.value;

    // Show scroll to bottom button
    showScrollToBottom.value = !isNearBottom();

    // Auto load more when scrolled to top
    if (scrollTop < 100 && canLoadMore.value && !isLoadingMore.value && messages.value.length > 0) {
        loadMoreMessages();
    }
};

// WebSocket functions
const setupWebSocket = () => {
    cleanup();

    if (!window.Echo) {
        console.warn('Laravel Echo not available');
        return;
    }

    try {
        const conversationChannel = window.Echo.private(`conversation.${props.conversationId}`);
        conversationChannel.listen('.message.sent', (e) => {
            if (e.message) handleNewMessage(e.message);
        });

        const userChannel = window.Echo.private(`user.${props.currentUser.id}`);
        userChannel.listen('.message.sent', (e) => {
            if (e.message) handleNewMessage(e.message);
        });

        channels.value = [conversationChannel, userChannel];

    } catch (error) {
        console.error('Error setting up WebSocket listeners:', error);
    }
};

const cleanup = () => {
    channels.value.forEach(channel => {
        try {
            channel?.stopListening('.message.sent');
        } catch (error) {
            console.error('Error cleaning up channel:', error);
        }
    });
    channels.value = [];
};

// Lifecycle hooks
onMounted( async () => {
    loadMessages();
    setupWebSocket();

});

onUnmounted(() => {
    cleanup();
    closeImageModal(); // Cleanup modal state
    if (messagesContainer.value) {
        messagesContainer.value.removeEventListener('scroll', handleScroll);
    }
});

// Watchers
watch(() => props.conversationId, (newId, oldId) => {
    if (newId && newId !== oldId) {
        cleanup();
        messages.value = [];
        canLoadMore.value = true;
        showScrollToBottom.value = false;
        closeImageModal();
        loadMessages();
        setupWebSocket();
    }
}, { immediate: false });

// Expose methods for parent component
defineExpose({
    loadMessages,
    scrollToBottom,
    handleNewMessage
});
</script>

<style scoped>
.conversation-container {
    position: relative;
    height: 100%;
}

.messages {
    height: calc(100vh - 220px);
    overflow-y: auto;
    scroll-behavior: smooth;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    border-radius: 15px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}

.load-more-container {
    text-align: center;
    padding: 15px;
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    z-index: 5;
    border-bottom: 1px solid rgba(0, 0, 0, 0.1);
}

.btn-load-more {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 5px;
    margin: 0 auto;
}

.btn-load-more:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.loading-more {
    text-align: center;
    padding: 15px;
    color: #6c757d;
    font-size: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: sticky;
    top: 0;
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(10px);
    z-index: 5;
}

.messages-content {
    padding: 20px;
    min-height: 100%;
    display: flex;
    flex-direction: column;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #6c757d;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 200px;
    color: #6c757d;
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.message {
    display: flex;
    animation: fadeIn 0.3s ease-in-out;
    margin-bottom: 8px;
}

.message.hide-avatar {
    margin-bottom: 4px;
}

.message-sent {
    flex-direction: row-reverse;
}

.avatar-container {
    flex-shrink: 0;
    margin: 0 5px;
}

.avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.avatar-spacer {
    width: 46px;
    flex-shrink: 0;
}

.message-bubble {
    max-width: 70%;
    position: relative;
}

.message-content {
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.message-sent .message-content {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.message-text {
    padding: 7px 10px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
}

.message-sent .message-text {
    color: white;
}

.message-image-container {
    position: relative;
}

.message-image {
    width: 100%;
    max-width: 230px;
    height: auto;
    max-height: 400px;
    object-fit: cover;
    cursor: pointer;
    transition: transform 0.2s ease;
    display: block;
}

.message-image:hover {
    transform: scale(1.02);
}

/* Multiple Images Styles */
.message-images-container {
    position: relative;
}

.images-gallery {
    display: grid;
    gap: 8px;
    max-width: 400px;
}

.gallery-image-item {
    position: relative;
    cursor: pointer;
    border-radius: 12px;
    overflow: hidden;
    transition: transform 0.2s ease;
}

.gallery-image-item:hover {
    transform: scale(1.02);
}

.gallery-image {
    width: 100%;
    height: auto;
    max-height: 200px;
    object-fit: cover;
    display: block;
}

.gallery-caption {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    color: white;
    padding: 8px 12px;
    font-size: 12px;
    line-height: 1.3;
}

.images-text-caption {
    margin-top: 8px;
    padding: 8px 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 8px;
    font-size: 14px;
    line-height: 1.4;
}

.message-sent .images-text-caption {
    background: rgba(255, 255, 255, 0.1);
    color: white;
}

/* Gallery Layouts */
.images-gallery[style*="grid-template-columns: 1fr"] .gallery-image {
    max-height: 300px;
}

.images-gallery[style*="grid-template-columns: 1fr 1fr"] .gallery-image {
    max-height: 150px;
}

.images-gallery[style*="grid-template-columns: 1fr 1fr 1fr"] .gallery-image {
    max-height: 120px;
}

.image-caption {
    padding: 8px 12px;
    font-size: 13px;
    line-height: 1.4;
    color: #333;
    background: rgba(255, 255, 255, 0.9);
    border-top: 1px solid rgba(0, 0, 0, 0.1);
}

.message-sent .image-caption {
    color: white;
    background: rgba(255, 255, 255, 0.1);
    border-top: 1px solid rgba(255, 255, 255, 0.2);
}

.message-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 4px;
    padding: 0 8px;
}

.message-time {
    font-size: 11px;
    color: #6c757d;
    opacity: 0.8;
}

.btn-delete {
    background: none;
    border: none;
    color: #dc3545;
    font-size: 14px;
    cursor: pointer;
    padding: 4px;
    border-radius: 4px;
    opacity: 0;
    transition: all 0.2s ease;
}

.message:hover .btn-delete {
    opacity: 1;
}

.btn-delete:hover {
    background: rgba(220, 53, 69, 0.1);
    transform: scale(1.1);
}

.deleting-indicator {
    color: #6c757d;
    font-size: 12px;
}

.scroll-to-bottom {
    position: absolute;
    bottom: 20px;
    right: 20px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    z-index: 10;
}

.scroll-to-bottom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
}

/* Image Modal Styles */
.image-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    animation: fadeIn 0.3s ease-in-out;
}

.image-modal {
    position: relative;
    max-width: 90vw;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.modal-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    transition: all 0.2s ease;
    z-index: 10;
}

.modal-nav-btn:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-50%) scale(1.1);
}

.modal-nav-prev {
    left: 20px;
}

.modal-nav-next {
    right: 20px;
}

.modal-counter {
    position: absolute;
    top: -40px;
    left: 50%;
    transform: translateX(-50%);
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.modal-close-btn {
    position: absolute;
    top: -40px;
    right: 0;
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background-color 0.2s;
}

.modal-close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.modal-image {
    max-width: 100%;
    max-height: 80vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
}

.modal-caption {
    margin-top: 16px;
    padding: 8px 16px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    color: white;
    font-size: 14px;
    text-align: center;
    max-width: 80%;
}

.modal-info {
    display: flex;
    gap: 16px;
    margin-top: 12px;
    font-size: 12px;
    color: rgba(255, 255, 255, 0.8);
}

.modal-sender {
    font-weight: 500;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .messages {
        height: calc(100vh - 180px);
        border-radius: 0;
    }

    .messages-content {
        padding: 15px;
    }

    .message-bubble {
        max-width: 85%;
    }

    .avatar {
        width: 32px;
        height: 32px;
    }

    .avatar-spacer {
        width: 42px;
    }

    .message-image {
        max-width: 250px;
        max-height: 300px;
    }

    .modal-close-btn {
        top: -50px;
        width: 40px;
        height: 40px;
        font-size: 20px;
    }
}

/* Custom scrollbar */
.messages::-webkit-scrollbar {
    width: 6px;
}

.messages::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
    border-radius: 3px;
}

.messages::-webkit-scrollbar-thumb {
    background: rgba(0, 0, 0, 0.3);
    border-radius: 3px;
}

.messages::-webkit-scrollbar-thumb:hover {
    background: rgba(0, 0, 0, 0.5);
}
</style>
