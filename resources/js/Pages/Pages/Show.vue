<template>
    <App>
        <div class="page-detail-container">
            <!-- Page Header -->
            <PageHeader
                :page="page"
                :is-following="isFollowing"
                :is-admin="isAdmin"
                :current-user="currentUser"
                @update="handlePageUpdate"
                @follow-toggled="handleFollowToggle"
                @edit-page="openEditModal"
            />

            <!-- Navigation Tabs -->
            <PageNavigationTabs
                :active-tab="activeTab"
                :is-admin="isAdmin"
                :stats="pageStats"
                @tab-changed="handleTabChange"
            />
            <div v-if="isAdmin" class="admin-actions mb-3">
                <button class="btn-manage-admins" @click="openAdminModal">
                    <i class="bx bx-shield-quarter mr-2"></i>Quản lý quyền trang
                </button>
            </div>

            <!-- Content Area -->
            <div class="page-content">
                <!-- Home Tab -->
                <div v-if="activeTab === 'home'" class="tab-content">
                    <!-- Post Creator (only for admins) -->
                    <Post
                        v-if="isAdmin"
                        :page="page"
                        @post-created="handlePostCreated"
                    />

                    <!-- Posts Feed -->
                    <div class="posts-feed">
                        <div v-if="posts.length === 0 && !isLoading" class="empty-state">
                            <i class="bx bx-file-blank empty-icon"></i>
                            <p class="empty-text">Chưa có bài viết nào</p>
                        </div>

                        <div v-else-if="isLoading" class="posts-loading">
                            <div class="skeleton-post" v-for="i in 3" :key="i"></div>
                        </div>

                        <template v-else>
                            <ItemPost
                                v-for="post in posts"
                                :key="post.id"
                                :post="post"
                                :user="resolvePostAuthor(post)"
                                @updated="handlePostUpdated"
                                @deleted="handlePostDeleted"
                            />
                        </template>

                        <!-- Load More Button -->
                        <div v-if="hasMore && !isLoading" class="load-more-container">
                            <button @click="loadMorePosts" class="btn-load-more">
                                Tải thêm bài viết
                            </button>
                        </div>
                    </div>
                </div>

                <!-- About Tab -->
                <div v-if="activeTab === 'about'" class="tab-content">
                    <div class="about-section">
                        <h2 class="section-title">Giới thiệu</h2>
                        <p v-if="page.description" class="description">{{ page.description }}</p>
                        <p v-else class="no-description">Chưa có mô tả</p>

                        <div v-if="page.category" class="info-item">
                            <strong>Danh mục:</strong> {{ page.category }}
                        </div>
                        <div v-if="page.website" class="info-item">
                            <strong>Website:</strong>
                            <a :href="page.website" target="_blank">{{ page.website }}</a>
                        </div>
                        <div v-if="page.phone" class="info-item">
                            <strong>Điện thoại:</strong> {{ page.phone }}
                        </div>
                        <div v-if="page.email" class="info-item">
                            <strong>Email:</strong> {{ page.email }}
                        </div>
                    </div>
                </div>

                <!-- Photos Tab -->
                <div v-if="activeTab === 'photos'" class="tab-content">
                    <PageImage
                        :images="photos"
                        :loading="photosLoading"
                        :has-more="!!photosMeta.next_page_url"
                        @load-more="loadPhotos(true)"
                    />
                </div>

                <!-- Community Tab -->
                <div v-if="activeTab === 'community'" class="tab-content">
                    <div v-if="communityLoading" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <div v-else-if="community" class="tab-content">
                        <PageCommunity
                            :counts="community.counts"
                            :admins="community.admins"
                            :followers="community.followers"
                        />
                    </div>
                    <div v-else class="text-muted py-4 text-center">
                        Đang tải dữ liệu cộng đồng...
                    </div>
                </div>

                <!-- Insights Tab (Admin only) - Navigate to separate page -->
                <div v-if="activeTab === 'insights' && isAdmin" class="tab-content">
                    <div class="insights-redirect">
                        <p>Đang chuyển đến trang Phân tích...</p>
                    </div>
                </div>

                <!-- Other tabs placeholder -->
                <div
                    v-else-if="
                        activeTab !== 'home' &&
                        activeTab !== 'about' &&
                        activeTab !== 'insights' &&
                        activeTab !== 'community' &&
                        activeTab !== 'photos'
                    "
                    class="tab-content"
                >
                    <div class="tab-placeholder">
                        <i class="bx bx-info-circle"></i>
                    <p>Tính năng này đang được phát triển</p>
                </div>
            </div>
        </div>
    </div>

        <!-- Edit Modal -->
        <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <h3>Chỉnh sửa trang</h3>
                    <button @click="closeEditModal" class="modal-close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Tên trang</label>
                            <input v-model="formName" type="text" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Username</label>
                            <input v-model="formUsername" type="text" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Danh mục</label>
                            <input v-model="formCategory" type="text" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Website</label>
                            <input v-model="formWebsite" type="url" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Điện thoại</label>
                            <input v-model="formPhone" type="text" class="form-control" />
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input v-model="formEmail" type="email" class="form-control" />
                        </div>
                        <div class="form-group full-width">
                            <label>Mô tả</label>
                            <textarea v-model="formDescription" rows="3" class="form-control"></textarea>
                        </div>
                        <div class="form-group">
                            <label>Ảnh đại diện</label>
                            <input type="file" class="form-control" accept="image/*" @change="onProfileChange" />
                        </div>
                        <div class="form-group">
                            <label>Ảnh bìa</label>
                            <input type="file" class="form-control" accept="image/*" @change="onCoverChange" />
                        </div>
                    </div>
                    <div v-if="editError" class="text-danger mt-2">{{ editError }}</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeEditModal" :disabled="editLoading">Hủy</button>
                    <button class="btn btn-primary" @click="submitPageEdit" :disabled="editLoading">
                        {{ editLoading ? 'Đang lưu...' : 'Lưu thay đổi' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Manage Admins Modal -->
        <div v-if="showAdminModal" class="modal-overlay" @click="closeAdminModal">
            <div class="modal-content wide" @click.stop>
                <div class="modal-header">
                    <h3>Phân quyền trang</h3>
                    <button @click="closeAdminModal" class="modal-close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="admin-form">
                        <div class="form-group">
                            <label>User ID</label>
                            <input v-model="newAdminId" type="number" class="form-control" placeholder="Nhập ID người dùng" />
                        </div>
                        <div class="form-group">
                            <label>Role</label>
                            <select v-model="newAdminRole" class="form-control">
                                <option v-for="role in roleOptions" :key="role" :value="role">
                                    {{ role }}
                                </option>
                            </select>
                        </div>
                        <button class="btn btn-primary" :disabled="adminLoading" @click="addAdmin">
                            {{ adminLoading ? 'Đang lưu...' : 'Thêm/Cấp quyền' }}
                        </button>
                        <div v-if="adminError" class="text-danger mt-2">{{ adminError }}</div>
                    </div>

                    <div class="admin-list">
                        <h4>Danh sách quản trị</h4>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Role</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="admin in admins" :key="admin.id">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img :src="admin.avatar || '/images/default/avatar.jpg'" class="avatar-sm mr-2" />
                                            <div>
                                                <div class="font-weight-bold">{{ admin.name }}</div>
                                                <small class="text-muted">ID: {{ admin.id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <select
                                            class="form-control form-control-sm"
                                            v-model="admin.pivot.role"
                                            @change="updateAdminRole(admin)"
                                        >
                                            <option v-for="role in roleOptions" :key="role" :value="role">
                                                {{ role }}
                                            </option>
                                        </select>
                                    </td>
                                    <td class="text-right">
                                        <button class="btn btn-outline-danger btn-sm" @click="removeAdmin(admin)">
                                            Xóa
                                        </button>
                                    </td>
                                </tr>
                                <tr v-if="!admins.length">
                                    <td colspan="3" class="text-center text-muted">Chưa có quản trị viên</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';
import PageHeader from '../../Components/Pages/PageHeader.vue';
import PageNavigationTabs from '../../Components/Pages/PageNavigationTabs.vue';
import Post from '../../Components/Post.vue';
import PageInsights from '../../Components/Pages/PageInsights.vue';
import PageCommunity from '../../Components/Pages/PageCommunity.vue';
import PageImage from '../../Components/Pages/PageImage.vue';
import ItemPost from '../../Components/Item/ItemPost.vue';
import axios from 'axios';

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
    posts: {
        type: Object,
        default: () => ({ data: [] })
    },
    currentUser: {
        type: Object,
        required: true
    },
    pageStats: {
        type: Object,
        default: () => ({
            posts: 0,
            followers: 0,
            photos: 0,
            videos: 0,
        })
    }
});

const activeTab = ref('home');
const showEditModal = ref(false);
const showAdminModal = ref(false);
const isLoading = ref(false);
const posts = ref(props.posts.data || []);
const hasMore = ref(props.posts.next_page_url ? true : false);
const insights = ref(null);
const isFollowing = ref(props.isFollowing);
const admins = ref(props.page.admins || []);
const community = ref(null);
const communityLoading = ref(false);
const photos = ref([]);
const photosMeta = ref({ next_page_url: null });
const photosLoading = ref(false);
const roleOptions = ['admin', 'editor', 'moderator', 'analyst', 'advertiser'];
const newAdminId = ref('');
const newAdminRole = ref('editor');
const adminLoading = ref(false);
const adminError = ref(null);
const formName = ref(props.page.name || '');
const formUsername = ref(props.page.username || '');
const formCategory = ref(props.page.category || '');
const formDescription = ref(props.page.description || '');
const formWebsite = ref(props.page.website || '');
const formPhone = ref(props.page.phone || '');
const formEmail = ref(props.page.email || '');
const profileFile = ref(null);
const coverFile = ref(null);
const editLoading = ref(false);
const editError = ref(null);

const resolvePostAuthor = (post) => {
    if (post.page_id && post.page_id === props.page.id) {
        return {
            id: props.page.id,
            name: props.page.name,
            avatar: props.page.profile_picture_url || null,
            profile_url: `/pages/${props.page.username || props.page.id}`,
        };
    }

    if (post.user) {
        return {
            ...post.user,
            profile_url: post.user.username ? `/${post.user.username}` : '#',
        };
    }

    return {
        ...props.currentUser,
        profile_url: props.currentUser.username ? `/${props.currentUser.username}` : '#',
    };
};

const handleTabChange = (tab) => {
    if (tab === 'insights' && props.isAdmin) {
        // Navigate to insights page
        router.visit(`/pages/${props.page.id}/insights`);
        return;
    }

    if (tab === 'community' && !community.value && !communityLoading.value) {
        loadCommunity();
    }
    if (tab === 'photos' && photos.value.length === 0 && !photosLoading.value) {
        loadPhotos();
    }

    activeTab.value = tab;
};

const handlePageUpdate = (updatedPage) => {
    // Reload page data
    router.reload({ only: ['page'] });
};

const handleFollowToggle = (data) => {
    isFollowing.value = data.isFollowing;
};

const handlePostCreated = (post) => {
    posts.value.unshift(post);
};

const handlePostUpdated = (updatedPost) => {
    const index = posts.value.findIndex((post) => post.id === updatedPost.id);
    if (index !== -1) {
        posts.value[index] = {
            ...posts.value[index],
            ...updatedPost,
        };
    }
};

const handlePostDeleted = (postId) => {
    posts.value = posts.value.filter((post) => post.id !== postId);
};

const openEditModal = () => {
    formName.value = props.page.name || '';
    formUsername.value = props.page.username || '';
    formCategory.value = props.page.category || '';
    formDescription.value = props.page.description || '';
    formWebsite.value = props.page.website || '';
    formPhone.value = props.page.phone || '';
    formEmail.value = props.page.email || '';
    profileFile.value = null;
    coverFile.value = null;
    editError.value = null;
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
};

const onProfileChange = (e) => {
    profileFile.value = e.target.files[0] || null;
};

const onCoverChange = (e) => {
    coverFile.value = e.target.files[0] || null;
};

const submitPageEdit = async () => {
    editLoading.value = true;
    editError.value = null;
    try {
        const formData = new FormData();
        formData.append('name', formName.value);
        formData.append('username', formUsername.value || '');
        formData.append('category', formCategory.value || '');
        formData.append('description', formDescription.value || '');
        formData.append('website', formWebsite.value || '');
        formData.append('phone', formPhone.value || '');
        formData.append('email', formEmail.value || '');
        if (profileFile.value) {
            formData.append('profile_picture', profileFile.value);
        }
        if (coverFile.value) {
            formData.append('cover_photo', coverFile.value);
        }

        await axios.post(`/pages/${props.page.id}/update`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        router.reload({ only: ['page'] });
        showEditModal.value = false;
    } catch (error) {
        editError.value = error.response?.data?.message || 'Không thể cập nhật trang.';
    } finally {
        editLoading.value = false;
    }
};

const openAdminModal = () => {
    adminError.value = null;
    newAdminId.value = '';
    newAdminRole.value = 'editor';
    showAdminModal.value = true;
};

const closeAdminModal = () => {
    showAdminModal.value = false;
};

const addAdmin = async () => {
    if (!newAdminId.value) {
        adminError.value = 'Vui lòng nhập user ID';
        return;
    }
    adminLoading.value = true;
    adminError.value = null;
    try {
        await axios.post(`/pages/${props.page.id}/admins`, {
            user_id: newAdminId.value,
            role: newAdminRole.value,
        });
        const exists = admins.value.find((a) => a.id == newAdminId.value);
        if (exists) {
            exists.pivot.role = newAdminRole.value;
        } else {
            admins.value.push({
                id: Number(newAdminId.value),
                name: `User ${newAdminId.value}`,
                avatar: null,
                pivot: { role: newAdminRole.value },
            });
        }
        newAdminId.value = '';
    } catch (error) {
        adminError.value = error.response?.data?.message || 'Không thể cấp quyền.';
    } finally {
        adminLoading.value = false;
    }
};

const updateAdminRole = async (admin) => {
    adminLoading.value = true;
    adminError.value = null;
    try {
        await axios.put(`/pages/${props.page.id}/admins/${admin.id}`, {
            role: admin.pivot.role,
        });
    } catch (error) {
        adminError.value = error.response?.data?.message || 'Không thể cập nhật quyền.';
    } finally {
        adminLoading.value = false;
    }
};

const removeAdmin = async (admin) => {
    adminLoading.value = true;
    adminError.value = null;
    try {
        await axios.delete(`/pages/${props.page.id}/admins/${admin.id}`);
        admins.value = admins.value.filter((a) => a.id !== admin.id);
    } catch (error) {
        adminError.value = error.response?.data?.message || 'Không thể xóa admin.';
    } finally {
        adminLoading.value = false;
    }
};

const loadMorePosts = async () => {
    if (isLoading.value || !hasMore.value) return;

    isLoading.value = true;
    try {
        const response = await axios.get(`/pages/${props.page.id}/posts`, {
            params: { page: Math.floor(posts.value.length / 10) + 1 }
        });

        posts.value.push(...response.data.data);
        hasMore.value = response.data.next_page_url ? true : false;
    } catch (error) {
        console.error('Error loading more posts:', error);
    } finally {
        isLoading.value = false;
    }
};

const loadInsights = async () => {
    try {
        const response = await axios.get(`/pages/${props.page.id}/insights`);
        insights.value = response.data;
    } catch (error) {
        console.error('Error loading insights:', error);
    }
};

const loadCommunity = async () => {
    communityLoading.value = true;
    try {
        const response = await axios.get(`/pages/${props.page.id}/community`);
        community.value = response.data;
    } catch (error) {
        console.error('Error loading community:', error);
    } finally {
        communityLoading.value = false;
    }
};

const loadPhotos = async (loadMore = false) => {
    if (photosLoading.value) return;
    const nextUrl = loadMore ? photosMeta.value.next_page_url : `/pages/${props.page.id}/photos`;
    if (!nextUrl) return;

    photosLoading.value = true;
    try {
        const response = await axios.get(nextUrl);
        const data = response.data;
        photos.value = loadMore ? [...photos.value, ...data.data] : data.data;
        photosMeta.value.next_page_url = data.next_page_url;
    } catch (error) {
        console.error('Error loading photos:', error);
    } finally {
        photosLoading.value = false;
    }
};

onMounted(() => {
    if (props.posts && props.posts.data) {
        posts.value = props.posts.data;
    }
});
</script>

<style scoped>
.page-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.page-content {
    background: #f0f2f5;
    min-height: 500px;
    padding: 20px 0;
}

.tab-content {
    background: transparent;
}

.posts-feed {
    max-width: 680px;
    margin: 0 auto;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 8px;
    margin-bottom: 20px;
}

.empty-icon {
    font-size: 64px;
    color: #bcc0c4;
    margin-bottom: 16px;
}

.empty-text {
    color: #65676b;
    font-size: 16px;
}

.posts-loading {
    max-width: 680px;
    margin: 0 auto;
}

.skeleton-post {
    height: 200px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
    margin-bottom: 20px;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.load-more-container {
    text-align: center;
    margin-top: 20px;
}

.btn-load-more {
    padding: 12px 24px;
    background: #1877f2;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-load-more:hover {
    background: #166fe5;
}

.about-section {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.section-title {
    font-size: 24px;
    font-weight: 700;
    color: #050505;
    margin-bottom: 16px;
}

.description {
    font-size: 15px;
    color: #050505;
    line-height: 1.6;
    margin-bottom: 24px;
}

.no-description {
    color: #65676b;
    font-style: italic;
    margin-bottom: 24px;
}

.info-item {
    margin-bottom: 12px;
    font-size: 15px;
    color: #050505;
}

.info-item strong {
    color: #65676b;
    margin-right: 8px;
}

.tab-placeholder {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border-radius: 8px;
    color: #65676b;
}

.tab-placeholder i {
    font-size: 48px;
    margin-bottom: 16px;
    color: #bcc0c4;
}

.admin-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 10px;
}

.btn-manage-admins {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
}

.btn-manage-admins:hover {
    background: #1d4ed8;
}

.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    background: #fff;
    border-radius: 8px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content.wide {
    max-width: 800px;
}

.admin-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
    align-items: end;
    margin-bottom: 20px;
}

.admin-list table {
    width: 100%;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e4e6eb;
}

.modal-header h3 {
    font-size: 20px;
    font-weight: 600;
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #65676b;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: background 0.2s;
}

.modal-close:hover {
    background: #f0f2f5;
}

.modal-body {
    padding: 20px;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

@media (max-width: 768px) {
    .page-detail-container {
        padding: 10px;
    }

    .posts-feed {
        max-width: 100%;
    }
}
</style>
