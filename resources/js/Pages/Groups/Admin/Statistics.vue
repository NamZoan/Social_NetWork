<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div class="statistics-container">
            <!-- Overview Stats -->
            <div class="row mb-4">
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card stat-card-primary">
                        <div class="stat-icon">
                            <i class='bx bx-user'></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.total_members }}</div>
                            <div class="stat-label">Tổng thành viên</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card stat-card-success">
                        <div class="stat-icon">
                            <i class='bx bx-news'></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.total_posts }}</div>
                            <div class="stat-label">Tổng bài viết</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card stat-card-warning">
                        <div class="stat-icon">
                            <i class='bx bx-time'></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.pending_requests }}</div>
                            <div class="stat-label">Yêu cầu chờ duyệt</div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-3 col-sm-6 mb-3">
                    <div class="stat-card stat-card-info">
                        <div class="stat-icon">
                            <i class='bx bx-check-circle'></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-value">{{ stats.pending_posts }}</div>
                            <div class="stat-label">Bài viết chờ duyệt</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Stats -->
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-calendar me-2'></i>
                                Thống kê tháng này
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="monthly-stat">
                                        <div class="monthly-stat-icon">
                                            <i class='bx bx-user-plus'></i>
                                        </div>
                                        <div class="monthly-stat-content">
                                            <div class="monthly-stat-value">{{ stats.new_members_this_month }}</div>
                                            <div class="monthly-stat-label">Thành viên mới</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="monthly-stat">
                                        <div class="monthly-stat-icon">
                                            <i class='bx bx-edit-alt'></i>
                                        </div>
                                        <div class="monthly-stat-content">
                                            <div class="monthly-stat-value">{{ stats.posts_this_month }}</div>
                                            <div class="monthly-stat-label">Bài viết mới</div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="monthly-stat">
                                        <div class="monthly-stat-icon">
                                            <i class='bx bx-user-check'></i>
                                        </div>
                                        <div class="monthly-stat-content">
                                            <div class="monthly-stat-value">{{ stats.active_members }}</div>
                                            <div class="monthly-stat-label">Thành viên hoạt động</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Activity Summary -->
            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-trending-up me-2'></i>
                                Tỷ lệ tăng trưởng
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="growth-item">
                                <div class="growth-label">Thành viên</div>
                                <div class="growth-bar">
                                    <div 
                                        class="growth-progress" 
                                        :style="{ width: memberGrowthPercentage + '%' }"
                                    ></div>
                                </div>
                                <div class="growth-percentage">{{ memberGrowthPercentage }}%</div>
                            </div>
                            
                            <div class="growth-item mt-3">
                                <div class="growth-label">Bài viết</div>
                                <div class="growth-bar">
                                    <div 
                                        class="growth-progress growth-progress-success" 
                                        :style="{ width: postGrowthPercentage + '%' }"
                                    ></div>
                                </div>
                                <div class="growth-percentage">{{ postGrowthPercentage }}%</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-bar-chart-alt me-2'></i>
                                Tổng quan hoạt động
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="activity-summary-item">
                                <div class="activity-summary-label">Tỷ lệ thành viên hoạt động</div>
                                <div class="activity-summary-value">
                                    {{ activePercentage }}%
                                </div>
                            </div>
                            
                            <div class="activity-summary-item mt-3">
                                <div class="activity-summary-label">Trung bình bài viết/thành viên</div>
                                <div class="activity-summary-value">
                                    {{ averagePostsPerMember }}
                                </div>
                            </div>
                            
                            <div class="activity-summary-item mt-3">
                                <div class="activity-summary-label">Trạng thái nhóm</div>
                                <div class="activity-summary-value">
                                    <span :class="statusBadgeClass">{{ groupStatus }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class='bx bx-cog me-2'></i>
                                Quản lý nhanh
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="quick-actions">
                                <Link 
                                    :href="`/groups/${group.id}/pending-requests`" 
                                    class="quick-action-btn btn-primary"
                                >
                                    <i class='bx bx-user-plus'></i>
                                    <span>Duyệt thành viên ({{ stats.pending_requests }})</span>
                                </Link>
                                
                                <Link 
                                    :href="`/groups/${group.id}/pending-posts`" 
                                    class="quick-action-btn btn-warning"
                                >
                                    <i class='bx bx-list-check'></i>
                                    <span>Duyệt bài viết ({{ stats.pending_posts }})</span>
                                </Link>
                                
                                <Link 
                                    :href="`/groups/${group.id}/members`" 
                                    class="quick-action-btn btn-info"
                                >
                                    <i class='bx bx-group'></i>
                                    <span>Xem thành viên</span>
                                </Link>
                                
                                <Link 
                                    :href="`/groups/${group.id}/edit`" 
                                    class="quick-action-btn btn-secondary"
                                >
                                    <i class='bx bx-edit'></i>
                                    <span>Cài đặt nhóm</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import GroupDetail from '../GroupDetail.vue';

const props = defineProps({
    group: { type: Object, required: true },
    stats: { type: Object, required: true },
    user_auth: { type: Object, required: true },
    isMember: { type: Boolean, required: true },
    isPending: { type: Boolean, required: true },
    isAdmin: { type: Boolean, required: true }
});

const memberGrowthPercentage = computed(() => {
    if (props.stats.total_members === 0) return 0;
    return Math.round((props.stats.new_members_this_month / props.stats.total_members) * 100);
});

const postGrowthPercentage = computed(() => {
    if (props.stats.total_posts === 0) return 0;
    return Math.round((props.stats.posts_this_month / props.stats.total_posts) * 100);
});

const activePercentage = computed(() => {
    if (props.stats.total_members === 0) return 0;
    return Math.round((props.stats.active_members / props.stats.total_members) * 100);
});

const averagePostsPerMember = computed(() => {
    if (props.stats.total_members === 0) return 0;
    return (props.stats.total_posts / props.stats.total_members).toFixed(1);
});

const groupStatus = computed(() => {
    const percentage = activePercentage.value;
    if (percentage >= 50) return 'Rất hoạt động';
    if (percentage >= 30) return 'Hoạt động tốt';
    if (percentage >= 10) return 'Hoạt động vừa';
    return 'Cần cải thiện';
});

const statusBadgeClass = computed(() => {
    const percentage = activePercentage.value;
    if (percentage >= 50) return 'badge bg-success';
    if (percentage >= 30) return 'badge bg-primary';
    if (percentage >= 10) return 'badge bg-warning';
    return 'badge bg-danger';
});
</script>

<style scoped>
.statistics-container {
    padding: 0;
}

.stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
}

.stat-card-primary .stat-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-card-success .stat-icon {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.stat-card-warning .stat-icon {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-card-info .stat-icon {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-content {
    flex: 1;
}

.stat-value {
    font-size: 2rem;
    font-weight: 700;
    color: #1e293b;
    line-height: 1;
}

.stat-label {
    font-size: 0.875rem;
    color: #64748b;
    margin-top: 0.25rem;
}

.monthly-stat {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.monthly-stat-icon {
    width: 50px;
    height: 50px;
    background: #667eea;
    color: white;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}

.monthly-stat-value {
    font-size: 1.75rem;
    font-weight: 700;
    color: #1e293b;
}

.monthly-stat-label {
    font-size: 0.875rem;
    color: #64748b;
}

.growth-item {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.growth-label {
    flex: 0 0 120px;
    font-weight: 600;
    color: #1e293b;
}

.growth-bar {
    flex: 1;
    height: 10px;
    background: #e2e8f0;
    border-radius: 5px;
    overflow: hidden;
}

.growth-progress {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
    transition: width 0.3s ease;
}

.growth-progress-success {
    background: linear-gradient(90deg, #11998e 0%, #38ef7d 100%);
}

.growth-percentage {
    flex: 0 0 50px;
    text-align: right;
    font-weight: 600;
    color: #667eea;
}

.activity-summary-item {
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
}

.activity-summary-label {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.5rem;
}

.activity-summary-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1e293b;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
}

.quick-action-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 1rem;
    border-radius: 8px;
    color: white;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.2s ease;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.quick-action-btn.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.quick-action-btn.btn-warning {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.quick-action-btn.btn-info {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.quick-action-btn.btn-secondary {
    background: linear-gradient(135deg, #868f96 0%, #596164 100%);
}

@media (max-width: 768px) {
    .stat-card {
        flex-direction: column;
        text-align: center;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>
