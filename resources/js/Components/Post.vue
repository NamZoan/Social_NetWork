<template>
    <ul class="list-unstyled" data-toggle="modal" data-target="#exampleModal">
        <li class="media post-form">
            <div class="media-body">
                <div class="form-group post-input">
                    <textarea v-model="form.content" class="form-control post-textarea" id="postForm" rows="2"
                        :placeholder="placeholder"></textarea>
                </div>
                <div class="post-actions">
                    <div class="action-buttons">
                        <button type="button" class="btn-action" @click="triggerFileInput">
                            <div class="action-icon photo-icon">
                                <i class='bx bx-image'></i>
                            </div>
                            <span>Photo/Video</span>
                        </button>
                        <input type="file" ref="fileInput" @change="handleFileChange" multiple style="display: none" accept="image/*,video/*">
                        
                        <button type="button" class="btn-publish" @click="submitPost" :disabled="form.processing">
                            <span v-if="!form.processing">Đăng bài</span>
                            <span v-else>
                                <i class='bx bx-loader-alt bx-spin'></i>
                                Đang đăng...
                            </span>
                        </button>
                    </div>
                    <div class="publish-section">
                        
                    </div>
                </div>
            </div>
        </li>
    </ul>

    <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content modern-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">
                        <i class='bx bx-edit'></i>
                        Tạo bài viết
                    </h5>
                    <button type="button" class="close modern-close" data-dismiss="modal" aria-label="Close">
                        <i class='bx bx-x'></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitPost">
                        <!-- Chọn quyền riêng tư -->
                        <div v-if="!props.group_id && !pageId" class="privacy-selector">
                            <label class="privacy-label">
                                <i class='bx bx-lock-alt'></i>
                                Quyền riêng tư
                            </label>
                            <select v-model="form.privacy_setting" class="form-control modern-select">
                                <option value="public">
                                    <i class='bx bx-globe'></i> Công Khai
                                </option>
                                <option value="friends">
                                    <i class='bx bx-user'></i> Bạn Bè
                                </option>
                                <option value="private">
                                    <i class='bx bx-lock'></i> Chỉ Mình Tôi
                                </option>
                            </select>
                        </div>
                        <div v-else-if="pageId" class="alert modern-alert alert-info">
                            <i class='bx bx-info-circle'></i>
                            Bài viết sẽ được đăng công khai trên trang {{ props.page?.name }}
                        </div>
                        <div v-else-if="props.group" class="alert modern-alert" :class="props.group?.post_approval_required ? 'alert-info' : 'alert-warning'">
                            <i class='bx' :class="props.group?.post_approval_required ? 'bx-check-circle' : 'bx-error-circle'"></i>
                            {{ props.group?.post_approval_required ? 'Đăng bài tự do' : 'Cần quản trị viên duyệt bài viết' }}
                        </div>

                        <!-- Nội dung bài viết -->
                        <div class="form-group content-group">
                            <label for="message-text" class="content-label">
                                <i class='bx bx-message-rounded-dots'></i>
                                Bạn đang nghĩ gì:
                            </label>
                            <textarea v-model="form.content" class="form-control modern-textarea" id="message-text" 
                                rows="5" placeholder="Chia sẻ suy nghĩ của bạn..."></textarea>
                        </div>

                        <!-- Upload file -->
                        <div class="upload-section">
                            <label class="upload-label">
                                <i class='bx bx-image-add'></i>
                                Thêm ảnh/video
                            </label>
                            <input id="input-b3" type="file" class="file modern-file-input" multiple @change="handleFileUpload">
                        </div>

                        <div class="modal-footer modern-footer">
                            <button type="button" class="btn-cancel" data-dismiss="modal">
                                <i class='bx bx-x'></i>
                                Hủy
                            </button>
                            <button type="submit" class="btn-submit" :disabled="form.processing">
                                <i class='bx' :class="form.processing ? 'bx-loader-alt bx-spin' : 'bx-send'"></i>
                                <span v-if="!form.processing">Đăng Tin</span>
                                <span v-else>Đang đăng...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>


<script setup>
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import { onMounted, defineProps, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import $ from 'jquery';
import axios from 'axios';

const props = defineProps({
    group_id: {
        type: Number,
        default: null
    },
    page_id: {
        type: Number,
        default: null
    },
    user: {
        type: Object,
        required: true
    },
    group: {
        type: Object,
        default: null
    },
    page: {
        type: Object,
        default: null
    },
    createdGroups: {
        type: Array,
        default: () => []
    },
    joinedGroups: {
        type: Array,
        default: () => []
    }
});

// Ưu tiên page_id được truyền trực tiếp, fallback lấy từ đối tượng page
const pageId = props.page_id ?? (props.page ? props.page.id : null);

const fileInput = ref(null);
const form = useForm({
    content: '',
    privacy_setting: 'public',
    files: [],
    group_id: props.group_id || null,
    page_id: pageId
});

const placeholder = pageId 
    ? `Đăng bài viết trên trang ${props.page?.name}...`
    : props.group_id 
        ? `Đăng bài viết trong nhóm ${props.group?.name}...`
        : props.user 
            ? `Bạn đang nghĩ gì..., ${props.user.name}?` 
            : "Bạn đang nghĩ gì...?";

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileChange = (event) => {
    form.files = Array.from(event.target.files);
};

// Gán file vào form khi người dùng chọn file
const handleFileUpload = (event) => {
    form.files = Array.from(event.target.files);
};

// Khởi tạo bootstrap-fileinput
onMounted(() => {
    $('#input-b3').fileinput({
        showUpload: false,
        showPreview: true,
        allowedFileExtensions: ['jpg', 'png', 'gif'],
    });
});

// Gửi form lên server Laravel
const submitPost = async () => {
    if (!form.content.trim()) return;

    // Page posts cần JSON response nên xử lý riêng bằng axios để tránh lỗi Inertia overlay
    if (pageId) {
        try {
            const payload = new FormData();
            payload.append('content', form.content);
            payload.append('privacy_setting', 'public');
            payload.append('page_id', pageId);
            form.files.forEach((file, idx) => {
                payload.append(`files[${idx}]`, file);
            });

            await axios.post('/posts', payload, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });

            // Reload lại trang để thấy bài viết mới
            window.location.reload();
        } catch (error) {
            console.error('Error creating page post:', error);
        }
        return;
    }

    form.post('/posts', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
            window.location.reload();
        }
    });
};

</script>

<style scoped>
/* Post Form Card */
.post-form {
    background: white;
    border-radius: 16px;
    padding: 20px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    margin-bottom: 20px;
    transition: all 0.3s ease;
    animation: fadeInUp 0.4s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.post-form:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

/* Post Input */
.post-input {
    margin-bottom: 16px;
}

.post-textarea {
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    padding: 12px 16px;
    font-size: 15px;
    transition: all 0.3s ease;
    resize: none;
}

.post-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

.post-textarea::placeholder {
    color: #a0aec0;
}

/* Post Actions */
.post-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    background: transparent;
    border: none;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #4a5568;
}

.btn-action:hover {
    background: linear-gradient(90deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%);
    transform: translateY(-2px);
}

.action-icon {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.action-icon i {
    font-size: 20px;
}

.photo-icon {
    background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(16, 185, 129, 0.1) 100%);
    color: #10b981;
}

.btn-action:hover .photo-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    transform: scale(1.1) rotate(5deg);
}

.tag-icon {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
    color: #3b82f6;
}

.btn-action:hover .tag-icon {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    transform: scale(1.1) rotate(5deg);
}

.location-icon {
    background: linear-gradient(135deg, rgba(239, 68, 68, 0.1) 0%, rgba(220, 38, 38, 0.1) 100%);
    color: #ef4444;
}

.btn-action:hover .location-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    transform: scale(1.1) rotate(5deg);
}

/* Publish Button */
.btn-publish {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-publish:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-publish:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-publish i {
    margin-right: 6px;
}

/* Modal Styling */
.modern-modal {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-header {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    padding: 20px 24px;
    border: none;
}

.modal-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 600;
    font-size: 18px;
}

.modal-title i {
    font-size: 24px;
}

.modern-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 1;
}

.modern-close:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: rotate(90deg);
}

.modern-close i {
    font-size: 24px;
}

.modal-body {
    padding: 24px;
}

/* Privacy Selector */
.privacy-selector {
    margin-bottom: 20px;
}

.privacy-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 8px;
    font-size: 14px;
}

.privacy-label i {
    font-size: 18px;
    color: #667eea;
}

.modern-select {
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    padding: 10px 14px;
    font-size: 14px;
    transition: all 0.3s ease;
}

.modern-select:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

/* Modern Alert */
.modern-alert {
    border-radius: 12px;
    padding: 14px 16px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 14px;
    font-weight: 500;
    border: none;
}

.modern-alert i {
    font-size: 20px;
}

.alert-info {
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
    color: #1e40af;
}

.alert-warning {
    background: linear-gradient(135deg, rgba(251, 191, 36, 0.1) 0%, rgba(245, 158, 11, 0.1) 100%);
    color: #92400e;
}

/* Content Group */
.content-group {
    margin-bottom: 20px;
}

.content-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 10px;
    font-size: 14px;
}

.content-label i {
    font-size: 18px;
    color: #667eea;
}

.modern-textarea {
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 15px;
    transition: all 0.3s ease;
    resize: vertical;
    min-height: 120px;
}

.modern-textarea:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    outline: none;
}

.modern-textarea::placeholder {
    color: #a0aec0;
}

/* Upload Section */
.upload-section {
    margin-bottom: 20px;
}

.upload-label {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    color: #2d3748;
    margin-bottom: 10px;
    font-size: 14px;
}

.upload-label i {
    font-size: 18px;
    color: #10b981;
}

/* Modal Footer */
.modern-footer {
    border-top: 1px solid #e2e8f0;
    padding: 16px 0 0;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn-cancel {
    background: #f7fafc;
    color: #4a5568;
    border: 2px solid #e2e8f0;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 6px;
}

.btn-cancel:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    transform: translateY(-2px);
}

.btn-submit {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    border: none;
    padding: 10px 24px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-submit i {
    font-size: 18px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .post-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .action-buttons {
        justify-content: space-between;
    }

    .btn-action {
        flex: 1;
        justify-content: center;
        min-width: 0;
    }

    .btn-action span {
        display: none;
    }

    .publish-section {
        width: 100%;
    }

    .btn-publish {
        width: 100%;
    }
}

/* Animation for modal */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
    transform: scale(0.9);
}

.modal.show .modal-dialog {
    transform: scale(1);
}
</style>
