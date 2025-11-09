<template>
    <div class="page-insights">
        <!-- Loading State -->
        <div v-if="isLoading" class="insights-loading">
            <div class="skeleton-loader" v-for="i in 4" :key="i"></div>
        </div>

        <!-- Insights Content -->
        <div v-else class="insights-content">
            <!-- Time Period Selector -->
            <div class="time-period-selector">
                <select v-model="selectedPeriod" @change="loadInsights" class="period-select">
                    <option value="7">7 ngày qua</option>
                    <option value="30">30 ngày qua</option>
                    <option value="90">90 ngày qua</option>
                </select>
            </div>

            <!-- Key Metrics Cards -->
            <div class="metrics-grid">
                <div class="metric-card" v-for="metric in keyMetrics" :key="metric.key">
                    <div class="metric-icon" :style="{ background: metric.color }">
                        <i :class="metric.icon"></i>
                    </div>
                    <div class="metric-info">
                        <div class="metric-value">{{ formatNumber(metric.value) }}</div>
                        <div class="metric-label">{{ metric.label }}</div>
                        <div class="metric-change" :class="metric.change >= 0 ? 'positive' : 'negative'">
                            <i :class="metric.change >= 0 ? 'bx bx-up-arrow-alt' : 'bx bx-down-arrow-alt'"></i>
                            {{ Math.abs(metric.change) }}%
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="charts-section">
                <!-- Followers Growth Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">Tăng trưởng người theo dõi</h3>
                    <div class="chart-container">
                        <canvas ref="followersChart"></canvas>
                    </div>
                </div>

                <!-- Engagement Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">Lượt tương tác</h3>
                    <div class="chart-container">
                        <canvas ref="engagementChart"></canvas>
                    </div>
                </div>

                <!-- Post Reach Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">Lượt tiếp cận bài đăng</h3>
                    <div class="chart-container">
                        <canvas ref="reachChart"></canvas>
                    </div>
                </div>

                <!-- Demographics Chart -->
                <div class="chart-card">
                    <h3 class="chart-title">Phân bố người theo dõi</h3>
                    <div class="chart-container">
                        <canvas ref="demographicsChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Posts -->
            <div class="top-posts-section">
                <h3 class="section-title">Bài đăng phổ biến</h3>
                <div class="posts-grid">
                    <div
                        v-for="post in topPosts"
                        :key="post.id"
                        class="post-card"
                        @click="viewPost(post)"
                    >
                        <div class="post-image">
                            <img
                                v-if="post.media && post.media[0]"
                                :src="getImageUrl(post.media[0].url)"
                                :alt="post.content"
                            />
                            <div v-else class="post-placeholder">
                                <i class="bx bx-file"></i>
                            </div>
                        </div>
                        <div class="post-stats">
                            <div class="stat-item">
                                <i class="bx bx-like"></i>
                                <span>{{ formatNumber(post.likes_count || 0) }}</span>
                            </div>
                            <div class="stat-item">
                                <i class="bx bx-message"></i>
                                <span>{{ formatNumber(post.comments_count || 0) }}</span>
                            </div>
                            <div class="stat-item">
                                <i class="bx bx-share-alt"></i>
                                <span>{{ formatNumber(post.shares_count || 0) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

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

const isLoading = ref(false);
const selectedPeriod = ref(30);
const keyMetrics = ref([]);
const topPosts = ref([]);
const followersChart = ref(null);
const engagementChart = ref(null);
const reachChart = ref(null);
const demographicsChart = ref(null);

const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M';
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K';
    }
    return num.toString();
};

const getImageUrl = (url) => {
    if (!url) return '/images/placeholder.jpg';
    if (url.startsWith('http')) return url;
    return `/storage/${url}`;
};

const loadInsights = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/pages/${props.page.id}/insights`, {
            params: { period: selectedPeriod.value }
        });
        
        keyMetrics.value = response.data.metrics || [];
        topPosts.value = response.data.top_posts || [];
        
        // TODO: Initialize charts with Chart.js
        // initializeCharts(response.data.chart_data);
    } catch (error) {
        console.error('Error loading insights:', error);
    } finally {
        isLoading.value = false;
    }
};

const viewPost = (post) => {
    // TODO: Navigate to post detail
    console.log('View post:', post);
};

onMounted(() => {
    loadInsights();
});

watch(() => props.insights, (newInsights) => {
    if (newInsights) {
        keyMetrics.value = newInsights.metrics || [];
    }
}, { deep: true });
</script>

<style scoped>
.page-insights {
    background: #fff;
    border-radius: 8px;
    padding: 24px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.insights-loading {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.skeleton-loader {
    height: 120px;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    border-radius: 8px;
}

@keyframes loading {
    0% {
        background-position: 200% 0;
    }
    100% {
        background-position: -200% 0;
    }
}

.time-period-selector {
    margin-bottom: 24px;
    display: flex;
    justify-content: flex-end;
}

.period-select {
    padding: 8px 16px;
    border: 1px solid #e4e6eb;
    border-radius: 6px;
    font-size: 15px;
    background: #fff;
    cursor: pointer;
    outline: none;
}

.period-select:focus {
    border-color: #1877f2;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 32px;
}

.metric-card {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
    transition: transform 0.2s, box-shadow 0.2s;
}

.metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.metric-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 24px;
    flex-shrink: 0;
}

.metric-info {
    flex: 1;
}

.metric-value {
    font-size: 28px;
    font-weight: 700;
    color: #050505;
    margin-bottom: 4px;
}

.metric-label {
    font-size: 14px;
    color: #65676b;
    margin-bottom: 8px;
}

.metric-change {
    font-size: 13px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 4px;
}

.metric-change.positive {
    color: #41b35d;
}

.metric-change.negative {
    color: #f02849;
}

.charts-section {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 24px;
    margin-bottom: 32px;
}

.chart-card {
    background: #fff;
    border: 1px solid #e4e6eb;
    border-radius: 8px;
    padding: 20px;
}

.chart-title {
    font-size: 18px;
    font-weight: 600;
    color: #050505;
    margin-bottom: 16px;
}

.chart-container {
    position: relative;
    height: 300px;
}

.top-posts-section {
    margin-top: 32px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #050505;
    margin-bottom: 20px;
}

.posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 16px;
}

.post-card {
    position: relative;
    aspect-ratio: 1;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.2s;
}

.post-card:hover {
    transform: scale(1.02);
}

.post-image {
    width: 100%;
    height: 100%;
    background: #f0f2f5;
}

.post-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.post-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #bcc0c4;
}

.post-stats {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0, 0, 0, 0.7));
    padding: 12px;
    display: flex;
    gap: 16px;
    color: #fff;
    font-size: 14px;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 4px;
}

.stat-item i {
    font-size: 16px;
}

@media (max-width: 768px) {
    .page-insights {
        padding: 16px;
    }

    .metrics-grid {
        grid-template-columns: 1fr;
    }

    .charts-section {
        grid-template-columns: 1fr;
    }

    .posts-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>


