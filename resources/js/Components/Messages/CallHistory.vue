<template>
    <div v-if="showCallHistory" class="call-history-modal">
        <div class="call-history-backdrop" @click="closeModal"></div>
        <div class="call-history-container">
            <div class="call-history-header">
                <h3 class="call-history-title">
                    <i class="bx bx-history"></i>
                    Lịch sử cuộc gọi
                </h3>
                <button class="call-history-close" @click="closeModal" title="Đóng">
                    <i class="bx bx-x"></i>
                </button>
            </div>

            <div class="call-history-filters">
                <button
                    class="filter-btn"
                    :class="{ active: filter === 'all' }"
                    @click="filter = 'all'"
                >
                    Tất cả
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: filter === 'missed' }"
                    @click="filter = 'missed'"
                >
                    Nhỡ cuộc gọi
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: filter === 'outgoing' }"
                    @click="filter = 'outgoing'"
                >
                    Đã gọi
                </button>
                <button
                    class="filter-btn"
                    :class="{ active: filter === 'incoming' }"
                    @click="filter = 'incoming'"
                >
                    Đã nhận
                </button>
            </div>

            <div class="call-history-content" ref="historyContent">
                <!-- Loading State -->
                <div v-if="isLoading" class="call-history-loading">
                    <div class="spinner"></div>
                    <p>Đang tải lịch sử...</p>
                </div>

                <!-- Empty State -->
                <div v-else-if="filteredCalls.length === 0" class="call-history-empty">
                    <i class="bx bx-phone-off"></i>
                    <p>Chưa có cuộc gọi nào</p>
                </div>

                <!-- Calls List -->
                <div v-else class="call-history-list">
                    <div
                        v-for="call in filteredCalls"
                        :key="call.id"
                        class="call-history-item"
                        :class="getCallItemClass(call)"
                        @click="call.other_user && redialCall(call)"
                    >
                        <div class="call-item-avatar">
                            <img
                                v-if="call.other_user?.avatar"
                                :src="call.other_user.avatar"
                                :alt="call.other_user.name"
                            />
                            <div v-else class="avatar-placeholder">
                                <i class="bx bx-user"></i>
                            </div>
                            <div class="call-type-icon" :class="getCallTypeClass(call)">
                                <i :class="getCallIcon(call)"></i>
                            </div>
                        </div>

                        <div class="call-item-info">
                            <div class="call-item-header">
                                <span class="call-item-name">
                                    {{ call.other_user?.name || 'Người dùng không xác định' }}
                                </span>
                                <span class="call-item-direction" :class="call.is_outgoing ? 'outgoing' : 'incoming'">
                                    <i :class="call.is_outgoing ? 'bx bx-arrow-up' : 'bx bx-arrow-down'"></i>
                                </span>
                            </div>
                            <div class="call-item-meta">
                                <span class="call-item-status" :class="getStatusClass(call.status)">
                                    {{ getStatusText(call.status) }}
                                </span>
                                <span class="call-item-time">{{ formatCallTime(call.started_at) }}</span>
                                <span v-if="call.duration" class="call-item-duration">
                                    · {{ formatDuration(call.duration) }}
                                </span>
                            </div>
                        </div>

                        <div class="call-item-actions" v-if="call.other_user">
                            <button
                                class="call-action-btn"
                                @click.stop="redialCall(call)"
                                title="Gọi lại"
                            >
                                <i class="bx bx-phone"></i>
                            </button>
                            <button
                                class="call-action-btn"
                                @click.stop="videoCall(call)"
                                title="Gọi video"
                            >
                                <i class="bx bx-video"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Load More -->
                <div v-if="hasMore && !isLoading" class="call-history-load-more">
                    <button class="load-more-btn" @click="loadMore">
                        Tải thêm
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['close', 'redial', 'video-call']);

const showCallHistory = ref(props.visible);
const calls = ref([]);
const isLoading = ref(false);
const filter = ref('all');
const hasMore = ref(false);
const offset = ref(0);
const historyContent = ref(null);

const filteredCalls = computed(() => {
    if (filter.value === 'all') return calls.value;
    if (filter.value === 'missed') {
        return calls.value.filter(call => call.status === 'missed');
    }
    if (filter.value === 'outgoing') {
        return calls.value.filter(call => call.is_outgoing);
    }
    if (filter.value === 'incoming') {
        return calls.value.filter(call => !call.is_outgoing);
    }
    return calls.value;
});

watch(() => props.visible, (newVal) => {
    showCallHistory.value = newVal;
    if (newVal) {
        loadCalls();
    }
});

const loadCalls = async (loadMore = false) => {
    if (isLoading.value) return;

    isLoading.value = true;
    try {
        const response = await axios.get('/calls/history', {
            params: {
                limit: 50,
                offset: loadMore ? offset.value : 0
            }
        });

        if (loadMore) {
            calls.value.push(...response.data.calls);
        } else {
            calls.value = response.data.calls;
        }

        hasMore.value = response.data.has_more;
        offset.value = calls.value.length;
    } catch (error) {
        console.error('Error loading call history:', error);
    } finally {
        isLoading.value = false;
    }
};

const loadMore = () => {
    loadCalls(true);
};

const closeModal = () => {
    showCallHistory.value = false;
    emit('close');
};

const formatCallTime = (time) => {
    if (!time) return 'Không xác định';

    const callDate = new Date(time);
    const now = new Date();
    const diffInMs = now - callDate;
    const diffInMinutes = Math.floor(diffInMs / (1000 * 60));
    const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
    const diffInDays = Math.floor(diffInMs / (1000 * 60 * 60 * 24));

    if (diffInMinutes < 1) return 'Vừa xong';
    if (diffInMinutes < 60) return `${diffInMinutes} phút trước`;
    if (diffInHours < 24) return `${diffInHours} giờ trước`;
    if (diffInDays < 7) return `${diffInDays} ngày trước`;

    return new Intl.DateTimeFormat('vi-VN', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(callDate);
};

const formatDuration = (seconds) => {
    if (!seconds) return '';
    const minutes = Math.floor(seconds / 60);
    const secs = seconds % 60;
    return `${minutes}:${String(secs).padStart(2, '0')}`;
};

const getStatusText = (status) => {
    const statusMap = {
        'pending': 'Đang chờ',
        'ringing': 'Đang gọi',
        'active': 'Đã kết nối',
        'ended': 'Đã kết thúc',
        'missed': 'Nhỡ cuộc gọi',
        'failed': 'Thất bại'
    };
    return statusMap[status] || status;
};

const getStatusClass = (status) => {
    return {
        'status-pending': status === 'pending',
        'status-ringing': status === 'ringing',
        'status-active': status === 'active',
        'status-ended': status === 'ended',
        'status-missed': status === 'missed',
        'status-failed': status === 'failed'
    };
};

const getCallItemClass = (call) => {
    return {
        'call-missed': call.status === 'missed',
        'call-failed': call.status === 'failed'
    };
};

const getCallTypeClass = (call) => {
    return {
        'call-type-video': true, // Có thể thêm logic để phân biệt video/voice
        'call-type-voice': false
    };
};

const getCallIcon = (call) => {
    if (call.status === 'missed') return 'bx bx-phone-call';
    if (call.status === 'failed') return 'bx bx-error-circle';
    return 'bx bx-phone';
};

const redialCall = (call) => {
    if (call.other_user) {
        emit('redial', call.other_user.id);
    }
};

const videoCall = (call) => {
    if (call.other_user) {
        emit('video-call', call.other_user.id);
    }
};

onMounted(() => {
    if (props.visible) {
        loadCalls();
    }
});
</script>

<style scoped>
.call-history-modal {
    position: fixed;
    inset: 0;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: 'Inter', sans-serif;
}

.call-history-backdrop {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
}

.call-history-container {
    position: relative;
    width: min(600px, 90vw);
    max-height: 80vh;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 1;
}

.call-history-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.call-history-title {
    margin: 0;
    font-size: 1.25rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.call-history-close {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
    font-size: 1.25rem;
}

.call-history-close:hover {
    background: rgba(255, 255, 255, 0.3);
}

.call-history-filters {
    height: 70px;
    display: flex;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    background: #f9fafb;
    overflow-x: auto;
}

.filter-btn {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.filter-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
}

.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

.call-history-content {
    flex: 1;
    overflow-y: auto;
}

.call-history-loading,
.call-history-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 3rem 1.5rem;
    color: #6b7280;
}

.call-history-loading .spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top-color: #667eea;
    border-radius: 50%;
    animation: spin 0.8s linear infinite;
    margin-bottom: 1rem;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.call-history-empty i {
    font-size: 3rem;
    margin-bottom: 1rem;
    opacity: 0.5;
}

.call-history-list {
    display: flex;
    flex-direction: column;
}

.call-history-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f3f4f6;
    transition: background 0.2s ease;
    cursor: pointer;
}

.call-history-item:hover {
    background: #f9fafb;
}

.call-history-item.call-missed {
    background: #fef2f2;
}

.call-history-item.call-failed {
    background: #fff7ed;
}

.call-item-avatar {
    position: relative;
    flex-shrink: 0;
}

.call-item-avatar img {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #e5e7eb;
}

.avatar-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.call-type-icon {
    position: absolute;
    bottom: -2px;
    right: -2px;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.call-type-icon.call-type-video {
    color: #667eea;
}

.call-type-icon.call-type-voice {
    color: #10b981;
}

.call-item-info {
    flex: 1;
    min-width: 0;
}

.call-item-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    margin-bottom: 0.25rem;
}

.call-item-name {
    font-weight: 600;
    color: #111827;
    font-size: 0.95rem;
}

.call-item-direction {
    font-size: 0.75rem;
    padding: 0.125rem 0.375rem;
    border-radius: 4px;
    font-weight: 500;
}

.call-item-direction.outgoing {
    background: #dbeafe;
    color: #1e40af;
}

.call-item-direction.incoming {
    background: #dcfce7;
    color: #166534;
}

.call-item-meta {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8125rem;
    color: #6b7280;
}

.call-item-status {
    font-weight: 500;
}

.status-missed {
    color: #dc2626;
}

.status-failed {
    color: #f59e0b;
}

.status-ended {
    color: #10b981;
}

.status-active {
    color: #3b82f6;
}

.call-item-time {
    color: #9ca3af;
}

.call-item-duration {
    color: #6b7280;
    font-weight: 500;
}

.call-item-actions {
    display: flex;
    gap: 0.5rem;
    flex-shrink: 0;
}

.call-action-btn {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 1.125rem;
}

.call-action-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #667eea;
    transform: scale(1.1);
}

.call-history-load-more {
    padding: 1rem 1.5rem;
    text-align: center;
}

.load-more-btn {
    padding: 0.625rem 1.5rem;
    border-radius: 20px;
    border: 1px solid #e5e7eb;
    background: white;
    color: #6b7280;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.load-more-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #667eea;
}

/* Custom scrollbar */
.call-history-content::-webkit-scrollbar {
    width: 6px;
}

.call-history-content::-webkit-scrollbar-track {
    background: #f1f5f9;
}

.call-history-content::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.call-history-content::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Responsive */
@media (max-width: 640px) {
    .call-history-container {
        width: 100vw;
        max-height: 100vh;
        border-radius: 0;
    }

    .call-history-item {
        padding: 0.875rem 1rem;
    }

    .call-item-avatar img,
    .avatar-placeholder {
        width: 40px;
        height: 40px;
    }
}
</style>

