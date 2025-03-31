<template>
    <div class="post border-bottom p-3 bg-white w-shadow">
        <div class="media text-muted pt-3">
            <img src="assets/images/users/user-4.jpg" alt="Online user" class="mr-3 post-user-image">
            <div class="media-body pb-3 mb-0 small lh-125">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <span class="post-type text-muted"><a href="#" class="text-gray-dark post-user-name mr-2">Arthur
                            Minasyan</a> updated his cover photo.</span>
                    <div class="dropdown">
                        <a href="#" class="post-more-settings" role="button" data-toggle="dropdown" id="postOptions"
                            aria-haspopup="true" aria-expanded="false">
                            <i class='bx bx-dots-horizontal-rounded'></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left post-dropdown-menu">
                            <a href="#" class="dropdown-item" aria-describedby="savePost">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class='bx bx-bookmark-plus post-option-icon'></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Save post</span>
                                        <small id="savePost" class="form-text text-muted">Add this to your saved
                                            items</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item" aria-describedby="hidePost">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class='bx bx-hide post-option-icon'></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Hide post</span>
                                        <small id="hidePost" class="form-text text-muted">See fewer posts like
                                            this</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item" aria-describedby="snoozePost">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class='bx bx-time post-option-icon'></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Snooze Arthur for 30 days</span>
                                        <small id="snoozePost" class="form-text text-muted">Temporarily stop seeing
                                            posts</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item" aria-describedby="reportPost">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class='bx bx-block post-option-icon'></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Report</span>
                                        <small id="reportPost" class="form-text text-muted">I'm concerned about this
                                            post</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <span class="d-block">{{ props.post.created_at }} <i class='bx bx-globe ml-3'></i></span>
            </div>
        </div>
        <div class="mt-3">
            <p>{{ props.post.content }}</p>
        </div>

        <div class="d-block mt-3 mb-3">
            <div :class="galleryClass" class="gallery">
                <div v-for="(src, index) in displayImages" :key="index" class="gallery-item">
                    <img :src="'/images/client/post/' + src" :alt="`Gallery Image ${index + 1}`" loading="lazy" />
                </div>
                <div v-if="images.length > 2" class="more-overlay" data-toggle="modal"
                    data-target="#exampleModalCenter">+{{ images.length - 2 }}</div>
            </div>
        </div>

        <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            <div v-for="(src, index) in images" :key="index" class="carousel-item"
                                :class="{ active: index === 0 }">
                                <img :src="'/images/client/post/' + src" class="d-block w-100">
                            </div>
                        </div>
                        <a class="carousel-control-prev" href="#carouselExampleControls" role="button"
                            data-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="sr-only">Previous</span>
                        </a>
                        <a class="carousel-control-next" href="#carouselExampleControls" role="button"
                            data-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="sr-only">Next</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>



        <div class="mb-2">

            <!-- Reactions -->
            <div class="argon-reaction">
                <span class="like-btn">
                    <a href="#" class="post-card-buttons">
                        <i class='bx bxs-like mr-2'></i> {{ likesCount }}
                    </a>
                    <ul class="reactions-box dropdown-shadow">
                        <li v-for="reaction in reactions" :key="reaction.type" class="reaction"
                            :class="'reaction-' + reaction.type" @click="toggleLike(reaction.type)">
                        </li>
                    </ul>
                </span>
            </div>

            <a href="javascript:void(0)" @click="ShowComment" class="post-card-buttons" id="show-comments"><i
                    class='bx bx-message-rounded mr-2'></i> {{ post.comments_count }}</a>
            <div class="dropdown dropup share-dropup">
                <a href="#" class="post-card-buttons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class='bx bx-share-alt mr-2'></i> Share
                </a>
                <div class="dropdown-menu post-dropdown-menu">
                    <a href="#" class="dropdown-item">
                        <div class="row">
                            <div class="col-md-2">
                                <i class='bx bx-share-alt'></i>
                            </div>
                            <div class="col-md-10">
                                <span>Share Now (Public)</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="dropdown-item">
                        <div class="row">
                            <div class="col-md-2">
                                <i class='bx bx-share-alt'></i>
                            </div>
                            <div class="col-md-10">
                                <span>Share...</span>
                            </div>
                        </div>
                    </a>
                    <a href="#" class="dropdown-item">
                        <div class="row">
                            <div class="col-md-2">
                                <i class='bx bx-message'></i>
                            </div>
                            <div class="col-md-10">
                                <span>Send as Message</span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        <div class="border-top pt-3 hide-comments" style="display: none;">
            <div class="row bootstrap snippets">
                <div class="col-md-12">
                    <div class="comment-wrapper">
                        <div class="panel panel-info">
                            <div class="panel-body">
                                <ul class="media-list comments-list">
                                    <li class="media comment-form">
                                        <a href="#" class="pull-left">
                                            <img src="assets/images/users/user-4.jpg" alt="" class="img-circle">
                                        </a>
                                        <div class="media-body">
                                            <form action="" method="" role="form">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="input-group">
                                                            <input type="text" class="form-control comment-input"
                                                                placeholder="Write a comment...">

                                                            <div class="input-group-btn">
                                                                <button type="button" class="btn comment-form-btn"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    title="Tooltip on top"><i
                                                                        class='bx bxs-smiley-happy'></i></button>
                                                                <button type="button"
                                                                    class="btn comment-form-btn comment-form-btn"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    title="Tooltip on top"><i
                                                                        class='bx bx-camera'></i></button>
                                                                <button type="button"
                                                                    class="btn comment-form-btn comment-form-btn"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    title="Tooltip on top"><i
                                                                        class='bx bx-microphone'></i></button>
                                                                <button type="button" class="btn comment-form-btn"
                                                                    data-toggle="tooltip" data-placement="top"
                                                                    title="Tooltip on top"><i
                                                                        class='bx bx-file-blank'></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <a href="#" class="pull-left">
                                            <img src="assets/images/users/user-2.jpg" alt="" class="img-circle">
                                        </a>
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <strong class="text-gray-dark"><a href="#" class="fs-8">Karen
                                                        Minas</a></strong>
                                                <a href="#"><i class='bx bx-dots-horizontal-rounded'></i></a>
                                            </div>
                                            <span class="d-block comment-created-time">30 min ago</span>
                                            <p class="fs-8 pt-2">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                Lorem ipsum dolor sit amet, <a href="#">#consecteturadipiscing </a>.
                                            </p>
                                            <div class="commentLR">
                                                <button type="button" class="btn btn-link fs-8">Like</button>
                                                <button type="button" class="btn btn-link fs-8">Reply</button>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <a href="#" class="pull-left">
                                            <img src="https://bootdey.com/img/Content/user_2.jpg" alt=""
                                                class="img-circle">
                                        </a>
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <strong class="text-gray-dark"><a href="#" class="fs-8">Lia
                                                        Earnest</a></strong>
                                                <a href="#"><i class='bx bx-dots-horizontal-rounded'></i></a>
                                            </div>
                                            <span class="d-block comment-created-time">2 hours ago</span>
                                            <p class="fs-8 pt-2">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                Lorem ipsum dolor sit amet, <a href="#">#consecteturadipiscing </a>.
                                            </p>
                                            <div class="commentLR">
                                                <button type="button" class="btn btn-link fs-8">Like</button>
                                                <button type="button" class="btn btn-link fs-8">Reply</button>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <a href="#" class="pull-left">
                                            <img src="https://bootdey.com/img/Content/user_3.jpg" alt=""
                                                class="img-circle">
                                        </a>
                                        <div class="media-body">
                                            <div class="d-flex justify-content-between align-items-center w-100">
                                                <strong class="text-gray-dark"><a href="#" class="fs-8">Rusty
                                                        Mickelsen</a></strong>
                                                <a href="#"><i class='bx bx-dots-horizontal-rounded'></i></a>
                                            </div>
                                            <span class="d-block comment-created-time">17 hours ago</span>
                                            <p class="fs-8 pt-2">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                                                Lorem ipsum dolor sit amet, <a href="#">#consecteturadipiscing </a>.
                                            </p>
                                            <div class="commentLR">
                                                <button type="button" class="btn btn-link fs-8">Like</button>
                                                <button type="button" class="btn btn-link fs-8">Reply</button>
                                            </div>
                                        </div>
                                    </li>
                                    <li class="media">
                                        <div class="media-body">
                                            <div class="comment-see-more text-center">
                                                <button type="button" class="btn btn-link fs-8">See More</button>
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
    </div>
</template>

<script setup>
import { ref, computed, defineProps } from "vue";
import axios from "axios";

const props = defineProps({
    post: Object
});

const likesCount = ref(props.post.likes_count);

const reactions = [
    { type: "like" },
    { type: "love" },
    { type: "haha" },
    { type: "wow" },
    { type: "sad" },
    { type: "angry" },
];


const toggleLike = async (reactionType) => {
    if (!props.post?.id) {
        console.error("Post ID is missing");
        return;
    }

    try {
        const response = await axios.post(`/posts/reaction/${props.post.id}`, { reaction_type: reactionType });
        if (response.data?.likes_count !== undefined) {
            likesCount.value = response.data.likes_count;
        } else {
            console.warn("Unexpected response structure:", response.data);
        }
    } catch (error) {
        console.error("Error liking post:", error);
    }
};


const ShowComment = () => {
    const comments = document.querySelector('.hide-comments');
    comments.style.display = comments.style.display === 'none' ? 'block' : 'none';
}





// Lấy danh sách URL ảnh
const images = computed(() => props.post.media ? props.post.media.map(media => media.media_url) : []);

const displayImages = computed(() => images.value.slice(0, 2));
const galleryClass = computed(() => (images.value.length === 1 ? "single-image" : "multi-images"));


</script>
<style scoped>
.gallery {
    display: grid;
    gap: 1px;
    cursor: pointer;
    position: relative;
}

.gallery-item {
    width: 100%;
    aspect-ratio: 1;
    overflow: hidden;
}

.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.gallery.single-image {
    grid-template-columns: repeat(1, 1fr);
}

/* For two or more images */
.gallery.multi-images {
    grid-template-columns: repeat(2, 1fr);
}

.more-overlay {
    position: absolute;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    font-size: 1.5em;
    padding: 10px;
    border-radius: 5px;
    bottom: 0;
    right: 0;
}
</style>
