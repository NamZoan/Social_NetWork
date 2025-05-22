<template>
    <div class="card">
        <div class="card-body">
            <div class="iq-todo-page">
                <ul class="todo-task-list p-0 m-0">
                    <li>
                        <h4>Tìm kiếm dựa trên</h4>
                        <ul id="todo-task1" class="sub-task show mt-2 p-0">
                            <li :class="{active: type==='people'}">
                                <a href="#" @click.prevent="changeType('people')">Mọi Người</a>
                            </li>
                            <li :class="{active: type==='post'}">
                                <a href="#" @click.prevent="changeType('post')">Bài Viết</a>
                            </li>
                            <li :class="{active: type==='group'}">
                                <a href="#" @click.prevent="changeType('group')">Nhóm</a>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
<script setup>
import { router, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';

const page = usePage();
const props = defineProps({ q: String, type: String });

const type = ref(props.type || 'people');

const changeType = (newType) => {
    type.value = newType;
    router.get('/search', { q: props.q, type: newType });
};
</script>
<style>
@import '../../../css/socialv.css';
</style>