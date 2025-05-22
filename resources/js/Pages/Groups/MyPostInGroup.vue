<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div class="container mt-4">
            <h4>Bài viết của bạn trong nhóm</h4>
            <div v-if="myPosts.length > 0">
                <div v-for="post in myPosts" :key="post.id" class="mb-3">
                    <div class="small mt-1">
                        <span v-if="post.privacy_setting === 'public'" class="text-success">Đã được duyệt</span>
                        <span v-else-if="post.privacy_setting === 'pending'" class="text-warning">Chờ duyệt</span>
                        <span v-else-if="post.privacy_setting === 'rejected'" class="text-danger">Đã từ chối</span>
                    </div>
                    <ItemPost :post="post" :user="user_auth" />

                </div>
            </div>
            <div v-else class="text-center text-muted py-5">
                Bạn chưa có bài viết nào trong nhóm này.
            </div>
        </div>
    </GroupDetail>
</template>

<script setup>
import ItemPost from '../../Components/Item/ItemPost.vue';
import GroupDetail from './GroupDetail.vue';

const props = defineProps({
    group: { type: Object, required: true },
    myPosts: { type: Array, required: true },
    user_auth: { type: Object, required: true },
    isMember: { type: Boolean, required: true },
    isAdmin: { type: Boolean, required: true }
});
</script>

<style scoped></style>
