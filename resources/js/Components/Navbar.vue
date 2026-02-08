<template>
    <nav id="navbar-main" class="navbar navbar-expand-lg sticky-top">
        <div class="navbar-container">
            <ul class="navbar-nav" id="main_menu">
                <!-- Logo -->
                <li class="nav-item logo-item">
                    <Link class="navbar-brand" href="/">
                        <img :src="'/images/web/logo-64x64.png'" width="40" height="40" alt="Logo" class="logo-img">
                    </Link>
                </li>

                <!-- Search Bar -->
                <li class="nav-item search-item">
                    <form class="search-form" @submit.prevent="submitSearch">
                        <div class="search-wrapper">
                            <i class='bx bx-search search-icon-left'></i>
                            <input
                                v-model="keyword"
                                @keydown.enter="submitSearch"
                                type="text"
                                class="search-input"
                                placeholder="Tìm kiếm bài viết, nhóm, bạn bè..."
                                aria-label="Search"
                            >
                            <button class="search-button" type="button" @click="submitSearch" v-if="keyword">
                                <i class='bx bx-right-arrow-alt'></i>
                            </button>
                        </div>
                    </form>
                </li>

                <!-- Right Side Icons -->
                <div class="nav-icons-wrapper">
                    <MessageDropdown />
                    <Notification />

                    <!-- User Profile -->
                    <li class="nav-item user-nav">
                        <Link :href="`/${user.username}`" class="nav-link user-link">
                            <div class="user-avatar-wrapper">
                                <img :src="getAvatarUrl(user.avatar)"
                                    class="user-avatar" alt="User Avatar">
                            </div>
                        </Link>
                    </li>

                    <!-- Settings Dropdown -->
                    <li class="nav-item dropdown settings-nav">
                        <a href="#" data-toggle="dropdown" class="nav-link settings-link" id="settings-dropdown">
                            <div class="icon-wrapper">
                                <i class='bx bx-cog'></i>
                            </div>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right settings-dropdown"
                            aria-labelledby="settings-dropdown">
                            <Link class="dropdown-item" href="/cai-dat">
                                <i class='bx bx-cog'></i>
                                <span>Cài Đặt</span>
                            </Link>
                            <div class="dropdown-divider"></div>
                            <Link class="dropdown-item logout-item" href="/dang-xuat" method="post" as="button">
                                <i class='bx bx-log-out'></i>
                                <span>Đăng Xuất</span>
                            </Link>
                        </div>
                    </li>

                    <!-- Menu Toggle -->
                    <li class="nav-item menu-toggle-nav">
                        <button type="button" class="nav-link menu-toggle-btn" id="menu-toggle">
                            <i class='bx bx-menu'></i>
                        </button>
                    </li>
                </div>
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
import MessageDropdown from '../Components/Messages/MessageDropdown.vue';
import Notification from './Notification/Notification.vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const keyword = ref('');
const searchType = ref('people'); // mặc định là tìm mọi người

const submitSearch = () => {
    if (keyword.value.trim()) {
        router.get('/search', { q: keyword.value, type: searchType.value });
    }
};

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
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

<style scoped>
/* Main Navbar Styling */
#navbar-main {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    padding: 0;
    box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    transition: all 0.3s ease;
}

.navbar-container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 0 20px;
}

#main_menu {
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
    margin: 0;
    padding: 8px 0;
    list-style: none;
}

/* Logo Styling */
.logo-item {
    margin-right: 20px;
}

.navbar-brand {
    display: flex;
    align-items: center;
    padding: 8px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.navbar-brand:hover {
    background: rgba(255, 255, 255, 0.15);
    transform: scale(1.05);
}

.logo-img {
    filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
    transition: transform 0.3s ease;
}

.navbar-brand:hover .logo-img {
    transform: rotate(5deg);
}

/* Search Bar Styling */
.search-item {
    flex: 1;
    max-width: 500px;
    margin: 0 20px;
}

.search-form {
    width: 100%;
}

.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 25px;
    padding: 0 16px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.search-wrapper:hover {
    background: white;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
    transform: translateY(-1px);
}

.search-wrapper:focus-within {
    background: white;
    box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.3);
}

.search-icon-left {
    color: #667eea;
    font-size: 20px;
    margin-right: 10px;
    transition: color 0.3s ease;
}

.search-wrapper:focus-within .search-icon-left {
    color: #764ba2;
}

.search-input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 12px 8px;
    font-size: 15px;
    color: #333;
    outline: none;
}

.search-input::placeholder {
    color: #999;
    font-weight: 400;
}

.search-button {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 50%;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 8px;
}

.search-button:hover {
    transform: scale(1.1) rotate(90deg);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.search-button i {
    font-size: 18px;
}

/* Right Side Icons */
.nav-icons-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item {
    list-style: none;
}

.nav-link {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px;
    color: white;
    text-decoration: none;
    transition: all 0.3s ease;
}

/* Icon Wrapper */
.icon-wrapper {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 50%;
    transition: all 0.3s ease;
}

.icon-wrapper:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.1);
}

.icon-wrapper i {
    font-size: 22px;
    color: white;
}

/* User Avatar */
.user-nav .nav-link {
    padding: 4px;
}

.user-avatar-wrapper {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    overflow: hidden;
    border: 2px solid rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.user-avatar-wrapper:hover {
    border-color: white;
    transform: scale(1.1);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
}

.user-avatar {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Settings Dropdown */
.settings-dropdown {
    background: white;
    border: none;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
    margin-top: 8px;
    min-width: 200px;
    overflow: hidden;
    animation: dropdownFadeIn 0.3s ease;
}

@keyframes dropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.settings-dropdown .dropdown-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 20px;
    color: #333;
    transition: all 0.2s ease;
    font-size: 15px;
    border: none;
    background: transparent;
    width: 100%;
    text-align: left;
    cursor: pointer;
}

.settings-dropdown .dropdown-item:hover {
    background: linear-gradient(135deg, #667eea 0%, #3b82f6 100%);
    color: white;
    padding-left: 24px;
}

.settings-dropdown .dropdown-item i {
    font-size: 20px;
}

.dropdown-divider {
    margin: 0;
    border-color: #f0f0f0;
}

.logout-item:hover {
    background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%) !important;
}

/* Menu Toggle Button */
.menu-toggle-btn {
    background: rgba(255, 255, 255, 0.15);
    border: none;
    border-radius: 12px;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.menu-toggle-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: scale(1.05);
}

.menu-toggle-btn i {
    font-size: 24px;
}

/* Responsive Design */
@media (max-width: 992px) {
    .navbar-container {
        padding: 0 15px;
    }

    .search-item {
        max-width: 300px;
        margin: 0 10px;
    }

    .nav-icons-wrapper {
        gap: 6px;
    }
}

@media (max-width: 768px) {
    #main_menu {
        flex-wrap: wrap;
        padding: 8px 0;
    }

    .search-item {
        order: 3;
        flex: 1 1 100%;
        max-width: 100%;
        margin: 10px 0 0 0;
    }

    .logo-item {
        margin-right: auto;
    }

    .nav-icons-wrapper {
        margin-left: auto;
    }
}

@media (max-width: 576px) {
    .search-input {
        font-size: 14px;
        padding: 10px 8px;
    }

    .icon-wrapper {
        width: 36px;
        height: 36px;
    }

    .user-avatar-wrapper {
        width: 36px;
        height: 36px;
    }

    .search-wrapper {
        padding: 0 12px;
    }
}

/* Smooth Scrolling Effect */
#navbar-main.scrolled {
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
}

/* Loading Animation */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

/* Focus visible for accessibility */
.nav-link:focus-visible,
.search-button:focus-visible,
.menu-toggle-btn:focus-visible {
    outline: 2px solid white;
    outline-offset: 2px;
}
</style>
