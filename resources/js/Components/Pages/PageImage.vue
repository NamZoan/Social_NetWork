<template>
    <div class="photos-section">
        <div v-if="loading && !images.length" class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div v-else-if="images.length" class="photo-grid">
            <div v-for="img in images" :key="img.id" class="photo-item">
                <img :src="imageUrl(img.media_url)" :alt="`photo-${img.id}`" loading="lazy" />
                <div class="photo-meta">
                    <i class="bx bx-image"></i>
                    <span>{{ formatDate(img.created_at) }}</span>
                </div>
            </div>
        </div>

        <div v-else class="text-muted py-4 text-center">
            Chưa có ảnh nào.
        </div>

        <div class="text-center mt-3" v-if="hasMore && !loading">
            <button class="btn btn-outline-primary btn-sm" @click="$emit('load-more')">Tải thêm ảnh</button>
        </div>
        <div class="text-center mt-3" v-else-if="loading && images.length">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    images: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    hasMore: { type: Boolean, default: false }
});

const imageUrl = (path) => path?.startsWith('http') ? path : `/images/client/post/${path}`;
const formatDate = (value) => value ? new Date(value).toLocaleDateString('vi-VN') : '';
</script>

<style scoped>
.photos-section {
    background: transparent;
}

.photo-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
}

.photo-item {
    position: relative;
    background: #fff;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
}

.photo-item img {
    width: 100%;
    height: 180px;
    object-fit: cover;
    display: block;
}

.photo-meta {
    position: absolute;
    bottom: 8px;
    left: 8px;
    background: rgba(0,0,0,0.6);
    color: #fff;
    padding: 4px 8px;
    border-radius: 999px;
    font-size: 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
</style>
