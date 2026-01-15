<template>
    <App>
        <div class="pages-management-container">
            <!-- Header Section -->
            <div class="pages-header">
                <div class="pages-header-content">
                    <div>
                        <h1 class="pages-title">
                            <i class="bx bx-store"></i>
                            Quản lý Trang
                        </h1>
                        <p class="pages-subtitle">Quản lý các trang bạn đã tạo hoặc là admin</p>
                    </div>
                    <Link
                        :href="route('pages.create')"
                        class="btn-create-page"
                    >
                        <i class="bx bx-plus"></i>
                        Tạo trang mới
                    </Link>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="pages-stats" v-if="pages.data && pages.data.length > 0">
                <div class="stat-card">
                    <div class="stat-icon stat-icon-primary">
                        <i class="bx bx-store"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ pages.total || 0 }}</p>
                        <p class="stat-label">Tổng số trang</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-success">
                        <i class="bx bx-user-plus"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ totalFollowers }}</p>
                        <p class="stat-label">Tổng người theo dõi</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon stat-icon-info">
                        <i class="bx bx-file"></i>
                    </div>
                    <div class="stat-info">
                        <p class="stat-value">{{ totalPosts }}</p>
                        <p class="stat-label">Tổng bài đăng</p>
                    </div>
                </div>
            </div>

            <!-- Pages Grid -->
            <div class="pages-content">
                <!-- Loading State -->
                <div v-if="isLoading" class="pages-loading">
                    <div class="spinner"></div>
                    <p>Đang tải...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="!pages.data || pages.data.length === 0" class="pages-empty">
                    <div class="empty-icon">
                        <i class="bx bx-store"></i>
                    </div>
                    <h3>Chưa có trang nào</h3>
                    <p>Tạo trang đầu tiên của bạn để bắt đầu kết nối với người theo dõi</p>
                    <Link
                        :href="route('pages.create')"
                        class="btn-create-first"
                    >
                        <i class="bx bx-plus"></i>
                        Tạo trang mới
                    </Link>
                </div>

                <!-- Pages Grid -->
                <div v-else class="pages-grid">
                    <div
                        v-for="page in pages.data"
                        :key="page.id"
                        class="page-card"
                    >
                        <!-- Cover Photo -->
                        <div class="page-card-cover">
                            <img
                                v-if="resolveCoverUrl(page)"
                                :src="resolveCoverUrl(page)"
                                :alt="page.name"
                            />
                            <div v-else class="cover-placeholder"></div>
                            <div class="page-card-overlay">
                                <div class="page-card-actions">
                                    <Link
                                        :href="route('pages.show', page.username || page.id)"
                                        class="action-btn action-view"
                                        title="Xem trang"
                                    >
                                        <i class="bx bx-show"></i>
                                    </Link>
                                    <Link
                                        v-if="isCreator(page)"
                                        :href="route('pages.show', page.username || page.id)"
                                        class="action-btn action-edit"
                                        title="Chỉnh sửa"
                                    >
                                        <i class="bx bx-edit"></i>
                                    </Link>
                                    <Link
                                        v-if="isAdmin(page)"
                                        :href="route('pages.insights', page.id)"
                                        class="action-btn action-insights"
                                        title="Thống kê"
                                    >
                                        <i class="bx bx-bar-chart-alt-2"></i>
                                    </Link>
                                    <button
                                        v-if="isCreator(page)"
                                        @click="confirmDelete(page)"
                                        class="action-btn action-delete"
                                        title="Xóa trang"
                                    >
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Page Info -->
                        <div class="page-card-body">
                            <div class="page-card-avatar">
                                <img
                                    v-if="resolveAvatarUrl(page)"
                                    :src="resolveAvatarUrl(page)"
                                    :alt="page.name"
                                />
                                <div v-else class="avatar-placeholder">
                                    <i class="bx bx-store"></i>
                                </div>
                            </div>

                            <div class="page-card-info">
                                <h3 class="page-card-name">
                                    {{ page.name }}
                                    <i v-if="page.verified" class="bx bx-check-circle verified-badge" title="Đã xác minh"></i>
                                </h3>
                                <p v-if="page.username" class="page-card-username">@{{ page.username }}</p>
                                <p v-if="page.category" class="page-card-category">{{ page.category }}</p>
                                <p v-if="page.description" class="page-card-description">
                                    {{ truncateText(page.description, 100) }}
                                </p>
                            </div>

                            <div class="page-card-stats">
                                <div class="page-stat-item">
                                    <i class="bx bx-user"></i>
                                    <span>{{ formatNumber(page.follower_count || 0) }}</span>
                                    <small>Người theo dõi</small>
                                </div>
                                <div class="page-stat-item">
                                    <i class="bx bx-file"></i>
                                    <span>{{ formatNumber(page.posts_count || 0) }}</span>
                                    <small>Bài đăng</small>
                                </div>
                            </div>

                            <div class="page-card-footer">
                                <Link
                                    :href="route('pages.show', page.username || page.id)"
                                    class="btn-page-view"
                                >
                                    <i class="bx bx-show"></i>
                                    Xem trang
                                </Link>
                                <div class="page-card-badges">
                                    <span v-if="isCreator(page)" class="badge badge-creator">
                                        <i class="bx bx-crown"></i>
                                        Người tạo
                                    </span>
                                    <span v-else-if="isAdmin(page)" class="badge badge-admin">
                                        <i class="bx bx-shield"></i>
                                        Admin
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="pages.data && pages.data.length > 0 && (pages.prev_page_url || pages.next_page_url)" class="pages-pagination">
                    <Link
                        v-if="pages.prev_page_url"
                        :href="pages.prev_page_url"
                        class="pagination-btn"
                    >
                        <i class="bx bx-chevron-left"></i>
                        Trước
                    </Link>
                    <span class="pagination-info">
                        Trang {{ pages.current_page }} / {{ pages.last_page }}
                    </span>
                    <Link
                        v-if="pages.next_page_url"
                        :href="pages.next_page_url"
                        class="pagination-btn"
                    >
                        Sau
                        <i class="bx bx-chevron-right"></i>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div v-if="showDeleteModal" class="delete-modal-overlay" @click="closeDeleteModal">
            <div class="delete-modal" @click.stop>
                <div class="delete-modal-header">
                    <h3>Xác nhận xóa trang</h3>
                    <button class="delete-modal-close" @click="closeDeleteModal">
                        <i class="bx bx-x"></i>
                    </button>
                </div>
                <div class="delete-modal-body">
                    <div class="delete-warning">
                        <i class="bx bx-error-circle"></i>
                        <p>Bạn có chắc chắn muốn xóa trang <strong>"{{ pageToDelete?.name }}"</strong>?</p>
                    </div>
                    <p class="delete-warning-text">
                        Hành động này không thể hoàn tác. Tất cả dữ liệu của trang sẽ bị xóa vĩnh viễn.
                    </p>
                </div>
                <div class="delete-modal-footer">
                    <button class="btn-cancel" @click="closeDeleteModal">
                        Hủy
                    </button>
                    <button class="btn-delete-confirm" @click="deletePage" :disabled="isDeleting">
                        <span v-if="isDeleting">
                            <i class="bx bx-loader-alt bx-spin"></i>
                            Đang xóa...
                        </span>
                        <span v-else>
                            <i class="bx bx-trash"></i>
                            Xóa trang
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';
import axios from 'axios';

const page = usePage();
const props = defineProps({
    pages: {
        type: Object,
        required: true
    },
    currentUser: {
        type: Object,
        required: true
    }
});

const isLoading = ref(false);
const showDeleteModal = ref(false);
const pageToDelete = ref(null);
const isDeleting = ref(false);

const totalFollowers = computed(() => {
    if (!props.pages.data) return 0;
    return props.pages.data.reduce((sum, page) => sum + (page.follower_count || 0), 0);
});

const totalPosts = computed(() => {
    if (!props.pages.data) return 0;
    return props.pages.data.reduce((sum, page) => sum + (page.posts_count || 0), 0);
});

const sanitizePath = (path) => {
    if (!path) return null;
    if (path.startsWith('http')) return path;

    let normalized = path;
    normalized = normalized.replace(/^storage\/(app\/)?public\//i, '');
    normalized = normalized.replace(/^storage\//i, '');
    normalized = normalized.replace(/^\/+/g, '');

    return `/${normalized}`;
};

const resolveCoverUrl = (page) => {
    return sanitizePath(page?.cover_photo_url);
};

const resolveAvatarUrl = (page) => {
    return sanitizePath(page?.profile_picture_url);
};

const isCreator = (page) => {
    return page.creator_id === props.currentUser.id;
};

const isAdmin = (page) => {
    if (isCreator(page)) return true;
    // Kiểm tra xem user có trong danh sách admins không
    return page.admins && page.admins.some(admin => admin.id === props.currentUser.id);
};

const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};

const formatNumber = (num) => {
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const confirmDelete = (page) => {
    pageToDelete.value = page;
    showDeleteModal.value = true;
};

const closeDeleteModal = () => {
    showDeleteModal.value = false;
    pageToDelete.value = null;
};

const deletePage = async () => {
    if (!pageToDelete.value || isDeleting.value) return;

    isDeleting.value = true;
    try {
        await axios.delete(route('pages.destroy', pageToDelete.value.id));
        router.reload({ only: ['pages'] });
        closeDeleteModal();
    } catch (error) {
        console.error('Error deleting page:', error);
        alert('Không thể xóa trang: ' + (error.response?.data?.message || error.message));
    } finally {
        isDeleting.value = false;
    }
};

const route = (name, params = null) => {
    const routes = {
        'pages.index': '/pages',
        'pages.create': '/pages/create',
        'pages.show': (id) => `/pages/${id}`, // Dùng backtick cho đồng bộ
        'pages.insights': (id) => `/pages/${id}/insights`, // Đã sửa lỗi cú pháp ở đây
        'pages.destroy': (id) => `/pages/${id}`,
    };

    if (typeof routes[name] === 'function') {
        // Kiểm tra xem params có tồn tại không trước khi gọi hàm
        return params ? routes[name](params) : '#';
    }
    
    return routes[name] || '#';
};
</script>

<style scoped>
.pages-management-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem 1.5rem;
    font-family: 'Inter', sans-serif;
}

.pages-header {
    margin-bottom: 2rem;
}

.pages-header-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1.5rem;
}

.pages-title {
    margin: 0 0 0.5rem;
    font-size: 2rem;
    font-weight: 700;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.pages-title i {
    font-size: 2rem;
    color: #667eea;
}

.pages-subtitle {
    margin: 0;
    font-size: 1rem;
    color: #6b7280;
}

.btn-create-page {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-create-page:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.pages-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.5rem;
    background: white;
    border-radius: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
}

.stat-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.stat-icon-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.stat-icon-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-icon-info {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
}

.stat-info {
    flex: 1;
}

.stat-value {
    margin: 0 0 0.25rem;
    font-size: 1.75rem;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
}

.pages-content {
    min-height: 400px;
}

.pages-loading,
.pages-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
}

.pages-loading .spinner {
    width: 48px;
    height: 48px;
    border: 4px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.pages-empty .empty-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 4rem;
    color: white;
    margin-bottom: 1.5rem;
}

.pages-empty h3 {
    margin: 0 0 0.5rem;
    font-size: 1.5rem;
    color: #111827;
}

.pages-empty p {
    margin: 0 0 1.5rem;
    color: #6b7280;
    max-width: 400px;
}

.btn-create-first {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.875rem 2rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 12px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
}

.btn-create-first:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
}

.pages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
}

.page-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border: 1px solid #e5e7eb;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.page-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.12);
}

.page-card-cover {
    position: relative;
    height: 160px;
    overflow: hidden;
}

.page-card-cover img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.cover-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.page-card-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.page-card:hover .page-card-overlay {
    opacity: 1;
}

.page-card-actions {
    display: flex;
    gap: 0.75rem;
}

.action-btn {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.95);
    color: #111827;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.25rem;
    text-decoration: none;
}

.action-btn:hover {
    transform: scale(1.1);
    background: white;
}

.action-view {
    color: #3b82f6;
}

.action-edit {
    color: #10b981;
}

.action-insights {
    color: #f59e0b;
}

.action-delete {
    color: #ef4444;
}

.page-card-body {
    padding: 1.5rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.page-card-avatar {
    margin-top: -60px;
    margin-bottom: 1rem;
    position: relative;
    z-index: 1;
}

.page-card-avatar img {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid white;
    object-fit: cover;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.avatar-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 4px solid white;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 2rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.page-card-info {
    flex: 1;
    margin-bottom: 1rem;
}

.page-card-name {
    margin: 0 0 0.25rem;
    font-size: 1.25rem;
    font-weight: 700;
    color: #111827;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.verified-badge {
    color: #3b82f6;
    font-size: 1.125rem;
}

.page-card-username {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
    color: #6b7280;
}

.page-card-category {
    margin: 0 0 0.5rem;
    font-size: 0.875rem;
    color: #667eea;
    font-weight: 500;
}

.page-card-description {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.5;
}

.page-card-stats {
    display: flex;
    gap: 1.5rem;
    padding: 1rem 0;
    border-top: 1px solid #f3f4f6;
    border-bottom: 1px solid #f3f4f6;
    margin-bottom: 1rem;
}

.page-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.25rem;
    flex: 1;
}

.page-stat-item i {
    font-size: 1.25rem;
    color: #667eea;
}

.page-stat-item span {
    font-size: 1.125rem;
    font-weight: 700;
    color: #111827;
}

.page-stat-item small {
    font-size: 0.75rem;
    color: #6b7280;
}

.page-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}

.btn-page-view {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.875rem;
}

.btn-page-view:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.page-card-badges {
    display: flex;
    gap: 0.5rem;
}

.badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-creator {
    background: #fef3c7;
    color: #92400e;
}

.badge-admin {
    background: #dbeafe;
    color: #1e40af;
}

.pages-pagination {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 1rem;
    margin-top: 2rem;
    padding: 1.5rem;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: white;
    color: #667eea;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-btn:hover {
    background: #f9fafb;
    border-color: #667eea;
}

.pagination-info {
    color: #6b7280;
    font-weight: 500;
}

/* Delete Modal */
.delete-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.delete-modal {
    background: white;
    border-radius: 20px;
    width: min(500px, 90vw);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    overflow: hidden;
}

.delete-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
}

.delete-modal-header h3 {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.delete-modal-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}

.delete-modal-close:hover {
    background: #e5e7eb;
}

.delete-modal-body {
    padding: 1.5rem;
}

.delete-warning {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1rem;
}

.delete-warning i {
    font-size: 2rem;
    color: #ef4444;
    flex-shrink: 0;
}

.delete-warning p {
    margin: 0;
    color: #111827;
    line-height: 1.6;
}

.delete-warning-text {
    margin: 0;
    font-size: 0.875rem;
    color: #6b7280;
    line-height: 1.6;
}

.delete-modal-footer {
    display: flex;
    gap: 0.75rem;
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    justify-content: flex-end;
}

.btn-cancel {
    padding: 0.625rem 1.5rem;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    border-radius: 10px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-cancel:hover {
    background: #f9fafb;
}

.btn-delete-confirm {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-delete-confirm:hover:not(:disabled) {
    background: #dc2626;
}

.btn-delete-confirm:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .pages-grid {
        grid-template-columns: 1fr;
    }

    .pages-header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .pages-stats {
        grid-template-columns: 1fr;
    }
}
</style>
