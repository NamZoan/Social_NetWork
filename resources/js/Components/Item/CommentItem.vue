<template>
    <div class="comment-card">
        <div class="comment-avatar">
            <img :src="buildAvatarUrl(comment.user)" alt="avatar" />
        </div>
        <div class="comment-main">
            <div class="comment-top">
                <div>
                    <div class="comment-author">{{ comment.user?.name }}</div>
                    <div class="comment-meta">{{ formatTime(comment.created_at) }}</div>
                </div>
                <div class="comment-actions" v-if="comment.user_id === currentUserId">
                    <button class="link-btn" @click="$emit('start-edit', comment)">Chỉnh sửa</button>
                    <span class="divider-dot">•</span>
                    <button class="link-btn danger" @click="$emit('delete-comment', comment.id)">Xóa</button>
                </div>
            </div>

            <div class="comment-content" v-html="highlightMentions(comment.content)"></div>

            <div class="comment-footer">
                <button class="link-btn" @click="$emit('set-reply', comment.id)">Trả lời</button>
                <template v-if="hasChildren">
                    <span class="dot">•</span>
                    <button class="link-btn" v-if="!showReplies[comment.id]" @click="$emit('toggle-replies', comment.id)">
                        Xem phản hồi ({{ replyCount }})
                    </button>
                    <button class="link-btn" v-else @click="$emit('toggle-replies', comment.id)">
                        Ẩn phản hồi
                    </button>
                </template>
            </div>

            <div v-if="replyTo === comment.id" class="inline-reply">
                <input
                    class="form-control"
                    :value="replyContent"
                    placeholder="Viết phản hồi..."
                    @input="updateReplyContent($event.target.value)"
                    @keydown.enter.prevent="$emit('submit-reply', comment.id)"
                />
                <button class="btn btn-primary btn-sm" @click="$emit('submit-reply', comment.id)">Gửi</button>
            </div>
        </div>
    </div>

    <div v-if="showReplies[comment.id]" class="comment-children">
        <div v-if="isLoadingReplies[comment.id]" class="comment-loading">
            <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
        </div>

        <CommentItem
            v-for="child in comment.replies || []"
            :key="child.id"
            :comment="child"
            :current-user-id="currentUserId"
            :reply-to="replyTo"
            :reply-content="replyContent"
            :show-replies="showReplies"
            :is-loading-replies="isLoadingReplies"
            :highlight-mentions="highlightMentions"
            :format-time="formatTime"
            :build-avatar-url="buildAvatarUrl"
            :update-reply-content="updateReplyContent"
            @set-reply="$emit('set-reply', $event)"
            @submit-reply="$emit('submit-reply', $event)"
            @toggle-replies="$emit('toggle-replies', $event)"
            @delete-comment="$emit('delete-comment', $event)"
            @start-edit="$emit('start-edit', $event)"
        />

        <div v-if="!comment.replies || comment.replies.length === 0" class="no-replies">
            Chưa có phản hồi
        </div>
    </div>
</template>

<script setup>
import { computed, defineOptions } from "vue";

defineOptions({
    name: "CommentItem",
});

defineEmits([
    "set-reply",
    "submit-reply",
    "start-edit",
    "delete-comment",
    "toggle-replies",
]);

const props = defineProps({
    comment: { type: Object, required: true },
    currentUserId: { type: [Number, String], required: true },
    replyTo: { type: [Number, String, null], default: null },
    replyContent: { type: String, default: "" },
    showReplies: { type: Object, required: true },
    isLoadingReplies: { type: Object, required: true },
    highlightMentions: { type: Function, required: true },
    formatTime: { type: Function, required: true },
    buildAvatarUrl: { type: Function, required: true },
    updateReplyContent: { type: Function, required: true },
});

const replyCount = computed(() => {
    if (props.comment.replies && props.comment.replies.length) {
        return props.comment.replies.length;
    }
    return props.comment.replies_count || 0;
});

const hasChildren = computed(() => replyCount.value > 0);
</script>

<style scoped>
.comment-card {
    display: flex;
    gap: 12px;
    padding: 12px;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
    box-shadow: 0 6px 18px rgba(17, 24, 39, 0.06);
    margin-bottom: 10px;
}

.comment-avatar img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eef2ff;
}

.comment-main {
    flex: 1;
}

.comment-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.comment-author {
    font-weight: 700;
    color: #111827;
}

.comment-meta {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}

.comment-actions {
    display: flex;
    gap: 8px;
    align-items: center;
}

.link-btn {
    border: none;
    background: transparent;
    color: #2563eb;
    font-weight: 600;
    padding: 0;
    cursor: pointer;
}

.link-btn:hover {
    color: #1d4ed8;
    text-decoration: underline;
}

.link-btn.danger {
    color: #dc2626;
}

.divider-dot,
.dot {
    color: #d1d5db;
}

.comment-content {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 10px 12px;
    color: #111827;
    font-size: 14px;
}

.comment-footer {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 8px;
    color: #6b7280;
}

.inline-reply {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.inline-reply input {
    border-radius: 10px;
}

.comment-children {
    margin-left: 52px;
    border-left: 2px solid #e5e7eb;
    padding-left: 14px;
    padding-top: 6px;
}

.comment-loading {
    padding: 8px 0;
}

.no-replies {
    color: #9ca3af;
    font-size: 13px;
    margin: 6px 0 12px;
}

@media (max-width: 576px) {
    .comment-card {
        flex-direction: column;
    }

    .comment-children {
        margin-left: 24px;
    }
}
</style>
