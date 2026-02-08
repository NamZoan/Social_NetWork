<template>
    <App>
        <div class="col-md-12 message-right-side">
            <div class="row profile-right-side-content">
                <div class="user-profile">
                    <div class="profile-header-background">
                        <div class="profile-cover">
                            <img
                                :src="
                                    user.cover_photo
                                        ? `/images/client/cover/${user.cover_photo}`
                                        : '/images/web/users/cover/cover-1.gif'
                                "
                                alt="Profile Header Background"
                            />
                            <div v-if="isOwner" class="cover-overlay">
                                <label class="btn btn-update-cover" for="updateCoverInput">
                                    <i class="bx bxs-camera"></i>
                                    Đổi ảnh bìa
                                    <input
                                        id="updateCoverInput"
                                        type="file"
                                        accept="image/*"
                                        style="display: none"
                                        @change="onCoverChange"
                                    />
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row profile-rows px-5">
                        <div class="col-md-4">
                            <div class="profile-info-left">
                                <div class="text-center">
                                    <div class="profile-img w-shadow">
                                        <div class="profile-img-overlay"></div>
                                        <img :src="getAvatarUrl(user.avatar)" alt="Avatar" class="avatar img-circle" />

                                        <div v-if="isOwner" class="profile-img-caption">
                                            <label for="updateProfilePicInput" class="upload">
                                                <i class="bx bxs-camera"></i>
                                                Update
                                                <input type="file" id="updateProfilePicInput"
                                                    class="text-center upload"
                                                    @change="onAvatarChange"
                                                    accept="image/*"
                                                    style="display: none;"
                                                />
                                            </label>
                                        </div>
                                    </div>
                                    <p class="profile-fullname mt-3">
                                        {{ user.name }}
                                    </p>
                                    <p class="profile-username mb-3 text-muted">
                                        {{ '@' + user.username }}
                                    </p>
                                </div>
                                <div class="intro mt-4">
                                    <div class="d-flex">
                                        <!-- Nếu chưa kết bạn -->
                                        <button v-if="
                                            friendshipStatus === 'none' &&
                                            !isOwner
                                        " @click="sendFriendRequest" class="btn btn-follow">
                                            <i class="bx bx-plus"></i> Kết bạn
                                        </button>

                                        <!-- Nếu đã gửi lời mời kết bạn -->
                                        <button v-else-if="
                                            friendshipStatus === 'sent' &&
                                            !isOwner
                                        " class="btn btn-follow" @click="unfriend">
                                            <i class="bx bx-time"></i> Đã gửi
                                            yêu cầu
                                        </button>

                                        <!-- Nếu nhận được lời mời kết bạn -->
                                        <button v-else-if="
                                            friendshipStatus ===
                                            'received' && !isOwner
                                        " @click="acceptFriendRequest" class="btn btn-follow">
                                            <i class="bx bx-check"></i> Xác nhận
                                        </button>

                                        <!-- Nếu đã là bạn bè -->
                                        <button v-else-if="
                                            friendshipStatus ===
                                            'friends' && !isOwner
                                        " class="btn btn-follow" @click="unfriend">
                                            <i class="bx bx-user-check"></i> Bạn bè
                                        </button>

                                        <button v-if="!isOwner" type="button" class="btn btn-start-chat"
                                            @click="openMessageModal">
                                            <i class="bx bxs-message-rounded"></i>
                                            <span class="fs-8">Nhắn tin</span>
                                        </button>

                                        <button type="button" class="btn btn-follow" id="moreMobile"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                            <span class="fs-8">More</span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right profile-ql-dropdown"
                                            aria-labelledby="moreMobile">
                                            <span class="dropdown-item text-muted small">
                                                Tính năng khác đang phát triển
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="intro mt-5">
                                    <div class="intro-item d-flex justify-content-between align-items-center mb-2">
                                        <h3 class="intro-about mb-0">Giới thiệu</h3>
                                    </div>
                                    <div v-if="user.email" class="intro-item d-flex align-items-center">
                                        <p class="intro-title text-muted mb-1">
                                            <i class="bx bx-envelope text-primary"></i>
                                            {{ user.email }}
                                        </p>
                                    </div>
                                    <div v-if="user.phone" class="intro-item d-flex align-items-center">
                                        <p class="intro-title text-muted mb-1">
                                            <i class="bx bx-phone text-primary"></i>
                                            {{ user.phone }}
                                        </p>
                                    </div>
                                    <div v-if="user.birthday" class="intro-item d-flex align-items-center">
                                        <p class="intro-title text-muted mb-0">
                                            <i class="bx bx-cake text-primary"></i>
                                            {{ user.birthday }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 p-0">
                            <div class="profile-info-right">
                                <div class="col-md-12 profile-center">
                                    <ul
                                        class="list-inline profile-links d-flex justify-content-start w-shadow rounded">
                                        <li
                                            class="list-inline-item"
                                            :class="{ 'profile-active': activeTab === 'listpost' }"
                                        >
                                            <Link :href="`/${user.username}`">Bài viết</Link>
                                        </li>
                                        <li
                                            class="list-inline-item"
                                            :class="{ 'profile-active': activeTab === 'friend' }"
                                        >
                                            <Link :href="`/${user.username}/friend`">Bạn bè</Link>
                                        </li>
                                    </ul>
                                    <slot name="filters"></slot>
                                    <slot></slot>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Message Modal -->
        <ProfileMessageModal
            v-if="!isOwner"
            :recipient-id="user.id"
            :recipient-name="user.name"
            :recipient-username="user.username"
            :recipient-avatar="user.avatar"
        />
    </App>
</template>

<script setup>
import App from "../../Layouts/App.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { defineProps, computed, ref, onMounted, nextTick } from "vue";
import axios from "axios";
import { useForm } from "@inertiajs/vue3";
import ProfileMessageModal from "../../Components/Profile/ProfileMessageModal.vue";
const props = defineProps({
    user: Object,
    activeTab: String,
});
const page = usePage();
const user_auth = computed(() => page.props.auth.user);

const isOwner = computed(() => {
    return props.user.id === user_auth.value.id;
});



const friendshipStatus = ref("none"); // Trạng thái kết bạn: 'none', 'pending', 'accepted'
// Lấy trạng thái kết bạn
const fetchFriendshipStatus = async () => {
    try {
        const response = await axios.get(
            `/friendship-status/${props.user.username}`
        );
        friendshipStatus.value = response.data.status;
    } catch (error) {
        console.error("Lỗi khi lấy trạng thái kết bạn:", error);
    }
};

// Gửi lời mời kết bạn
const sendFriendRequest = async () => {
    try {
        await axios.post("/send-friend-request", { user_id: props.user.id });
        friendshipStatus.value = "sent"; // Cập nhật trạng thái ngay mà không cần load lại trang
    } catch (error) {
        console.error("Lỗi khi gửi lời mời kết bạn:", error);
    }
};

// Chấp nhận lời mời kết bạn
const acceptFriendRequest = async () => {
    try {
        await axios.post("/accept-friend-request", { user_id: props.user.id });
        friendshipStatus.value = "friends"; // Cập nhật trạng thái ngay mà không cần load lại trang
    } catch (error) {
        console.error("Lỗi khi chấp nhận lời mời kết bạn:", error);
    }
};

// Hủy kết bạn mà không load lại trang
const unfriend = async () => {
    try {
        const response = await axios.post("/unfriend", {
            user_id: props.user.id,
        });
        friendshipStatus.value = "none"; // Cập nhật trạng thái ngay
    } catch (error) {
        console.error("Lỗi khi hủy kết bạn:", error);
    }
};

// Avatar
const avatarForm = useForm({
    avatar: null,
});

const coverForm = useForm({
    cover_photo: null,
});

const onAvatarChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    avatarForm.avatar = file;
    avatarForm.post("/user/update-avatar", {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
    });
};

const onCoverChange = (e) => {
    const file = e.target.files[0];
    if (!file) return;
    coverForm.cover_photo = file;
    coverForm.post("/user/update-cover", {
        preserveScroll: true,
        onSuccess: () => {
            window.location.reload();
        },
    });
};

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

// Mở modal nhắn tin
const openMessageModal = async () => {
    await nextTick();
    $('#newMessageModal').modal('show');
};

onMounted(() => {
    fetchFriendshipStatus();
});
</script>
<style scoped>
@import "../../../css/profile.css";
</style>

