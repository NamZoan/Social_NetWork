<template>
    <App>
        <div class="row newsfeed-right-side-content">
            <div class="row newsfeed-right-side-content mt-3">
                <Left />
                <div class="col-md-6 second-section" id="page-content-wrapper">
                    <!-- Create Post -->
                    <Post :user="user_auth" />

                    <!-- Posts Section -->
                    <div class="posts-section mb-5">
                        <TransitionGroup name="post-list" tag="div">
                            <div v-for="(post, index) in posts" :key="post.id">
                                <div v-if="post.is_suggested && index === firstSuggestedIndex" class="suggested-header">
                                    <i class="bi bi-lightbulb"></i>
                                    Suggested for you
                                </div>
                                <ItemPost :post="post" :user="post.user" :ref="el => setPostRef(el, post.id)"
                                    @post-visible="handlePostVisible" />
                            </div>
                        </TransitionGroup>

                        <!-- Loading Indicator -->
                        <div v-if="isLoading" class="d-flex justify-content-center my-5 load-post">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span class="ms-3">Đang tải thêm bài viết...</span>
                        </div>

                        <!-- Intersection Observer Trigger -->
                        <div ref="loadMoreTrigger" class="load-more-trigger" style="height: 20px;"></div>

                        <!-- No Posts -->
                        <div v-if="!isLoading && posts.length === 0" class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="mt-3 text-muted">Chưa có bài viết nào</p>
                            <p class="text-muted">Hãy kết bạn hoặc theo dõi trang để xem bài viết</p>
                        </div>
                    </div>
                </div>
                <Right
                    :suggested-groups="suggestedGroups"
                    :suggested-friends="suggestedFriends"
                    :suggested-pages="suggestedPages"
                    :contacts="contacts"
                    :notifications="notifications"
                />
            </div>
        </div>

        <!-- Scroll to Top Button -->
        <Transition name="fade">
            <button v-if="showScrollTop" @click="scrollToTop" class="scroll-to-top-btn" aria-label="Scroll to top">
                <i class="bi bi-arrow-up"></i>
            </button>
        </Transition>

        <!-- New Posts Available Banner -->
        <Transition name="slide-down">
            <div v-if="newPostsAvailable > 0" @click="loadNewPosts" class="new-posts-banner">
                <i class="bi bi-arrow-clockwise"></i>
                {{ newPostsAvailable }} bài viết mới
            </div>
        </Transition>
    </App>
</template>

<script setup>
import App from "../Layouts/App.vue";
import Left from '../Components/Left.vue';
import Right from '../Components/Right.vue';
import Post from '../Components/Post.vue';
import ItemPost from "../Components/Item/ItemPost.vue";
import { computed, defineProps, ref, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    initialPosts: {
        type: Array,
        required: true
    },
    nextCursor: {
        type: String,
        default: null
    },
    hasMore: {
        type: Boolean,
        default: true
    },
    user: {
        type: Object,
        required: true
    },
    sidebar: {
        type: Object,
        default: () => ({
            suggestedGroups: [],
            contacts: [],
            notifications: []
        })
    }
});

// State
const page = usePage();
const user_auth = computed(() => page.props.auth.user);
const posts = ref([...props.initialPosts]);
const cursor = ref(props.nextCursor);
const hasMore = ref(props.hasMore);
const isLoading = ref(false);
const showScrollTop = ref(false);
const loadMoreTrigger = ref(null);
const postRefs = ref({});
const seenPostIds = ref(new Set());
const feedType = ref(null);
const newPostsAvailable = ref(0);
const firstSuggestedIndex = computed(() =>
    posts.value.findIndex((post) => post?.is_suggested)
);

// Sidebar data
const sidebar = computed(() => props.sidebar || {
    suggestedGroups: [],
    suggestedFriends: [],
    suggestedPages: [],
    contacts: [],
    notifications: []
});
const suggestedGroups = computed(() => sidebar.value.suggestedGroups || []);
const suggestedFriends = computed(() => sidebar.value.suggestedFriends || []);
const suggestedPages = computed(() => sidebar.value.suggestedPages || []);
const contacts = computed(() => sidebar.value.contacts || []);
const notifications = computed(() => sidebar.value.notifications || []);

// Intersection Observer
let observer = null;
let scrollThrottle = null;

/**
 * Load more posts
 */
const loadMore = async () => {
    if (!hasMore.value || isLoading.value || !cursor.value) {
        return;
    }

    isLoading.value = true;

    try {
        const response = await axios.get('/newsfeed', {
            params: {
                cursor: cursor.value,
                per_page: 5,
                seen_posts: Array.from(seenPostIds.value)
            }
        });

        const { data, next_cursor, has_more, feed_type } = response.data;

        if (data && data.length > 0) {
            // Thêm posts mới
            posts.value.push(...data);

            // Cập nhật cursor và hasMore
            cursor.value = next_cursor;
            hasMore.value = has_more;
            feedType.value = feed_type;

            // Track seen posts
            data.forEach(post => seenPostIds.value.add(post.id));
        } else {
            hasMore.value = false;
        }
    } catch (error) {
        console.error('Error loading more posts:', error);
    } finally {
        isLoading.value = false;
    }
};

/**
 * Setup Intersection Observer
 */
const setupIntersectionObserver = () => {
    if (!loadMoreTrigger.value) return;

    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !isLoading.value && hasMore.value) {
                    loadMore();
                }
            });
        },
        {
            root: null,
            rootMargin: '300px',
            threshold: 0.1
        }
    );

    observer.observe(loadMoreTrigger.value);
};

/**
 * Handle scroll event
 */
const handleScroll = () => {
    if (scrollThrottle) return;

    scrollThrottle = setTimeout(() => {
        // Show/hide scroll to top button
        showScrollTop.value = window.scrollY > 500;

        // Track post views
        Object.entries(postRefs.value).forEach(([postId, element]) => {
            if (!element || seenPostIds.value.has(parseInt(postId))) return;

            const rect = element.getBoundingClientRect?.();
            if (!rect) return;

            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;

            if (isVisible) {
                trackPostView(postId);
                seenPostIds.value.add(parseInt(postId));
            }
        });

        scrollThrottle = null;
    }, 200);
};

/**
 * Track post view
 */
const trackPostView = async (postId) => {
    try {
        await axios.post('/track-interaction', {
            post_id: postId,
            interaction_type: 'view'
        });
    } catch (error) {
        console.error('Error tracking view:', error);
    }
};

/**
 * Handle post visible
 */
const handlePostVisible = (postId) => {
    if (!seenPostIds.value.has(postId)) {
        trackPostView(postId);
        seenPostIds.value.add(postId);
    }
};

/**
 * Set post ref
 */
const setPostRef = (el, postId) => {
    if (!el) return;

    const element = el.$el ?? el;
    if (element?.getBoundingClientRect) {
        postRefs.value[postId] = element;
        return;
    }

    if (element?.nodeType === 8 && element.nextElementSibling?.getBoundingClientRect) {
        postRefs.value[postId] = element.nextElementSibling;
    }
};

/**
 * Scroll to top
 */
const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

/**
 * Handle new post created
 */
const handleNewPost = (newPost) => {
    // Add new post to top
    posts.value.unshift(newPost);

    // Scroll to top smoothly
    setTimeout(() => {
        scrollToTop();
    }, 100);
};

/**
 * Load new posts (from banner)
 */
const loadNewPosts = () => {
    window.location.reload();
};

/**
 * Check for new posts periodically
 */
const checkForNewPosts = async () => {
    if (posts.value.length === 0) return;

    try {
        const response = await axios.get('/newsfeed/check-new');

        if (response.data.count > 0) {
            newPostsAvailable.value = response.data.count;
        }
    } catch (error) {
        console.error('Error checking for new posts:', error);
    }
};

// Lifecycle hooks
onMounted(() => {
    // Setup intersection observer
    nextTick(() => {
        setupIntersectionObserver();
    });

    // Add scroll listener
    window.addEventListener('scroll', handleScroll, { passive: true });

    // Check for new posts every 30 seconds
    const newPostsInterval = setInterval(checkForNewPosts, 30000);

    // Cleanup
    onUnmounted(() => {
        if (observer) {
            observer.disconnect();
        }
        window.removeEventListener('scroll', handleScroll);
        clearInterval(newPostsInterval);
        if (scrollThrottle) {
            clearTimeout(scrollThrottle);
        }
    });
});
</script>

<style scoped>
@import '../../css/chat.css';

/* Feed Type Badge */
.feed-type-badge {
    text-align: center;
}

.feed-type-badge .badge {
    padding: 8px 16px;
    font-size: 14px;
    font-weight: 500;
}

.suggested-header {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    background: #f0f2f5;
    color: #050505;
    font-weight: 600;
    padding: 10px 12px;
    border-radius: 10px;
    margin-bottom: 12px;
}

/* Scroll to Top Button */
.scroll-to-top-btn {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: #1877f2;
    color: white;
    border: none;
    border-radius: 50%;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transition: all 0.3s;
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.scroll-to-top-btn:hover {
    background: #166fe5;
    transform: translateY(-2px);
}

/* New Posts Banner */
.new-posts-banner {
    position: fixed;
    top: 60px;
    left: 50%;
    transform: translateX(-50%);
    background: #1877f2;
    color: white;
    padding: 12px 24px;
    border-radius: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    font-weight: 500;
    transition: all 0.3s;
}

.new-posts-banner:hover {
    background: #166fe5;
    transform: translateX(-50%) translateY(-2px);
}

/* Transitions */
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.3s;
}

.slide-down-enter-from,
.slide-down-leave-to {
    transform: translateX(-50%) translateY(-100%);
    opacity: 0;
}

/* Post List Transitions */
.post-list-enter-active {
    transition: all 0.3s ease-out;
}

.post-list-leave-active {
    transition: all 0.2s ease-in;
}

.post-list-enter-from {
    opacity: 0;
    transform: translateY(-30px);
}

.post-list-leave-to {
    opacity: 0;
    transform: translateY(30px);
}

/* Loading Indicator */
.load-post {
    padding: 30px 0;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .scroll-to-top-btn {
        bottom: 20px;
        right: 20px;
        width: 45px;
        height: 45px;
    }

    .new-posts-banner {
        top: 50px;
        padding: 10px 20px;
        font-size: 14px;
    }
}
</style>
