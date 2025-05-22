<template>
    <App>
        <div class="row newsfeed-right-side-content">
            <div class="row newsfeed-right-side-content mt-3">
                <Left />
                <div class="col-md-6 second-section" id="page-content-wrapper">

                    <Post :user="user_auth" />

                    <!-- Posts -->
                    <div class="posts-section mb-5">
                        <ItemPost v-for="post in allPosts" :key="post.id" :post="post" :user="post.user" />
                        <div class="d-flex justify-content-center my-5 load-post" v-if="isLoading">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
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
import { computed, defineProps, ref, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    user: Object,
    posts: Object // Laravel paginate() trả về object
});

const page = usePage();
const user_auth = computed(() => page.props.auth.user);
const allPosts = ref([...props.posts.data]);
const nextPageUrl = ref(props.posts.next_page_url);
const isLoading = ref(false);

const loadMore = async () => {
    if (!nextPageUrl.value || isLoading.value) return;

    isLoading.value = true;
    try {
        console.log('Loading more posts from:', nextPageUrl.value);
        const response = await axios.get(nextPageUrl.value);
        if (Array.isArray(response.data.data)) {
            allPosts.value.push(...response.data.data);
        }
        nextPageUrl.value = response.data.next_page_url;
    } catch (error) {
        console.error("Error loading more posts", error);
    } finally {
        isLoading.value = false;
    }
};


// Auto load on scroll
const handleScroll = () => {
    const bottomReached = window.innerHeight + window.scrollY >= document.body.offsetHeight - 100;
    if (bottomReached) {
        loadMore();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

</script>

<style scoped>
@import '../../css/chat.css';
</style>
