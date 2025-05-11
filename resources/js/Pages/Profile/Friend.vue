<template>
    <Index :user="user" :activeTab="activeTab">
        <ul class="list-group list-group-horizontal types-list fs-8">
            <a href="#" class="list-group-item">Recently Added</a>
            <a href="#" class="list-group-item">Current City</a>
            <a href="#" class="list-group-item">Work</a>
            <a href="#" class="list-group-item">Family</a>
            <form class="list-group-item d-flex w-100 align-items-center p-0 form-inline dropdown search-form">
                <div class="input-group w-95" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                    id="searchDropdown">
                    <input type="text" class="form-control search-input"
                        placeholder="Search for people, companies, events and more..." aria-label="Search"
                        aria-describedby="search-addon">
                    <div class="input-group-append">
                        <button class="btn search-button" type="button"><i class='bx bx-search'></i></button>
                    </div>
                </div>
                <ul class="dropdown-menu notify-drop nav-drop shadow-sm" aria-labelledby="searchDropdown">
                    <div class="notify-drop-title">
                        <div class="row">
                            <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Search Results <span
                                    class="badge badge-pill badge-primary ml-2">29</span></div>
                        </div>
                    </div>
                    <!-- end notify title -->
                    <!-- notify content -->
                    <div class="drop-content">
                        <h6 class="dropdown-header">Peoples</h6>
                        <li class="dropdown-item">
                            <div class="col-md-2 col-sm-2 col-xs-2">
                                <div class="notify-img">
                                    <img src="assets/images/users/user-6.png" alt="Search result">
                                </div>
                            </div>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <a href="#" class="notification-user">Susan P. Jarvis</a>
                                <a href="#" class="btn btn-quick-link join-group-btn border text-right float-right">
                                    Add Friend
                                </a>
                                <p class="time">6 Mutual friends</p>
                            </div>
                        </li>
                        <li class="dropdown-item">
                            <div class="col-md-2 col-sm-2 col-xs-2">
                                <div class="notify-img">
                                    <img src="assets/images/users/user-5.png" alt="Search result">
                                </div>
                            </div>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <a href="#" class="notification-user">Ruth D. Greene</a>
                                <a href="#" class="btn btn-quick-link join-group-btn border text-right float-right">
                                    Add Friend
                                </a>
                            </div>
                        </li>
                        <h6 class="dropdown-header">Groups</h6>
                        <li class="dropdown-item">
                            <div class="col-md-2 col-sm-2 col-xs-2">
                                <div class="notify-img">
                                    <img src="assets/images/groups/group-2.jpg" alt="Search result">
                                </div>
                            </div>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <a href="#" class="notification-user">Tourism</a>
                                <a href="#" class="btn btn-quick-link join-group-btn border text-right float-right">
                                    Join
                                </a>
                                <p class="time">2.5k Members 35+ post a week</p>
                            </div>
                        </li>
                        <li class="dropdown-item">
                            <div class="col-md-2 col-sm-2 col-xs-2">
                                <div class="notify-img">
                                    <img src="assets/images/groups/group-1.png" alt="Search result">
                                </div>
                            </div>
                            <div class="col-md-10 col-sm-10 col-xs-10">
                                <a href="#" class="notification-user">Argon Social Network <img
                                        src="assets/images/theme/verify.png" width="10px" class="verify"
                                        alt="Group verified"></a>
                                <a href="#" class="btn btn-quick-link join-group-btn border text-right float-right">
                                    Join
                                </a>
                                <p class="time">10k Members 20+ post a week</p>
                            </div>
                        </li>
                    </div>
                    <div class="notify-drop-footer text-center">
                        <a href="#">See More</a>
                    </div>
                </ul>
            </form>
        </ul>
        <div class="bg-white py-3 px-4 shadow-sm">
            <div class="card-head d-flex justify-content-between">
                <h5 class="mb-4">Danh sách bạn bè</h5>
            </div>
            <div class="row">
                <div v-for="friend in friends" :key="friend.id" class="col-md-4 col-sm-6 mb-4">
                    <div class="card group-card shadow-sm">
                        <img :src="friend.avatar
                            ? `/images/client/avatar/${friend.avatar}`
                            : '/images/web/users/avatar.jpg'
                            " class="card-img-top group-card-image" alt="Avatar">
                        <div class="card-body">
                            <h5 class="card-title">{{ friend.name }}</h5>
                            <p class="card-text">
                                {{ friend.mutualFriendsCount > 0 ? 
                                    `${friend.mutualFriendsCount} bạn chung` : 
                                    'Không có bạn chung' }}
                            </p>
                            <Link :href="`/${friend.username}`" class="btn btn-quick-link join-group-btn border w-100">
                                Xem trang cá nhân
                            </Link>
                        </div>
                    </div>
                </div>
                
                <div v-if="friends.length === 0" class="col-12 text-center py-5">
                    <p class="text-muted">Không có bạn bè nào.</p>
                </div>
            </div>
        </div>
    </Index>


</template>
<script setup>
import Index from './Index.vue';

import { defineProps, computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";

const props = defineProps({
    user: Object,
    activeTab: String,
    friends: Array,
});
const page = usePage();
const user_auth = computed(() => page.props.auth.user);

const isOwner = computed(() => {
    return props.user.id === user_auth.value.id;
});

</script>
<style scoped>
@import "../../../css/update.css";
</style>
