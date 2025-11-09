<template>
    <div class="container-fluid newsfeed" id="wrapper">
        <div class="row newsfeed-size">
            <div class="col-md-12 message-right-side">
                <Navbar />
                <slot></slot>
            </div>
        </div>
    </div>

    <CallIncomingListener />
    <!-- <ChatPopup/> -->
</template>

<script setup>
import Navbar from "../Components/Navbar.vue";
import CallIncomingListener from "../Components/Messages/CallIncomingListener.vue";
</script>
