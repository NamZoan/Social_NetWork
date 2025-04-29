<template>
    <div class="container-fluid newsfeed" id="wrapper">
        <div class="row newsfeed-size">
            <div class="col-md-12 message-right-side">
                <Navbar />
                <slot></slot>
            </div>
        </div>
    </div>

    <!-- <ChatPopup/> -->
</template>

<script setup>
import Navbar from "../Components/Navbar.vue";

import ChatPopup from "../Components/ChatPopup.vue";
</script>
