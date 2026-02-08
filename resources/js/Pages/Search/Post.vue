<template>
    <ul class="list-group">
        <li v-for="post in posts" :key="post.id" class="list-group-item">
            <div v-if="post.page_id" class="mb-3">
                <Link :href="`/pages/${post.page_id}`" class="text-decoration-none">
                    <img 
                        v-if="post.page?.cover_photo_url" 
                        :src="`/images/client/page/thumbnail/${post.page.cover_photo_url}`" 
                        class="img-fluid rounded mb-2" 
                        style="max-height: 300px; object-fit: cover; width: 100%;"
                    />
                    <div class="fw-bold text-dark">{{ post.page?.name }}</div>
                </Link>
            </div>
            <ItemPost :post="post" :user="post.user" />
        </li>
    </ul>
</template>

<script setup>
import ItemPost from '../../Components/Item/ItemPost.vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({ posts: Array });
</script>