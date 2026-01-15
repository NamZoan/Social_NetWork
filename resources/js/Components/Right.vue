<template>
    <div class="col-md-3 third-section">
        <div class="p-3 bg-white rounded w-shadow">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="card-title mb-0">Gợi ý kết bạn</h6>
                <Link href="/search" class="fs-8 text-primary">Xem thêm</Link>
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
                <Link href="/groups" class="fs-8 text-primary">Xem Thêm</Link>
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
                <Link href="/pages/index" class="fs-8 text-primary">Xem thêm</Link>
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

const defaultAvatar = '/images/default/avatar.jpg';
const defaultGroupCover = '/images/default/group.jpg';
const defaultPageAvatar = '/images/default/page.jpg';

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
    border: 1px solid #e9eef3;
    box-shadow: 0 12px 26px rgba(16, 24, 40, 0.08);
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #f9fbfd 100%);
    max-height: calc(100vh - 110px);
    overflow-y: auto;
}

.third-section .w-shadow::-webkit-scrollbar {
    width: 6px;
}

.third-section .w-shadow::-webkit-scrollbar-thumb {
    background: #d5dbe3;
    border-radius: 6px;
}

.third-section .w-shadow::-webkit-scrollbar-track {
    background: transparent;
}

.third-section .card-title {
    font-weight: 700;
    color: #1f2a37;
    letter-spacing: 0.01em;
}

.third-section .contacts {
    border: 1px solid #edf1f5;
    border-radius: 12px;
    padding: 0.5rem 0.75rem;
    background: #ffffff;
}

.third-section .contacts .media {
    align-items: center;
}

.group-suggestions .suggestion-card + .suggestion-card {
    margin-top: 1rem;
}

.third-section .contacts .media + .media {
    border-top: 1px dashed #edf1f5;
    margin-top: 0.65rem;
    padding-top: 0.65rem;
}

.third-section .suggestion-card {
    border: 1px solid #e7edf4;
    box-shadow: 0 10px 18px rgba(15, 23, 42, 0.1);
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.third-section .suggestion-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.14);
}

.limit-1-lines {
    display: -webkit-box;
    -webkit-line-clamp: 1;
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
