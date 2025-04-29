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
                                    <img class="rounded-circle img-fluid avatar-100"
                                        :src="`/images/client/group/thumbnail/${group.cover_photo_url}`">
                                </div>
                                <div class="info">
                                    <h3>{{ group.name }}</h3>
                                    <p class="mb-0">
                                        <i
                                            :class="group.privacy_setting ? 'bx bx-lock-alt pe-2' : 'bx bx-lock-open pe-2'"></i>
                                        {{ group.privacy_setting ? 'Private' : 'Public' }} Group · {{
                                            group.members_count }} thành viên
                                    </p>

                                </div>
                            </div>
                            <div class="d-flex align-items-center">
                                <div class="group-member d-flex align-items-center mt-md-0 mt-2" v-if="isMember">
                                    <button @click="leaveGroup" type="button" class="btn btn-primary mb-2">Rời
                                        nhóm</button>
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
                        <ListPost v-if="isMember" :group="group" :user_auth="user_auth" :is-member="isMember" />
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
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Groups</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3 border-bottom">
                                        <div class="iq-search-bar members-search p-0">
                                            <form action="#" class="searchbox w-auto">
                                                <input type="text" class="text search-input bg-grey"
                                                    placeholder="Type here to search...">
                                                <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                                            </form>
                                        </div>
                                    </li>
                                    <li>
                                        <button type="submit" class="btn btn-primary d-block w-100 mb-3"><i
                                                class="ri-add-line pe-2"></i>Tìm Kiếm</button>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="avatar-40 rounded-circle bg-gray text-center me-3"><i
                                                class='bx bxs-news'></i></div>
                                        <h6 class="mb-0">Bài Viết</h6>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="avatar-40 rounded-circle bg-gray text-center me-3"><i
                                                class='bx bxs-user-detail'></i></div>
                                        <h6 class="mb-0">Thành Viên</h6>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="avatar-40 rounded-circle bg-gray text-center me-3"><i
                                                class='bx bxs-edit-alt'></i></div>
                                        <h6 class="mb-0">Bài Viết Của Tôi</h6>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Admin Panel -->
                        <div v-if="isCreator" class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Dành Cho Admin</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3 d-flex align-items-center">
                                        <button @click="router.visit(`/groups/${group.id}/pending-posts`)"
                                            class="d-flex align-items-center w-100 text-decoration-none text-dark border-0 bg-transparent">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bx-list-check'></i>
                                            </div>
                                            <h6 class="mb-0">Duyệt Bài Viết</h6>
                                        </button>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <button @click="router.visit(`/groups/${group.id}/pending-requests`)"
                                            class="d-flex align-items-center w-100 text-decoration-none text-dark border-0 bg-transparent">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-user-detail'></i>
                                            </div>
                                            <h6 class="mb-0">Yêu Cầu Tham Gia</h6>
                                        </button>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <button @click="router.visit(`/groups/${group.id}/edit`)"
                                            class="d-flex align-items-center w-100 text-decoration-none text-dark border-0 bg-transparent">
                                            <div class="avatar-40 rounded-circle bg-gray text-center me-3">
                                                <i class='bx bxs-edit'></i>
                                            </div>
                                            <h6 class="mb-0">Cập Nhật Thông Tin Nhóm</h6>
                                        </button>
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
import ListPost from './ListPost.vue';
import PendingPosts from './Admin/PendingPosts.vue';
import PendingRequests from './Admin/PendingRequests.vue';
import UpdateGroup from './Admin/UpdateGroup.vue';
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    group: Object,
    isMember: Boolean,
    isPending: Boolean,
    pendingPosts: Object,
    pendingRequests: Object
});

const page = usePage();
const user_auth = computed(() => page.props.auth.user);
const isCreator = computed(() => props.group.creator_id === user_auth.value.id);

const joinGroup = () => {
    router.post(`/groups/${props.group.id}/join`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload();
        }
    });
};

const leaveGroup = () => {
    router.post(`/groups/${props.group.id}/leave`, {}, {
        preserveScroll: true,
        onSuccess: () => {
            router.reload();
        }
    });
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

.bg-transparent:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.text-primary {
    color: #0d6efd !important;
}

/* Thêm hiệu ứng cho nút active */
button.text-primary {
    font-weight: 600;
}

button.text-primary .avatar-40 {
    background-color: rgba(13, 110, 253, 0.1);
}
</style>