<template>
        <h1>ĐMDMDMDMMD</h1>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    pendingMembers: {
        type: Object,
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
.avatar-50 {
    width: 50px;
    height: 50px;
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

.card {
    border: none;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.card-header {
    background-color: #fff;
    border-bottom: 1px solid rgba(0,0,0,0.1);
}
</style> 