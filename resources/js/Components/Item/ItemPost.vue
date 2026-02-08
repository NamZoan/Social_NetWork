<template>
    <div class="post border-bottom p-3 bg-white w-shadow mb-3">
        <div class="media text-muted pt-3">
            <img :src="displayAvatar" class="mr-3 post-user-image" />
            <div class="media-body pb-3 mb-0 small lh-125">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <span class="post-type text-muted">
                        <template v-if="isPagePost">
                            <Link :href="pageLink" class="text-gray-dark post-user-name">{{ pageName }}</Link>
                        </template>
                        <template v-else>
                            <template v-if="postData.group">
                                <Link :href="`/groups/${postData.group.id}`" class="text-gray-dark post-user-name group-name">
                                    {{ postData.group.name }}
                                </Link>
                                <span class="post-separator">•</span>
                                <Link :href="authorLink" class="text-gray-dark post-user-name">{{ authorName }}</Link>
                            </template>
                            <template v-else>
                                <Link :href="authorLink" class="text-gray-dark post-user-name">{{ authorName }}</Link>
                            </template>
                        </template>
                    </span>
                    <div v-if="showMenus" class="dropdown">
                        <a href="#" class="post-more-settings" role="button" data-toggle="dropdown" id="postOptions"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div v-show="canManagePost" class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left post-dropdown-menu">
                            <a v-if="canEditPost" href="#" class="dropdown-item" aria-describedby="editPost"
                                data-toggle="modal" :data-target="'#modal-update' + postData.id">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class="bx bx-edit-alt post-option-icon"></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Chỉnh sửa</span>
                                        <small id="editPost" class="form-text text-muted">chỉnh sửa bài viết</small>
                                    </div>
                                </div>
                            </a>
                            <a v-if="canDeletePost" href="#" class="dropdown-item" aria-describedby="deletePost"
                                @click.prevent="deletePost(postData.id)">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class="bx bx-trash post-option-icon"></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Xóa bỏ</span>
                                        <small id="deletePost" class="form-text text-muted">xóa bài viết</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <span class="d-block">{{ formatTime(postData.created_at) }}
                    <span v-if="postData.is_suggested" class="suggested-badge">Suggested</span>
                    <div v-if="showMenus" class="dropdown d-inline-block">
                        <i :class="privacyIcon" class="ml-3 privacy-icon" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"></i>
                        <div class="dropdown-menu dropdown-menu-right privacy-dropdown">
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'public',
                            }" @click="updatePrivacy('public')">
                                <i class="bx bx-globe mr-2"></i>
                                <span>Công khai</span>
                                <small class="d-block text-muted">Mọi người có thể xem bài viết này</small>
                            </div>
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'friends',
                            }" @click="updatePrivacy('friends')">
                                <i class="bx bx-user mr-2"></i>
                                <span>Bạn bè</span>
                                <small class="d-block text-muted">Chỉ bạn bè có thể xem bài viết này</small>
                            </div>
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'private',
                            }" @click="updatePrivacy('private')">
                                <i class="bx bx-lock-alt mr-2"></i>
                                <span>Chỉ mình tôi</span>
                                <small class="d-block text-muted">Chỉ mình tôi có thể xem bài viết này</small>
                            </div>
                        </div>
                    </div>
                </span>
            </div>
        </div>
        <p>{{ postData.content }}</p>

        <div v-if="sharedPost" class="shared-post-card">
            <div class="d-flex align-items-center mb-2">
                <img :src="sharedAuthorAvatar" class="mr-2 shared-post-avatar" />
                <div>
                    <Link :href="sharedAuthorLink" class="shared-post-user">
                        {{ sharedAuthorName }}
                    </Link>
                        <div class="text-muted small">Bài viết gốc</div>
                </div>
            </div>
            <p class="mb-2">{{ sharedPost.content }}</p>
            <div v-if="sharedImages.length" :class="sharedGalleryClass" class="gallery shared-gallery">
                <div v-for="(src, index) in sharedDisplayImages" :key="index" class="gallery-item">
                    <img :src="'/images/client/post/' + src" loading="lazy" />
                </div>
                <div v-if="sharedImages.length > 2" class="more-overlay">
                    +{{ sharedImages.length - 2 }}
                </div>
            </div>
        </div>

        <div class="border-bottom"></div>

        <div class="d-block mb-3">
            <div :class="galleryClass" class="gallery">
                <div v-for="(src, index) in displayImages" :key="index" class="gallery-item">
                    <img :src="'/images/client/post/' + src" loading="lazy" />
                </div>
                <div v-if="images.length > 2" class="more-overlay" data-toggle="modal"
                    data-target="#exampleModalCenter">
                    +{{ images.length - 2 }}
                </div>
            </div>
        </div>

        <Teleport to="body">
            <div v-if="images.length > 2" class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                aria-labelledby="exampleModalCenterTitle" aria-modal="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalCenterTitle">
                                Image Gallery
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                            <div class="carousel-inner">
                                <div v-for="(src, index) in images" :key="index" class="carousel-item"
                                    :class="{ active: index === 0 }">
                                    <img :src="'/images/client/post/' + src" class="d-block w-100" loading="lazy"
                                        alt="Gallery image" />
                                </div>
                            </div>
                            <a class="carousel-control-prev" href="#carouselExampleControls" role="button"
                                data-slide="prev" aria-label="Previous image">
                                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                <span class="sr-only">Previous</span>
                            </a>
                            <a class="carousel-control-next" href="#carouselExampleControls" role="button"
                                data-slide="next" aria-label="Next image">
                                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                <span class="sr-only">Next</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Reactions -->
        <div class="argon-reaction">
            <span class="like-btn">
                <!-- Nút chính: Hiển thị ảnh reaction hoặc icon like -->
                <a class="post-card-buttons" @click="removeReaction">
                    <img v-if="isReaction" :src="getReactionImage" width="24px" class="mr-1 mb-1" />
                    <i v-else class="bx bxs-like mr-2"></i>

                    {{ totalReaction }}
                </a>

                <!-- Danh sách các reaction -->
                    <ul class="reactions-box dropdown-shadow">
                    <li v-for="reaction in reactions" :key="reaction.type" class="reaction"
                        :class="'reaction-' + reaction.type" @click="toggleLike(reaction.type)"></li>
                </ul>
            </span>
        </div>
        <a href="javascript:void(0)" class="post-card-buttons" id="show-comments" data-toggle="modal"
            @click="handleCommentModalShow"
            :data-target="'#exampleModalScrollable-' + postData.id"><i class="bx bx-message-rounded mr-1"></i>
            {{ commentsCount }}</a>
        <div class="dropdown dropup share-dropup">
            <a href="#" class="post-card-buttons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-share-alt mr-1"></i>
                Share
                <span v-if="shareCount">({{ shareCount }})</span>
            </a>
            <div class="dropdown-menu post-dropdown-menu">
                <a href="#" class="dropdown-item" @click.prevent="shareNow('public')">
                    <div class="row">
                        <div class="col-md-2">
                            <i class="bx bx-share-alt"></i>
                        </div>
                        <div class="col-md-10">
                            <span>Chia sẻ (Công khai)</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        <div class="modal fade" :id="`shareModal-${postData.id}`" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Chia sẻ bài viết</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div v-if="shareError" class="alert alert-danger">{{ shareError }}</div>
                        <div class="form-group">
                            <label class="col-form-label">Thêm lời nhắn</label>
                            <textarea class="form-control" rows="3" v-model="shareContent"
                                placeholder="Nói gì đó về bài viết này..."></textarea>
                        </div>
                        <div class="form-group">
                            <label class="col-form-label">Quyền riêng tư</label>
                            <select class="form-control" v-model="sharePrivacy">
                                <option value="public">Công khai</option>
                                <option value="friends">Bạn bè</option>
                                <option value="private">Chỉ mình tôi</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="button" class="btn btn-primary" :disabled="shareLoading" @click="submitShare()">
                            {{ shareLoading ? 'Đang chia sẻ...' : 'Chia sẻ' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Slot cho admin actions -->
        <slot name="actions"></slot>

        <!-- Modal bình luận -->
        <div class="modal fade bd-example-modal-lg" :id="'exampleModalScrollable-' + postData.id" tabindex="-1"
            role="dialog" aria-labelledby="exampleModalScrollableTitle" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalScrollableTitle">
                            Bình luận
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body comment-modal-body">
                        <div class="comment-thread-card">
                            <div class="comment-thread-header">
                                <div>
                                    <span class="count-pill">{{ commentsCount }} bình luận</span>
                                    <button type="button" class="btn btn-link p-0 ml-2 text-muted" @click="fetchComments(true)" :disabled="isLoading">
                                        Làm mới
                                    </button>
                                </div>
                                <div v-if="commentError" class="text-danger small">
                                    {{ commentError }}
                                </div>
                            </div>

                            <div class="comments-scroll">
                                <template v-if="comments.length">
                                    <CommentItem
                                        v-for="comment in comments"
                                        :key="comment.id"
                                        :comment="comment"
                                        :current-user-id="user.id"
                                        :reply-to="replyTo"
                                        :reply-content="replyContent"
                                        :show-replies="showReplies"
                                        :is-loading-replies="isLoadingReplies"
                                        :highlight-mentions="highlightMentions"
                                        :format-time="formatTime"
                                        :build-avatar-url="buildAvatarUrl"
                                        :update-reply-content="updateReplyContent"
                                        @set-reply="setReply"
                                        @submit-reply="submitReply"
                                        @toggle-replies="toggleReplies"
                                        @delete-comment="deleteComment"
                                        @start-edit="startEdit"
                                    />
                                </template>
                                <div v-else-if="hasLoadedComments && !isLoading" class="empty-comments-state">
                                    <p class="mb-1 font-weight-semibold">Chưa có bình luận</p>
                                    <small class="text-muted">Hãy là người đầu tiên để lại ý kiến.</small>
                                </div>

                                <div v-if="isLoading" class="comment-loading text-center py-3">
                                    <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                </div>
                                <div v-if="hasMore && !isLoading" class="load-more-row text-center mt-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" @click="loadMoreComments">
                                        Xem thêm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer comment-footer">
                        <img :src="authorAvatar" alt="avatar" class="comment-footer-avatar" />
                        <div class="input-group">
                            <input
                                type="text"
                                v-model="content_comment"
                                class="form-control comment-input"
                                placeholder="Nhập bình luận của bạn..."
                                @keydown.enter.prevent="submitComment"
                                :disabled="commentLoading"
                            />
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" :disabled="commentLoading" @click="submitComment">
                                    {{ commentLoading ? "Đang gửi..." : "Gửi" }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <UpdatePost :post="postData" @updated="handlePostUpdated" />
</template>
<script setup>
import { ref, computed, defineProps, onMounted, watch, onUnmounted } from "vue";
import axios from "axios";
import UpdatePost from "./UpdatePost.vue";
import CommentItem from "./CommentItem.vue";
import { Teleport } from "vue";
import $ from "jquery";
import { Link } from "@inertiajs/vue3";

const props = defineProps({
    post: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            content: "",
            created_at: "",
            privacy_setting: "public",
            media: [],
            user: {},
            shares_count: 0,
            is_suggested: false,
            suggested_reason: null,
            original_post: null,
        }),
    },
    user: {
        type: Object,
        required: true,
        default: () => ({
            id: null,
            name: "",
            avatar: null,
        }),
    },
    showMenus: {
        type: Boolean,
        default: true,
    },
    canEdit: {
        type: Boolean,
        default: false,
    },
    canDelete: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(["updated", "deleted"]);
const showMenus = computed(() => props.showMenus);

// Thay computed bằng ref để có thể cập nhật
const postData = ref(props.post);
const userData = ref(props.user);

// Watch props để cập nhật khi props thay đổi
watch(
    () => props.post,
    (newPost, oldPost) => {
        postData.value = newPost;
        if (newPost?.id && newPost?.id !== oldPost?.id) {
            // reset comment state when switching to a different post
            comments.value = [];
            page.value = 1;
            hasMore.value = true;
            hasLoadedComments.value = false;
            showReplies.value = {};
            isLoadingReplies.value = {};
        }
    }
);

watch(
    () => props.user,
    (newUser) => {
        userData.value = newUser;
    },
    { deep: true }
);

const getPostId = () => postData.value?.id ?? null;
const logInteraction = async (interactionType) => {
    const postId = getPostId();
    if (!postId || !interactionType) return;
    try {
        await axios.post(`/posts/${postId}/interactions`, {
            interaction_type: interactionType,
        });
    } catch (error) {
        console.error("Error logging interaction:", error);
    }
};


const initializePostData = () => {
    const postId = getPostId();
    if (!postId) {
        return;
    }
    CheckReaction();
    const hasLikesCount =
        postData.value?.likes_count !== undefined &&
        postData.value?.likes_count !== null;
    if (!hasLikesCount) {
        totalReactions();
    }
    if (!hasLoggedView.value) {
        logInteraction("view");
        hasLoggedView.value = true;
    }
};
watch(
    () => postData.value?.id,
    (newId, oldId) => {
        if (newId && newId !== oldId) {
            hasLoggedView.value = false;
            initializePostData();
        }
    }
);
const defaultAvatar = "/images/web/users/avatar.jpg";
const defaultPageAvatar = "/images/client/pages/default-page.png";
const buildAvatarUrl = (user) => {
    const avatar = user?.avatar;
    if (!avatar) {
        return defaultAvatar;
    }
    if (avatar.startsWith("http")) {
        return avatar;
    }
    if (avatar.startsWith("/")) {
        return avatar;
    }
    if (avatar.includes("/")) {
        return `/${avatar.replace(/^\/+/, "")}`;
    }
    return `/images/client/avatar/${avatar}`;
};

const buildPageAvatarUrl = (page) => {
    const avatar = page?.profile_picture_url || page?.profile_picture || page?.avatar;
    if (!avatar) {
        return defaultPageAvatar;
    }
    if (avatar.startsWith("http")) {
        return avatar;
    }
    if (avatar.startsWith("/")) {
        return avatar;
    }
    return `/${avatar.replace(/^\/+/, "")}`;
};

const pageData = computed(() => postData.value?.page || null);
const isPagePost = computed(() => !!pageData.value);
const pageName = computed(() => pageData.value?.name || "Page");
const pageLink = computed(() => {
    const page = pageData.value;
    if (!page) {
        return "#";
    }
    if (page.url) {
        return page.url;
    }
    if (page.username) {
        return `/pages/${page.username}`;
    }
    if (page.id) {
        return `/pages/${page.id}`;
    }
    return "#";
});

const authorAvatar = computed(() => buildAvatarUrl(userData.value));
const pageAvatar = computed(() => buildPageAvatarUrl(pageData.value));
const displayAvatar = computed(() =>
    isPagePost.value ? pageAvatar.value : authorAvatar.value
);

const authorLink = computed(() => {
    if (userData.value?.profile_url) {
        return userData.value.profile_url;
    }
    if (userData.value?.username) {
        return `/${userData.value.username}`;
    }
    return "#";
});

const authorName = computed(() => userData.value?.name || "User");

// Kiểm tra xem user hiện tại có phải là chủ bài viết không
const isPostOwner = computed(() => {
    const currentUserId = props.user?.id;
    const postOwnerId = postData.value?.user_id || userData.value?.id;
    return (currentUserId && postOwnerId && currentUserId === postOwnerId);
});

// Quyền chỉnh sửa và xóa (kết hợp chủ bài viết và permissions được truyền vào)
const canEditPost = computed(() => isPostOwner.value || props.canEdit);
const canDeletePost = computed(() => isPostOwner.value || props.canDelete);
const canManagePost = computed(() => canEditPost.value || canDeletePost.value);


const sharedPost = computed(() => postData.value?.original_post || null);
const sharedAuthor = computed(() => sharedPost.value?.user || null);
const sharedAuthorAvatar = computed(() => buildAvatarUrl(sharedAuthor.value));
const sharedAuthorLink = computed(() => {
    if (sharedAuthor.value?.profile_url) {
        return sharedAuthor.value.profile_url;
    }
    if (sharedAuthor.value?.username) {
        return '/' + sharedAuthor.value.username;
    }
    return "#";
});
const sharedAuthorName = computed(() => sharedAuthor.value?.name || "Bài viết gốc");

// Lưu trạng thái like và số lượng like
const totalReaction = ref(
    Number(
        postData.value?.likes_count ??
        postData.value?.reactions_count ??
        postData.value?.total_reactions ??
        (Array.isArray(postData.value?.likes) ? postData.value.likes.length : 0)
    ) || 0
);
const commentsCount = computed(() => {
    if (postData.value?.comments_count !== undefined && postData.value?.comments_count !== null) {
        return postData.value.comments_count;
    }
    if (Array.isArray(postData.value?.comments)) {
        return postData.value.comments.length;
    }
    return 0;
});
const isReaction = ref(false);

// Danh sách reactions
const reactions = [
    { type: "like" },
    { type: "love" },
    { type: "haha" },
    { type: "wow" },
    { type: "sad" },
    { type: "angry" },
];

// Lấy ảnh của reaction hiện tại
const getReactionImage = computed(() => {
    return isReaction.value
        ? `/images/web/icons/reactions/reactions_${isReaction.value}.png`
        : "";
});

// Kiểm tra xem user đã like chưa
const CheckReaction = async () => {
    const postId = getPostId();
    if (!postId) return;
    try {
        const response = await axios.get(
            `/posts/check-reaction/${postId}`
        );
        if (response.data && response.data.reaction) {
            isReaction.value = response.data.reaction;
        } else {
            isReaction.value = null;
        }
    } catch (error) {
        console.error("Error fetching reaction:", error);
        isReaction.value = null;
    }
};

// 🛠 Gửi reaction khi click (CẬP NHẬT UI NGAY LẬP TỨC)
const toggleLike = async (reactionType) => {
    const postId = getPostId();
    if (!postId) {
        console.error("Post ID is missing");
        return;
    }

    const previousReaction = isReaction.value;
    isReaction.value = reactionType;

    try {
        const response = await axios.post(`/posts/reaction/${postId}`, { reaction: reactionType });

        if (response.data && response.data.reaction) {
            isReaction.value = response.data.reaction;
            totalReaction.value = response.data.likes_count ?? totalReaction.value;
        }

        logInteraction("like");
    } catch (error) {
        console.error("Error liking post:", error);
        isReaction.value = previousReaction;
    }
};

// 🛠 Xóa reaction khi click (CẬP NHẬT UI NGAY LẬP TỨC)
const removeReaction = async () => {
    const postId = getPostId();
    if (!isReaction.value || !postId) return;

    // Cập nhật UI ngay lập tức
    const previousReaction = isReaction.value;
    isReaction.value = null;

    try {
        const response = await axios.post(
            `/posts/remove-reaction/${postId}`
        );
        if (response.data && response.data.success) {
            totalReaction.value =
                response.data.likes_count ?? totalReaction.value;
        }
    } catch (error) {
        console.error("Error removing reaction:", error);
        // Nếu có lỗi, quay lại trạng thái trước đó
        isReaction.value = previousReaction;
    }
};

// 🛠 Tổng số luợt reaction
const totalReactions = async () => {
    const postId = getPostId();
    if (!postId) return;
    try {
        const response = await axios.get(
            `/posts/total-reaction/${postId}`
        );
        const likesCount =
            response.data?.likes_count ?? response.data?.totalReaction;
        if (likesCount !== undefined && likesCount !== null) {
            totalReaction.value = likesCount;
        }
    } catch (error) {
        console.error("Error calculating total reactions:", error);
        return 0;
    }
};

// 🛠 Lấy danh sách ảnh của bài viết
const images = computed(() => {
    if (!postData.value?.media) return [];
    return postData.value.media
        .filter((media) => media.media_type === "image")
        .map((media) => media.media_url);
});
const displayImages = computed(() => images.value.slice(0, 2));
const galleryClass = computed(() =>
    images.value.length === 1 ? "single-image" : "multi-images"
);

const sharedImages = computed(() => {
    if (!sharedPost.value?.media) return [];
    return sharedPost.value.media
        .filter((media) => media.media_type === "image")
        .map((media) => media.media_url);
});
const sharedDisplayImages = computed(() => sharedImages.value.slice(0, 2));
const sharedGalleryClass = computed(() =>
    sharedImages.value.length === 1 ? "single-image" : "multi-images"
);

const shareContent = ref("");
const sharePrivacy = ref("public");
const shareError = ref(null);
const shareLoading = ref(false);
const shareCount = computed(() => postData.value?.shares_count ?? 0);

const openShareModal = () => {
    shareError.value = null;
    shareContent.value = "";
    sharePrivacy.value = "public";
    const modalId = `#shareModal-${getPostId()}`;
    $(modalId).modal("show");
};

const submitShare = async (
    content = null,
    privacy = null,
    shouldCloseModal = true
) => {
    const postId = getPostId();
    if (!postId) return;

    shareLoading.value = true;
    shareError.value = null;

    try {
        const response = await axios.post(`/posts/${postId}/share`, {
            content: content !== null ? content : shareContent.value,
            privacy_setting: privacy || sharePrivacy.value,
        });
        const updatedShares = response.data?.shares_count;
        if (updatedShares !== undefined && updatedShares !== null) {
            postData.value.shares_count = updatedShares;
        } else {
            postData.value.shares_count = (postData.value.shares_count || 0) + 1;
        }
        logInteraction("share");
        shareContent.value = "";
        if (shouldCloseModal) {
            $(`#shareModal-${postId}`).modal("hide");
        }
    } catch (error) {
        shareError.value =
            error.response?.data?.message || "Không thể chia sẻ bài viết.";
    } finally {
        shareLoading.value = false;
    }
};

const shareNow = async (privacy = "public") => {
    await submitShare("", privacy, false);
};

const content_comment = ref("");
const comments = ref([]);
const page = ref(1);
const hasMore = ref(true);
const parentCommentId = ref(null);
const isLoading = ref(false);
const hasLoggedView = ref(false);
const commentLoading = ref(false);
const commentError = ref(null);
const hasLoadedComments = ref(false);
const replyContent = ref("");
const replyTo = ref(null);
const showReplies = ref({});
const isLoadingReplies = ref({});

const normalizeComment = (comment) => {
    if (!comment) return null;
    const nestedReplies = normalizeComments(
        comment.replies || comment.replies_recursive || []
    );

    return {
        ...comment,
        replies: nestedReplies,
        replies_count:
            comment.replies_count !== undefined
                ? comment.replies_count
                : nestedReplies.length,
    };
};

const normalizeComments = (list = []) => {
    return list.map((item) => normalizeComment(item));
};

const findCommentById = (list, id) => {
    for (const item of list) {
        if (item.id === id) return item;
        if (item.replies && item.replies.length) {
            const found = findCommentById(item.replies, id);
            if (found) return found;
        }
    }
    return null;
};
const removeCommentById = (list, id, parent = null) => {
    for (let i = 0; i < list.length; i++) {
        const item = list[i];
        if (item.id === id) {
            list.splice(i, 1);
            if (parent && parent.replies_count !== undefined && parent.replies_count > 0) {
                parent.replies_count -= 1;
            }
            return true;
        }
        if (item.replies && item.replies.length) {
            if (removeCommentById(item.replies, id, item)) {
                return true;
            }
        }
    }
    return false;
};
const editComment = ref(null);
const editContent = ref("");

const updateReplyContent = (value) => {
    replyContent.value = value;
};

// Thêm hàm để cập nhật số lượng comment
const updateCommentsCount = async () => {
    const postId = getPostId();
    if (!postId) return;
    try {
        const response = await axios.get(
            `/posts/${postId}/comments-count`
        );
        const count =
            response.data?.comments_count ?? response.data?.count;
        if (count !== undefined) {
            postData.value.comments_count = count;
        }
    } catch (error) {
        console.error("Error updating comments count:", error);
    }
};

// 🛠 Gửi bình luận bài viết
const submitComment = async () => {
    const postId = getPostId();
    if (!content_comment.value.trim() || !postId) return;

    try {
        commentLoading.value = true;
        commentError.value = null;
        const response = await axios.post("/comments", {
            post_id: postId,
            content: content_comment.value,
            parent_comment_id: parentCommentId.value,
        });

        content_comment.value = "";
        parentCommentId.value = null;

        const newComment = normalizeComment(response.data.comment);

        if (newComment?.parent_comment_id) {
            const parentComment = findCommentById(comments.value, newComment.parent_comment_id);
            if (parentComment) {
                if (!Array.isArray(parentComment.replies)) {
                    parentComment.replies = [];
                }
                parentComment.replies.unshift(newComment);
                showReplies.value[parentComment.id] = true;
            }
        } else {
            comments.value.unshift(newComment);
        }

        logInteraction("comment");
        await updateCommentsCount();
    } catch (error) {
        console.error("Error sending comment:", error);
        commentError.value = "Không thể gửi bình luận. Vui lòng thử lại sau.";
    } finally {
        commentLoading.value = false;
    }
};

// 🛠 Hiển thị bình luận bài viết
const fetchComments = async (reset = false) => {
    const postId = getPostId();
    if (!postId || isLoading.value) return;

    if (reset) {
        comments.value = [];
        page.value = 1;
        hasMore.value = true;
        showReplies.value = {};
        isLoadingReplies.value = {};
        commentError.value = null;
    }

    try {
        isLoading.value = true;
        const response = await axios.get(`/comments/${postId}`, {
            params: { page: page.value }
        });

        const paginatedData = response.data;

        if (paginatedData.data.length === 0) {
            hasMore.value = false;
        } else {
            const normalized = normalizeComments(paginatedData.data);
            comments.value.push(...normalized);
            page.value++;
            hasMore.value = paginatedData.next_page_url !== null;
        }
        hasLoadedComments.value = true;
    } catch (error) {
        console.error("Error fetching comments:", error);
        commentError.value = "Không thể tải bình luận. Vui lòng thử lại sau.";
    } finally {
        isLoading.value = false;
    }
};

const loadMoreComments = () => {
    if (!isLoading.value && hasMore.value) {
        fetchComments();
    }
};

const getCommentModalSelector = () => {
    const postId = getPostId();
    return postId ? `#exampleModalScrollable-${postId}` : null;
};

const handleCommentModalShow = async () => {
    if (hasLoadedComments.value) return;
    await fetchComments(true);
};

const setReply = (commentId) => {
    replyTo.value = commentId;
    replyContent.value = "";
};

const highlightMentions = (text) => {
    return text.replace(
        /(@\w[\w\s]*)/g,
        '<span class="text-primary">$1</span>'
    );
};

const formatTime = (time) => {
    if (!time) return "";
    const date = new Date(time);
    if (Number.isNaN(date.getTime())) {
        return time;
    }
    return date.toLocaleString();
};

// 🛠 Xóa bài viết
const deletePost = async (postId) => {
    try {
        const response = await axios.post(`/posts/${postId}`);
        if (response.status === 200) {
            emit("deleted", postId);
        }
    } catch (error) {
        console.error("Error deleting post:", error);
    }
};

// 🛠 Cập nhật quyền riêng tư của bài viết
const updatePrivacy = async (privacy) => {
    const postId = getPostId();
    if (!postId) return;
    try {
        const response = await axios.post(
            `/posts/${postId}/privacy`,
            {
                privacy_setting: privacy,
            }
        );

            // Cập nhật trạng thái quyền riêng tư trong component
            postData.value.privacy_setting = privacy;

            // Hiển thị thông báo thành công
            // Bạn có thể thêm toast notification ở đây
    } catch (error) {
            console.error("Lỗi khi cập nhật quyền riêng tư:", error);
            // Hiển thị thông báo lỗi
    }
};

// 🛠 Xác định icon quyền riêng tư
const privacyIcon = computed(() => {
    switch (postData.value.privacy_setting) {
        case "public":
            return "bx bx-globe";
        case "friends":
            return "bx bx-user";
        case "private":
            return "bx bx-lock-alt";
        default:
            return "bx bx-globe";
    }
});

const handlePostUpdated = (updatedPost) => {
    postData.value = {
        ...postData.value,
        content: updatedPost.content,
        media: updatedPost.media,
    };
    // Cập nhật lại danh sách ảnh
    images.value = updatedPost.media
        ? updatedPost.media
            .filter((media) => media.media_type === "image")
            .map((media) => media.media_url)
        : [];
    emit("updated", postData.value);
};

const setupEchoListener = () => {
    if (!window.Echo || !window.userId) return;

    window.Echo.private(`user.${window.userId}`).listen(
        ".reaction.added",
        (e) => {
            if (e.post_id === postData.value.id) {
                totalReaction.value++;
            }
        }
    );
};

const toggleReplies = async (commentId) => {
    const target = findCommentById(comments.value, commentId);
    if (!target) return;

    const shouldShow = !showReplies.value[commentId];

    if (shouldShow && (!target.replies || target.replies.length === 0)) {
        try {
            isLoadingReplies.value[commentId] = true;
            await fetchReplies(commentId);
        } catch (error) {
            console.error("Error fetching replies:", error);
        } finally {
            isLoadingReplies.value[commentId] = false;
        }
    }

    showReplies.value[commentId] = shouldShow;
};

const fetchReplies = async (commentId) => {
    const comment = findCommentById(comments.value, commentId);
    if (!comment) return;
    const response = await axios.get(`/comments/${commentId}/replies`);
    const dataReplies = normalizeComments(response.data.replies || []);

    comment.replies = dataReplies;
};

const submitReply = async (commentId) => {
    const postId = getPostId();
    if (!replyContent.value.trim() || !postId) return;

    try {
        const response = await axios.post("/comments", {
            post_id: postId,
            content: replyContent.value,
            parent_comment_id: commentId,
        });

        const newReply = normalizeComment(response.data.comment);
        const comment = findCommentById(comments.value, commentId);
        if (comment) {
            if (!comment.replies) comment.replies = [];
            comment.replies.unshift(newReply);
            if (comment.replies_count !== undefined) {
                comment.replies_count += 1;
            } else {
                comment.replies_count = comment.replies.length;
            }
            showReplies.value[commentId] = true;
        }

        logInteraction("comment");
        replyContent.value = "";
        replyTo.value = null;
        await updateCommentsCount();
    } catch (error) {
        console.error("Error submitting reply:", error);
    }
};

const startEdit = (comment) => {
    editComment.value = comment;
    editContent.value = comment.content;
};

const deleteComment = async (commentId) => {
    if (!confirm('Bạn có chắc chắn muốn xóa bình luận này?')) return;

    try {
        const response = await axios.delete(`/comments/${commentId}`);

        if (response.data.comment_id) {
            // Xóa comment khỏi danh sách
            removeCommentById(comments.value, commentId);

            // Cập nhật số lượng comment
            await updateCommentsCount();

            // Hiển thị thông báo thành công
            alert('Xóa bình luận thành công');
        }
    } catch (error) {
        console.error('Error deleting comment:', error);
        alert('Đây không phải bình luận của bạn.');
    }
};

const toggleCommentVisibility = async (commentId) => {
    try {
        const response = await axios.put(
            `/api/comments/${commentId}/toggle-visibility`
        );
        const target = findCommentById(comments.value, commentId);
        if (target) {
            target.is_hidden = response.data.is_hidden;
        }
    } catch (error) {
        console.error("Error toggling comment visibility:", error);
    }
};

onMounted(() => {
    initializePostData();
    const modalSelector = getCommentModalSelector();
    if (modalSelector) {
        $(modalSelector).on("show.bs.modal", handleCommentModalShow);
        
    }
    setupEchoListener();
});

onUnmounted(() => {
    const modalSelector = getCommentModalSelector();
    if (modalSelector) {
        $(modalSelector).off("show.bs.modal", handleCommentModalShow);
    }
    if (window.Echo) {
        window.Echo.leave(`user.${window.userId}`);
    }
});
</script>

<style scoped>
@media (forced-colors: active) {
    .privacy-dropdown .dropdown-item {
        border: 1px solid CanvasText;
        forced-color-adjust: none;
    }

    .privacy-dropdown .dropdown-item.active {
        background-color: Highlight;
        color: HighlightText;
        forced-color-adjust: none;
    }

    .privacy-icon {
        color: CanvasText;
        forced-color-adjust: none;
    }

    .privacy-icon:hover {
        color: Highlight;
        forced-color-adjust: none;
    }
}

.privacy-dropdown {
    min-width: 250px;
    padding: 10px;
}

.privacy-dropdown .dropdown-item {
    padding: 8px 12px;
    cursor: pointer;
    border-radius: 5px;
    margin-bottom: 2px;
}

.privacy-dropdown .dropdown-item:hover {
    background-color: #f8f9fa;
}

.privacy-dropdown .dropdown-item.active {
    background-color: #e7f3ff;
    color: #1877f2;
}

.privacy-dropdown i {
    font-size: 1.2em;
    vertical-align: middle;
}

.privacy-dropdown small {
    font-size: 0.8em;
    margin-top: 2px;
}

.suggested-badge {
    display: inline-flex;
    align-items: center;
    background: #f0f2f5;
    color: #65676b;
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 999px;
    margin-left: 8px;
}

.bx-globe {
    cursor: pointer;
    color: #65676b;
}

.bx-globe:hover {
    color: #1877f2;
}

.privacy-icon {
    cursor: pointer;
    color: #65676b;
    font-size: 1.2em;
    vertical-align: middle;
}

.privacy-icon:hover {
    color: #1877f2;
}

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

/* Modal styles */
.modal {
    display: none;
    position: fixed;
    z-index: 1050;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    overflow: hidden;
    outline: 0;
}

.comment-see-more {
    margin-top: 10px;
    margin-bottom: 10px;
}

.comment-see-more .btn-link {
    color: #1877f2;
    text-decoration: none;
    font-weight: 600;
}

.comment-see-more .btn-link:hover {
    text-decoration: underline;
}

.comment-see-more .btn-link:disabled {
    color: #65676b;
    cursor: not-allowed;
}

.post-header {
    display: flex;
    align-items: center;
    gap: 10px;
}

.user-name {
    font-weight: bold;
}

.group-name {
    color: #007bff;
    font-size: 14px;
    margin-left: 5px;
}

.post-separator {
    color: #9ca3af;
    margin: 0 4px;
    font-weight: 600;
}

.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.replies-container {
    margin-left: 50px;
    margin-top: 10px;
    border-left: 2px solid #e9ecef;
    padding-left: 15px;
}

.reply-item {
    margin-bottom: 15px;
}

.reply-item:last-child {
    margin-bottom: 0;
}

.comment-input:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.btn-primary:disabled {
    cursor: not-allowed;
}

.spinner-border {
    width: 1.5rem;
    height: 1.5rem;
}

.comments-section {
    border-top: 1px solid #e5e7eb;
    padding-top: 1rem;
}

.comment-item {
    position: relative;
}

.reply-item {
    position: relative;
}

.reply-item::before {
    content: "";
    position: absolute;
    left: -1rem;
    top: 1rem;
    width: 1rem;
    height: 1px;
    background-color: #e5e7eb;
}

.reply-input {
    margin-left: 50px;
}

.reply-input .form-control {
    border-radius: 20px;
    padding: 8px 15px;
}

.reply-input .btn {
    border-radius: 20px;
    padding: 8px 20px;
}

.commentLR {
    margin-top: 5px;
}

.commentLR .btn-link {
    padding: 0;
    font-size: 0.875rem;
}

.commentLR .btn-link:hover {
    color: #1877f2;
    text-decoration: none;
}

.shared-post-card {
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 12px;
    background: #f8fafc;
}

.shared-post-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    object-fit: cover;
}

.shared-post-user {
    font-weight: 600;
    color: #1d2129;
}

.shared-gallery {
    margin-top: 8px;
}
/* Comment UI refresh */
.comment-modal-body {
    background: #f8fafc;
}

.comment-thread-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px;
    background: #ffffff;
    box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
}

.comment-thread-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 12px;
}

.count-pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    background: #eef2ff;
    color: #1d4ed8;
    font-weight: 700;
    font-size: 13px;
}

.comments-scroll {
    max-height: 620px;
    overflow-y: auto;
    padding-right: 6px;
}

.empty-comments-state {
    text-align: center;
    padding: 28px 12px;
    color: #6b7280;
    background: #f9fafb;
    border: 1px dashed #e5e7eb;
    border-radius: 12px;
}

.load-more-row button {
    border-radius: 999px;
    padding: 6px 14px;
}

.comment-footer {
    display: flex;
    align-items: center;
    gap: 12px;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.comment-footer-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    object-fit: cover;
    border: 1px solid #e5e7eb;
}

.comment-footer .input-group {
    flex: 1;
}

.comment-footer .comment-input {
    border-radius: 20px 0 0 20px;
}

.comment-footer .btn {
    border-radius: 0 20px 20px 0;
}
</style>










