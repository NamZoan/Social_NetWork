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
                <Notification />


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
