<template>
    <div class="modal fade" id="newMessageModal" tabindex="-1" role="dialog" aria-labelledby="newMessageModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newMessageModalLabel">
                        <i class="bx bxs-message-rounded"></i>
                        Nhắn tin cho {{ recipientName }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" @click="closeModal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Recipient Info -->
                    <div class="recipient-info mb-3">
                        <div class="d-flex align-items-center">
                            <img :src="getAvatarUrl(recipientAvatar)"
                                 :alt="recipientName"
                                 class="recipient-avatar mr-3"
                                 @error="handleImageError">
                            <div>
                                <h6 class="mb-0">{{ recipientName }}</h6>
                                <small class="text-muted">{{ recipientUsername }}</small>
                            </div>
                        </div>
                    </div>

                    <!-- Message Input -->
                    <div class="form-group">
                        <label for="message-content" class="form-label">Tin nhắn</label>
                        <textarea
                            id="message-content"
                            class="form-control"
                            rows="5"
                            v-model="messageText"
                            placeholder="Nhập tin nhắn của bạn..."
                            :disabled="isSending"
                        ></textarea>
                        <small class="form-text text-muted">
                            {{ messageText.length }}/1000 ký tự
                        </small>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group">
                        <label class="form-label">Hình ảnh (tùy chọn)</label>
                        <div class="image-upload-area">
                            <input
                                type="file"
                                ref="imageInput"
                                @change="handleImageSelect"
                                accept="image/*"
                                multiple
                                style="display: none"
                                id="message-images"
                            >
                            <label for="message-images" class="upload-label">
                                <i class="bx bx-image"></i>
                                Chọn hình ảnh
                            </label>
                            <div v-if="selectedImages.length > 0" class="selected-images mt-2">
                                <div v-for="(image, index) in selectedImages" :key="index" class="image-preview-item">
                                    <img :src="image.preview" :alt="`Preview ${index + 1}`">
                                    <button type="button" class="btn-remove-image" @click="removeImage(index)">
                                        <i class="bx bx-x"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errorMessage" class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ errorMessage }}
                        <button type="button" class="close" @click="errorMessage = ''">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal" @click="closeModal" :disabled="isSending">
                        Hủy
                    </button>
                    <button
                        type="button"
                        class="btn btn-primary"
                        @click="sendMessage"
                        :disabled="!canSend || isSending"
                    >
                        <span v-if="isSending">
                            <span class="spinner-border spinner-border-sm mr-2" role="status"></span>
                            Đang gửi...
                        </span>
                        <span v-else>
                            <i class="bx bx-send mr-2"></i>
                            Gửi tin nhắn
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    recipientId: {
        type: Number,
        required: true
    },
    recipientName: {
        type: String,
        required: true
    },
    recipientUsername: {
        type: String,
        default: ''
    },
    recipientAvatar: {
        type: String,
        default: null
    }
});

const messageText = ref('');
const selectedImages = ref([]);
const imageInput = ref(null);
const isSending = ref(false);
const errorMessage = ref('');

const canSend = computed(() => {
    return (messageText.value.trim().length > 0 || selectedImages.value.length > 0) && !isSending.value;
});

const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

const handleImageError = (e) => {
    e.target.src = '/images/web/users/avatar.jpg';
};

const handleImageSelect = (event) => {
    const files = Array.from(event.target.files);
    if (files.length + selectedImages.value.length > 10) {
        errorMessage.value = 'Tối đa 10 hình ảnh';
        return;
    }

    files.forEach(file => {
        if (file.size > 10 * 1024 * 1024) {
            errorMessage.value = 'Mỗi hình ảnh không được vượt quá 10MB';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            selectedImages.value.push({
                file: file,
                preview: e.target.result
            });
        };
        reader.readAsDataURL(file);
    });
};

const removeImage = (index) => {
    selectedImages.value.splice(index, 1);
};

const closeModal = () => {
    messageText.value = '';
    selectedImages.value = [];
    errorMessage.value = '';
    if (imageInput.value) {
        imageInput.value.value = '';
    }
    $('#newMessageModal').modal('hide');
};

const sendMessage = async () => {
    if (!canSend.value) return;

    isSending.value = true;
    errorMessage.value = '';

    try {
        const formData = new FormData();
        formData.append('recipient_id', props.recipientId);

        if (messageText.value.trim()) {
            formData.append('content', messageText.value.trim());
        }

        if (selectedImages.value.length > 0) {
            selectedImages.value.forEach((img, index) => {
                formData.append('images[]', img.file);
                if (messageText.value.trim()) {
                    formData.append('captions[]', messageText.value.trim());
                } else {
                    formData.append('captions[]', '');
                }
            });
        }

        const response = await axios.post('/messages/send', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.success) {
            // Emit event để cập nhật conversation list nếu cần
            window.dispatchEvent(new CustomEvent('new-conversation', {
                detail: response.data.conversation
            }));

            // Đóng modal và reset form
            closeModal();

            // Chuyển đến trang messages hoặc reload
            if (response.data.conversation) {
                router.visit(`/messages/${response.data.conversation.id}`, {
                    preserveScroll: false
                });
            } else {
                router.visit('/messages', {
                    preserveScroll: false
                });
            }
        } else {
            errorMessage.value = response.data.message || 'Không thể gửi tin nhắn. Vui lòng thử lại.';
        }
    } catch (error) {
        console.error('Error sending message:', error);
        errorMessage.value = error.response?.data?.message || 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại.';
    } finally {
        isSending.value = false;
    }
};

// Watch messageText để giới hạn độ dài
watch(messageText, (newVal) => {
    if (newVal.length > 1000) {
        messageText.value = newVal.substring(0, 1000);
    }
});

// Cleanup khi component unmount
onUnmounted(() => {
    closeModal();
});
</script>

<style scoped>
.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-bottom: none;
}

.modal-header .close {
    color: white;
    opacity: 0.9;
}

.modal-header .close:hover {
    opacity: 1;
}

.modal-header h5 {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.recipient-info {
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 8px;
}

.recipient-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e0e7ff;
}

.upload-label {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: #f0f2f5;
    border: 1px dashed #cbd5e0;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    color: #4a5568;
}

.upload-label:hover {
    background: #e2e8f0;
    border-color: #667eea;
    color: #667eea;
}

.selected-images {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.image-preview-item {
    position: relative;
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
    border: 2px solid #e2e8f0;
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.btn-remove-image {
    position: absolute;
    top: 4px;
    right: 4px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    color: white;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.2s;
}

.btn-remove-image:hover {
    background: rgba(0, 0, 0, 0.8);
}

.form-control:disabled {
    background-color: #e9ecef;
    opacity: 1;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
}

.btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #5568d3 0%, #6a3d8f 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.alert {
    margin-top: 1rem;
}

.spinner-border-sm {
    width: 1rem;
    height: 1rem;
}
</style>
