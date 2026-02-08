<template>
    <App>
        <div class="jumbotron groups-banner">
            <div class="container group-banner-content">
                <h1 class="jumbotron-heading mt-auto">
                    <img :src="'/images/web/icons/theme/group-white.png'" class="mr-3" alt="Welcome to groups">
                </h1>
                <p>Get latest active from your groups.</p>
            </div>
        </div>
        <div id="content-page" class="content-page">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap">
                            <div class="group-info d-flex align-items-center">
                                <div class="me-3">
                                    <img class="rounded-circle img-fluid avatar-100" :src="group.cover_photo_url
                                        ? `/images/client/group/thumbnail/${group.cover_photo_url}`
                                        : '/images/web/groups/group.webp'" alt="Group Cover" />
                                </div>
                                <div class="info">
                                    <h3>{{ group.name }}</h3>
                                    <p class="mb-0">
                                        <i
                                            :class="group.privacy_setting ? 'bx bx-lock-open pe-2' : 'bx bx-lock-alt pe-2'"></i>
                                        {{ group.privacy_setting ? 'Public' : 'Private' }} Group · {{
                                            group.members_count }} thành viên
                                    </p>

                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="group-member d-flex align-items-center mt-md-0 mt-2" v-if="isMember">
                                    <button @click="leaveGroup" type="button" class="btn btn-primary mb-2 me-2">
                                        Rời nhóm
                                    </button>
                                    <button v-if="isAdmin" @click="deleteGroup" type="button"
                                        class="btn btn-danger mb-2">
                                        Xóa nhóm
                                    </button>
                                </div>
                                <div class="group-member d-flex align-items-center mt-md-0 mt-2" v-else>
                                    <button @click="joinGroup" type="button" class="btn btn-primary mb-2">Tham gia
                                        nhóm</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Main Content Area -->
                    <div class="col-lg-8">
                        <slot v-if="isMember"></slot>
                        <!-- <ListPost v-if="isMember" :group="group" :user_auth="user_auth" :is-member="isMember" /> -->
                        <div v-else-if="isPending" class="card">
                            <div class="card-body">
                                <p>Yêu cầu tham gia nhóm của bạn đang chờ phê duyệt.</p>
                            </div>
                        </div>
                        <div v-else class="card">
                            <div class="card-body">
                                <p v-if="group.privacy_setting == 0">Bạn cần được phê duyệt để tham gia nhóm này.</p>
                                <p v-else>Bạn cần tham gia nhóm để xem nội dung và đăng bài.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div v-if="isMember" class="col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Điều hướng</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.show') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-news'></i>
                                            </div>
                                            <h6 class="mb-0">Bài Viết</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/about`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.about') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bx-info-circle'></i>
                                            </div>
                                            <h6 class="mb-0">Giới Thiệu</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/members`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.members') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-user-detail'></i>
                                            </div>
                                            <h6 class="mb-0">Thành Viên</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/my-posts`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.my-posts') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-edit-alt'></i>
                                            </div>
                                            <h6 class="mb-0">Bài Viết Của Tôi</h6>
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Admin Panel -->
                        <div v-if="isAdmin" class="card mt-3">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Dành Cho Admin</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/pending-posts`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.pending-posts') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bx-list-check'></i>
                                            </div>
                                            <h6 class="mb-0">Duyệt Bài Viết</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/pending-requests`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.pending-requests') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-user-detail'></i>
                                            </div>
                                            <h6 class="mb-0">Yêu Cầu Tham Gia</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/statistics`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.statistics') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bx-bar-chart-alt-2'></i>
                                            </div>
                                            <h6 class="mb-0">Thống Kê</h6>
                                        </Link>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <Link :href="`/groups/${group.id}/edit`" class="nav-link-item"
                                            :class="{ 'active': isCurrentRoute('groups.edit') }">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-edit'></i>
                                            </div>
                                            <h6 class="mb-0">Cập Nhật Nhóm</h6>
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import App from '../../Layouts/App.vue';
import { router, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    group: Object,
    isMember: Boolean,
    isPending: Boolean,
    pendingPosts: Object,
    pendingRequests: Object,
    isAdmin: Boolean,
});

const page = usePage();

const isCurrentRoute = (routeName) => {
    return page.props.ziggy?.location?.includes(routeName) || false;
};

const joinGroup = () => {
    router.post(`/groups/${props.group.id}/join`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload();
        }
    });
};

const leaveGroup = () => {
    if (!confirm('Bạn có chắc muốn rời khỏi nhóm này?')) return;

    router.post(`/groups/${props.group.id}/leave`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload();
        }
    });
};

const deleteGroup = () => {
    if (confirm('Bạn có chắc muốn xóa nhóm này? Hành động này không thể hoàn tác!')) {
        router.delete(`/groups/${props.group.id}`, {
            onSuccess: () => {
                router.visit('/groups');
            }
        });
    }
};
</script>

<style scoped>
@import '../../../css/socialv.css';

.avatar-100 {
    width: 100px;
    height: 100px;
    object-fit: cover;
}

.groups-banner {
    background-color: #0d6efd;
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
}

.group-banner-content {
    text-align: center;
}

.group-info {
    flex: 1;
}

.info h3 {
    margin-bottom: 0.5rem;
}

.info p {
    color: #6c757d;
}

.btn-primary {
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.btn-primary:hover {
    opacity: 0.9;
}

.nav-link-item {
    display: flex;
    align-items: center;
    width: 100%;
    text-decoration: none;
    color: #1e293b;
    border: 0;
    background: transparent;
    padding: 0.75rem;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.nav-link-item:hover {
    background-color: rgba(102, 126, 234, 0.1);
    color: #667eea;
}

.nav-link-item.active {
    background-color: rgba(102, 126, 234, 0.15);
    color: #667eea;
    font-weight: 600;
}

.nav-link-item.active .avatar-40 {
    background-color: #667eea !important;
    color: white;
}

.nav-link-item .avatar-40 {
    transition: all 0.2s ease;
}

.nav-link-item h6 {
    margin: 0;
}

.text-primary {
    color: #0d6efd !important;
}
</style>
