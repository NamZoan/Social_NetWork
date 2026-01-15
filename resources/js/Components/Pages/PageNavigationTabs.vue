<template>
    <div class="navigation-tabs">
        <div class="tabs-container">
            <button
                v-for="tab in visibleTabs"
                :key="tab.key"
                @click="selectTab(tab.key)"
                :class="['tab-button', { 'tab-active': activeTab === tab.key }]"
            >
                <i :class="tab.icon"></i>
                <span class="tab-label">{{ tab.label }}</span>
                <span v-if="tab.count !== null" class="tab-count">{{ tab.count }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    activeTab: {
        type: String,
        default: 'home'
    },
    isAdmin: {
        type: Boolean,
        default: false
    },
    stats: {
        type: Object,
        default: () => ({
            posts: 0,
            photos: 0,
            videos: 0,
            followers: 0,
        })
    }
});

const emit = defineEmits(['tab-changed']);

const baseTabs = [
    { key: 'home', label: 'Trang chủ', icon: 'bx bx-home', visible: true, countKey: null },
    { key: 'about', label: 'Giới thiệu', icon: 'bx bx-info-circle', visible: true, countKey: null },
    { key: 'posts', label: 'Bài viết', icon: 'bx bx-file', visible: true, countKey: 'posts' },
    { key: 'photos', label: 'Ảnh', icon: 'bx bx-image', visible: true, countKey: 'photos' },
    { key: 'community', label: 'Cộng đồng', icon: 'bx bx-group', visible: true, countKey: 'followers' },
    { key: 'insights', label: 'Phân tích', icon: 'bx bx-bar-chart-alt-2', visible: false, countKey: null },
];

const visibleTabs = computed(() => {
    return baseTabs
        .filter((tab) => {
            if (tab.key === 'insights' || tab.key === 'settings') {
                return props.isAdmin;
            }
            return tab.visible;
        })
        .map((tab) => ({
            ...tab,
            count: tab.countKey ? Number(props.stats?.[tab.countKey] || 0) : null,
        }));
});

const selectTab = (tabKey) => {
    emit('tab-changed', tabKey);
};
</script>

<style scoped>
.navigation-tabs {
    background: #fff;
    border-bottom: 1px solid #e4e6eb;
    border-radius: 8px 8px 0 0;
    margin-bottom: 20px;
}

.tabs-container {
    display: flex;
    overflow-x: auto;
    scrollbar-width: none;
    -ms-overflow-style: none;
}

.tabs-container::-webkit-scrollbar {
    display: none;
}

.tab-button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 18px;
    border: none;
    background: transparent;
    color: #65676b;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    border-bottom: 3px solid transparent;
    transition: all 0.2s;
    position: relative;
}

.tab-button:hover {
    background: #f2f3f5;
    color: #050505;
}

.tab-active {
    color: #1877f2;
    border-bottom-color: #1877f2;
    background: transparent;
}

.tab-active:hover {
    background: transparent;
}

.tab-label {
    font-size: 15px;
}

.tab-count {
    background: #eef2ff;
    color: #1d4ed8;
    border-radius: 999px;
    padding: 2px 8px;
    font-size: 12px;
    font-weight: 700;
}

@media (max-width: 768px) {
    .tab-button {
        padding: 12px 16px;
        font-size: 14px;
    }

    .tab-label {
        display: none;
    }
}
</style>
