<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember">
        <div v-if="isMember" class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title mb-0">
                    <i class='bx bx-user-plus me-2'></i>
                    Danh sách yêu cầu tham gia nhóm
                </h4>
                <span class="badge bg-primary text-white btn">{{ pendingRequests.length }} yêu cầu</span>
            </div>
            <div class="card-body">
                <div v-if="pendingRequests && pendingRequests.length > 0">
                    <div v-for="request in pendingRequests" :key="request.id"
                        class="d-flex align-items-center justify-content-between mb-3 p-3 border rounded hover-shadow">
                        <div class="d-flex align-items-center">
                            <div class="position-relative">
                                <img :src="`/images/client/avatar/${request.user.avatar_url}`"
                                    class="rounded-circle me-3" style="width: 60px; height: 60px; object-fit: cover;"
                                    :alt="request.user.name">
                            </div>
                            <div>
                                <Link :href="`/${request.user.username}`" class="text-decoration-none">
                                <h6 class="mb-1 fw-bold text-dark hover-primary">{{ request.user.name }}</h6>
                                </Link>
                                <small class="text-muted d-flex align-items-center">
                                    {{ formatDate(request.created_at) }}
                                </small>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button @click="approveMember(request.user.id)"
                                class="btn btn-primary d-flex align-items-center btn-sm mr-2" :disabled="loading">
                                <i class="bx bx-check me-1"></i>
                                <span>Duyệt</span>
                            </button>
                            <button @click="rejectMember(request.user.id)"
                                class="btn btn-outline-danger d-flex align-items-center btn-sm" :disabled="loading">
                                <i class="bx bx-x me-1"></i>
                                <span>Từ chối</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div v-else class="text-center py-5">
                    <div class="empty-state">
                        <i class='bx bx-user-x display-4 text-muted mb-3'></i>
                        <p class="text-muted mb-0">Không có yêu cầu tham gia nào đang chờ duyệt</p>
                    </div>
                </div>
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import { ref } from 'vue';
import GroupDetail from '../GroupDetail.vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    user_auth: {
        type: Object,
        required: true
    },
    isMember: {
        type: Boolean,
        required: true
    },
    pendingRequests: {
        type: Array,
        required: true
    }
});

const loading = ref(false);

const approveMember = async (memberId) => {
    try {
        loading.value = true;
        await router.post(`/groups/${props.group.id}/members/${memberId}/approve`);
        router.reload();
    } catch (error) {
        console.error('Error approving member:', error);
    } finally {
        loading.value = false;
    }
};

const rejectMember = async (memberId) => {
    try {
        loading.value = true;
        await router.post(`/groups/${props.group.id}/members/${memberId}/reject`);
        router.reload();
    } catch (error) {
        console.error('Error rejecting member:', error);
    } finally {
        loading.value = false;
    }
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<style scoped>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    font-weight: 500;
    transition: all 0.2s ease;
}

.btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.btn-success {
    background-color: #198754;
    border-color: #198754;
}

.btn-success:hover:not(:disabled) {
    background-color: #157347;
    border-color: #146c43;
}

.btn-outline-danger {
    color: #dc3545;
    border-color: #dc3545;
}

.btn-outline-danger:hover:not(:disabled) {
    background-color: #dc3545;
    color: white;
}

.empty-state {
    padding: 2rem;
}

.empty-state i {
    font-size: 4rem;
    color: #6c757d;
}

.badge {
    font-size: 0.875rem;
    padding: 0.75rem
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, .125);
}

.card-title {
    color: #212529;
    font-weight: 600;
}

.avatar-status {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid white;
}

.hover-primary {
    transition: color 0.2s ease;
}

.hover-primary:hover {
    color: #0d6efd !important;
}
</style>