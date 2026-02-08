<template>
    <div class="col-md-3 third-section">
        <div class="p-3 bg-white rounded w-shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">Gợi ý kết bạn</h6>
            </div>
            <div class="bg-white rounded contacts">
                <template v-if="suggestedFriends.length">
                    <div v-for="(friend, idx) in suggestedFriends" :key="friend.id"
                        :class="['media', 'text-muted', { 'pt-3': idx !== 0 }]">
                        <img :src="avatarUrl(friend)" alt="Suggested friend" class="online-user-image align-middle">
                        <div class="media-body mb-0 small lh-125">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <strong class="text-gray-dark">
                                    <Link :href="`/${friend.username}`" class="smFLname">{{ friend.name }}</Link>
                                </strong>
                            </div>
                            <small class="d-block text-muted">@{{ friend.username }}</small>
                            <small v-if="Number(friend.mutual_count) > 0" class="d-block text-muted fs-9">
                                {{ mutualLabel(Number(friend.mutual_count)) }}
                            </small>
                        </div>
                    </div>
                </template>
                <div v-else class="py-3 text-center text-muted small">
                    Chưa có gợi ý kết bạn.
                </div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                <h6 class="card-title mb-0">Nhóm Gợi Ý</h6>
            </div>
            <div v-if="suggestedGroups.length" class="group-suggestions">
                <div v-for="group in suggestedGroups" :key="group.id" class="card suggestion-card mb-3">
                    <img class="card-img-top suggestion-image" :src="groupCover(group)" alt="Group cover">
                    <div class="overlay-card suggestion-card-body">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h5 class="mb-0 limit-1-lines">{{ group.name }}</h5>
                                <span class="badge badge-light text-uppercase fs-9">{{ group.group_type || 'Khác' }}</span>
                            </div>
                            <p class="card-text limit-2-lines mb-2">
                                {{ group.description || 'Hãy tham gia để cập nhật bài viết mới.' }}
                            </p>
                            <div class="d-flex justify-content-between text-muted fs-9">
                                <span><i class="bx bx-lock-open"></i> {{ privacyLabel(group.privacy_setting) }}</span>
                                <span><i class="bx bx-user"></i> {{ formatMembers(group) }} thành viên</span>
                            </div>
                            <Link :href="`/groups/${group.id}`" class="btn btn-quick-links mt-2">Khám phá</Link>
                        </div>
                    </div>
                </div>
            </div>
            <div v-else class="text-muted small mb-4">Chưa có gợi ý</div>

            <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
                <h6 class="card-title mb-0">Gợi ý trang</h6>
            </div>
            <div class="bg-white rounded contacts">
                <template v-if="suggestedPages.length">
                    <div v-for="(page, idx) in suggestedPages" :key="page.id"
                        :class="['media', 'text-muted', { 'pt-3': idx !== 0 }]">
                        <img :src="pageAvatar(page)" alt="Suggested page" class="online-user-image align-middle">
                        <div class="media-body mb-0 small lh-125">
                            <div class="d-flex justify-content-between align-items-center w-100">
                                <strong class="text-gray-dark">
                                    <Link :href="pageLink(page)" class="smFLname">{{ page.name }}</Link>
                                </strong>
                            </div>
                            <small class="d-block text-muted">{{ page.category || 'Trang' }}</small>
                            <small class="d-block text-muted">{{ formatCount(page.follower_count) }} theo dõi</small>
                        </div>
                    </div>
                </template>
                <div v-else class="py-3 text-center text-muted small">
                    Chưa có gợi ý trang.
                </div>
            </div>

            
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    suggestedFriends: {
        type: Array,
        default: () => []
    },
    suggestedGroups: {
        type: Array,
        default: () => []
    },
    suggestedPages: {
        type: Array,
        default: () => []
    },
    contacts: {
        type: Array,
        default: () => []
    },
    notifications: {
        type: Array,
        default: () => []
    }
});

const defaultAvatar = '/images/web/users/avatar.jpg';
const defaultGroupCover = '/images/web/groups/group.webp';
const defaultPageAvatar = '/images/client/pages/default-page.png';

const avatarUrl = (user) => {
    const avatar = user?.avatar;
    if (!avatar) return defaultAvatar;
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    if (avatar.includes('/')) return `/${avatar.replace(/^\/+/, '')}`;
    return `/images/client/avatar/${avatar}`;
};

const groupCover = (group) => group?.cover_photo_url
    ? `/images/client/group/thumbnail/${group.cover_photo_url}`
    : defaultGroupCover;

const pageAvatar = (page) => {
    const avatar = page?.profile_picture_url;
    if (!avatar) return defaultPageAvatar;
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/${avatar.replace(/^\/+/, '')}`;
};

const pageLink = (page) => {
    if (!page) return '/pages/index';
    const identifier = page.username || page.id;
    return `/pages/${identifier}`;
};

const privacyLabel = (privacy) => String(privacy) === '1' ? 'Tham gia tự do' : 'Duyệt thành viên';
const formatMembers = (group) => group?.active_members_count ?? group?.members_count ?? 0;
const mutualLabel = (count) => `${count} bạn chung`;

const formatCount = (value) => {
    if (!value) return '0';
    if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`;
    if (value >= 1000) return `${(value / 1000).toFixed(1)}K`;
    return value.toString();
};

const formatTime = (timestamp) => {
    if (!timestamp) return '';
    const date = new Date(timestamp);
    return date.toLocaleString('vi-VN', { hour: '2-digit', minute: '2-digit', day: '2-digit', month: '2-digit' });
};

const notificationLabel = (type) => {
    const labels = {
        comment: 'Bình luận mới',
        like: 'Có người đã thả cảm xúc',
        friend_request: 'Yêu cầu kết bạn',
        message: 'Tin nhắn mới'
    };
    return labels[type] || 'Thông báo mới';
};

const notificationItems = computed(() =>
    props.notifications.map((note) => ({
        ...note,
        displayTime: formatTime(note.created_at)
    }))
);
</script>

<style scoped>
.third-section {
    position: sticky;
    top: 90px;
    align-self: flex-start;
}

.third-section .w-shadow {
    border: 1px solid #e5e9f0;
    box-shadow: 0 4px 20px rgba(16, 24, 40, 0.06), 0 0 1px rgba(16, 24, 40, 0.04);
    border-radius: 16px;
    background: #ffffff;
    max-height: calc(100vh - 110px);
    overflow-y: auto;
    transition: box-shadow 0.3s ease;
}

.third-section .w-shadow:hover {
    box-shadow: 0 8px 30px rgba(16, 24, 40, 0.1), 0 0 1px rgba(16, 24, 40, 0.08);
}

.third-section .w-shadow::-webkit-scrollbar {
    width: 6px;
}

.third-section .w-shadow::-webkit-scrollbar-thumb {
    background: linear-gradient(180deg, #cbd5e1 0%, #94a3b8 100%);
    border-radius: 6px;
}

.third-section .w-shadow::-webkit-scrollbar-track {
    background: transparent;
}

.third-section .card-title {
    font-weight: 700;
    font-size: 1rem;
    color: #0f172a;
    letter-spacing: -0.01em;
}

.third-section .fs-8 {
    font-size: 0.813rem;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s ease;
}

.third-section .fs-8:hover {
    color: #2563eb !important;
    text-decoration: underline;
}

.third-section .contacts {
    border: 1px solid #f1f5f9;
    border-radius: 14px;
    padding: 0.75rem;
    background: #fafbfc;
    box-shadow: inset 0 1px 3px rgba(15, 23, 42, 0.03);
}

.third-section .contacts .media {
    align-items: center;
    padding: 0.5rem;
    border-radius: 10px;
    transition: background-color 0.2s ease;
}

.third-section .contacts .media:hover {
    background-color: #ffffff;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
}

.third-section .online-user-image {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
    border: 2px solid #ffffff;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.12);
    margin-right: 0.75rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.third-section .online-user-image:hover {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.18);
}

.third-section .media-body .smFLname {
    color: #1e293b;
    font-weight: 600;
    text-decoration: none;
    font-size: 0.938rem;
    transition: color 0.2s ease;
}

.third-section .media-body .smFLname:hover {
    color: #3b82f6;
}

.third-section .media-body small {
    font-size: 0.813rem;
    color: #64748b;
}

.third-section .fs-9 {
    font-size: 0.75rem;
    color: #94a3b8;
}

.group-suggestions .suggestion-card + .suggestion-card {
    margin-top: 1rem;
}

.third-section .contacts .media + .media {
    border-top: 1px solid #f1f5f9;
    margin-top: 0.5rem;
    padding-top: 0.5rem;
}

.third-section .suggestion-card {
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
}

.third-section .suggestion-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15);
    border-color: #cbd5e1;
}

.third-section .suggestion-image {
    height: 120px;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.third-section .suggestion-card:hover .suggestion-image {
    transform: scale(1.05);
}

.third-section .overlay-card {
    position: relative;
}

.third-section .card-body {
    padding: 1rem;
}

.third-section .card-body h5 {
    font-size: 1rem;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: -0.01em;
}

.third-section .badge-light {
    background-color: #f1f5f9;
    color: #475569;
    padding: 0.25rem 0.5rem;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.688rem;
}

.third-section .card-text {
    font-size: 0.875rem;
    color: #64748b;
    line-height: 1.5;
}

.third-section .btn-quick-links {
    width: 100%;
    padding: 0.5rem;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: #ffffff;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
}

.third-section .btn-quick-links:hover {
    background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
}

.limit-1-lines {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.limit-2-lines {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

@media (max-width: 991.98px) {
    .third-section {
        position: static;
    }

    .third-section .w-shadow {
        max-height: none;
        overflow: visible;
    }
}
</style>
