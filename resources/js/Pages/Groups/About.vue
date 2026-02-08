<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div class="about-container">
            <!-- Group Description -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-info-circle me-2'></i>
                        Giới thiệu
                    </h5>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ group.description || 'Chưa có mô tả cho nhóm này' }}</p>
                </div>
            </div>

            <!-- Group Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-detail me-2'></i>
                        Thông tin chi tiết
                    </h5>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <i class='bx bx-lock-alt info-icon'></i>
                            <div>
                                <div class="info-label">Quyền riêng tư</div>
                                <div class="info-value">
                                    {{ group.privacy_setting ? 'Nhóm công khai' : 'Nhóm riêng tư' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class='bx bx-check-circle info-icon'></i>
                            <div>
                                <div class="info-label">Phê duyệt bài viết</div>
                                <div class="info-value">
                                    {{ group.post_approval_required ? 'Bài viết cần được duyệt' : 'Đăng bài tự do' }}
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class='bx bx-user info-icon'></i>
                            <div>
                                <div class="info-label">Thành viên</div>
                                <div class="info-value">{{ group.members_count }} người</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class='bx bx-news info-icon'></i>
                            <div>
                                <div class="info-label">Bài viết</div>
                                <div class="info-value">{{ group.posts_count }} bài</div>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class='bx bx-user-circle info-icon'></i>
                            <div>
                                <div class="info-label">Người tạo</div>
                                <div class="info-value">
                                    <Link :href="`/${group.creator.username}`" class="text-primary">
                                        {{ group.creator.name }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div class="info-item">
                            <i class='bx bx-calendar info-icon'></i>
                            <div>
                                <div class="info-label">Ngày tạo</div>
                                <div class="info-value">{{ formatDate(group.created_at) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Group Rules -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class='bx bx-list-ul me-2'></i>
                        Quy tắc nhóm
                    </h5>
                    <button 
                        v-if="isAdmin"
                        @click="showEditRules = !showEditRules"
                        class="btn btn-sm btn-outline-primary"
                    >
                        <i class='bx bx-edit me-1'></i>
                        {{ showEditRules ? 'Hủy' : 'Chỉnh sửa' }}
                    </button>
                </div>
                <div class="card-body">
                    <div v-if="!showEditRules">
                        <div v-if="group.rules" class="rules-content" v-html="formatRules(group.rules)"></div>
                        <p v-else class="text-muted mb-0">Nhóm chưa có quy tắc cụ thể</p>
                    </div>
                    
                    <form v-else @submit.prevent="updateRules">
                        <textarea 
                            v-model="rulesForm.rules" 
                            class="form-control mb-3" 
                            rows="8"
                            placeholder="Nhập quy tắc nhóm (mỗi quy tắc một dòng)..."
                        ></textarea>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary" :disabled="rulesForm.processing">
                                <i class='bx bx-save me-1'></i>
                                Lưu thay đổi
                            </button>
                            <button 
                                type="button" 
                                @click="cancelEditRules" 
                                class="btn btn-secondary"
                            >
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Stats (For members) -->
            <div v-if="isMember" class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class='bx bx-trending-up me-2'></i>
                        Hoạt động gần đây
                    </h5>
                </div>
                <div class="card-body">
                    <div class="activity-stats">
                        <div class="stat-box">
                            <div class="stat-number">{{ group.posts_count || 0 }}</div>
                            <div class="stat-label">Tổng bài viết</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-number">{{ group.members_count || 0 }}</div>
                            <div class="stat-label">Thành viên</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import { ref } from 'vue';
import { useForm, Link, router } from '@inertiajs/vue3';
import GroupDetail from './GroupDetail.vue';

const props = defineProps({
    group: { type: Object, required: true },
    user_auth: { type: Object, required: true },
    isMember: { type: Boolean, required: true },
    isPending: { type: Boolean, required: true },
    isAdmin: { type: Boolean, required: true }
});

const showEditRules = ref(false);

const rulesForm = useForm({
    rules: props.group.rules || ''
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const formatRules = (rules) => {
    if (!rules) return '';
    
    const lines = rules.split('\n').filter(line => line.trim());
    const formattedLines = lines.map((line, index) => {
        return `<div class="rule-item"><strong>${index + 1}.</strong> ${line}</div>`;
    });
    
    return formattedLines.join('');
};

const updateRules = () => {
    rulesForm.post(`/groups/${props.group.id}/rules`, {
        preserveScroll: true,
        onSuccess: () => {
            showEditRules.value = false;
            router.reload({ only: ['group'] });
        }
    });
};

const cancelEditRules = () => {
    showEditRules.value = false;
    rulesForm.rules = props.group.rules || '';
};
</script>

<style scoped>
.about-container {
    max-width: 100%;
}

.info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1.5rem;
}

.info-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
}

.info-icon {
    font-size: 1.75rem;
    color: #667eea;
    flex-shrink: 0;
}

.info-label {
    font-size: 0.875rem;
    color: #64748b;
    margin-bottom: 0.25rem;
}

.info-value {
    font-size: 1rem;
    font-weight: 600;
    color: #1e293b;
}

.rules-content {
    line-height: 1.8;
}

.rule-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f5f9;
}

.rule-item:last-child {
    border-bottom: none;
}

.rule-item strong {
    color: #667eea;
    margin-right: 0.5rem;
}

.activity-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 1.5rem;
}

.stat-box {
    text-align: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
}

.stat-number {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.875rem;
    opacity: 0.9;
}

.card-header h5 {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .info-grid {
        grid-template-columns: 1fr;
    }
    
    .activity-stats {
        grid-template-columns: 1fr;
    }
}
</style>
