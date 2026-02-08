<template>
    <div v-if="role" :class="['role-badge', `role-${role}`]" :title="roleInfo.description">
        <i :class="roleInfo.icon"></i>
        <span>{{ roleInfo.name }}</span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    role: {
        type: String,
        required: true,
        validator: (value) => ['admin', 'editor', 'analyst'].includes(value)
    }
});

const roleInfo = computed(() => {
    const roles = {
        admin: {
            name: 'Admin',
            description: 'Quản trị viên - Toàn quyền',
            icon: 'bx bx-crown',
            color: '#ef4444'
        },
        editor: {
            name: 'Editor',
            description: 'Biên tập viên - Quản lý nội dung',
            icon: 'bx bx-pencil',
            color: '#3b82f6'
        },
        analyst: {
            name: 'Analyst',
            description: 'Phân tích viên - Xem báo cáo',
            icon: 'bx bx-bar-chart',
            color: '#f59e0b'
        }
    };
    return roles[props.role] || {};
});
</script>

<style scoped>
.role-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
    color: white;
    white-space: nowrap;
}

.role-badge i {
    font-size: 14px;
}

.role-admin {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 2px 4px rgba(239, 68, 68, 0.3);
}

.role-editor {
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
}

.role-analyst {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 2px 4px rgba(245, 158, 11, 0.3);
}

.role-badge:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.2s ease;
}
</style>
