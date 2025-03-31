<template>
    <App>
        <div class="col-md-12 message-right-side">
            <div class="row profile-right-side-content">
                <div class="user-profile">
                    <div class="profile-header-background">
                        <a href="#" class="profile-cover">
                            <img :src="'/images/web/users/cover/cover-1.gif'" alt="Profile Header Background" />
                            <div class="cover-overlay">
                                <a href="#" class="btn btn-update-cover"><i class="bx bxs-camera"></i> Update Cover
                                    Photo</a>
                            </div>
                        </a>
                    </div>
                    <div class="row profile-rows px-5">
                        <div class="col-md-4">
                            <div class="profile-info-left">
                                <div class="text-center">
                                    <div class="profile-img w-shadow">
                                        <div class="profile-img-overlay"></div>
                                        <img :src="user.avatar
                                                ? `/images/client/avatar/${user.avatar}`
                                                : '/images/web/users/avatar.jpg'
                                            " alt="Avatar" class="avatar img-circle" />

                                        <div v-if="isOwner" class="profile-img-caption">
                                            <label for="updateProfilePic" class="upload">
                                                <i class="bx bxs-camera"></i>
                                                Update
                                                <input type="file" id="updateProfilePicInput"
                                                    class="text-center upload" />
                                            </label>
                                        </div>
                                    </div>
                                    <p class="profile-fullname mt-3">
                                        {{ user.name }}
                                    </p>
                                    <p class="profile-username mb-3 text-muted">
                                        @arthur_minasyan
                                    </p>
                                </div>
                                <div class="intro mt-4">
                                    <div class="d-flex">
                                        <!-- Nếu chưa kết bạn -->
                                        <button v-if="
                                            friendshipStatus === 'none' &&
                                            !isOwner
                                        " @click="sendFriendRequest" class="btn btn-follow">
                                            <i class="bx bx-plus"></i> Kết Bạn
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
                                            <i class="bx bx-user-check"></i> Bạn
                                            bè
                                        </button>

                                        <button v-if="!isOwner" type="button" class="btn btn-start-chat"
                                            data-toggle="modal" data-target="#newMessageModal">
                                            <i class="bx bxs-message-rounded"></i>
                                            <span class="fs-8">Nhắn Tin</span>
                                        </button>

                                        <button type="button" class="btn btn-follow" id="moreMobile"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="bx bx-dots-horizontal-rounded"></i>
                                            <span class="fs-8">More</span>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right profile-ql-dropdown"
                                            aria-labelledby="moreMobile">
                                            <a href="newsfeed.html" class="dropdown-item">Bài Viết</a>
                                            <a href="about.html" class="dropdown-item">Bạn Bè</a>
                                            <a href="followers.html" class="dropdown-item">Ảnh</a>
                                            <a href="following.html" class="dropdown-item">Video</a>
                                            <a href="photos.html" class="dropdown-item">Nhóm</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="intro mt-5 mv-hidden">
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <h3 class="intro-about">Giới Thiệu</h3>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bx-briefcase text-primary"></i>
                                            Web Developer at
                                            <a href="#">Company Name</a>
                                        </p>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bx-map text-primary"></i>
                                            Lives in
                                            <a href="#">City, Country</a>
                                        </p>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bx-time text-primary"></i>
                                            Last Login
                                            <a href="#">Online
                                                <span class="ml-1 online-status bg-success"></span></a>
                                        </p>
                                    </div>
                                    <div v-if="isOwner"
                                        class="intro-item d-flex justify-content-between align-items-center">
                                        <a href="#" class="btn btn-quick-link join-group-btn border w-100">Edit
                                            Details</a>
                                    </div>
                                </div>
                                <div class="intro mt-5 row mv-hidden">
                                    <div class="col-md-4">
                                        <img :src="'/images/web/users/album/album-1.jpg'" width="95" alt="" />
                                    </div>
                                    <div class="col-md-4">
                                        <img :src="'/images/web/users/album/album-2.jpg'" width="95" alt="" />
                                    </div>
                                    <div class="col-md-4">
                                        <img :src="'/images/web/users/album/album-3.jpg'" width="95" alt="" />
                                    </div>
                                </div>
                                <div class="intro mt-5 mv-hidden">
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <h3 class="intro-about">
                                            Other Social Accounts
                                        </h3>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bxl-facebook-square facebook-color"></i>
                                            <a href="#" target="_blank">facebook.com/username</a>
                                        </p>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bxl-twitter twitter-color"></i>
                                            <a href="#" target="_blank">twitter.com/username</a>
                                        </p>
                                    </div>
                                    <div class="intro-item d-flex justify-content-between align-items-center">
                                        <p class="intro-title text-muted">
                                            <i class="bx bxl-instagram instagram-color"></i>
                                            <a href="#" target="_blank">instagram.com/username</a>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-8 p-0">
                            <div class="profile-info-right">
                                <div class="col-md-12 profile-center">
                                    <ul
                                        class="list-inline profile-links d-flex justify-content-between w-shadow rounded">
                                        <li class="list-inline-item " :class="{ 'profile-active': activeTab === 'listpost' }">
                                            <Link :href="`/${user.username}`">Bài Viết</Link>
                                        </li>
                                        <li class="list-inline-item">
                                            <Link :href="`/${user.username}`">Giới Thiệu</Link>
                                        </li>
                                        <li class="list-inline-item" :class="{ 'profile-active': activeTab === 'friend' }">
                                            <Link :href="`/${user.username}/friend`" >Bạn Bè</Link>
                                        </li>
                                        <li class="list-inline-item">
                                            <Link href="#">Nhóm</Link>
                                        </li>
                                        <li class="list-inline-item">
                                            <Link href="#">Ảnh</Link>
                                        </li>
                                        <li class="list-inline-item">
                                            <Link href="#">Video</Link>
                                        </li>
                                        <li class="list-inline-item dropdown">
                                            <a href="#" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="bx bx-dots-vertical-rounded"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-right profile-ql-dropdown">
                                                <a href="#" class="dropdown-item">Activity Log</a>
                                                <a href="#" class="dropdown-item">Videos</a>
                                                <a href="#" class="dropdown-item">Check-Ins</a>
                                                <a href="#" class="dropdown-item">Events</a>
                                                <a href="#" class="dropdown-item">Likes</a>
                                            </div>
                                        </li>
                                    </ul>
                                    <slot></slot>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </App>
</template>

<script setup>
import App from "../../Layouts/App.vue";
import { Link, usePage } from "@inertiajs/vue3";
import { defineProps, computed, ref, onMounted } from "vue";
import axios from "axios";
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

onMounted(() => {
    fetchFriendshipStatus();
});
</script>
<style scoped>
@import "../../../css/profile.css";
</style>
