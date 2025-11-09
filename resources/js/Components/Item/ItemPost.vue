<template>
    <div class="post border-bottom p-3 bg-white w-shadow mb-3">
        <div class="media text-muted pt-3">
            <img :src="userData.avatar
                    ? `/images/client/avatar/${userData.avatar}`
                    : '/images/web/users/avatar.jpg'
                " class="mr-3 post-user-image" />
            <div class="media-body pb-3 mb-0 small lh-125">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <span class="post-type text-muted">
                        <Link :href="`/${userData.username}`" class="text-gray-dark post-user-name">{{ userData.name }}
                        </Link>
                        <!-- Hiển thị nhóm nếu có -->
                        <template v-if="postData.group">
                            <span class="group-name">
                                <Link :href="`/groups/${postData.group.id}`" class="text-gray-dark post-user-name">
                                {{ "> " + postData.group.name }}</Link>
                            </span>
                        </template>
                    </span>
                    <div class="dropdown">
                        <a href="#" class="post-more-settings" role="button" data-toggle="dropdown" id="postOptions"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left post-dropdown-menu">
                            <a href="#" class="dropdown-item" data-toggle="modal"
                                :data-target="'#modal-update' + postData.id">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class="bx bx-edit-alt post-option-icon"></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Chỉnh sửa</span>
                                        <small id="editPost" class="form-text text-muted">edit post article</small>
                                    </div>
                                </div>
                            </a>
                            <a href="#" class="dropdown-item" aria-describedby="deletePost"
                                @click.prevent="deletePost(postData.id)">
                                <div class="row">
                                    <div class="col-md-2">
                                        <i class="bx bx-trash post-option-icon"></i>
                                    </div>
                                    <div class="col-md-10">
                                        <span class="fs-9">Xóa bỏ</span>
                                        <small id="deletePost" class="form-text text-muted">delete post</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <span class="d-block">{{ postData.created_at }}
                    <div class="dropdown d-inline-block">
                        <i :class="privacyIcon" class="ml-3 privacy-icon" data-toggle="dropdown" aria-haspopup="true"
                            aria-expanded="false"></i>
                        <div class="dropdown-menu dropdown-menu-right privacy-dropdown">
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'public',
                            }" @click="updatePrivacy('public')">
                                <i class="bx bx-globe mr-2"></i>
                                <span>Public</span>
                                <small class="d-block text-muted">Mọi người có thể xem bài viết này</small>
                            </div>
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'friends',
                            }" @click="updatePrivacy('friends')">
                                <i class="bx bx-user mr-2"></i>
                                <span>Friends</span>
                                <small class="d-block text-muted">Chỉ bạn bè có thể xem bài viết này</small>
                            </div>
                            <div class="dropdown-item" :class="{
                                active:
                                    postData.privacy_setting === 'private',
                            }" @click="updatePrivacy('private')">
                                <i class="bx bx-lock-alt mr-2"></i>
                                <span>Private</span>
                                <small class="d-block text-muted">Chỉ bạn có thể xem bài viết này</small>
                            </div>
                        </div>
                    </div>
                </span>
            </div>
        </div>
        <p>{{ postData.content }}</p>

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
            :data-target="'#exampleModalScrollable-' + postData.id"><i class="bx bx-message-rounded mr-1"></i>
            {{ postData.comments_count || 0 }}</a>
        <div class="dropdown dropup share-dropup">
            <a href="#" class="post-card-buttons" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="bx bx-share-alt mr-1"></i> Share
            </a>
            <div class="dropdown-menu post-dropdown-menu">
                <a href="#" class="dropdown-item">
                    <div class="row">
                        <div class="col-md-2">
                            <i class="bx bx-share-alt"></i>
                        </div>
                        <div class="col-md-10">
                            <span>Share Now (Public)</span>
                        </div>
                    </div>
                </a>
                <a href="#" class="dropdown-item">
                    <div class="row">
                        <div class="col-md-2">
                            <i class="bx bx-share-alt"></i>
                        </div>
                        <div class="col-md-10">
                            <span>Share...</span>
                        </div>
                    </div>
                </a>
                <a href="#" class="dropdown-item">
                    <div class="row">
                        <div class="col-md-2">
                            <i class="bx bx-message"></i>
                        </div>
                        <div class="col-md-10">
                            <span>Send as Message</span>
                        </div>
                    </div>
                </a>
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
                    <div class="modal-body">
                        <div class="hide-comments">
                            <div class="row bootstrap snippets">
                                <div class="col-md-12">
                                    <div class="comment-wrapper">
                                        <div class="panel panel-info">
                                            <div class="panel-body">
                                                <ul class="media-list comments-list overflow-auto"
                                                    style="max-height: 600px">
                                                    <!-- v-for lặp qua các comment cha -->
                                                    <li class="media" v-for="comment in comments" :key="comment.id">
                                                        <a href="#" class="pull-left">
                                                            <img :src="comment.user
                                                                    .avatar
                                                                    ? `/images/client/avatar/${comment.user.avatar}`
                                                                    : '/images/web/users/avatar.jpg'
                                                                " alt="" class="img-circle" />
                                                        </a>
                                                        <div class="media-body">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center w-100">
                                                                <strong class="text-gray-dark">
                                                                    <a href="#" class="fs-8">{{
                                                                        comment
                                                                            .user
                                                                            .name
                                                                    }}</a>
                                                                </strong>
                                                                <div class="dropdown">
                                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                                                        <i class="bx bx-dots-horizontal-rounded"></i>
                                                                    </a>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        <a class="dropdown-item" href="#" @click.prevent="toggleReplies(comment.id)">
                                                                            {{ showReplies[comment.id] ? 'Ẩn phản hồi' : 'Xem phản hồi' }}
                                                                        </a>
                                                                        <a class="dropdown-item" href="#" @click.prevent="startEdit(comment)" v-if="comment.user_id === user.id">
                                                                            Chỉnh sửa
                                                                        </a>
                                                                        <a class="dropdown-item" href="#" @click.prevent="deleteComment(comment.id)">
                                                                            Xóa
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <span class="d-block comment-created-time">{{
                                                                formatTime(
                                                                    comment.created_at
                                                                )
                                                            }}</span>
                                                            <p class="fs-8 pt-2" v-html="highlightMentions(
                                                                comment.content
                                                            )
                                                                "></p>
                                                            <div class="commentLR">
                                                                <button type="button" class="btn btn-link fs-8" @click="
                                                                    setReply(
                                                                        comment.id
                                                                    )
                                                                    ">
                                                                    Reply
                                                                </button>
                                                            </div>

                                                            <!-- Reply input -->
                                                            <div v-if="
                                                                replyTo ===
                                                                comment.id
                                                            " class="reply-input mt-2">
                                                                <div class="input-group">
                                                                    <input type="text" v-model="replyContent
                                                                        " class="form-control"
                                                                        placeholder="Viết phản hồi..."
                                                                        @keydown.enter.prevent="
                                                                            submitReply(
                                                                                comment.id
                                                                            )
                                                                            " />
                                                                    <div class="input-group-append">
                                                                        <button class="btn btn-primary" @click="
                                                                            submitReply(
                                                                                comment.id
                                                                            )
                                                                            ">
                                                                            Gửi
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Replies section -->
                                                            <div v-if="
                                                                showReplies[
                                                                comment
                                                                    .id
                                                                ]
                                                            " class="replies-container mt-2">
                                                                <div v-if="
                                                                    isLoadingReplies[
                                                                    comment
                                                                        .id
                                                                    ]
                                                                " class="text-center py-2">
                                                                    <div class="spinner-border spinner-border-sm text-primary"
                                                                        role="status">
                                                                        <span class="sr-only">Loading...</span>
                                                                    </div>
                                                                </div>
                                                                <div v-else-if="
                                                                    comment.replies &&
                                                                    comment
                                                                        .replies
                                                                        .length >
                                                                    0
                                                                ">
                                                                    <div v-for="reply in comment.replies" :key="reply.id
                                                                        " class="media reply-item">
                                                                        <a href="#" class="pull-left">
                                                                            <img :src="reply
                                                                                    .user
                                                                                    .avatar
                                                                                    ? `/images/client/avatar/${reply.user.avatar}`
                                                                                    : '/images/web/users/avatar.jpg'
                                                                                " alt="" class="img-circle" />
                                                                        </a>
                                                                        <div class="media-body">
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center w-100">
                                                                                <strong class="text-gray-dark">
                                                                                    <a href="#" class="fs-8">{{
                                                                                        reply
                                                                                            .user
                                                                                            .name
                                                                                    }}</a>
                                                                                </strong>
                                                                                <div class="dropdown" v-if="
                                                                                    reply.user_id ===
                                                                                    user.id
                                                                                ">
                                                                                    <a href="#" class="dropdown-toggle"
                                                                                        data-toggle="dropdown">
                                                                                        <i
                                                                                            class="bx bx-dots-horizontal-rounded"></i>
                                                                                    </a>
                                                                                    <div
                                                                                        class="dropdown-menu dropdown-menu-right">
                                                                                        <a class="dropdown-item"
                                                                                            href="#" @click.prevent="
                                                                                                startEdit(
                                                                                                    reply
                                                                                                )
                                                                                                ">
                                                                                            Chỉnh
                                                                                            sửa
                                                                                        </a>
                                                                                        <a class="dropdown-item"
                                                                                            href="#" @click.prevent="
                                                                                                deleteComment(
                                                                                                    reply.id
                                                                                                )
                                                                                                ">
                                                                                            Xóa
                                                                                        </a>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <span
                                                                                class="d-block comment-created-time">{{
                                                                                    formatTime(
                                                                                        reply.created_at
                                                                                    )
                                                                                }}</span>
                                                                            <p class="fs-8 pt-2" v-html="highlightMentions(
                                                                                reply.content
                                                                            )
                                                                                "></p>
                                                                            <div class="commentLR">
                                                                                <button type="button"
                                                                                    class="btn btn-link fs-8" @click="
                                                                                        setReply(
                                                                                            reply.id
                                                                                        )
                                                                                        ">
                                                                                    Reply
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div v-else class="text-center text-muted py-2">
                                                                    Chưa có phản
                                                                    hồi
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </li>

                                                    <!-- Loading indicator -->
                                                    <li v-if="isLoading" class="media">
                                                        <div class="media-body text-center">
                                                            <div class="spinner-border text-primary" role="status">
                                                                <span class="sr-only">Loading...</span>
                                                            </div>
                                                        </div>
                                                    </li>

                                                    <!-- Load more button -->
                                                    <li v-if="hasMore" class="media">
                                                        <div class="media-body">
                                                            <div class="comment-see-more text-center">
                                                                <button type="button" class="btn btn-link fs-8" @click="
                                                                    loadMoreComments
                                                                " :disabled="isLoading
                                                                        ">
                                                                    {{
                                                                        isLoading
                                                                            ? "Loading..."
                                                                            : "Xem thêm"
                                                                    }}
                                                                </button>
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
                    <form class="modal-footer" @submit.prevent="submitComment">
                        <input type="text" v-model="content_comment" class="form-control comment-input"
                            placeholder="Nhập bình luận của bạn..." @keydown.enter.prevent="submitComment"
                            :disabled="commentLoading" />
                        <button type="submit" class="btn btn-primary" :disabled="commentLoading">
                            {{ commentLoading ? "Đang gửi..." : "Gửi" }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <UpdatePost :post="postData" @updated="handlePostUpdated" />
</template>

<script setup>
import "bootstrap-fileinput/css/fileinput.min.css";
import "bootstrap-fileinput/js/fileinput.min.js";
import { ref, computed, defineProps, onMounted, watch, onUnmounted } from "vue";
import axios from "axios";
import UpdatePost from "./UpdatePost.vue";
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
});

const emit = defineEmits(["updated", "deleted"]);

// Thay computed bằng ref để có thể cập nhật
const postData = ref(props.post);
const userData = ref(props.user);

// Watch props để cập nhật khi props thay đổi
watch(
    () => props.post,
    (newPost) => {
        postData.value = newPost;
    },
    { deep: true }
);

watch(
    () => props.user,
    (newUser) => {
        userData.value = newUser;
    },
    { deep: true }
);

// Lưu trạng thái like và số lượng like
const totalReaction = ref(0);
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

// 🛠 Kiểm tra xem user đã like chưa
const CheckReaction = async () => {
    try {
        const response = await axios.get(
            `/posts/check-reaction/${postData.value.id}`
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
    if (!postData.value?.id) {
        console.error("Post ID is missing");
        return;
    }

    // Cập nhật UI ngay lập tức
    const previousReaction = isReaction.value;
    isReaction.value = reactionType;

    try {
        const response = await axios.post(
            `/posts/reaction/${postData.value.id}`,
            {
                reaction: reactionType,
            }
        );

        if (response.data && response.data.reaction) {
            // Nếu server trả về reaction khác, cập nhật lại
            isReaction.value = response.data.reaction;
            totalReaction.value =
                response.data.likes_count ?? totalReaction.value;

            // Tạo thông báo nếu reaction thành công
            if (response.data.notification) {
                // Thông báo đã được tạo từ server
                console.log(
                    "Reaction notification created:",
                    response.data.notification
                );
            }
        }
    } catch (error) {
        console.error("Error liking post:", error);
        // Nếu có lỗi, quay lại trạng thái trước đó
        isReaction.value = previousReaction;
    }
};

// 🛠 Xóa reaction khi click (CẬP NHẬT UI NGAY LẬP TỨC)
const removeReaction = async () => {
    if (!isReaction.value) return;

    // Cập nhật UI ngay lập tức
    const previousReaction = isReaction.value;
    isReaction.value = null;

    try {
        const response = await axios.post(
            `/posts/remove-reaction/${postData.value.id}`
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

// 🛠 Tổng số lượt reaction
const totalReactions = async () => {
    try {
        const response = await axios.get(
            `/posts/total-reaction/${postData.value.id}`
        );
        totalReaction.value = response.data.totalReaction;
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

const content_comment = ref("");
const comments = ref([]);
const page = ref(1);
const hasMore = ref(true);
const parentCommentId = ref(null);
const isLoading = ref(false);
const commentLoading = ref(false);
const commentError = ref(null);
const replyContent = ref("");
const replyTo = ref(null);
const showReplies = ref({});
const isLoadingReplies = ref({});
const editComment = ref(null);
const editContent = ref("");

// Thêm hàm để cập nhật số lượng comment
const updateCommentsCount = async () => {
    try {
        const response = await axios.get(
            `/posts/${postData.value.id}/comments-count`
        );
        if (response.data && response.data.count !== undefined) {
            postData.value.comments_count = response.data.count;
        }
    } catch (error) {
        console.error("Error updating comments count:", error);
    }
};

// 🛠 Gửi comment bình luận bài post
const submitComment = async () => {
    if (!content_comment.value.trim()) return;

    try {
        commentLoading.value = true;
        const response = await axios.post("/comments", {
            post_id: postData.value.id,
            content: content_comment.value,
            parent_comment_id: parentCommentId.value,
        });

        content_comment.value = "";
        parentCommentId.value = null;

        const newComment = response.data.comment;

        if (newComment.parent_comment_id) {
            const parentComment = comments.value.find(
                (comment) => comment.id === newComment.parent_comment_id
            );
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

        await updateCommentsCount();
    } catch (error) {
        console.error("Lỗi khi gửi bình luận:", error);
        commentError.value = "Không thể gửi bình luận. Vui lòng thử lại sau.";
    } finally {
        commentLoading.value = false;
    }
};

// 🛠 Hiện comment bình luận bài post
const fetchComments = async () => {
    if (isLoading.value) return;

    try {
        isLoading.value = true;
        const response = await axios.get(
            `/comments/${postData.value.id}?page=${page.value}`
        );

        const paginatedData = response.data;

        if (paginatedData.data.length === 0) {
            hasMore.value = false;
        } else {
            // Chỉ thêm comments mới vào mảng hiện có
            comments.value.push(...paginatedData.data);
            page.value++;
            hasMore.value = paginatedData.next_page_url !== null;
        }
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
    return new Date(time).toLocaleString();
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
    try {
        const response = await axios.post(
            `/posts/${postData.value.id}/privacy`,
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
    try {
        if (!showReplies.value[commentId]) {
            isLoadingReplies.value[commentId] = true;
            const response = await axios.get(`/comments/${commentId}/replies`);
            const comment = comments.value.find((c) => c.id === commentId);
            if (comment) {
                comment.replies = response.data.replies;
            }
        }
        showReplies.value[commentId] = !showReplies.value[commentId];
    } catch (error) {
        console.error("Error fetching replies:", error);
    } finally {
        isLoadingReplies.value[commentId] = false;
    }
};

const submitReply = async (commentId) => {
    if (!replyContent.value.trim()) return;

    try {
        const response = await axios.post("/comments", {
            post_id: postData.value.id,
            content: replyContent.value,
            parent_comment_id: commentId,
        });

        const comment = comments.value.find((c) => c.id === commentId);
        if (comment) {
            if (!comment.replies) comment.replies = [];
            comment.replies.unshift(response.data.comment);
            showReplies.value[commentId] = true;
        }

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
            comments.value = comments.value.filter(c => c.id !== commentId);

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
        const index = comments.value.findIndex((c) => c.id === commentId);
        if (index !== -1) {
            comments.value[index].is_hidden = response.data.is_hidden;
        }
    } catch (error) {
        console.error("Error toggling comment visibility:", error);
    }
};

onMounted(() => {
    CheckReaction();
    totalReactions();
    fetchComments();
    updateCommentsCount();
    setupEchoListener();
});

onUnmounted(() => {
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
</style>
