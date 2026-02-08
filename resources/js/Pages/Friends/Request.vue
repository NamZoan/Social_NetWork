<template>
    <div class="col-md-12 third-section d-none d-md-block">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bx bx-bell me-2"></i>Lời mời kết bạn
                </h5>
            </div>
            <div class="card-body">
                <!-- Tabs Navigation -->
                <ul class="nav nav-tabs nav-sm mb-2" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button 
                            class="nav-link nav-link-sm" 
                            :class="{ active: activeTab === 'received' }"
                            @click="activeTab = 'received'"
                            type="button" 
                            role="tab"
                        >
                            <i class="bx bx-inbox me-1"></i>Nhận
                            <span class="badge bg-danger ms-1" v-if="received && received.length > 0">
                                {{ received.length }}
                            </span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button 
                            class="nav-link nav-link-sm" 
                            :class="{ active: activeTab === 'sent' }"
                            @click="activeTab = 'sent'"
                            type="button" 
                            role="tab"
                        >
                            <i class="bx bx-send me-1"></i>Gửi
                            <span class="badge bg-warning ms-1" v-if="sent && sent.length > 0">
                                {{ sent.length }}
                            </span>
                        </button>
                    </li>
                </ul>

                <!-- Received Requests Tab -->
                <div v-show="activeTab === 'received'" class="requests-list-sm">
                    <div v-if="received && received.length > 0">
                        <div v-for="user in received" :key="`rec-${user.id}`" class="request-item-sm">
                            <Link :href="`/${user.username}`" class="avatar-link">
                                <img
                                    :src="getAvatarUrl(user.avatar)"
                                    :alt="user.name"
                                    class="avatar-sm"
                                />
                            </Link>
                            <div class="info-sm">
                                <Link :href="`/${user.username}`" class="name-link">
                                    {{ user.name }}
                                </Link>
                            </div>
                            <div class="actions-sm">
                                <button 
                                    @click="acceptRequest(user.id)"
                                    class="btn-icon"
                                    title="Chấp nhận"
                                    :disabled="loadingStates[user.id]"
                                >
                                    <span v-if="loadingStates[user.id]" class="spinner-border spinner-border-sm"></span>
                                    <i v-else class="bx bx-check"></i>
                                </button>
                                <button 
                                    @click="rejectRequest(user.id)"
                                    class="btn-icon"
                                    title="Từ chối"
                                    :disabled="loadingStates[user.id]"
                                >
                                    <span v-if="loadingStates[user.id]" class="spinner-border spinner-border-sm"></span>
                                    <i v-else class="bx bx-x"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted">
                        <small>Không có lời mời</small>
                    </div>
                </div>

                <!-- Sent Requests Tab -->
                <div v-show="activeTab === 'sent'" class="requests-list-sm">
                    <div v-if="sent && sent.length > 0">
                        <div v-for="user in sent" :key="`sent-${user.id}`" class="request-item-sm">
                            <Link :href="`/${user.username}`" class="avatar-link">
                                <img
                                    :src="getAvatarUrl(user.avatar)"
                                    :alt="user.name"
                                    class="avatar-sm"
                                />
                            </Link>
                            <div class="info-sm">
                                <Link :href="`/${user.username}`" class="name-link">
                                    {{ user.name }}
                                </Link>
                            </div>
                            <div class="actions-sm">
                                <button 
                                    @click="cancelRequest(user.id)"
                                    class="btn-icon btn-icon-danger"
                                    title="Hủy"
                                    :disabled="loadingStates[user.id]"
                                >
                                    <span v-if="loadingStates[user.id]" class="spinner-border spinner-border-sm"></span>
                                    <i v-else class="bx bx-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center text-muted">
                        <small>Chưa gửi lời mời nào</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    receivedRequests: {
        type: Array,
        default: () => []
    },
    sentRequests: {
        type: Array,
        default: () => []
    }
});

const activeTab = ref('received');
const received = reactive([...props.receivedRequests]);
const sent = reactive([...props.sentRequests]);
const loadingStates = reactive({});

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

// Chấp nhận lời mời kết bạn
const acceptRequest = async (userId) => {
    loadingStates[userId] = true;
    try {
        await axios.post('/accept-friend-request', { user_id: userId });
        // Xóa khỏi danh sách lời mời nhận được
        const index = received.findIndex(u => u.id === userId);
        if (index > -1) {
            received.splice(index, 1);
        }
    } catch (error) {
        const message = error.response?.data?.message || 'Chấp nhận lời mời thất bại!';
        alert(message);
    } finally {
        loadingStates[userId] = false;
    }
};

// Từ chối lời mời kết bạn
const rejectRequest = async (userId) => {
    if (confirm('Bạn có chắc muốn từ chối lời mời này?')) {
        loadingStates[userId] = true;
        try {
            await axios.post('/unfriend', { user_id: userId });
            // Xóa khỏi danh sách lời mời nhận được
            const index = received.findIndex(u => u.id === userId);
            if (index > -1) {
                received.splice(index, 1);
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Từ chối lời mời thất bại!';
            alert(message);
        } finally {
            loadingStates[userId] = false;
        }
    }
};

// Hủy lời mời đã gửi
const cancelRequest = async (userId) => {
    if (confirm('Bạn có chắc muốn hủy lời mời kết bạn này?')) {
        loadingStates[userId] = true;
        try {
            await axios.post('/unfriend', { user_id: userId });
            // Xóa khỏi danh sách lời mời đã gửi
            const index = sent.findIndex(u => u.id === userId);
            if (index > -1) {
                sent.splice(index, 1);
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Hủy lời mời thất bại!';
            alert(message);
        } finally {
            loadingStates[userId] = false;
        }
    }
};
</script>

<style scoped>
.nav-link.nav-link-sm {
    padding: 0.4rem 0.6rem;
    font-size: 12px;
}

.nav-link {
    color: #6c757d;
    border-bottom: 2px solid transparent;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: #495057;
    border-bottom-color: #dee2e6;
}

.nav-link.active {
    color: #0d6efd;
    border-bottom-color: #0d6efd;
    background: none;
}

.requests-list-sm {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: none;
    overflow-y: auto;
}

.request-item-sm {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #f8f9fa;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.request-item-sm:hover {
    background: #e9ecef;
}

.avatar-link {
    text-decoration: none;
    flex-shrink: 0;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.info-sm {
    flex: 1;
    min-width: 0;
}

.name-link {
    text-decoration: none;
    color: #212529;
    font-size: 12px;
    font-weight: 500;
    display: block;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.name-link:hover {
    color: #0d6efd;
}

.actions-sm {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.btn-icon {
    background: none;
    border: 1px solid #dee2e6;
    color: #6c757d;
    padding: 4px 6px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.3s ease;
}

.btn-icon:hover:not(:disabled) {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.btn-icon:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.btn-icon-danger:hover:not(:disabled) {
    background: #dc3545;
    border-color: #dc3545;
}

.badge {
    font-size: 10px;
    padding: 2px 4px;
    border-radius: 10px;
}

.btn-link {
    padding: 0.25rem 0.5rem;
    font-size: 12px;
    color: #0d6efd;
    text-decoration: none;
}

.btn-link:hover {
    text-decoration: underline;
}
</style>