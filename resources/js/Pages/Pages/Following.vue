<template>
    <App>
        <div class="pages-management-container">
            <!-- Header Section -->
            <div class="pages-header">
                <div class="pages-header-content">
                    <div>
                        <h1 class="pages-title">
                            <i class="bx bx-bookmark-heart"></i>
                            Trang đang theo dõi
                        </h1>
                        <p class="pages-subtitle">
                            Danh sách các trang bạn đã theo dõi nhưng không phải là người tạo
                        </p>
                    </div>
                    <Link
                        :href="route('pages.index')"
                        class="btn-create-page"
                    >
                        <i class="bx bx-store"></i>
                        Quản lý trang của bạn
                    </Link>
                </div>
            </div>

            <!-- Pages Grid -->
            <div class="pages-content">
                <!-- Empty State -->
                <div v-if="!pages.data || pages.data.length === 0" class="pages-empty">
                    <div class="empty-icon">
                        <i class="bx bx-bookmark-heart"></i>
                    </div>
                    <h3>Bạn chưa theo dõi trang nào</h3>
                    <p>Hãy tìm và theo dõi những trang bạn quan tâm để nhận được nhiều nội dung hơn.</p>
                    <Link href="/search" class="btn-create-first">
                        <i class="bx bx-search"></i>
                        Tìm kiếm trang
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
                                    <span>{{ formatNumber(page.follower_count ?? page.followers_count ?? 0) }}</span>
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
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="pages.data && pages.data.length > 0 && (pages.prev_page_url || pages.next_page_url)"
                    class="pages-pagination"
                >
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
    </App>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import App from '../../Layouts/App.vue';

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

const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};

const formatNumber = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const route = (name, params = null) => {
    const routes = {
        'pages.index': '/pages/index',
        'pages.show': (id) => `/pages/${id}`,
    };

    if (typeof routes[name] === 'function') {
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
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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

.pages-content {
    min-height: 400px;
}

.pages-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 4rem 2rem;
    text-align: center;
}

.pages-empty .empty-icon {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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
    justify-content: center;
    gap: 1rem;
}

.btn-page-view {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
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

@media (max-width: 768px) {
    .pages-grid {
        grid-template-columns: 1fr;
    }

    .pages-header-content {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>


