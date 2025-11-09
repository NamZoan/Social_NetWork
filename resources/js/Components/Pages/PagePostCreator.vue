<template>
    <div class="post-creator-card">
        <div class="post-creator-header">
            <img
                :src="page.profile_picture_url
                    ? `/storage/${page.profile_picture_url}`
                    : '/images/web/users/avatar.jpg'"
                :alt="page.name"
                class="creator-avatar"
            />
            <input
                v-model="postContent"
                type="text"
                class="post-input"
                :placeholder="`Bạn đang nghĩ gì, ${page.name}?`"
                @focus="showOptions = true"
            />
        </div>

        <div v-if="showOptions || selectedImages.length" class="post-creator-options">
            <!-- Image Preview -->
            <div v-if="selectedImages.length" class="image-preview-container">
                <div
                    v-for="(img, index) in selectedImages"
                    :key="index"
                    class="image-preview-item"
                >
                    <img :src="img.preview" :alt="`Preview ${index + 1}`" />
                    <button @click="removeImage(index)" class="remove-image-btn">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <label class="action-button action-photo">
                    <input
                        type="file"
                        ref="imageInput"
                        @change="handleImageSelect"
                        accept="image/*,video/*"
                        multiple
                        style="display: none;"
                    />
                    <i class="bx bx-image"></i>
                    <span>Ảnh/Video</span>
                </label>

                <button
                    @click="startLive"
                    class="action-button action-live"
                    type="button"
                >
                    <i class="bx bx-video"></i>
                    <span>Phát trực tiếp</span>
                </button>

                <button
                    @click="createEvent"
                    class="action-button action-event"
                    type="button"
                >
                    <i class="bx bx-calendar"></i>
                    <span>Sự kiện</span>
                </button>
            </div>

            <!-- Post Button -->
            <div class="post-actions">
                <button
                    @click="publishPost"
                    :disabled="!canPost || isPosting"
                    :class="['btn-post', { 'btn-post-disabled': !canPost || isPosting }]"
                    type="button"
                >
                    <i v-if="isPosting" class="bx bx-loader-alt bx-spin"></i>
                    <span v-else>Đăng</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    page: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['post-created']);

const postContent = ref('');
const showOptions = ref(false);
const selectedImages = ref([]);
const imageInput = ref(null);
const isPosting = ref(false);

const canPost = computed(() => {
    return postContent.value.trim().length > 0 || selectedImages.value.length > 0;
});

const handleImageSelect = (event) => {
    const files = Array.from(event.target.files || []);

    files.forEach(file => {
        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                selectedImages.value.push({
                    file: file,
                    preview: e.target.result
                });
            };
            reader.readAsDataURL(file);
        }
    });

    event.target.value = '';
};

const removeImage = (index) => {
    selectedImages.value.splice(index, 1);
};

const publishPost = async () => {
    if (!canPost.value || isPosting.value) return;

    isPosting.value = true;

    try {
        const formData = new FormData();
        formData.append('content', postContent.value);
        formData.append('page_id', props.page.id);
        formData.append('privacy_setting', 'public');

        selectedImages.value.forEach((img) => {
            formData.append('images[]', img.file);
        });

        const response = await axios.post('/posts', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data.success && response.data.post) {
            // Reset form
            postContent.value = '';
            selectedImages.value.forEach((_, idx) => {
                if (previewUrls.value[idx]) {
                    URL.revokeObjectURL(previewUrls.value[idx]);
                }
            });
            selectedImages.value = [];
            previewUrls.value = [];
            captions.value = [];
            showOptions.value = false;

            emit('post-created', response.data.post);
        } else {
            throw new Error('Không thể tạo bài đăng');
        }
    } catch (error) {
        console.error('Error creating post:', error);
    } finally {
        isPosting.value = false;
    }
};

const startLive = () => {
    // TODO: Implement live streaming
    console.log('Start live');
};

const createEvent = () => {
    // TODO: Open event creation modal
    console.log('Create event');
};
</script>

<style scoped>
.post-creator-card {
    background: #fff;
    border-radius: 8px;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.post-creator-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.creator-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

.post-input {
    flex: 1;
    padding: 12px 16px;
    border: 1px solid #e4e6eb;
    border-radius: 20px;
    font-size: 15px;
    background: #f0f2f5;
    outline: none;
    transition: background 0.2s;
}

.post-input:focus {
    background: #fff;
    border-color: #1877f2;
}

.post-creator-options {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #e4e6eb;
}

.image-preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}

.image-preview-item {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.remove-image-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    transition: background 0.2s;
}

.remove-image-btn:hover {
    background: rgba(0, 0, 0, 0.8);
}

.action-buttons-row {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}

.action-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 15px;
    font-weight: 600;
    transition: background 0.2s;
    background: transparent;
}

.action-photo {
    color: #41b35d;
}

.action-photo:hover {
    background: #f0f2f5;
}

.action-live {
    color: #f02849;
}

.action-live:hover {
    background: #f0f2f5;
}

.action-event {
    color: #f7b928;
}

.action-event:hover {
    background: #f0f2f5;
}

.post-actions {
    display: flex;
    justify-content: flex-end;
}

.btn-post {
    padding: 8px 24px;
    background: #1877f2;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-post:hover:not(.btn-post-disabled) {
    background: #166fe5;
}

.btn-post-disabled {
    background: #e4e6eb;
    color: #bcc0c4;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .post-creator-card {
        padding: 12px;
    }

    .action-button span {
        display: none;
    }

    .action-button {
        padding: 8px;
        border-radius: 50%;
    }
}
</style>

