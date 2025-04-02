<template>
    <Index :user="user" :activeTab="activeTab">

        <Post v-if="isOwner" :user="user_auth"></Post>

            <ItemPost v-for="post in allPosts" :key="post.id" :post="post">
            </ItemPost>


        <div v-if="hasMorePages" ref="loadMoreTrigger">
            <div class="d-flex justify-content-center my-5 load-post">
                <button type="button" class="btn btn-quick-link join-group-btn border shadow" data-toggle="tooltip"
                    data-placement="top" data-title="Load More Post"><i
                        class='bx bx-dots-horizontal-rounded'></i></button>
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

const isOwner = computed(() => {
    return props.user.id === user_auth.value.id;
});

const allPosts = ref(props.posts.data)
const currentPage = ref(props.posts.current_page)
const hasMorePages = ref(props.posts.last_page > props.posts.current_page)

const loadMoreTrigger = ref(null)
let observer = null

const observeLastPost = () => {
    if (loadMoreTrigger.value) {
        observer = new IntersectionObserver(async (entries) => {
            const entry = entries[0]
            if (entry.isIntersecting && hasMorePages.value) {
                await loadMorePosts()
            }
        }, { threshold: 0.1 })

        observer.observe(loadMoreTrigger.value)
    }
}

const loadMorePosts = async () => {
    if (!hasMorePages.value) return

    try {
        const response = await axios.get(`/${props.user.username}/load-more`, {
            params: { page: currentPage.value + 1 }
        });

        // Kiểm tra xem phản hồi có thuộc tính data không
        const newPosts = response.data.data || []
        allPosts.value = [...allPosts.value, ...newPosts]

        currentPage.value = response.data.current_page
        hasMorePages.value = response.data.last_page > response.data.current_page
    } catch (error) {
        console.error('Lỗi tải thêm bài viết:', error)
    }
}

onMounted(() => {
    observeLastPost()
})

onUnmounted(() => {
    if (observer) {
        observer.disconnect()
    }
})


</script>
