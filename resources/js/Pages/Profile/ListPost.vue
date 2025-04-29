<template>
    <Index :user="user" :activeTab="activeTab">
        <Post v-if="isOwner" :user="user_auth"></Post>

        <ItemPost v-for="post in allPosts" :key="post.id" :post="post" :user="user" @deleted="removePost">
        </ItemPost>

        <div v-if="hasMorePages" ref="loadMoreTrigger" class="text-center mt-3">
            <div v-if="loading" class="spinner-border text-primary" role="status">
            </div>
        </div>
    </Index>
</template>
<script setup>
import Index from './Index.vue';
import Post from '../../Components/Post.vue';
import ItemPost from '../../Components/Item/ItemPost.vue';
import { usePage } from "@inertiajs/vue3";
import { ref, computed, onMounted, onUnmounted, defineProps } from 'vue'
import axios from 'axios'

const props = defineProps({
    user: Object,
    activeTab: String,
    posts: Object,
});

const page = usePage();
const user_auth = computed(() => page.props.auth.user);
const isOwner = computed(() => props.user.id === user_auth.value.id);

const allPosts = ref(props.posts.data)
const currentPage = ref(props.posts.current_page)
const hasMorePages = ref(props.posts.last_page > props.posts.current_page)
const loading = ref(false)

const loadMoreTrigger = ref(null)
let observer = null

const loadMorePosts = async () => {
    if (!hasMorePages.value || loading.value) return

    loading.value = true
    try {
        const nextPage = currentPage.value + 1
        const response = await axios.get(`/${props.user.username}/load-more-posts`, {
            params: { page: nextPage }
        });

        if (response.data.data && response.data.data.length > 0) {
            allPosts.value = [...allPosts.value, ...response.data.data]
            currentPage.value = response.data.current_page
            hasMorePages.value = response.data.last_page > response.data.current_page
        } else {
            hasMorePages.value = false
        }
    } catch (error) {
        console.error('Lỗi tải thêm bài viết:', error)
        hasMorePages.value = false
    } finally {
        loading.value = false
    }
}

const observeLastPost = () => {
    if (loadMoreTrigger.value) {
        observer = new IntersectionObserver(async (entries) => {
            const entry = entries[0]
            if (entry.isIntersecting && hasMorePages.value && !loading.value) {
                await loadMorePosts()
            }
        }, { 
            threshold: 0.1,
            rootMargin: '100px' 
        })

        observer.observe(loadMoreTrigger.value)
    }
}

const removePost = (postId) => {
    allPosts.value = allPosts.value.filter(post => post.id !== postId);
};

onMounted(() => {
    observeLastPost()
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
    }
})
</script>
