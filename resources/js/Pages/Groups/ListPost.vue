<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember">
        <div v-if="isMember">
            <Post :group_id="group.id" :user="user_auth" :group="group" />
            <div v-for="post in initialPosts" :key="post.id">
                <ItemPost :post="post" :user="post.user" @updated="handlePostUpdated" />
            </div>
            <div v-if="group.posts.next_page_url" class="text-center mt-3">
                <div v-if="loading" class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import Post from '../../Components/Post.vue';
import ItemPost from '../../Components/Item/ItemPost.vue';
import GroupDetail from './GroupDetail.vue';
import { computed, ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    group: {
        type: Object,
        required: true
    },
    user_auth: {
        type: Object,
        required: true
    },
    isMember: {
        type: Boolean,
        required: true
    }
});

const loading = ref(false);

// Đảm bảo các bài viết ban đầu có comments_count
const initialPosts = computed(() => {
    console.log('Initial posts data:', props.group.posts.data);
    return props.group.posts.data.map(post => {
        console.log('Post comments count:', post.id, post.comments_count);
        return {
            ...post,
            comments_count: post.comments_count || 0
        };
    });
});

const loadMorePosts = async () => {
    if (loading.value || !props.group.posts.next_page_url) return;

    loading.value = true;
    try {
        const nextPage = props.group.posts.current_page + 1;
        const response = await axios.get(`/groups/${props.group.id}/load-more-posts?page=${nextPage}`);
        console.log('Load more posts response:', response.data);

        // Cập nhật dữ liệu bài viết mới với comments_count
        const newPosts = response.data.posts.data.map(post => {
            console.log('New post comments count:', post.id, post.comments_count);
            return {
                ...post,
                comments_count: post.comments_count || 0
            };
        });

        // Tạo một bản sao mới của mảng posts.data
        const updatedPosts = [...props.group.posts.data];
        updatedPosts.push(...newPosts);

        // Cập nhật lại props.group.posts
        props.group.posts = {
            ...props.group.posts,
            data: updatedPosts,
            next_page_url: response.data.posts.next_page_url,
            current_page: nextPage
        };
    } catch (error) {
        console.error('Error loading more posts:', error);
    } finally {
        loading.value = false;
    }
};

const handlePostUpdated = (updatedPost) => {
    const index = props.group.posts.data.findIndex(post => post.id === updatedPost.id);
    if (index !== -1) {
        // Tạo một bản sao mới của mảng posts.data
        const updatedPosts = [...props.group.posts.data];
        updatedPosts[index] = {
            ...updatedPost,
            comments_count: updatedPost.comments_count || 0
        };

        // Cập nhật lại props.group.posts
        props.group.posts = {
            ...props.group.posts,
            data: updatedPosts
        };
    }
};

const handleScroll = () => {
    const bottomOfWindow = window.innerHeight + window.scrollY >= document.documentElement.scrollHeight - 100;
    if (bottomOfWindow) {
        loadMorePosts();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<style scoped>
@import '../../../css/socialv.css';
</style>
