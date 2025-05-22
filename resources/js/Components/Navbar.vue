<template>
    <nav id="navbar-main" class="navbar navbar-expand-lg shadow-sm sticky-top">
        <div class="w-100 justify-content-md-center">

            <ul class="navbar-nav mr-5 flex-row" id="main_menu">
                <Link class="navbar-brand nav-item mr-lg-5" href="/"><img :src="'/images/web/logo-64x64.png'" width="40"
                    height="40" class="mr-3" alt="Logo"></Link>
                <!-- Collect the nav links, forms, and other content for toggling -->
                <form class="w-30 mx-2 my-auto d-inline form-inline mr-5 dropdown search-form" @submit.prevent="submitSearch">
                    <div class="input-group" id="searchDropdown">
                        <input
                            v-model="keyword"
                            @keydown.enter="submitSearch"
                            type="text"
                            class="form-control search-input w-75"
                            placeholder="Tìm kiếm bài viết, nhóm, bạn bè, ..."
                            aria-label="Search"
                            aria-describedby="search-addon"
                        >
                        <div class="input-group-append">
                            <button class="btn search-button" type="button" @click="submitSearch">
                                <i class='bx bx-search'></i>
                            </button>
                        </div>
                    </div>
                </form>

                <MessageDropdown />
                <li class="nav-item s-nav dropdown notification">
                    <a href="#" class="nav-link nav-links rm-drop-mobile drop-w-tooltip" data-toggle="dropdown"
                        data-placement="bottom" data-title="Notifications" role="button" aria-haspopup="true"
                        aria-expanded="false">
                        <img :src="'/images/web/icons/navbar/notification.png'" class="notification-bell"
                            alt="navbar icon"> <span class="badge badge-pill badge-primary">3</span>
                    </a>
                    <ul class="dropdown-menu notify-drop dropdown-menu-right nav-drop shadow-sm">
                        <div class="notify-drop-title">
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Notifications <span
                                        class="badge badge-pill badge-primary ml-2">3</span></div>
                                <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                                    <a href="#" class="notify-right-icon">
                                        Mark All as Read
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- end notify title -->
                        <!-- notify content -->
                        <div class="drop-content">
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-10.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <a href="#" class="notification-user">Sean</a> <span
                                        class="notification-type">replied to your comment on a post in </span><a
                                        href="#" class="notification-for">PHP</a>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i class='bx bxs-group'></i></span>
                                        3h
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-7.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <a href="#" class="notification-user">Kimberly</a> <span
                                        class="notification-type">likes your comment "I would really... </span>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i class='bx bxs-like'></i></span>
                                        7h
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-8.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <span class="notification-type">10 people saw your story before it disappeared. See
                                        who saw it.</span>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i class='bx bx-images'></i></span>
                                        23h
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-11.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <a href="#" class="notification-user">Michelle</a> <span
                                        class="notification-type">posted in </span><a href="#"
                                        class="notification-for">Argon Social Design System</a>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i
                                                class='bx bxs-quote-right'></i></span> 1d
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-5.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <a href="#" class="notification-user">Karen</a> <span
                                        class="notification-type">likes your comment "Sure, here... </span>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i class='bx bxs-like'></i></span>
                                        2d
                                    </p>
                                </div>
                            </li>
                            <li>
                                <div class="col-md-2 col-sm-2 col-xs-2">
                                    <div class="notify-img">
                                        <img :src="'/images/web/users/user-12.png'" alt="notification user image">
                                    </div>
                                </div>
                                <div class="col-md-10 col-sm-10 col-xs-10">
                                    <a href="#" class="notification-user">Irwin</a> <span
                                        class="notification-type">posted in </span><a href="#"
                                        class="notification-for">Themeforest</a>
                                    <a href="#" class="notify-right-icon">
                                        <i class='bx bx-radio-circle-marked'></i>
                                    </a>
                                    <p class="time">
                                        <span class="badge badge-pill badge-primary"><i
                                                class='bx bxs-quote-right'></i></span> 3d
                                    </p>
                                </div>
                            </li>
                        </div>
                        <div class="notify-drop-footer text-center">
                            <a href="#">See More</a>
                        </div>
                    </ul>
                </li>


                <li class="nav-item s-nav">
                    <Link :href="`/${user.username}`" class="nav-link nav-links">
                    <div class="menu-user-image">
                        <img :src="user.avatar ? `/images/client/avatar/${user.avatar}` : '/images/web/users/avatar.jpg'"
                            class="menu-user-img ml-1" alt="Menu Image">
                    </div>
                    </Link>
                </li>
                <li class="nav-item s-nav nav-icon dropdown">
                    <a href="settings.html" data-toggle="dropdown" data-placement="bottom" data-title="Settings"
                        class="nav-link settings-link rm-drop-mobile drop-w-tooltip" id="settings-dropdown"><img
                            :src="'/images/web/icons/navbar/settings.png'" class="nav-settings" alt="navbar icon"></a>
                    <div class="dropdown-menu dropdown-menu-right settings-dropdown shadow-sm"
                        aria-labelledby="settings-dropdown">

                        <Link class="dropdown-item" href="/cai-dat">
                        <img :src="'/images/web/icons/navbar/gear-1.png'" alt="Navbar icon"> Cài Đặt</Link>
                        <Link class="dropdown-item logout-btn" href="/dang-xuat" method="post" as="button">
                        <img :src="'/images/web/icons/navbar/logout.png'" alt="Navbar icon"> Đăng Xuất</Link>
                    </div>
                </li>
                <button type="button" class="btn nav-link" id="menu-toggle"><img
                        :src="'/images/web/icons/theme/navs.png'" alt="Navbar navs"></button>
            </ul>

        </div>
    </nav>

</template>
<script setup>
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Link } from '@inertiajs/vue3';
import { usePage } from "@inertiajs/vue3";
import { computed } from "vue";
import MessageDropdown from '../components/Messages/MessageDropdown.vue'

const page = usePage();
const user = computed(() => page.props.auth.user);

const keyword = ref('');
const searchType = ref('people'); // mặc định là tìm mọi người

const submitSearch = () => {
    if (keyword.value.trim()) {
        router.get('/search', { q: keyword.value, type: searchType.value });
    }
};

// Watch user changes to update window.userId
watch(user, (newUser) => {
    if (newUser) {
        window.userId = newUser.id;
        console.log('User ID set to:', window.userId);
    }
}, { immediate: true });

onMounted(() => {
    if (user.value) {
        window.userId = user.value.id;
        console.log('User ID set on mount:', window.userId);
    }
});
</script>
