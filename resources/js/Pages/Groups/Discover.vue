<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <!-- Header Section -->
            <div class="discover-header mb-4">
                <h2 class="page-title">
                    <i class='bx bx-search-alt me-2'></i>
                    Khám Phá Nhóm
                </h2>
                <p class="text-muted">Tìm kiếm và tham gia các nhóm phù hợp với sở thích của bạn</p>
            </div>

            <!-- Search and Filter Section -->
            <div class="card mb-4">
                <div class="card-body">
                    <form @submit.prevent="searchGroups" class="row g-3">
                        <div class="col-md-8">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class='bx bx-search'></i>
                                </span>
                                <input 
                                    v-model="searchForm.search" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Tìm kiếm nhóm theo tên hoặc mô tả..."
                                >
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select v-model="searchForm.privacy" class="form-select">
                                <option :value="null">Tất cả nhóm</option>
                                <option :value="1">Nhóm công khai</option>
                                <option :value="0">Nhóm riêng tư</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class='bx bx-search'></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Results Section -->
            <div class="groups-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        Kết quả tìm kiếm
                        <span class="badge bg-primary ms-2">{{ groups.total }}</span>
                    </h5>
                </div>

                <div class="row">
                    <template v-if="groups.data && groups.data.length > 0">
                        <div v-for="group in groups.data" :key="group.id" class="col-md-6 col-lg-4 mb-4">
                            <div class="group-discover-card">
                                <div class="group-cover-wrapper">
                                    <img 
                                        :src="groupCover(group)" 
                                        class="group-cover-img" 
                                        alt="Group cover"
                                    >
                                    <div class="group-privacy-badge">
                                        <i :class="group.privacy_setting ? 'bx bx-globe' : 'bx bx-lock-alt'"></i>
                                        {{ group.privacy_setting ? 'Công khai' : 'Riêng tư' }}
                                    </div>
                                </div>
                                
                                <div class="group-card-content">
                                    <h5 class="group-name">{{ group.name }}</h5>
                                    <p class="group-description">{{ group.description || 'Không có mô tả' }}</p>
                                    
                                    <div class="group-stats">
                                        <div class="stat-item">
                                            <i class='bx bx-user'></i>
                                            <span>{{ group.members_count }} thành viên</span>
                                        </div>
                                    </div>

                                    <div class="group-actions">
                                        <Link 
                                            :href="`/groups/${group.id}`" 
                                            class="btn btn-outline-primary btn-sm w-100 mb-2"
                                        >
                                            <i class='bx bx-show me-1'></i>
                                            Xem nhóm
                                        </Link>
                                        
                                        <button 
                                            v-if="!group.user_is_member && !group.user_is_pending"
                                            @click="joinGroup(group.id)"
                                            class="btn btn-primary btn-sm w-100"
                                            :disabled="joiningGroups.includes(group.id)"
                                        >
                                            <i class='bx bx-user-plus me-1'></i>
                                            {{ group.privacy_setting ? 'Tham gia' : 'Yêu cầu tham gia' }}
                                        </button>
                                        
                                        <button 
                                            v-else-if="group.user_is_pending"
                                            class="btn btn-secondary btn-sm w-100"
                                            disabled
                                        >
                                            <i class='bx bx-time me-1'></i>
                                            Đang chờ duyệt
                                        </button>
                                        
                                        <span 
                                            v-else
                                            class="badge bg-success w-100 py-2"
                                        >
                                            <i class='bx bx-check me-1'></i>
                                            Đã tham gia
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <div v-else class="col-12 text-center py-5">
                        <div class="empty-state">
                            <i class='bx bx-search-alt display-1 text-muted mb-3'></i>
                            <p class="text-muted">Không tìm thấy nhóm nào phù hợp</p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="groups.last_page > 1" class="d-flex justify-content-center mt-4">
                    <nav>
                        <ul class="pagination">
                            <li class="page-item" :class="{ disabled: !groups.prev_page_url }">
                                <Link 
                                    class="page-link" 
                                    :href="groups.prev_page_url || '#'"
                                    preserve-scroll
                                >
                                    Trước
                                </Link>
                            </li>
                            
                            <li 
                                v-for="page in paginationRange" 
                                :key="page"
                                class="page-item"
                                :class="{ active: page === groups.current_page }"
                            >
                                <Link 
                                    class="page-link" 
                                    :href="`/groups/discover?page=${page}&search=${searchForm.search || ''}&privacy=${searchForm.privacy || ''}`"
                                    preserve-scroll
                                >
                                    {{ page }}
                                </Link>
                            </li>
                            
                            <li class="page-item" :class="{ disabled: !groups.next_page_url }">
                                <Link 
                                    class="page-link" 
                                    :href="groups.next_page_url || '#'"
                                    preserve-scroll
                                >
                                    Sau
                                </Link>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </Index>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import Index from './Index.vue';
import axios from 'axios';

const props = defineProps({
    groups: {
        type: Object,
        required: true
    },
    search: {
        type: String,
        default: ''
    },
    privacy: {
        type: [Number, String],
        default: null
    }
});

const searchForm = ref({
    search: props.search || '',
    privacy: props.privacy
});

const joiningGroups = ref([]);

const groupCover = (group) => {
    const cover = group?.cover_photo_url;
    if (!cover) return '/images/web/groups/group.webp';
    if (cover.startsWith('http')) return cover;
    if (cover.startsWith('/')) return cover;
    return `/images/client/group/thumbnail/${cover}`;
};

const searchGroups = () => {
    router.get('/groups/discover', {
        search: searchForm.value.search,
        privacy: searchForm.value.privacy
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

const joinGroup = async (groupId) => {
    if (joiningGroups.value.includes(groupId)) return;
    
    joiningGroups.value.push(groupId);
    
    try {
        await axios.post(`/groups/${groupId}/join`);
        router.reload({ only: ['groups'] });
    } catch (error) {
        console.error('Error joining group:', error);
        alert('Có lỗi xảy ra khi tham gia nhóm!');
    } finally {
        joiningGroups.value = joiningGroups.value.filter(id => id !== groupId);
    }
};

const paginationRange = computed(() => {
    const current = props.groups.current_page;
    const last = props.groups.last_page;
    const delta = 2;
    
    const range = [];
    const rangeWithDots = [];
    
    for (let i = Math.max(2, current - delta); i <= Math.min(last - 1, current + delta); i++) {
        range.push(i);
    }
    
    if (current - delta > 2) {
        rangeWithDots.push(1, '...');
    } else {
        rangeWithDots.push(1);
    }
    
    rangeWithDots.push(...range);
    
    if (current + delta < last - 1) {
        rangeWithDots.push('...', last);
    } else if (last > 1) {
        rangeWithDots.push(last);
    }
    
    return rangeWithDots;
});
</script>

<style scoped>
.discover-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 2rem;
    border-radius: 16px;
    color: white;
}

.page-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.groups-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.group-discover-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.group-discover-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.group-cover-wrapper {
    position: relative;
    width: 100%;
    height: 160px;
    overflow: hidden;
}

.group-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.group-privacy-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    padding: 0.375rem 0.75rem;
    border-radius: 20px;
    font-size: 0.875rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.group-card-content {
    padding: 1.25rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.group-name {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 0.75rem;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.group-description {
    color: #64748b;
    font-size: 0.875rem;
    margin-bottom: 1rem;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
    flex: 1;
}

.group-stats {
    display: flex;
    gap: 1rem;
    margin-bottom: 1rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #e5e7eb;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.375rem;
    color: #64748b;
    font-size: 0.875rem;
}

.stat-item i {
    font-size: 1.125rem;
}

.group-actions {
    margin-top: auto;
}

.empty-state {
    padding: 3rem 1rem;
}

.pagination {
    margin-bottom: 0;
}

.page-link {
    color: #667eea;
    border: 1px solid #e5e7eb;
}

.page-item.active .page-link {
    background-color: #667eea;
    border-color: #667eea;
}

@media (max-width: 768px) {
    .discover-header {
        padding: 1.5rem;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
}
</style>
