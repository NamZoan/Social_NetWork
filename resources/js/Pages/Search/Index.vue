<template>
    <App>
        <div class="row">
            <div class="col-lg-3">
                <Nav :q="q" :type="type" />
            </div>
            <div class="col-lg-9">
                <h4 class="mt-3">Kết quả cho: "{{ q }}"</h4>
                <div v-if="type === 'people' && users.length">
                    <People :users="users" />
                </div>
                <div v-if="type === 'post' && posts.length">
                    <Post :posts="posts" />
                </div>
                <div v-if="type === 'group' && groups.length">
                    <Group :groups="groups" />
                </div>
                <div v-if="(type === 'people' && !users.length) || (type === 'post' && !posts.length) || (type === 'group' && !groups.length)">
                    <em>Không tìm thấy kết quả phù hợp.</em>
                </div>
            </div>
        </div>
    </App>

</template>
<script setup>
import Nav from './Nav.vue';
import App from "../../Layouts/App.vue";
import Group from './Group.vue';
import Post from './Post.vue';
import People from './People.vue';
import { defineProps } from 'vue';

const props = defineProps({
    q: String,
    type: String,
    users: Array,
    groups: Array,
    posts: Array,
});
</script>