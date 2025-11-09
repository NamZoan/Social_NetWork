<template>
    <div>
        <NotificationVideoCall
            :visible="Boolean(incomingCall)"
            :caller="incomingCall?.caller || {}"
            :subtitle="incomingCall?.subtitle || 'Đang gọi video...'"
            @accept="acceptCall"
            @reject="rejectCall"
        />

        <VideoCall
            v-if="activeCall"
            :call-id="activeCall.id"
            :me-id="currentUser?.id"
            :peer-id="activeCall.peerId"
            :user="activeCall.peer"
            :is-caller="activeCall.isCaller"
            @end-call="endActiveCall"
        />
    </div>
</template>

<script setup>
import { ref, computed, watch, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";
import NotificationVideoCall from "./NotificationVideoCall.vue";
import VideoCall from "./VideoCall.vue";

const page = usePage();
const currentUser = computed(() => page.props?.auth?.user ?? null);

const incomingCall = ref(null);
const activeCall = ref(null);

const channelName = computed(() =>
    currentUser.value?.id ? `user.${currentUser.value.id}` : null
);

const subscribeChannel = (name) => {
    if (!name || !window.Echo) return;

    window.Echo.private(name).listen(".call.invited", (payload) => {
        incomingCall.value = {
            id: payload.id,
            caller: payload.caller || {},
            subtitle: payload.subtitle || "Đang gọi video...",
        };
    });
};

const leaveChannel = (name) => {
    if (!name || !window.Echo) return;
    window.Echo.leave(name);
};

watch(
    channelName,
    (newName, oldName) => {
        if (oldName) {
            leaveChannel(oldName);
        }
        if (newName) {
            subscribeChannel(newName);
        }
    },
    { immediate: true }
);

const acceptCall = async () => {
    if (!incomingCall.value || !currentUser.value) return;

    const call = incomingCall.value;

    try {
        await axios.post("/calls/accept", { id: call.id });

        // Người chấp nhận cuộc gọi là callee (không phải caller)
        activeCall.value = {
            id: call.id,
            peerId: call.caller?.id ?? null,
            peer: call.caller,
            isCaller: false, // Người chấp nhận không phải caller
        };
    } catch (e) {
        console.error("Không thể chấp nhận cuộc gọi:", e);
        // Hoàn tác để user có thể thử lại
        incomingCall.value = call;
        return;
    }

    incomingCall.value = null;
};

const rejectCall = async () => {
    if (!incomingCall.value) return;

    const call = incomingCall.value;

    try {
        await axios.post("/calls/reject", { id: call.id });
    } catch (e) {
        console.error("Không thể từ chối cuộc gọi:", e);
        incomingCall.value = call;
        return;
    }

    incomingCall.value = null;
};

const endActiveCall = async () => {
    if (!activeCall.value) return;

    try {
        await axios.post("/calls/end", { id: activeCall.value.id });
    } catch (e) {
        console.error("Không thể kết thúc cuộc gọi:", e);
    } finally {
        activeCall.value = null;
    }
};

onBeforeUnmount(() => {
    if (channelName.value) {
        leaveChannel(channelName.value);
    }
});
</script>
