<template>
    <div class="container-fluid" id="wrapper">
        <div class="row newsfeed-size">
            <div class="col-md-12 newsfeed-right-side">
                <Navbar/>
                <div class="row newsfeed-right-side-content">
                    <slot></slot>
                </div>
            </div>
        </div>
    </div>

    <!-- <ChatPopup/> -->
</template>

<script setup>
import Navbar from '../Components/Navbar.vue';

import ChatPopup from '../Components/ChatPopup.vue';
</script>
