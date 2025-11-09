<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

</script>

<template>
    <div class="col-md-3 newsfeed-left-side sticky-top shadow-sm" id="sidebar-wrapper">
        <div class="card newsfeed-user-card h-100">
            <ul class="list-group list-group-flush newsfeed-left-sidebar">
                <li class="list-group-item">
                    <h6>Home</h6>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/newsfeed.png'"
                            alt="newsfeed"> Bảng Tin</Link>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/messages" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/message.png'"
                            alt="message"> Tin Nhắn</Link>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/groups" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/group.png'"
                            alt="group"> Nhóm</Link>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/friend-requests" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/find-friends.png'"
                            alt="group"> Bạn Bè</Link>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/pages/create" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/page.png'"
                            alt="page"> Trang</Link>
                </li>
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <Link href="/thong-bao" class="sidebar-item"><img :src="'/images/web/icons/left-sidebar/event.png'"
                            alt="group"> Thông Báo</Link>
                </li>
            </ul>
        </div>
    </div>
</template>
