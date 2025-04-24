<template>
    <App>
        <div class="jumbotron groups-banner">
            <div class="container group-banner-content">
                <h1 class="jumbotron-heading mt-auto"><img :src="'assets/images/icons/theme/group-white.png'"
                        class="mr-3" alt="Welcome to groups"></h1>
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
                                    <p class="mb-0"><i class="bx bx-lock-alt pe-2"></i>Private Group . 323 thành viên
                                    </p>
                                </div>
                            </div>
                            <div class="group-member d-flex align-items-center  mt-md-0 mt-2">
                                <div class="iq-media-group me-3">
                                    <a href="#" class="iq-media">
                                        <!-- <img class="img-fluid avatar-40 rounded-circle"
                                            src="../assets/images/user/05.jpg" alt=""> -->
                                    </a>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">Rời nhóm</button>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <Post></Post>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">Groups</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3 pb-3 border-bottom">
                                        <div class="iq-search-bar members-search p-0">
                                            <form action="#" class="searchbox w-auto">
                                                <input type="text" class="text search-input bg-grey"
                                                    placeholder="Type here to search...">
                                                <a class="search-link" href="#"><i class="ri-search-line"></i></a>
                                            </form>
                                        </div>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="avatar-40 rounded-circle bg-gray text-center me-3"><i
                                                class="ri-bank-card-line h4"></i></div>
                                        <h6 class="mb-0">Your Feed</h6>
                                    </li>
                                    <li class="mb-3 d-flex align-items-center">
                                        <div class="avatar-40 rounded-circle bg-gray text-center me-3"><i
                                                class="ri-compass-3-line h4"></i></div>
                                        <h6 class="mb-0">Discover</h6>
                                    </li>
                                    <li>
                                        <button type="submit" class="btn btn-primary d-block w-100"><i
                                                class="ri-add-line pe-2"></i>Create New Group</button>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <div class="header-title">
                                    <h4 class="card-title">About</h4>
                                </div>
                            </div>
                            <div class="card-body">
                                <ul class="list-inline p-0 m-0">
                                    <li class="mb-3">
                                        <p class="mb-0">Developer's Group...</p>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="ri-lock-fill h4"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6>Private</h6>
                                                <p class="mb-0">Success in slowing economic activity.</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="mb-3">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="ri-eye-fill h4"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6>Visible</h6>
                                                <p class="mb-0">Various versions have evolved over the years</p>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0">
                                                <i class="ri-group-fill h4"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h6>General group</h6>
                                                <p class="mb-0">There are many variations of passages</p>
                                            </div>
                                        </div>
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
import Post from '../../Components/Post.vue';
import ItemPost from '../../Components/Item/ItemPost.vue';

defineProps({ group: Object })

</script>
<style scoped>
@import '../../../css/socialv.css';
</style>