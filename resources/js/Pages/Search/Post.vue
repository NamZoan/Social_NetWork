<template>
    <ul class="list-group">
        <li v-for="post in posts" :key="post.id" class="list-group-item">
            <ItemPost :post="post" :user="post.user" />
        </li>
    </ul>
</template>

<script setup>
import ItemPost from '../../Components/Item/ItemPost.vue';

const props = defineProps({ posts: Array });
</script>