<template>
    
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    pendingPosts: {
        type: Object,
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
.avatar-40 {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-danger {
    background-color: #dc3545;
    border-color: #dc3545;
}

.btn-success:hover, .btn-danger:hover {
    opacity: 0.9;
}
</style> 