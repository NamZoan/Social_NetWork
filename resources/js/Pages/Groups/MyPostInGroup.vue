<template>
    <GroupDetail :group="group" :user_auth="user_auth" :is-member="isMember" :isAdmin="isAdmin">
        <div class="container mt-4">
            <h4>Bài viết của bạn trong nhóm</h4>
            <div v-if="myPosts.length > 0">
                <ItemPost
                    v-for="post in myPosts"
                    :key="post.id"
                    :post="post"
                    :user="user_auth"
                />
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

<style scoped>
</style>
