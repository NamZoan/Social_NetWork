<template>
    <div class="post-creator-card">
        <div class="post-creator-header">
            <div class="avatar-ring">
                <img
                    :src="page.profile_picture_url
                        ? `/storage/${page.profile_picture_url}`
                        : '/images/web/users/avatar.jpg'"
                    :alt="page.name"
                    class="creator-avatar"
                />
            </div>
            <div class="header-meta">
                <p class="header-title">{{ page.name }}</p>
                <button class="privacy-pill" type="button">
                    <i class="bx bx-globe"></i>
                    <span>Công khai</span>
                    <i class="bx bx-chevron-down"></i>
                </button>
            </div>
        </div>

        <div class="post-input-wrapper">
            <textarea
                v-model="postContent"
                class="post-input"
                rows="3"
                :placeholder="placeholderText"
            ></textarea>
        </div>

        <transition name="fade">
            <div v-if="selectedImages.length" class="image-preview-container">
                <div
                    v-for="(img, index) in selectedImages"
                    :key="index"
                    class="image-preview-item"
                >
                    <img :src="img.preview" :alt="`Preview ${index + 1}`" />
                    <button @click="removeImage(index)" class="remove-image-btn" type="button">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
            </div>
        </transition>

        <div class="composer-divider"></div>

        <div class="add-to-post">
            <span class="add-to-post-label">Thêm vào bài viết của bạn</span>
            <div class="action-buttons-row">
                <button
                    @click="startLive"
                    class="action-button action-live"
                    type="button"
                >
                    <span class="action-icon">
                        <i class="bx bx-video"></i>
                    </span>
                    <span>Video trực tiếp</span>
                </button>

                <label class="action-button action-photo">
                    <input
                        type="file"
                        ref="imageInput"
                        @change="handleImageSelect"
                        accept="image/*,video/*"
                        multiple
                        style="display: none;"
                    />
                    <span class="action-icon">
                        <i class="bx bx-image"></i>
                    </span>
                    <span>Ảnh/Video</span>
                </label>

                <button
                    @click="createEvent"
                    class="action-button action-event"
                    type="button"
                >
                    <span class="action-icon">
                        <i class="bx bx-smile"></i>
                    </span>
                    <span>Cảm xúc/Hoạt động</span>
                </button>
            </div>
        </div>

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
const selectedImages = ref([]);
const imageInput = ref(null);
const isPosting = ref(false);

const canPost = computed(() => {
    return postContent.value.trim().length > 0 || selectedImages.value.length > 0;
});

const placeholderText = computed(() => {
    return `Bạn đang nghĩ gì, ${props.page?.name ?? 'bạn'}?`;
});

const handleImageSelect = (event) => {
    const files = Array.from(event.target.files || []);

    files.forEach((file) => {
        if (file.type.startsWith('image/') || file.type.startsWith('video/')) {
            const reader = new FileReader();
            reader.onload = (e) => {
                selectedImages.value.push({
                    file,
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

const resetComposer = () => {
    postContent.value = '';
    selectedImages.value = [];

    if (imageInput.value) {
        imageInput.value.value = '';
    }
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
            resetComposer();
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
    console.log('Start live stream');
};

const createEvent = () => {
    console.log('Open feelings/activity picker');
};
</script>

<style scoped>
.post-creator-card {
    background: #fff;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 12px 30px rgba(15, 34, 58, 0.08);
    border: 1px solid #e4e6eb;
    margin-bottom: 24px;
}

.post-creator-header {
    display: flex;
    align-items: center;
    gap: 12px;
}

.avatar-ring {
    padding: 2px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0f8af5, #7c5dfa);
    display: inline-flex;
}

.creator-avatar {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    background: #f0f2f5;
}

.header-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.header-title {
    font-weight: 600;
    color: #050505;
    margin: 0;
    font-size: 16px;
}

.privacy-pill {
    border: 1px solid #ced0d4;
    padding: 4px 10px;
    border-radius: 999px;
    background: #f0f2f5;
    color: #050505;
    font-size: 13px;
    font-weight: 600;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    cursor: pointer;
}

.post-input-wrapper {
    margin-top: 16px;
    background: #f0f2f5;
    border-radius: 16px;
    padding: 12px 16px;
}

.post-input {
    width: 100%;
    border: none;
    background: transparent;
    resize: none;
    font-size: 17px;
    line-height: 1.4;
    color: #050505;
    outline: none;
}

.composer-divider {
    height: 1px;
    background: #e4e6eb;
    margin: 20px 0;
}

.image-preview-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 12px;
    margin-top: 16px;
}

.image-preview-item {
    position: relative;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

.image-preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.remove-image-btn {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.65);
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
    background: rgba(0, 0, 0, 0.85);
}

.add-to-post {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fb;
    border: 1px solid #e4e6eb;
    border-radius: 14px;
    padding: 14px 16px;
}

.add-to-post-label {
    font-weight: 600;
    color: #65676b;
}

.action-buttons-row {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.action-button {
    border: none;
    background: transparent;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #65676b;
    cursor: pointer;
    padding: 6px 10px;
    border-radius: 12px;
    transition: background 0.2s, transform 0.2s;
}

.action-button:hover {
    background: #e4e6eb;
    transform: translateY(-1px);
}

.action-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
}

.action-live .action-icon {
    background: linear-gradient(45deg, #f02849, #f5533d);
}

.action-photo .action-icon {
    background: linear-gradient(45deg, #45bd62, #40c057);
}

.action-event .action-icon {
    background: linear-gradient(45deg, #f7b928, #f9c23c);
    color: #8a6116;
}

.post-actions {
    margin-top: 16px;
}

.btn-post {
    width: 100%;
    padding: 12px 24px;
    background: #1877f2;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-weight: 700;
    font-size: 16px;
    cursor: pointer;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
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

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

@media (max-width: 768px) {
    .post-creator-card {
        padding: 16px;
    }

    .add-to-post {
        flex-direction: column;
        align-items: flex-start;
    }

    .action-button span:last-child {
        display: none;
    }
}
</style>
