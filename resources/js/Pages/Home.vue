<template>
    <App>
        <div class="row newsfeed-right-side-content">
            <div class="row newsfeed-right-side-content mt-3">
                <Left />
                <div class="col-md-6 second-section" id="page-content-wrapper">

                    <Post :user="user_auth"/>

                    <!-- Posts -->
                    <div class="posts-section mb-5">
                        <ItemPost
                            v-for="post in posts"
                            :key="post.id"
                            :post="post"
                            :user="user_auth"
                        />
                        <div class="d-flex justify-content-center my-5 load-post">
                            <button type="button" class="btn btn-quick-link join-group-btn border shadow"
                                data-toggle="tooltip" data-placement="top" data-title="Load More Post">
                                <i class="bx bx-dots-horizontal-rounded"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <Right />
            </div>
        </div>
    </App>
</template>

<script setup>
import App from "../Layouts/App.vue";
import Left from '../Components/Left.vue';
import Right from '../Components/Right.vue';
import Post from '../Components/Post.vue';
import ItemPost from "../Components/Item/ItemPost.vue";
import { computed, defineProps } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
    user: Object,
    posts: {
        type: Array,
        default: () => []
    }
});

const page = usePage();
const user_auth = computed(() => page.props.auth.user);
</script>
<style scoped>
@import '../../css/chat.css';
</style>
