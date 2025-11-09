<template>
    <div class="col-md-12">
        <div class="message-footer">
            <!-- Images Preview -->
            <div v-if="selectedImages.length" class="image-preview-container">
                <!-- Upload Progress -->
                <div v-if="isUploading" class="upload-progress-container">
                    <div class="upload-progress-bar">
                        <div class="upload-progress-fill" :style="{ width: uploadProgress + '%' }"></div>
                    </div>
                    <span class="upload-progress-text">{{ uploadProgress }}%</span>
                </div>

                <div class="d-flex gap-2 flex-wrap" style="padding:12px 15px 0; border-bottom:1px solid #f0f0f0;">
                    <div v-for="(img, idx) in previewUrls" :key="idx" class="image-preview">
                        <div class="preview-wrapper">
                            <img :src="img" alt="Preview" class="preview-image" />
                            <button @click="removeImage(idx)" class="remove-image-btn" type="button" :disabled="isUploading">
                                <i class='bx bx-x'></i>
                            </button>
                            <div v-if="isUploading" class="upload-overlay">
                                <i class='bx bx-loader-alt bx-spin'></i>
                            </div>
                        </div>
                        <input
                            v-model="captions[idx]"
                            class="caption-input mt-2"
                            placeholder="Chú thích ảnh..."
                            :disabled="isUploading"
                        />
                    </div>
                </div>
            </div>

            <div class="wrap">
                <!-- File input hidden (multiple) -->
                <input
                    ref="fileInput"
                    type="file"
                    accept="image/*"
                    multiple
                    @change="handleImageSelect"
                    style="display: none;"
                />

                <!-- Image button -->
                <button
                    @click="openImagePicker"
                    class="btn-image"
                    type="button"
                    title="Gửi ảnh"
                >
                    <i class='bx bx-image'></i>
                </button>

                <!-- Message input -->
                <input
                    type="text"
                    v-model="message"
                    @keyup.enter="sendMessage"
                    :placeholder="selectedImages.length ? 'Nhập tin nhắn hoặc thêm chú thích cho ảnh...' : 'Nhập tin nhắn...'"
                    :disabled="isSending"
                />

                <!-- Send button -->
                <button
                    @click="sendMessage"
                    :disabled="(!message.trim() && !selectedImages.length) || isSending"
                    class="btn-send"
                    type="button"
                >
                    <i v-if="isSending" class='bx bx-loader-alt bx-spin'></i>
                    <i v-else class='bx bx-send'></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversationId: { type: Number, required: true }
});
const emit = defineEmits(['message-sent', 'error']);

const message = ref('');
const selectedImages = ref([]); // File[]
const previewUrls = ref([]); // string[]
const captions = ref([]); // string[]
const fileInput = ref(null);
const isSending = ref(false);
const uploadProgress = ref(0);
const isUploading = ref(false);

const openImagePicker = () => {
    fileInput.value?.click();
};

const handleImageSelect = (event) => {
    const files = Array.from(event.target.files || []);
    if (!files.length) return;

    // Kiểm tra số lượng ảnh tối đa
    if (selectedImages.value.length + files.length > 10) {
        emit('error', 'Bạn chỉ có thể chọn tối đa 10 ảnh.');
        event.target.value = '';
        return;
    }

    for (const file of files) {
        if (!file.type.startsWith('image/')) {
            emit('error', 'Vui lòng chọn file ảnh hợp lệ.');
            continue;
        }
        if (file.size > 10 * 1024 * 1024) {
            emit('error', 'Kích thước ảnh không được vượt quá 10MB.');
            continue;
        }
        
        // Kiểm tra định dạng ảnh được phép
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            emit('error', 'Định dạng ảnh không được hỗ trợ. Chỉ chấp nhận JPEG, PNG, GIF, WebP.');
            continue;
        }
        
        selectedImages.value.push(file);
        previewUrls.value.push(URL.createObjectURL(file));
        captions.value.push('');
    }

    // clear input to allow selecting same file again if needed
    event.target.value = '';
};

const removeImage = (index) => {
    if (previewUrls.value[index]) {
        URL.revokeObjectURL(previewUrls.value[index]);
    }
    selectedImages.value.splice(index, 1);
    previewUrls.value.splice(index, 1);
    captions.value.splice(index, 1);
};

const sendMessage = async () => {
    if ((!message.value.trim() && !selectedImages.value.length) || isSending.value) return;

    isSending.value = true;
    isUploading.value = true;
    uploadProgress.value = 0;

    try {
        const formData = new FormData();
        formData.append('conversation_id', props.conversationId);
        formData.append('message_type', selectedImages.value.length ? 'images' : 'text');

        if (message.value.trim()) {
            formData.append('content', message.value.trim());
        }

        // Append multiple images and captions
        selectedImages.value.forEach((file, idx) => {
            formData.append('images[]', file);
            formData.append('captions[]', captions.value[idx] || '');
        });

        const response = await axios.post('/messages/send', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
            onUploadProgress: (progressEvent) => {
                if (progressEvent.total) {
                    uploadProgress.value = Math.round((progressEvent.loaded * 100) / progressEvent.total);
                }
            }
        });

        if (response.data?.success && response.data.message) {
            const sentMessage = response.data.message;
            // reset
            message.value = '';
            selectedImages.value.forEach((_, idx) => {
                if (previewUrls.value[idx]) URL.revokeObjectURL(previewUrls.value[idx]);
            });
            selectedImages.value = [];
            previewUrls.value = [];
            captions.value = [];
            uploadProgress.value = 0;

            emit('message-sent', {
                ...sentMessage,
                conversation_id: props.conversationId,
                sender: response.data.sender || sentMessage.sender
            });
        } else {
            emit('error', 'Có lỗi xảy ra khi gửi tin nhắn.');
        }
    } catch (error) {
        console.error(error);
        let errorMessage = 'Có lỗi xảy ra khi gửi tin nhắn.';
        if (error.response?.data?.message) errorMessage = error.response.data.message;
        else if (error.response?.data?.errors) {
            const errors = Object.values(error.response.data.errors).flat();
            errorMessage = errors.join(', ');
        }
        emit('error', errorMessage);
    } finally {
        isSending.value = false;
        isUploading.value = false;
        uploadProgress.value = 0;
    }
};

// cleanup
const cleanup = () => {
    previewUrls.value.forEach(url => URL.revokeObjectURL(url));
    previewUrls.value = [];
};
defineExpose({ cleanup });
</script>

<style scoped>
.message-footer {
    background: #fff;
    border-top: 1px solid #eee;
    border-radius: 0 0 15px 15px;
}

.image-preview-container {
    padding: 15px 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.upload-progress-container {
    padding: 10px 15px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    gap: 10px;
}

.upload-progress-bar {
    flex: 1;
    height: 6px;
    background: #e9ecef;
    border-radius: 3px;
    overflow: hidden;
}

.upload-progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #007bff, #0056b3);
    transition: width 0.3s ease;
    border-radius: 3px;
}

.upload-progress-text {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    min-width: 35px;
}

.image-preview {
    position: relative;
    display: inline-block;
    width: 160px;
    margin-right: 8px;
    margin-bottom: 10px;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    background: #fff;
    padding: 8px;
}

.preview-wrapper {
    position: relative;
    display: block;
}

.preview-image {
    width: 100%;
    height: 110px;
    object-fit: cover;
    display: block;
    border-radius: 6px;
}

.remove-image-btn {
    position: absolute;
    top: 6px;
    right: 6px;
    z-index: 2;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(220, 53, 69, 0.9);
    border: none;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s ease;
}

.remove-image-btn:hover:not(:disabled) {
    background: rgba(220, 53, 69, 1);
    transform: scale(1.1);
}

.remove-image-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.upload-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    color: white;
    font-size: 20px;
}

.image-caption {
    margin-top: 10px;
}

.caption-input {
    width: 100%;
    margin-top: 6px;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 13px;
    border: 1px solid #e6e6e6;
}

.caption-input:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.wrap {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
}

.btn-image {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: #f8f9fa;
    color: #6c757d;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-image:hover {
    background: #e9ecef;
    color: #495057;
    transform: scale(1.05);
}

.wrap input[type="text"] {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 20px;
    font-size: 14px;
    outline: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.wrap input[type="text"]:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.1);
}

.wrap input[type="text"]:disabled {
    background: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

.btn-send {
    width: 40px;
    height: 40px;
    border: none;
    border-radius: 50%;
    background: #007bff;
    color: white;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    flex-shrink: 0;
}

.btn-send:hover:not(:disabled) {
    background: #0056b3;
    transform: scale(1.05);
}

.btn-send:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
}

.btn-send i,
.btn-image i {
    font-size: 18px;
}

/* Animation cho loading */
.bx-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
    .image-preview {
        width: 120px;
    }

    .preview-image {
        height: 90px;
    }

    .wrap {
        padding: 10px;
        gap: 8px;
    }

    .btn-image,
    .btn-send {
        width: 36px;
        height: 36px;
    }

    .btn-image i,
    .btn-send i {
        font-size: 16px;
    }
}
</style>
