<template>
    <App>
        <div class="page-detail-container">
            <!-- Page Header -->
            <PageHeader
                :page="page"
                :is-following="isFollowing"
                :is-admin="isAdmin"
                :admin-role="adminRole"
                :can-edit-page="permissions.can_edit_page"
                :current-user="currentUser"
                @update="handlePageUpdate"
                @follow-toggled="handleFollowToggle"
                @edit-page="openEditModal"
            />

            <!-- Navigation Tabs -->
            <PageNavigationTabs
                :active-tab="activeTab"
                :is-admin="isAdmin"
                :can-view-insights="permissions.can_view_insights"
                :stats="pageStats"
                @tab-changed="handleTabChange"
            />
            <div v-if="permissions.can_manage_admins || permissions.can_view_insights" class="admin-actions mb-3">
                <button v-if="permissions.can_manage_admins" class="btn-manage-admins" @click="openAdminModal">
                    <i class="bx bx-shield-quarter mr-2"></i>Quản lý quyền trang
                </button>
                <button v-if="permissions.can_view_insights" class="btn-insights" @click="goToInsights">
                    <i class="bx bx-bar-chart mr-2"></i>Xem thống kê chi tiết
                </button>
            </div>

            <!-- Content Area -->
            <div class="page-content">
                <!-- Home Tab -->
                <div v-if="activeTab === 'home'" class="tab-content">
                    <!-- Post Creator (only for users with create_posts permission) -->
                    <Post
                        v-if="permissions.can_create_post"
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
                                :can-edit="permissions.can_edit_post"
                                :can-delete="permissions.can_delete_post"
                                @updated="handlePostUpdated"
                                @deleted="handlePostDeleted"
                            />
                        </template>

                        <!-- Load More Button -->
                        <div v-if="hasMore && !isLoading" class="load-more-container">
                            <button @click="loadMorePosts" class="btn-load-more">
                                <i class="bx bx-refresh mr-2"></i>Tải thêm bài viết
                            </button>
                        </div>
                        
                        <!-- Loading Indicator -->
                        <div v-if="isLoading" class="text-center py-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p class="text-muted mt-2 small">Đang tải thêm bài viết...</p>
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
                    <div class="modal-header-content">
                        <i class="bx bx-shield-quarter modal-icon"></i>
                        <h3>Quản lý phân quyền trang</h3>
                    </div>
                    <button @click="closeAdminModal" class="modal-close">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="admin-form-card">
                        <div class="form-card-header">
                            <i class="bx bx-user-plus"></i>
                            <h4>Thêm quản trị viên mới</h4>
                        </div>
                        <div class="admin-form">
                            <div class="form-group">
                                <label><i class="bx bx-id-card"></i> User ID</label>
                                <input v-model="newAdminId" type="number" class="form-control" placeholder="Nhập ID người dùng" />
                            </div>
                            <div class="form-group">
                                <label><i class="bx bx-badge-check"></i> Vai trò</label>
                                <select v-model="newAdminRole" class="form-control">
                                    <option v-for="role in roleOptions" :key="role" :value="role">
                                        {{ getRoleLabel(role) }}
                                    </option>
                                </select>
                            </div>
                            <button class="btn btn-primary btn-add-admin" :disabled="adminLoading" @click="addAdmin">
                                <i class="bx bx-plus-circle"></i>
                                {{ adminLoading ? 'Đang lưu...' : 'Thêm quản trị viên' }}
                            </button>
                        </div>
                        <div v-if="adminError" class="alert-error">
                            <i class="bx bx-error-circle"></i>
                            {{ adminError }}
                        </div>
                    </div>

                    <div class="admin-list">
                        <div class="list-header">
                            <div>
                                <h4>Danh sách quản trị viên</h4>
                                <p class="admin-count">{{ admins.length }} người</p>
                            </div>
                        </div>
                        <div v-if="!admins.length" class="empty-admin-state">
                            <i class="bx bx-user-x"></i>
                            <p>Chưa có quản trị viên nào</p>
                            <small>Thêm người dùng vào danh sách quản trị để bắt đầu</small>
                        </div>
                        <div v-else class="admin-items">
                            <div v-for="admin in admins" :key="admin.id" class="admin-item">
                                <div class="admin-info">
                                    <div class="admin-avatar-wrapper">
                                        <img 
                                            :src="admin.avatar || '/images/web/users/avatar.jpg'" 
                                            class="admin-avatar"
                                            @error="e => e.target.src = '/images/web/users/avatar.jpg'"
                                        />
                                        <div class="avatar-badge">
                                            <i class="bx bx-shield-alt-2"></i>
                                        </div>
                                    </div>
                                    <div class="admin-details">
                                        <div class="admin-name">{{ admin.name }}</div>
                                        <div class="admin-id">ID: {{ admin.id }}</div>
                                    </div>
                                </div>
                                <div class="admin-actions">
                                    <select
                                        class="role-select"
                                        v-model="admin.pivot.role"
                                        @change="updateAdminRole(admin)"
                                    >
                                        <option v-for="role in roleOptions" :key="role" :value="role">
                                            {{ getRoleLabel(role) }}
                                        </option>
                                    </select>
                                    <button class="btn-remove" @click="removeAdmin(admin)" title="Xóa quản trị viên">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { router } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';
import PageHeader from '../../Components/Pages/PageHeader.vue';
import PageNavigationTabs from '../../Components/Pages/PageNavigationTabs.vue';
import Post from '../../Components/Post.vue';
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
    adminRole: {
        type: String,
        default: null
    },
    permissions: {
        type: Object,
        default: () => ({
            can_create_post: false,
            can_edit_post: false,
            can_delete_post: false,
            can_manage_comments: false,
            can_view_insights: false,
            can_manage_admins: false,
            can_edit_page: false,
            can_delete_page: false,
        })
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

console.log(props);

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
const roleOptions = ['admin', 'editor', 'analyst'];
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

const defaultPageAvatar = '/images/client/pages/default-page.png';

const resolvePostAuthor = (post) => {
    if (post.page_id && post.page_id === props.page.id) {
        return {
            id: props.page.id,
            name: props.page.name,
            avatar: props.page.profile_picture_url || defaultPageAvatar,
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
        // Navigate to insights page with scroll preservation
        router.visit(`/pages/${props.page.id}/insights`, {
            preserveScroll: true
        });
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
    // Reload page data with scroll preservation
    router.reload({ 
        only: ['page'],
        preserveScroll: true 
    });
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

        router.reload({ 
            only: ['page'],
            preserveScroll: true 
        });
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

const goToInsights = () => {
    router.visit(`/pages/${props.page.id}/insights`);
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

const getRoleLabel = (role) => {
    const labels = {
        'admin': '👑 Quản trị viên',
        'editor': '✏️ Biên tập viên',
        'moderator': '🛡️ Người kiểm duyệt',
        'analyst': '📊 Phân tích viên',
        'advertiser': '📢 Quảng cáo viên'
    };
    return labels[role] || role;
};

const loadMorePosts = async () => {
    if (isLoading.value || !hasMore.value) return;

    // Save current scroll position
    const scrollPosition = window.scrollY;
    const scrollHeight = document.documentElement.scrollHeight;

    isLoading.value = true;
    try {
        const response = await axios.get(`/pages/${props.page.id}/more_posts`, {
            params: { page: Math.floor(posts.value.length / 10) + 1 }
        });

        posts.value.push(...response.data.data);
        hasMore.value = response.data.next_page_url ? true : false;

        // Restore scroll position after content loads
        await nextTick();
        const newScrollHeight = document.documentElement.scrollHeight;
        const heightDifference = newScrollHeight - scrollHeight;
        
        // Keep scroll position relative to the new content
        if (heightDifference > 0) {
            window.scrollTo({
                top: scrollPosition,
                behavior: 'instant'
            });
        }
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
    margin-bottom: 20px;
}

.btn-load-more {
    padding: 12px 32px;
    background: linear-gradient(135deg, #1877f2 0%, #0d65d9 100%);
    color: #fff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(24, 119, 242, 0.25);
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-load-more:hover {
    background: linear-gradient(135deg, #0d65d9 0%, #0a58ca 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(24, 119, 242, 0.35);
}

.btn-load-more:active {
    transform: translateY(0);
}

.btn-load-more i {
    font-size: 18px;
}

.spinner-border {
    width: 2rem;
    height: 2rem;
    border-width: 0.25em;
}

.visually-hidden {
    position: absolute;
    width: 1px;
    height: 1px;
    padding: 0;
    margin: -1px;
    overflow: hidden;
    clip: rect(0, 0, 0, 0);
    white-space: nowrap;
    border-width: 0;
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
    gap: 10px;
    margin-top: 10px;
}

.btn-manage-admins {
    background: #2563eb;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.btn-manage-admins:hover {
    background: #1d4ed8;
}

.btn-insights {
    background: #7c3aed;
    color: #fff;
    border: none;
    padding: 8px 14px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.btn-insights:hover {
    background: #6d28d9;
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

.admin-form-card {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 24px;
    box-shadow: 0 8px 24px rgba(102, 126, 234, 0.2);
}

.form-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #fff;
    margin-bottom: 20px;
}

.form-card-header i {
    font-size: 28px;
}

.form-card-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.admin-form {
    display: grid;
    grid-template-columns: 1fr 1fr auto;
    gap: 12px;
    align-items: end;
}

.admin-form .form-group {
    margin-bottom: 0;
}

.admin-form label {
    color: #fff;
    font-weight: 600;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 14px;
}

.admin-form label i {
    font-size: 16px;
}

.admin-form .form-control {
    background: rgba(255, 255, 255, 0.95);
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.admin-form .form-control:focus {
    background: #fff;
    border-color: #fbbf24;
    box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.1);
}

.btn-add-admin {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #fbbf24;
    color: #000;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.btn-add-admin:hover:not(:disabled) {
    background: #f59e0b;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(251, 191, 36, 0.4);
}

.btn-add-admin:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-add-admin i {
    font-size: 18px;
}

.alert-error {
    margin-top: 16px;
    padding: 12px 16px;
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.3);
    border-radius: 8px;
    color: #fff;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.alert-error i {
    font-size: 20px;
    color: #fca5a5;
}

.admin-list {
    background: #fff;
    border-radius: 12px;
    padding: 20px;
}

.list-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f2f5;
}

.list-header h4 {
    margin: 0;
    font-size: 18px;
    font-weight: 700;
    color: #050505;
}

.admin-count {
    margin: 4px 0 0 0;
    font-size: 13px;
    color: #65676b;
}

.empty-admin-state {
    text-align: center;
    padding: 60px 20px;
    color: #65676b;
}

.empty-admin-state i {
    font-size: 64px;
    color: #d0d3d9;
    margin-bottom: 16px;
}

.empty-admin-state p {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 8px 0;
}

.empty-admin-state small {
    color: #8a8d91;
    font-size: 14px;
}

.admin-items {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.admin-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.admin-item:hover {
    background: #fff;
    border-color: #667eea;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
    transform: translateY(-2px);
}

.admin-info {
    display: flex;
    align-items: center;
    gap: 16px;
    flex: 1;
}

.admin-avatar-wrapper {
    position: relative;
}

.admin-avatar {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.admin-item:hover .admin-avatar {
    transform: scale(1.05);
    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
}

.avatar-badge {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 24px;
    height: 24px;
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #fff;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

.avatar-badge i {
    color: #fff;
    font-size: 12px;
}

.admin-details {
    flex: 1;
}

.admin-name {
    font-size: 16px;
    font-weight: 700;
    color: #050505;
    margin-bottom: 4px;
}

.admin-id {
    font-size: 13px;
    color: #65676b;
}

.admin-actions {
    display: flex;
    align-items: center;
    gap: 12px;
}

.role-select {
    padding: 8px 32px 8px 12px;
    border: 2px solid #e4e6eb;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    color: #050505;
    background: #fff;
    cursor: pointer;
    transition: all 0.3s ease;
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23050505' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 10px center;
}

.role-select:hover {
    border-color: #667eea;
}

.role-select:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.btn-remove {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    border: 2px solid #fee2e2;
    background: #fef2f2;
    color: #dc2626;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-remove:hover {
    background: #dc2626;
    color: #fff;
    border-color: #dc2626;
    transform: scale(1.05);
}

.btn-remove i {
    font-size: 20px;
}

@media (max-width: 768px) {
    .admin-form {
        grid-template-columns: 1fr;
    }
    
    .btn-add-admin {
        width: 100%;
        justify-content: center;
    }
    
    .admin-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 16px;
    }
    
    .admin-actions {
        width: 100%;
        justify-content: space-between;
    }
    
    .role-select {
        flex: 1;
    }
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 2px solid #f0f2f5;
    background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
}

.modal-header-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.modal-icon {
    font-size: 28px;
    color: #667eea;
}

.modal-header h3 {
    font-size: 20px;
    font-weight: 700;
    margin: 0;
    color: #050505;
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
