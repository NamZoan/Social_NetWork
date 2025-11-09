<template>
    <div class="page-header">
        <!-- Cover Photo -->
        <div class="cover-photo-container" :style="{ backgroundImage: `url(${coverPhotoUrl})` }">
            <div v-if="isAdmin" class="cover-overlay">
                <label for="coverPhotoInput" class="btn-update-cover">
                    <i class="bx bxs-camera"></i>
                    Cập nhật ảnh bìa
                </label>
                <input type="file" id="coverPhotoInput" ref="coverPhotoInput" @change="handleCoverPhotoChange"
                    accept="image/*" style="display: none;" />
            </div>
        </div>

        <!-- Profile Section -->
        <div class="profile-section">
            <div class="container">
                <div class="profile-content">
                    <!-- Profile Picture -->
                    <div class="profile-picture-container">
                        <div class="profile-picture-wrapper">
                            <img :src="profilePictureUrl" :alt="page.name" class="profile-picture" />
                            <div v-if="isAdmin" class="profile-picture-overlay">
                                <label for="profilePictureInput" class="btn-update-avatar">
                                    <i class="bx bxs-camera"></i>
                                </label>
                                <input type="file" id="profilePictureInput" ref="profilePictureInput"
                                    @change="handleProfilePictureChange" accept="image/*" style="display: none;" />
                            </div>
                        </div>
                    </div>

                    <!-- Page Info -->
                    <div class="page-info">
                        <div class="page-name-section">
                            <h1 class="page-name">{{ page.name }}</h1>
                            <div v-if="page.verified" class="verified-badge">
                                <i class="bx bx-check-circle"></i>
                            </div>
                        </div>
                        <p v-if="page.username" class="page-username">@{{ page.username }}</p>
                        <p v-if="page.category" class="page-category">{{ page.category }}</p>
                        <div class="page-stats">
                            <span class="followers-count">
                                <strong>{{ formatNumber(page.follower_count) }}</strong> người theo dõi
                            </span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <!-- Follow/Following Button -->
                        <button v-if="!isAdmin" @click="toggleFollow" :class="[
                            'btn-follow',
                            isFollowing ? 'btn-following' : 'btn-follow-primary'
                        ]" :disabled="isLoading">
                            <i v-if="!isFollowing" class="bx bx-plus"></i>
                            <i v-else class="bx bx-check"></i>
                            {{ isFollowing ? 'Đang theo dõi' : 'Theo dõi' }}
                        </button>

                        <!-- Admin Actions -->
                        <template v-if="isAdmin">
                            <button @click="openEditModal" class="btn-action btn-edit" title="Chỉnh sửa trang">
                                <i class="bx bx-edit"></i>
                                <span class="btn-text">Chỉnh sửa</span>
                            </button>
                            <button @click="viewAsGuest" class="btn-action btn-view-as" title="Xem với tư cách khách">
                                <i class="bx bx-show"></i>
                                <span class="btn-text">Xem với tư cách khách</span>
                            </button>
                        </template>

                        <!-- Public Actions -->
                        <button v-if="!isAdmin" @click="likePage"
                            :class="['btn-action', isLiked ? 'btn-liked' : 'btn-like']" title="Thích trang">
                            <i class="bx bx-like"></i>
                        </button>
                        <button v-if="!isAdmin" @click="messagePage" class="btn-action btn-message" title="Nhắn tin">
                            <i class="bx bx-message"></i>
                        </button>
                        <button v-if="!isAdmin" @click="sharePage" class="btn-action btn-share" title="Chia sẻ">
                            <i class="bx bx-share-alt"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

      </div>  
</template>

<script setup>
import { ref, computed } from 'vue';
import axios from 'axios';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    page: {
        type: Object,
        required: true
    },
    isFollowing: {
        type: Boolean,
        default: false
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    currentUser: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['update', 'follow-toggled']);

const isLoading = ref(false);
const isLiked = ref(false);
const coverPhotoInput = ref(null);
const profilePictureInput = ref(null);

const coverPhotoUrl = computed(() => {
    return props.page.cover_photo_url
        ? `/storage/${props.page.cover_photo_url}`
        : '/images/web/default-cover.jpg';
});

const profilePictureUrl = computed(() => {
    return props.page.profile_picture_url
        ? `/storage/${props.page.profile_picture_url}`
        : '/images/web/users/avatar.jpg';
});

const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
};

const toggleFollow = async () => {
    if (isLoading.value) return;

    isLoading.value = true;
    try {
        const response = await axios.post(`/pages/${props.page.id}/follow`);
        emit('follow-toggled', response.data);
    } catch (error) {
        console.error('Error toggling follow:', error);
    } finally {
        isLoading.value = false;
    }
};

const handleCoverPhotoChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('cover_photo', file);

    try {
        const response = await axios.post(`/pages/${props.page.id}/update`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        emit('update', response.data);
    } catch (error) {
        console.error('Error updating cover photo:', error);
    }
};

const handleProfilePictureChange = async (event) => {
    const file = event.target.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('profile_picture', file);

    try {
        const response = await axios.post(`/pages/${props.page.id}/update`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        });
        emit('update', response.data);
    } catch (error) {
        console.error('Error updating profile picture:', error);
    }
};

const openEditModal = () => {
    // TODO: Emit event để mở modal chỉnh sửa
    emit('edit-page');
};

const viewAsGuest = () => {
    // TODO: Xem với tư cách khách
    router.visit(`/pages/${props.page.username || props.page.id}`, {
        data: { viewAsGuest: true }
    });
};

const likePage = () => {
    // TODO: Implement like page
    isLiked.value = !isLiked.value;
};

const messagePage = () => {
    // TODO: Navigate to message page
    router.visit(`/messages?page=${props.page.id}`);
};

const sharePage = () => {
    // TODO: Open share modal
    if (navigator.share) {
        navigator.share({
            title: props.page.name,
            text: props.page.description,
            url: window.location.href
        });
    }
};
</script>

<style scoped>
.page-header {
    position: relative;
    background: #fff;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
}

.cover-photo-container {
    width: 100%;
    height: 350px;
    background-size: cover;
    background-position: center;
    background-color: #e4e6eb;
    position: relative;
}

.cover-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.cover-photo-container:hover .cover-overlay {
    opacity: 1;
}

.btn-update-cover {
    background: rgba(255, 255, 255, 0.9);
    color: #000;
    padding: 10px 20px;
    border-radius: 6px;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    transition: background 0.2s;
}

.btn-update-cover:hover {
    background: #fff;
}

.profile-section {
    padding: 0 20px 20px;
    position: relative;
}

.profile-content {
    display: flex;
    align-items: flex-end;
    gap: 20px;
    margin-top: -80px;
    position: relative;
    z-index: 1;
}

.profile-picture-container {
    flex-shrink: 0;
}

.profile-picture-wrapper {
    position: relative;
    width: 160px;
    height: 160px;
}

.profile-picture {
    width: 160px;
    height: 160px;
    border-radius: 50%;
    border: 4px solid #fff;
    object-fit: cover;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.profile-picture-overlay {
    position: absolute;
    bottom: 0;
    right: 0;
    background: #1877f2;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: 3px solid #fff;
    transition: transform 0.2s;
}

.profile-picture-overlay:hover {
    transform: scale(1.1);
}

.btn-update-avatar {
    color: #fff;
    font-size: 18px;
    cursor: pointer;
}

.page-info {
    flex: 1;
    min-width: 0;
}

.page-name-section {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 4px;
}

.page-name {
    font-size: 32px;
    font-weight: 700;
    margin: 0;
    color: #050505;
}

.verified-badge {
    color: #1877f2;
    font-size: 24px;
}

.page-username {
    font-size: 15px;
    color: #65676b;
    margin: 0 0 4px 0;
}

.page-category {
    font-size: 15px;
    color: #65676b;
    margin: 0 0 8px 0;
}

.page-stats {
    font-size: 15px;
    color: #65676b;
}

.followers-count strong {
    color: #050505;
}

.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    align-items: center;
}

.btn-follow {
    padding: 8px 20px;
    border-radius: 6px;
    border: none;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
}

.btn-follow-primary {
    background: #1877f2;
    color: #fff;
}

.btn-follow-primary:hover {
    background: #166fe5;
}

.btn-following {
    background: #e4e6eb;
    color: #050505;
}

.btn-following:hover {
    background: #d8dadf;
}

.btn-action {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: #e4e6eb;
    color: #050505;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
    font-size: 20px;
}

.btn-action:hover {
    background: #d8dadf;
}

.btn-edit,
.btn-view-as {
    width: auto;
    padding: 8px 16px;
    border-radius: 6px;
}

.btn-text {
    margin-left: 6px;
    font-size: 15px;
    font-weight: 600;
}

.btn-liked {
    background: #1877f2;
    color: #fff;
}

@media (max-width: 768px) {
    .profile-content {
        flex-direction: column;
        align-items: flex-start;
        margin-top: -60px;
    }

    .profile-picture-wrapper {
        width: 120px;
        height: 120px;
    }

    .profile-picture {
        width: 120px;
        height: 120px;
    }

    .page-name {
        font-size: 24px;
    }

    .cover-photo-container {
        height: 250px;
    }

    .action-buttons {
        width: 100%;
        justify-content: space-between;
    }

    .btn-action {
        width: 36px;
        height: 36px;
        font-size: 18px;
    }
}
</style>

