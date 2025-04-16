<template>
    <div class="post border-bottom p-3 bg-white w-shadow mb-3">
        <div class="media text-muted pt-3">
            <img :src="props.user.avatar
                ? `/images/client/avatar/${props.user.avatar}`
                : '/images/web/users/avatar.jpg'
                " class="mr-3 post-user-image" />
            <div class="media-body pb-3 mb-0 small lh-125">
                <div class="d-flex justify-content-between align-items-center w-100">
                    <span class="post-type text-muted"><a href="#" class="text-gray-dark post-user-name mr-2">Arthur
                            Minasyan</a>
                        updated his cover photo.</span>
                    <div class="dropdown">
                        <a href="#" class="post-more-settings" role="button" data-toggle="dropdown" id="postOptions"
                            aria-haspopup="true" aria-expanded="false">
                            <i class="bx bx-dots-horizontal-rounded"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-left post-dropdown-menu">
                            <a href="#" class="dropdown-item" data-toggle="modal" :data-target="'#modal-update' + post.id">
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
                                @click.prevent="deletePost(post.id)">
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
                <span class="d-block">{{ props.post.created_at }} <i class="bx bx-globe ml-3"></i></span>
            </div>
        </div>
        <p>{{ props.post.content }}</p>

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

        <div v-if="images.length > 2" class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
            aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div id="carouselExampleControls" class="carousel slide" data-ride="carousel">
                        <div class="carousel-inner">
                            <div v-for="(src, index) in images" :key="index" class="carousel-item"
                                :class="{ active: index === 0 }">
                                <img :src="'/images/client/post/' + src" class="d-block w-100" loading="lazy" />
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
            :data-target="'#exampleModalScrollable-' + post.id"><i class="bx bx-message-rounded mr-1"></i>
            {{ post.comments_count }}</a>
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

        <!-- Modal bình luận -->
        <div class="modal fade bd-example-modal-lg" :id="'exampleModalScrollable-' + post.id" tabindex="-1"
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
                                                                <a href="#"><i
                                                                        class="bx bx-dots-horizontal-rounded"></i></a>
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
                                                                <button type="button" class="btn btn-link fs-8">
                                                                    Like
                                                                </button>
                                                                <button type="button" class="btn btn-link fs-8" @click="
                                                                    setReply(
                                                                        comment.id
                                                                    )
                                                                    ">
                                                                    Reply
                                                                </button>
                                                            </div>

                                                            <!-- REPLIES -->
                                                    <li class="media" v-for="reply in comment.replies" :key="reply.id">
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
                                                                <a href="#"><i
                                                                        class="bx bx-dots-horizontal-rounded"></i></a>
                                                            </div>
                                                            <span class="d-block comment-created-time">{{
                                                                formatTime(
                                                                    reply.created_at
                                                                )
                                                            }}</span>
                                                            <p class="fs-8 pt-2" v-html="highlightMentions(
                                                                reply.content
                                                            )
                                                                "></p>
                                                            <div class="commentLR">
                                                                <button type="button" class="btn btn-link fs-8">
                                                                    Like
                                                                </button>
                                                                <button type="button" class="btn btn-link fs-8" @click="
                                                                    setReply(
                                                                        reply.id
                                                                    )
                                                                    ">
                                                                    Reply
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </li>
                                            </div>
                                            </li>

                                            <!-- Nút Xem thêm -->
                                            <li class="media" v-if="hasMore">
                                                <div class="media-body">
                                                    <div class="comment-see-more text-center">
                                                        <button type="button" class="btn btn-link fs-8" @click="
                                                            loadMoreComments
                                                        ">
                                                            See More
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
                <form class="modal-footer">
                    <input type="text" v-model="content_comment" class="form-control comment-input"
                        placeholder="Nhập bình luận của bạn..." />
                    <button type="button" class="btn btn-primary" @click="submitComment">
                        Gửi
                    </button>
                </form>
            </div>
        </div>
    </div>
    </div>


    <!-- modal update post -->

    <div class="modal fade bd-example-modal-lg" :id="'modal-update'+post.id" tabindex="-1" role="dialog"
        aria-labelledby="myLargeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Chỉnh sửa bài viết</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <form @submit.prevent="submitPost">
                        <!-- Quyền riêng tư -->
                        <select v-model="form.privacy_setting" class="form-control mb-3">
                            <option value="public">Công Khai</option>
                            <option value="friends">Bạn Bè</option>
                            <option value="private">Chỉ Mình Tôi</option>
                        </select>

                        <!-- Nội dung bài viết -->
                        <div class="form-group">
                            <label for="message-text">Bạn đang nghĩ gì:</label>
                            <textarea v-model="form.content" class="form-control" id="message-text" rows="4"></textarea>
                        </div>

                        <!-- Upload ảnh -->
                        <input type="file" class="form-control mt-3" ref="fileInput" multiple @change="handleFileUpload">

                        <!-- Nút submit -->
                        <div class="modal-footer p-0 mt-4">
                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



</template>

<script setup>
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import $ from 'jquery';
import { ref, computed, defineProps, onMounted,watch,nextTick } from "vue";
import axios from "axios";

const props = defineProps({
    post: Object,
    user: Object,
});

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
            `/posts/check-reaction/${props.post.id}`
        );
        isReaction.value = response.data.reaction || null;
    } catch (error) {
        console.error("Error fetching reaction:", error);
    }
};

// 🛠 Gửi reaction khi click (CẬP NHẬT UI NGAY LẬP TỨC)
const toggleLike = async (reactionType) => {
    if (!props.post?.id) {
        console.error("Post ID is missing");
        return;
    }

    isReaction.value = reactionType; // ✅ Cập nhật UI ngay lập tức

    try {
        const response = await axios.post(`/posts/reaction/${props.post.id}`, {
            reaction: reactionType,
        });
        likesCount.value = response.data.likes_count ?? likesCount.value; // Cập nhật số like nếu API trả về
    } catch (error) {
        console.error("Error liking post:", error);
    }
};

// 🛠 Xóa reaction khi click (CẬP NHẬT UI NGAY LẬP TỨC)
const removeReaction = async () => {
    if (!isReaction.value) return; // Nếu chưa có reaction, không làm gì cả

    isReaction.value = null; // Cập nhật UI ngay lập tức

    try {
        const response = await axios.post(
            `/posts/remove-reaction/${props.post.id}`
        );
        likesCount.value = response.data.likes_count ?? likesCount.value; // Cập nhật số like nếu API trả về
    } catch (error) {
        console.error("Error removing reaction:", error);
    }
};

// 🛠 Tổng số lượt reaction
const totalReactions = async () => {
    try {
        const response = await axios.get(
            `/posts/total-reaction/${props.post.id}`
        );
        totalReaction.value = response.data.totalReaction;
    } catch (error) {
        console.error("Error calculating total reactions:", error);
        return 0;
    }
};

// 🛠 Lấy danh sách ảnh của bài viết
const images = computed(() =>
    props.post.media ? props.post.media.map((media) => media.media_url) : []
);
const displayImages = computed(() => images.value.slice(0, 2));
const galleryClass = computed(() =>
    images.value.length === 1 ? "single-image" : "multi-images"
);

const content_comment = ref("");
const comments = ref([]);
const page = ref(1);
const hasMore = ref(true);
const parentCommentId = ref(null);

// 🛠 Gửi comment bình luận bài post
const submitComment = async () => {
    try {
        const response = await axios.post("/comments", {
            post_id: props.post.id,
            content: content_comment.value,
            parent_comment_id: parentCommentId.value,
        });

        // Reset input
        content_comment.value = "";
        parentCommentId.value = null;

        const newComment = response.data.comment;

        if (newComment.parent_comment_id) {
            const parentComment = comments.value.find(
                (comment) => comment.id === newComment.parent_comment_id
            );
            if (parentComment) {
                // ✅ Nếu replies chưa tồn tại, khởi tạo nó là mảng rỗng
                if (!Array.isArray(parentComment.replies)) {
                    parentComment.replies = [];
                }

                parentComment.replies.unshift(newComment);
                console.log("comments vào con");
            }
        } else {
            comments.value.unshift(newComment);
            console.log("comments vào cha");
        }
    } catch (error) {
        console.error("Lỗi khi gửi bình luận:", error);
    }
};

// 🛠 Hiện comment bình luận bài post
const fetchComments = async () => {
    try {
        const response = await axios.get(
            `/comments/${props.post.id}?page=${page.value}`
        );
        if (response.data.length === 0) {
            hasMore.value = false;
        } else {
            comments.value.push(...response.data);
            page.value++;
        }
    } catch (error) {
        console.error("Error fetching comments:", error);
    }
};

const loadMoreComments = () => {
    fetchComments();
};

const setReply = (commentId) => {
    // Tìm comment cha hoặc reply con
    let replyTarget = null;

    for (const c of comments.value) {
        if (c.id === commentId) {
            replyTarget = c;
            break;
        }
        if (c.replies) {
            const found = c.replies.find((r) => r.id === commentId);
            if (found) {
                replyTarget = found;
                break;
            }
        }
    }

    console.log("replyTarget", replyTarget.user.name);

    if (replyTarget) {
        const mention = `@${replyTarget.user.name}`;

        // Chỉ thêm nếu chưa có
        if (!content_comment.value.includes(mention)) {
            content_comment.value = mention + " " + content_comment.value;
        }

        parentCommentId.value = replyTarget.id;
    }
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
const emit = defineEmits(['deleted']);
const deletePost = async (postId) => {
    try {
        const response = await axios.post(`/posts/${postId}`);
        if (response.status === 200) {
            emit('deleted', postId);
            console.log("Post deleted successfully");
        }
    } catch (error) {
        console.error("Error deleting post:", error);
    }
};
// Khởi tạo form với dữ liệu từ props.post
const form = ref({
  privacy_setting: props.post?.privacy_setting || 'public',
  content: props.post?.content || '',
  files: []
});
const fileInput = ref(null);

// Watch post để cập nhật lại form và khởi tạo fileinput
watch(() => props.post, (newPost) => {
  if (newPost) {
    // Reset form values
    form.value.privacy_setting = newPost.privacy_setting || 'public';
    form.value.content = newPost.content || '';
    form.value.files = [];

    // Re-init fileinput
    nextTick(() => {
      initFileInput();
    });
  }
}, { immediate: true });

// Hàm khởi tạo fileinput
const initFileInput = () => {
  if (!props.post?.media || !fileInput.value) return;

  const initialPreview = props.post.media.map(img => `/images/client/post/${img.media_url}`);
  const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

const initialPreviewConfig = props.post.media.map(img => ({
  caption: img.media_url,
  key: img.id,
  url: `/posts/${props.post.id}/media/delete`,
  extra: {
    id: img.id,
    _token: csrfToken
  }
}));

  // Huỷ khởi tạo cũ trước khi khởi tạo lại
  try {
    $(fileInput.value).fileinput('destroy');
  } catch (e) {
    console.warn("Không có fileinput để destroy:", e);
  }

  $(fileInput.value).fileinput({
    theme: 'fas',
    language: 'vi',
    showUpload: false,
    showCaption: true,
    browseClass: "btn btn-primary btn-sm",
    allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
    overwriteInitial: false,
    initialPreviewAsData: true,
    initialPreview,
    initialPreviewConfig,
    deleteUrl: `/posts/${props.post.id}/media/delete`
  });
};

// Nếu cần xử lý file mới được chọn
const handleFileUpload = (event) => {
  form.value.files = Array.from(event.target.files);
};

onMounted(() => {
    CheckReaction();
    totalReactions();
    fetchComments();
});
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
