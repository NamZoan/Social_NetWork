<template>
    <Index :user="user" :activeTab="activeTab">
        <template #filters>
            <div class="post-filter-bar w-shadow rounded mb-3">
                <button
                    v-for="option in filterOptions"
                    :key="option.key"
                    class="filter-chip"
                    :class="{ active: selectedFilter === option.key }"
                    @click="setFilter(option.key)"
                >
                    <i :class="option.icon"></i>
                    <span>{{ option.label }}</span>
                    <small v-if="option.key !== 'all'">{{ postCounts[option.key] || 0 }}</small>
                </button>
            </div>
        </template>

        <Post v-if="isOwner" :user="user_auth"></Post>

        <div v-if="filteredPosts.length === 0 && !loading" class="empty-filter-state">
            <i class="bx bx-note"></i>
            <p>Chưa có bài viết nào thuộc mục này</p>
        </div>

        <ItemPost
            v-for="post in filteredPosts"
            :key="post.id"
            :post="post"
            :user="user"
            @deleted="removePost"
        />

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

const allPosts = ref(props.posts.data || [])
const currentPage = ref(props.posts.current_page || 1)
const hasMorePages = ref((props.posts.last_page || 1) > (props.posts.current_page || 1))
const loading = ref(false)

const filterOptions = [
    { key: 'all', label: 'Tất cả', icon: 'bx bx-layer' },
    { key: 'user', label: 'Cá nhân', icon: 'bx bx-user' },
    { key: 'group', label: 'Nhóm', icon: 'bx bx-group' },
    { key: 'page', label: 'Trang', icon: 'bx bx-store' },
]

const selectedFilter = ref('all')

const getPostType = (post) => {
    if (post?.page_id || post?.page) return 'page'
    if (post?.group_id || post?.group) return 'group'
    return 'user'
}

const postCounts = computed(() => {
    return allPosts.value.reduce(
        (acc, post) => {
            const type = getPostType(post)
            acc[type] = (acc[type] || 0) + 1
            return acc
        },
        { user: 0, group: 0, page: 0 }
    )
})

const filteredPosts = computed(() => {
    if (selectedFilter.value === 'all') return allPosts.value
    return allPosts.value.filter((post) => getPostType(post) === selectedFilter.value)
})

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

const setFilter = (key) => {
    selectedFilter.value = key
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

<style scoped>
.post-filter-bar {
    display: flex;
    gap: 8px;
    padding: 8px;
    background: #fff;
}

.filter-chip {
    border: 1px solid #e5e7eb;
    border-radius: 999px;
    background: #f8fafc;
    color: #475569;
    padding: 6px 14px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 6px;
    cursor: pointer;
    transition: all 0.2s ease;
}

.filter-chip i {
    font-size: 16px;
}

.filter-chip small {
    font-size: 12px;
    color: #94a3b8;
}

.filter-chip.active {
    background: #2563eb;
    color: #fff;
    border-color: #2563eb;
}

.filter-chip.active small {
    color: rgba(255, 255, 255, 0.85);
}

.empty-filter-state {
    text-align: center;
    padding: 40px 16px;
    color: #94a3b8;
}

.empty-filter-state i {
    font-size: 48px;
    margin-bottom: 12px;
    display: block;
}
</style>
