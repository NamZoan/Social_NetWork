<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div v-if="isMember">
            <div class="card-header d-flex justify-content-between align-items-center mb-2">
                <h4 class="card-title mb-0">
                    <i class='bx bx-list-check me-2'></i>
                    Danh sách bài viết chờ duyệt
                </h4>
                <span class="badge bg-primary text-white btn">{{ pendingPosts.length }} bài viết</span>
            </div>
            <div v-if="pendingPosts && pendingPosts.length > 0">
                <div v-for="post in pendingPosts" :key="post.id" class="mb-4">
                    <ItemPost :post="post" :user="post.user">
                        <template #actions>
                            <div class="d-flex gap-2 mt-3">
                                <button @click="approvePost(post.id)"
                                    class="btn btn-primary d-flex align-items-center btn-sm mr-2" :disabled="loading">
                                    <i class="bx bx-check me-1"></i>
                                    <span>Duyệt</span>
                                </button>
                                <button @click="rejectPost(post.id)"
                                    class="btn btn-outline-danger d-flex align-items-center btn-sm" :disabled="loading">
                                    <i class="bx bx-x me-1"></i>
                                    <span>Từ chối</span>
                                </button>
                            </div>
                        </template>
                    </ItemPost>
                </div>
            </div>
            <div v-else class="text-center py-5">
                <div class="empty-state">
                    <i class='bx bx-file-blank display-4 text-muted mb-3'></i>
                    <p class="text-muted mb-0">Không có bài viết nào đang chờ duyệt</p>
                </div>
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import GroupDetail from '../GroupDetail.vue';
import ItemPost from '../../../Components/Item/ItemPost.vue';

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
    pendingPosts: {
        type: Array,
        required: true
    },
    isAdmin: {
        type: Boolean,
        required: true
    }
});

const loading = ref(false);

const approvePost = async (postId) => {
    try {
        loading.value = true;
        await router.post(`/groups/${props.group.id}/posts/${postId}/approve`);
        router.reload();
    } catch (error) {
        console.error('Error approving post:', error);
    } finally {
        loading.value = false;
    }
};

const rejectPost = async (postId) => {
    try {
        loading.value = true;
        await router.post(`/groups/${props.group.id}/posts/${postId}/reject`);
        router.reload();
    } catch (error) {
        console.error('Error rejecting post:', error);
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

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover:not(:disabled) {
    background-color: #0b5ed7;
    border-color: #0a58ca;
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
    padding: 0.75rem;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, .125);
}

.card-title {
    color: #212529;
    font-weight: 600;
}

.hover-primary {
    transition: color 0.2s ease;
}

.hover-primary:hover {
    color: #0d6efd !important;
}

.post-content {
    color: #212529;
    line-height: 1.5;
}

.post-media img {
    transition: transform 0.2s ease;
}

.post-media img:hover {
    transform: scale(1.05);
}
</style>