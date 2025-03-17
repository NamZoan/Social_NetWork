<template>
<App>
    <div class="row newsfeed-right-side-content mt-3">
        <Left/>
        <slot></slot>
    </div>

</App>
</template>

<script setup>
import App from '../../Layouts/App.vue';
import Left from '../../Components/Left.vue';
</script>
