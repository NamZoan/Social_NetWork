<template>
    <App>
        <div class="col-md-12">
            <div id="message-frame">
                <SidepanelSetting />
                <slot></slot>
            </div>
        </div>
    </App>
</template>
<script setup>
import App from "../../Layouts/App.vue";
import SidepanelSetting from '../../Components/SidepanelSetting.vue';

</script>
