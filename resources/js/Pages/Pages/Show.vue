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
                @tab-changed="handleTabChange"
            />

            <!-- Content Area -->
            <div class="page-content">
                <!-- Home Tab -->
                <div v-if="activeTab === 'home'" class="tab-content">
                    <!-- Post Creator (only for admins) -->
                    <PagePostCreator
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
                                :postData="post"
                                :userData="post.user || currentUser"
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

                <!-- Insights Tab (Admin only) - Navigate to separate page -->
                <div v-if="activeTab === 'insights' && isAdmin" class="tab-content">
                    <div class="insights-redirect">
                        <p>Đang chuyển đến trang Phân tích...</p>
                    </div>
                </div>

                <!-- Other tabs placeholder -->
                <div v-else-if="activeTab !== 'home' && activeTab !== 'about' && activeTab !== 'insights'" class="tab-content">
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
                    <!-- Edit form will go here -->
                    <p>Form chỉnh sửa trang</p>
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
import PagePostCreator from '../../Components/Pages/PagePostCreator.vue';
import PageInsights from '../../Components/Pages/PageInsights.vue';
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
    }
});

const activeTab = ref('home');
const showEditModal = ref(false);
const isLoading = ref(false);
const posts = ref(props.posts.data || []);
const hasMore = ref(props.posts.next_page_url ? true : false);
const insights = ref(null);
const isFollowing = ref(props.isFollowing);

const handleTabChange = (tab) => {
    if (tab === 'insights' && props.isAdmin) {
        // Navigate to insights page
        router.visit(`/pages/${props.page.id}/insights`);
        return;
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

const openEditModal = () => {
    showEditModal.value = true;
};

const closeEditModal = () => {
    showEditModal.value = false;
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

@media (max-width: 768px) {
    .page-detail-container {
        padding: 10px;
    }

    .posts-feed {
        max-width: 100%;
    }
}
</style>

