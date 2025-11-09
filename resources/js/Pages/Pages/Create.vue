<template>
    <App>
        <div class="create-page-container">
            <div class="create-page-card">
                <div class="card-header">
                    <h2>Tạo trang mới</h2>
                    <p class="subtitle">Tạo một trang để kết nối với người theo dõi của bạn</p>
                </div>

                <form @submit.prevent="submitForm" class="create-page-form">
                    <!-- Page Name -->
                    <div class="form-group">
                        <label for="name">Tên trang <span class="required">*</span></label>
                        <input
                            type="text"
                            id="name"
                            v-model="form.name"
                            class="form-input"
                            placeholder="Nhập tên trang"
                            required
                        />
                        <small class="form-hint">Tên này sẽ hiển thị công khai</small>
                    </div>

                    <!-- Username -->
                    <div class="form-group">
                        <label for="username">Tên người dùng</label>
                        <div class="username-input-wrapper">
                            <span class="username-prefix">@</span>
                            <input
                                type="text"
                                id="username"
                                v-model="form.username"
                                class="form-input username-input"
                                placeholder="username"
                                pattern="[a-z0-9_]+"
                            />
                        </div>
                        <small class="form-hint">URL trang của bạn sẽ là: /pages/{{ form.username || 'auto-generated' }}</small>
                    </div>

                    <!-- Category -->
                    <div class="form-group">
                        <label for="category">Danh mục</label>
                        <select id="category" v-model="form.category" class="form-select">
                            <option value="">Chọn danh mục</option>
                            <option value="Business">Doanh nghiệp</option>
                            <option value="Brand">Thương hiệu</option>
                            <option value="Product">Sản phẩm</option>
                            <option value="Artist">Nghệ sĩ</option>
                            <option value="Entertainment">Giải trí</option>
                            <option value="Cause">Nguyên nhân</option>
                            <option value="Community">Cộng đồng</option>
                        </select>
                    </div>

                    <!-- Description -->
                    <div class="form-group">
                        <label for="description">Mô tả</label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            class="form-textarea"
                            rows="4"
                            placeholder="Mô tả về trang của bạn..."
                        ></textarea>
                    </div>

                    <!-- Profile Picture -->
                    <div class="form-group">
                        <label>Ảnh đại diện</label>
                        <div class="image-upload-container">
                            <div class="image-preview" v-if="profilePreview">
                                <img :src="profilePreview" alt="Profile preview" />
                                <button type="button" @click="removeProfilePicture" class="remove-image-btn">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <label v-else for="profile_picture" class="upload-area">
                                <i class="bx bx-camera"></i>
                                <span>Tải lên ảnh đại diện</span>
                            </label>
                            <input
                                type="file"
                                id="profile_picture"
                                ref="profilePictureInput"
                                @change="handleProfilePictureChange"
                                accept="image/*"
                                style="display: none;"
                            />
                        </div>
                    </div>

                    <!-- Cover Photo -->
                    <div class="form-group">
                        <label>Ảnh bìa</label>
                        <div class="image-upload-container cover-upload">
                            <div class="image-preview cover-preview" v-if="coverPreview">
                                <img :src="coverPreview" alt="Cover preview" />
                                <button type="button" @click="removeCoverPhoto" class="remove-image-btn">
                                    <i class="bx bx-x"></i>
                                </button>
                            </div>
                            <label v-else for="cover_photo" class="upload-area cover-upload-area">
                                <i class="bx bx-image"></i>
                                <span>Tải lên ảnh bìa</span>
                            </label>
                            <input
                                type="file"
                                id="cover_photo"
                                ref="coverPhotoInput"
                                @change="handleCoverPhotoChange"
                                accept="image/*"
                                style="display: none;"
                            />
                        </div>
                    </div>

                    <!-- Error Messages -->
                    <div v-if="errors.length" class="error-container">
                        <div v-for="(error, index) in errors" :key="index" class="error-message">
                            {{ error }}
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="button" @click="cancel" class="btn-cancel">
                            Hủy
                        </button>
                        <button type="submit" :disabled="isSubmitting" class="btn-submit">
                            <i v-if="isSubmitting" class="bx bx-loader-alt bx-spin"></i>
                            <span v-else>Tạo trang</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </App>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';
import axios from 'axios';

const form = reactive({
    name: '',
    username: '',
    category: '',
    description: '',
    profile_picture: null,
    cover_photo: null,
});

const profilePreview = ref(null);
const coverPreview = ref(null);
const profilePictureInput = ref(null);
const coverPhotoInput = ref(null);
const isSubmitting = ref(false);
const errors = ref([]);

const handleProfilePictureChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.profile_picture = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            profilePreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const handleCoverPhotoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.cover_photo = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            coverPreview.value = e.target.result;
        };
        reader.readAsDataURL(file);
    }
};

const removeProfilePicture = () => {
    form.profile_picture = null;
    profilePreview.value = null;
    if (profilePictureInput.value) {
        profilePictureInput.value.value = '';
    }
};

const removeCoverPhoto = () => {
    form.cover_photo = null;
    coverPreview.value = null;
    if (coverPhotoInput.value) {
        coverPhotoInput.value.value = '';
    }
};

const submitForm = async () => {
    if (isSubmitting.value) return;

    errors.value = [];
    isSubmitting.value = true;

    try {
        const formData = new FormData();
        formData.append('name', form.name);
        if (form.username) {
            formData.append('username', form.username.toLowerCase().replace(/[^a-z0-9_]/g, ''));
        }
        if (form.category) {
            formData.append('category', form.category);
        }
        if (form.description) {
            formData.append('description', form.description);
        }
        if (form.profile_picture) {
            formData.append('profile_picture', form.profile_picture);
        }
        if (form.cover_photo) {
            formData.append('cover_photo', form.cover_photo);
        }

        const response = await axios.post('/pages', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });

        if (response.data) {
            router.visit(`/pages/${response.data.page?.username || response.data.page?.id}`);
        }
    } catch (error) {
        if (error.response?.data?.errors) {
            const errorMessages = Object.values(error.response.data.errors).flat();
            errors.value = errorMessages;
        } else if (error.response?.data?.message) {
            errors.value = [error.response.data.message];
        } else {
            errors.value = ['Có lỗi xảy ra khi tạo trang. Vui lòng thử lại.'];
        }
    } finally {
        isSubmitting.value = false;
    }
};

const cancel = () => {
    router.visit('/');
};
</script>

<style scoped>
.create-page-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.create-page-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    padding: 32px;
}

.card-header {
    margin-bottom: 32px;
    text-align: center;
}

.card-header h2 {
    font-size: 28px;
    font-weight: 700;
    color: #050505;
    margin-bottom: 8px;
}

.subtitle {
    color: #65676b;
    font-size: 15px;
}

.create-page-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group label {
    font-weight: 600;
    color: #050505;
    font-size: 15px;
}

.required {
    color: #f02849;
}

.form-input,
.form-select,
.form-textarea {
    padding: 12px 16px;
    border: 1px solid #e4e6eb;
    border-radius: 6px;
    font-size: 15px;
    outline: none;
    transition: border-color 0.2s;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
    border-color: #1877f2;
}

.form-textarea {
    resize: vertical;
    font-family: inherit;
}

.form-hint {
    color: #65676b;
    font-size: 13px;
}

.username-input-wrapper {
    display: flex;
    align-items: center;
}

.username-prefix {
    padding: 12px 8px 12px 16px;
    background: #f0f2f5;
    border: 1px solid #e4e6eb;
    border-right: none;
    border-radius: 6px 0 0 6px;
    color: #65676b;
    font-size: 15px;
}

.username-input {
    border-radius: 0 6px 6px 0;
    flex: 1;
}

.image-upload-container {
    position: relative;
}

.cover-upload {
    aspect-ratio: 16/9;
    max-height: 300px;
}

.upload-area {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    border: 2px dashed #e4e6eb;
    border-radius: 8px;
    background: #f8f9fa;
    cursor: pointer;
    transition: all 0.2s;
    gap: 12px;
}

.upload-area:hover {
    border-color: #1877f2;
    background: #f0f2f5;
}

.upload-area i {
    font-size: 32px;
    color: #65676b;
}

.upload-area span {
    color: #65676b;
    font-weight: 600;
}

.cover-upload-area {
    height: 100%;
    min-height: 200px;
}

.image-preview {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
}

.image-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.cover-preview {
    aspect-ratio: 16/9;
    max-height: 300px;
}

.remove-image-btn {
    position: absolute;
    top: 8px;
    right: 8px;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: rgba(0, 0, 0, 0.6);
    border: none;
    color: #fff;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    transition: background 0.2s;
}

.remove-image-btn:hover {
    background: rgba(0, 0, 0, 0.8);
}

.error-container {
    background: #fee;
    border: 1px solid #fcc;
    border-radius: 6px;
    padding: 12px;
}

.error-message {
    color: #f02849;
    font-size: 14px;
    margin-bottom: 4px;
}

.error-message:last-child {
    margin-bottom: 0;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    padding-top: 24px;
    border-top: 1px solid #e4e6eb;
}

.btn-cancel,
.btn-submit {
    padding: 10px 24px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
}

.btn-cancel {
    background: #e4e6eb;
    color: #050505;
}

.btn-cancel:hover {
    background: #d8dadf;
}

.btn-submit {
    background: #1877f2;
    color: #fff;
}

.btn-submit:hover:not(:disabled) {
    background: #166fe5;
}

.btn-submit:disabled {
    background: #bcc0c4;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .create-page-container {
        padding: 10px;
    }

    .create-page-card {
        padding: 20px;
    }

    .card-header h2 {
        font-size: 24px;
    }
}
</style>


