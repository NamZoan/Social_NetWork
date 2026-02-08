<template>
    <div class="insights-container">
        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <!-- Total Posts -->
            <div class="metric-card">
                <div class="metric-icon posts">
                    <i class="bx bx-file"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Tổng bài viết</div>
                    <div class="metric-value">{{ insights.total_posts || 0 }}</div>
                </div>
            </div>

            <!-- Total Reactions -->
            <div class="metric-card">
                <div class="metric-icon reactions">
                    <i class="bx bxs-like"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Tổng lượt yêu thích</div>
                    <div class="metric-value">{{ formatNumber(insights.total_likes || 0) }}</div>
                </div>
            </div>

            <!-- Total Comments -->
            <div class="metric-card">
                <div class="metric-icon comments">
                    <i class="bx bx-message-rounded"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Tổng bình luận</div>
                    <div class="metric-value">{{ formatNumber(insights.total_comments || 0) }}</div>
                </div>
            </div>

            <!-- Total Shares -->
            <div class="metric-card">
                <div class="metric-icon shares">
                    <i class="bx bx-share-alt"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Tổng chia sẻ</div>
                    <div class="metric-value">{{ formatNumber(insights.total_shares || 0) }}</div>
                </div>
            </div>

            <!-- Engagement Rate -->
            <div class="metric-card">
                <div class="metric-icon engagement">
                    <i class="bx bx-trending-up"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Tỉ lệ tương tác</div>
                    <div class="metric-value">{{ insights.engagement_rate || 0 }}%</div>
                </div>
            </div>

            <!-- Followers -->
            <div class="metric-card">
                <div class="metric-icon followers">
                    <i class="bx bx-user-plus"></i>
                </div>
                <div class="metric-content">
                    <div class="metric-label">Người theo dõi</div>
                    <div class="metric-value">{{ formatNumber(page.follower_count || 0) }}</div>
                </div>
            </div>
        </div>

        <!-- Top Posts Section -->
        <div class="top-posts-section">
            <h3 class="section-title">Bài viết được yêu thích nhất</h3>
            <div v-if="insights.top_posts && insights.top_posts.length > 0" class="top-posts-list">
                <div v-for="(post, index) in insights.top_posts" :key="post.id" class="top-post-item">
                    <div class="rank-badge">{{ index + 1 }}</div>
                    <div class="post-content">
                        <p class="post-text">{{ truncateText(post.content, 100) }}</p>
                        <div class="post-stats">
                            <span class="stat">
                                <i class="bx bxs-like"></i>
                                {{ post.likes_count || 0 }}
                            </span>
                            <span class="stat">
                                <i class="bx bx-message-rounded"></i>
                                {{ post.comments_count || 0 }}
                            </span>
                            <span class="stat">
                                <i class="bx bx-share-alt"></i>
                                {{ post.shares_count || 0 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="empty-state">
                <p>Chưa có dữ liệu bài viết</p>
            </div>
        </div>

        <!-- Activity Period Stats -->
        <div class="activity-section" v-if="insights.metrics">
            <h3 class="section-title">Thống kê hoạt động (30 ngày gần đây)</h3>
            <div class="activity-stats">
                <div class="activity-card">
                    <span class="activity-label">Bài viết trong kỳ:</span>
                    <span class="activity-value">{{ insights.metrics.posts_last_period || 0 }}</span>
                </div>
                <div class="activity-card">
                    <span class="activity-label">Tương tác trung bình:</span>
                    <span class="activity-value">{{ formatNumber(insights.metrics.avg_engagement || 0) }}</span>
                </div>
                <div class="activity-card">
                    <span class="activity-label">Bài viết tốt nhất:</span>
                    <span class="activity-value">{{ insights.metrics.best_performing_post || 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';

const props = defineProps({
    page: {
        type: Object,
        required: true
    },
    insights: {
        type: Object,
        default: () => ({})
    }
});

const formatNumber = (num) => {
    if (!num) return '0';
    if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
    if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
    return num.toString();
};

const truncateText = (text, maxLength) => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
};
</script>

<style scoped>
.insights-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem 1rem;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
    margin-bottom: 2rem;
}

.metric-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 20px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.metric-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    transform: translateY(-2px);
}

.metric-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: white;
}

.metric-icon.posts {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
}

.metric-icon.reactions {
    background: linear-gradient(135deg, #f472b6 0%, #ec4899 100%);
}

.metric-icon.comments {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
}

.metric-icon.shares {
    background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
}

.metric-icon.engagement {
    background: linear-gradient(135deg, #a78bfa 0%, #8b5cf6 100%);
}

.metric-icon.followers {
    background: linear-gradient(135deg, #fb7185 0%, #f43f5e 100%);
}

.metric-content {
    flex: 1;
}

.metric-label {
    font-size: 13px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 4px;
}

.metric-value {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
}

.top-posts-section,
.activity-section {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 2rem;
}

.section-title {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 16px;
}

.top-posts-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.top-post-item {
    display: flex;
    gap: 16px;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.top-post-item:hover {
    background: #f3f4f6;
}

.rank-badge {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.post-content {
    flex: 1;
}

.post-text {
    margin: 0 0 8px;
    color: #111827;
    line-height: 1.5;
}

.post-stats {
    display: flex;
    gap: 16px;
}

.stat {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 13px;
    color: #6b7280;
}

.stat i {
    font-size: 14px;
}

.activity-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
}

.activity-card {
    display: flex;
    flex-direction: column;
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
}

.activity-label {
    font-size: 13px;
    color: #6b7280;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.activity-value {
    font-size: 24px;
    font-weight: 700;
    color: #667eea;
}

.empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #6b7280;
}
</style>



