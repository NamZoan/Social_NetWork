<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <div class="mb-3">
                <div class="btn-group d-flex top-links-fg">
                    <a href="index.html" class="btn btn-quick-links mr-3 ql-active">
                        <img :src="'images/web/icons/theme/group-white.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Your Groups</span>
                    </a>
                    <a href="messages.html" class="btn btn-quick-links mr-3">
                        <img :src="'images/web/icons/theme/rocket.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Discover</span>
                    </a>
                    <a href="watch.html" class="btn btn-quick-links">
                        <img :src="'images/web/icons/theme/create.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Create</span>
                    </a>
                </div>
            </div>

            <div class="jumbotron groups-banner">
                <div class="container group-banner-content">
                    <h1 class="jumbotron-heading mt-auto"><img :src="'images/web/icons/theme/group-white.png'"
                            class="mr-3" alt="Welcome to groups"> Groups</h1>
                    <p>Get latest active from your groups.</p>
                </div>
            </div>

            <!-- Groups -->
            <div class="groups bg-white py-3 px-4 shadow-sm">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Suggested Groups</h5>
                    <a href="#" class="btn btn-link">See All</a>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-1.png'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Argon Social Network <img :src="'images/web/theme/verify.png'"
                                        width="15px" class="verify" alt="Group verified"></h5>
                                <p class="card-text">10k Members 20+ post a week</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-2.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Tourism</h5>
                                <p class="card-text">2.5k Members 35+ post a week</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-3.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Reading Books</h5>
                                <p class="card-text">1.3k Members 10+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-4.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Capture The Best</h5>
                                <p class="card-text">2.8k Members 8+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-5">
            <div class="groups py-3 px-4 mt-3">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Friends Groups</h5>
                    <a href="#" class="btn btn-link">See All</a>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="card group-card bg-transparent group-card-inline mb-3">
                            <div class="row no-gutters  d-flex align-items-center">
                                <div class="col-md-4">
                                    <img :src="'images/web/groups/group-5.jpg'" class="card-img group-card-inline-img"
                                        alt="Group image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title">Source Code Viewer</h5>
                                        <p class="card-text">8.6k Members 29+ post a day</p>
                                        <div class="like-user-img">
                                            <ul class="list-unstyled liked-users">
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-7.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-6.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-5.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <span class="liked-info fs-8">9 friends are members</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-quick-link bg-white">Join</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="card group-card bg-transparent group-card-inline mb-3">
                            <div class="row no-gutters  d-flex align-items-center">
                                <div class="col-md-4">
                                    <img :src="'images/web/groups/group-6.jpg'" class="card-img group-card-inline-img"
                                        alt="Group image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title">T-Shirt</h5>
                                        <p class="card-text">2.4k Members 5+ post a day</p>
                                        <div class="like-user-img">
                                            <ul class="list-unstyled liked-users">
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-7.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-6.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-5.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <span class="liked-info fs-8">4 friends are members</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-quick-link bg-white">Join</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-5">
            <div class="groups py-3 px-4 mt-3 mb-4">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Find By Categories</h5>
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target=".multi-collapse"
                        aria-expanded="false" aria-controls="group-category-1 group-category-2">Open</button>
                </div>
                <div class="row">
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-1.png'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">Arts &#38; Culture</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-2.jpg'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">School &#38; Education</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-3.jpg'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">Music &#38; TV</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-4.jpg'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">Sport &#38; Health</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-5.jpg'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">Dating</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 col-sm-4">
                        <div class="collapse multi-collapse group-collapse" id="group-category-1">
                            <div class="card group-card shadow-sm text-white">
                                <a href="#" class="group-category">
                                    <img :src="'images/web/groups/category-6.jpg'" class="card-img group-category-img"
                                        alt="Category type">
                                    <div class="card-img-overlay group-cat-overlay d-flex align-items-end">
                                        <h5 class="card-title">Business</h5>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Index>

</template>
<script setup>
import Index from "./Index.vue";
</script>
