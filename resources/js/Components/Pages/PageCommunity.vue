<template>
    <div class="community-section">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon followers">
                    <i class="bx bx-user-plus"></i>
                </div>
                <div>
                    <div class="stat-value">{{ counts.followers }}</div>
                    <div class="stat-label">Người theo dõi</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon posts">
                    <i class="bx bx-file"></i>
                </div>
                <div>
                    <div class="stat-value">{{ counts.posts }}</div>
                    <div class="stat-label">Bài viết</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon admins">
                    <i class="bx bx-shield-quarter"></i>
                </div>
                <div>
                    <div class="stat-value">{{ counts.admins }}</div>
                    <div class="stat-label">Quản trị viên</div>
                </div>
            </div>
        </div>

        <div class="grid">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h4>Quản trị viên</h4>
                        <small class="text-muted">Những người đang quản lý trang</small>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="admins.length" class="user-list">
                        <div v-for="admin in admins" :key="admin.id" class="user-row">
                            <img :src="avatarUrl(admin.avatar)" class="avatar" />
                            <div class="user-info">
                                <div class="name">{{ admin.name }}</div>
                                <div class="username">@{{ admin.username || admin.id }}</div>
                            </div>
                            <span class="badge-role">{{ admin.role }}</span>
                        </div>
                    </div>
                    <div v-else class="empty-state">Chưa có quản trị viên.</div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h4>Người theo dõi</h4>
                        <small class="text-muted">50 người theo dõi gần đây</small>
                    </div>
                </div>
                <div class="card-body">
                    <div v-if="followers.length" class="user-list">
                        <div v-for="follower in followers" :key="follower.id" class="user-row">
                            <img :src="avatarUrl(follower.avatar)" class="avatar" />
                            <div class="user-info">
                                <div class="name">{{ follower.name }}</div>
                                <div class="username">@{{ follower.username || follower.id }}</div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="empty-state">Chưa có người theo dõi.</div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
const props = defineProps({
    counts: { type: Object, default: () => ({ followers: 0, posts: 0, admins: 0 }) },
    admins: { type: Array, default: () => [] },
    followers: { type: Array, default: () => [] }
});

const defaultAvatar = '/images/default/avatar.jpg';
const avatarUrl = (avatar) => {
    if (!avatar) return defaultAvatar;
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};
</script>

<style scoped>
.community-section {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 12px;
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.stat-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 20px;
}

.stat-icon.followers { background: #2563eb; }
.stat-icon.posts { background: #f59e0b; }
.stat-icon.admins { background: #10b981; }

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    color: #6b7280;
    font-size: 13px;
}

.grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 16px;
}

.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
}

.card-header {
    padding: 14px 16px;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.card-body {
    padding: 14px 16px;
}

.user-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.user-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
}

.user-info .name {
    font-weight: 600;
    color: #111827;
}

.user-info .username {
    color: #6b7280;
    font-size: 13px;
}

.badge-role {
    margin-left: auto;
    background: #eef2ff;
    color: #1d4ed8;
    padding: 4px 8px;
    border-radius: 999px;
    font-weight: 700;
    font-size: 12px;
    text-transform: capitalize;
}

.empty-state {
    text-align: center;
    color: #6b7280;
    padding: 10px;
    font-size: 14px;
}
</style>
