<template>
    <App>
        <div class="row message-right-side-content">
            <div class="col-md-12">
                <div id="message-frame">
                    <SidepanelSetting />
                    <slot></slot>
                </div>
            </div>
        </div>
    </App>
</template>
<script setup>
import App from "../../Layouts/App.vue";
import SidepanelSetting from '../../Components/SidepanelSetting.vue';
import '../../../css/settings.css';

</script>
