<template>
    <Index :user="user" :activeTab="activeTab">
        <div class="bg-white py-3 px-4 shadow-sm">
            <div class="card-head d-flex justify-content-between">
                <h5 class="mb-4">Danh sách bạn bè</h5>
            </div>
            <div class="row">
                <div v-for="friend in friends" :key="friend.id" class="col-md-4 col-sm-6 mb-4">
                    <div class="card group-card shadow-sm">
                        <img :src="getAvatarUrl(friend.avatar)" class="card-img-top group-card-image" alt="Avatar">
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

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

const isOwner = computed(() => {
    return props.user.id === user_auth.value.id;
});

</script>
<style scoped>
@import "../../../css/update.css";
</style>

