<template>
    <div class="call-overlay font-sans">
        <div class="call-overlay__shade"></div>
        <div class="call-popup" :class="{ 'call-popup--wide': isTheaterMode }">
            <header class="call-top">
                <div class="call-top__info">
                    <div class="call-avatar">{{ userInitials }}</div>
                    <div>
                        <p class="call-username">{{ user.name }}</p>
                        <p class="call-meta">{{ formattedDuration }} · {{ callStatus }}</p>
                    </div>
                </div>
                <div class="call-top__actions">
                    <span class="call-pill" :class="connectionPillClass">
                        <span class="call-pill__dot"></span>
                        {{ connectionLabel }}
                    </span>
                    <button class="call-icon" @click="toggleLayout" title="Phóng to / thu nhỏ">
                        <i :class="isTheaterMode ? 'bx bx-collapse' : 'bx bx-expand'"></i>
                    </button>
                    <button class="call-icon" @click="toggleSidebar" title="Hiện/ẩn thông tin cuộc gọi">
                        <i class="bx bx-info-circle"></i>
                    </button>
                </div>
            </header>

            <section class="call-layout">
                <div class="call-stage" :class="{ 'call-stage--sharing': isScreenSharing }">
                    <video id="remoteVideo" autoplay playsinline class="call-stage__video"></video>
                    <div v-if="!remoteVideoTrack" class="call-stage__placeholder">
                        <div class="call-stage__avatar">
                            <i class="bx bx-user"></i>
                        </div>
                        <p class="call-stage__name">{{ user.name }}</p>
                        <p class="call-stage__status">{{ connectionLabel }}</p>
                    </div>

                    <div class="call-stage__pip">
                        <div v-if="isCameraOff" class="call-stage__pip-placeholder">
                            <i class="bx bx-user"></i>
                        </div>
                        <video ref="localVideo" class="call-stage__pip-video"
                            :class="{ 'call-stage__pip-video--hidden': isCameraOff }" autoplay playsinline
                            muted></video>
                        <div class="call-stage__pip-meta">
                            <span>Bạn</span>
                            <span>{{ isMuted ? 'Tắt tiếng' : 'Âm thanh bật' }}</span>
                        </div>
                    </div>
                </div>

                <aside v-if="showSidebar" class="call-sidebar">
                    <div>
                        <p class="call-sidebar__title">Trạng thái thiết bị</p>
                        <div class="call-sidebar__list">
                            <div class="device-card">
                                <div class="device-card__info">
                                    <span class="device-card__icon">
                                        <i class="bx bx-microphone"></i>
                                    </span>
                                    <div>
                                        <p class="device-card__label">Micro</p>
                                        <p class="device-card__caption">{{ isMuted ? 'Đang tắt tiếng' : 'Hoạt động' }}
                                        </p>
                                    </div>
                                </div>
                                <span class="device-card__status" :class="isMuted ? 'is-off' : 'is-on'">
                                    {{ isMuted ? 'Muted' : 'Live' }}
                                </span>
                            </div>

                            <div class="device-card">
                                <div class="device-card__info">
                                    <span class="device-card__icon">
                                        <i class="bx bx-video"></i>
                                    </span>
                                    <div>
                                        <p class="device-card__label">Camera</p>
                                        <p class="device-card__caption">{{ isCameraOff ? 'Đang tắt' : 'Đang phát' }}</p>
                                    </div>
                                </div>
                                <span class="device-card__status" :class="isCameraOff ? 'is-off' : 'is-on'">
                                    {{ isCameraOff ? 'Off' : 'On' }}
                                </span>
                            </div>

                            <div class="device-card">
                                <div class="device-card__info">
                                    <span class="device-card__icon">
                                        <i class="bx bx-desktop"></i>
                                    </span>
                                    <div>
                                        <p class="device-card__label">Chia sẻ màn hình</p>
                                        <p class="device-card__caption">{{ isScreenSharing ? 'Đang chia sẻ' : 'Sẵn sàng'
                                            }}</p>
                                    </div>
                                </div>
                                <span class="device-card__status" :class="isScreenSharing ? 'is-share' : 'is-idle'">
                                    {{ isScreenSharing ? 'Live' : 'Idle' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="call-sidebar__tips">
                        <p class="call-sidebar__tips-title">Gợi ý</p>
                        <ul>
                            <li>Sử dụng tai nghe để giảm tiếng vọng.</li>
                            <li>Giữ ánh sáng hướng vào khuôn mặt.</li>
                            <li>Khi muốn trình bày, hãy bật chia sẻ màn hình.</li>
                        </ul>
                    </div>
                </aside>
            </section>

            <footer class="call-controls">
                <div class="call-controls__group">
                    <button @click="toggleMute" class="control-btn" :class="{ 'control-btn--danger': isMuted }"
                        title="Bật/Tắt micro">
                        <i class="bx" :class="isMuted ? 'bxs-microphone-off' : 'bxs-microphone'"></i>
                    </button>
                    <button @click="toggleCamera" class="control-btn" :class="{ 'control-btn--danger': isCameraOff }"
                        title="Bật/Tắt camera">
                        <i class="bx" :class="isCameraOff ? 'bxs-video-off' : 'bxs-video'"></i>
                    </button>
                    <button @click="toggleScreenShare" class="control-btn"
                        :class="{ 'control-btn--share': isScreenSharing }" title="Chia sẻ màn hình">
                        <i class="bx bx-desktop"></i>
                    </button>
                </div>
                <button @click="endCall" class="call-end-btn" title="Kết thúc cuộc gọi">
                    <i class="bx bxs-phone-call"></i>
                </button>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, watch, onBeforeUnmount, nextTick } from "vue";
import axios from "axios";
import AgoraRTC from "agora-rtc-sdk-ng";

const props = defineProps({
    callId: {
        type: [Number, String],
        required: true,
    },
    meId: {
        type: Number,
        required: true,
    },
    peerId: {
        type: Number,
        required: false,
        default: null,
    },
    user: {
        type: Object,
        required: false,
        default: () => ({ name: 'Cuộc gọi', avatar: null }),
    },
    isCaller: {
        type: Boolean,
        required: false,
        default: false,
    },
});

const emit = defineEmits(["end-call"]);

const localVideo = ref(null);
const remoteVideo = ref(null);

const isMuted = ref(false);
const isCameraOff = ref(false);
const isScreenSharing = ref(false);
const isTheaterMode = ref(false);
const showSidebar = ref(true);
const callStatus = ref("Đang khởi tạo...");
const mediaError = ref(null);
const isCallActive = ref(false);

const callDuration = ref(0);
const timerHandle = ref(null);

// Agora SDK variables
let agoraClient = null;
let localAudioTrack = null;
let localVideoTrack = null;
let localScreenTrack = null;
let remoteAudioTrack = null;
let remoteVideoTrack = null;
let remoteUid = null;

let callChannel = null;
let isCallerFlag = ref(false);
const resolvedPeerId = ref(null);
const isCleaningUp = ref(false);
const channelName = ref(null);
const agoraConfig = ref(null);

// Xác định caller/callee và peerId dựa trên prop hoặc từ call data
const determineCallRole = async () => {
    try {
        const response = await axios.get(`/calls/${props.callId}`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const call = response.data.call || response.data;
        isCallerFlag.value = call.creator_id === props.meId || props.isCaller;

        // Lấy peerId từ props hoặc từ call data
        if (props.peerId) {
            resolvedPeerId.value = props.peerId;
        } else if (call.participants && call.participants.length > 0) {
            const otherParticipant = call.participants.find(p => p.user_id !== props.meId);
            if (otherParticipant) {
                resolvedPeerId.value = otherParticipant.user_id;
            }
        }
    } catch (error) {
        console.error("Error determining call role:", error);
        isCallerFlag.value = props.isCaller;
        resolvedPeerId.value = props.peerId;
    }
};

async function initializeAgoraClient() {
    try {
        console.log("[VideoCall] Initializing Agora client for call:", props.callId);

        // Lấy Agora config từ backend
        const response = await axios.get(`/calls/${props.callId}/agora-token`);
        console.log("[VideoCall] Agora config response:", response.data);
        agoraConfig.value = response.data;

        if (!agoraConfig.value.app_id) {
            console.error("[VideoCall] Agora App ID không được cấu hình");
            throw new Error("Agora App ID không được cấu hình");
        }

        if (!agoraConfig.value.token) {
            console.warn("[VideoCall] Agora token không có, có thể gây lỗi kết nối");
        }

        // Tạo Agora client
        console.log("[VideoCall] Creating Agora client...");
        agoraClient = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });
        console.log("[VideoCall] Agora client created successfully");

        // Xử lý khi user join channel
        agoraClient.on("user-published", async (user, mediaType) => {
            await agoraClient.subscribe(user, mediaType);

            if (mediaType === "video") {
                remoteVideoTrack = user.videoTrack;
                remoteUid = user.uid;
                await nextTick();
                const rv = document.querySelector('#remoteVideo');
                if (rv && remoteVideoTrack) {
                    remoteVideoTrack.play(rv);
                }
                callStatus.value = "Đang trò chuyện";
                if (!isCallActive.value) {
                    isCallActive.value = true;
                    startCallTimer();
                }
            }

            if (mediaType === "audio") {
                remoteAudioTrack = user.audioTrack;
                if (remoteAudioTrack) {
                    remoteAudioTrack.play();
                }
            }
        });

        // Xử lý khi user leave channel
        agoraClient.on("user-unpublished", (user, mediaType) => {
            if (mediaType === "video") {
                remoteVideoTrack = null;
                remoteUid = null;
            }
            if (mediaType === "audio") {
                remoteAudioTrack = null;
            }
        });

        // Xử lý khi user left
        agoraClient.on("user-left", () => {
            remoteVideoTrack = null;
            remoteAudioTrack = null;
            remoteUid = null;
        });

        // Xử lý connection state
        agoraClient.on("connection-state-change", (curState, revState) => {
            console.log("Agora connection state:", curState, revState);
            if (curState === "CONNECTED") {
                callStatus.value = "Đang trò chuyện";
                if (!isCallActive.value) {
                    isCallActive.value = true;
                    startCallTimer();
                }
            } else if (curState === "DISCONNECTED") {
                callStatus.value = "Kết nối bị gián đoạn";
            }
        });

        return true;
    } catch (error) {
        console.error("Error initializing Agora client:", error);
        callStatus.value = "Không thể khởi tạo kết nối Agora";
        return false;
    }
}

async function createLocalTracks() {
    try {
        console.log("[VideoCall] Creating local tracks...");
        const devices = await navigator.mediaDevices.enumerateDevices();
        const hasVideo = devices.some((d) => d.kind === "videoinput");
        const hasAudio = devices.some((d) => d.kind === "audioinput");

        console.log("[VideoCall] Available devices - Video:", hasVideo, "Audio:", hasAudio);

        if (!hasVideo && !hasAudio) {
            callStatus.value = "Không tìm thấy camera hoặc micro";
            console.error("[VideoCall] Không tìm thấy camera hoặc micro");
            return false;
        }

        // Tạo audio track
        if (hasAudio) {
            console.log("[VideoCall] Creating microphone track...");
            localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            console.log("[VideoCall] Microphone track created");
        }

        // Tạo video track
        if (hasVideo) {
            console.log("[VideoCall] Creating camera track...");
            localVideoTrack = await AgoraRTC.createCameraVideoTrack({
                encoderConfig: "720p_1",
            });
            console.log("[VideoCall] Camera track created");
        }

        // Hiển thị local video
        await nextTick();
        if (localVideo.value && localVideoTrack) {
            console.log("[VideoCall] Playing local video...");
            localVideoTrack.play(localVideo.value);
        } else {
            console.warn("[VideoCall] Local video element not found or track not available");
        }

        callStatus.value = isCallerFlag.value ? "Đang kết nối..." : "Đang chờ đối phương...";
        console.log("[VideoCall] Local tracks created successfully");
        return true;
    } catch (err) {
        console.error("Error creating local tracks:", err);
        isCameraOff.value = !err.name?.includes("video");
        isMuted.value = !err.name?.includes("audio");
        mediaError.value = err.name;

        if (err.name === "NotFoundError") {
            callStatus.value = "Không tìm thấy camera/micro.";
        } else if (err.name === "NotAllowedError") {
            callStatus.value = "Bạn đã từ chối quyền truy cập.";
        } else if (err.name === "NotReadableError") {
            callStatus.value = "Thiết bị đang được ứng dụng khác sử dụng.";
        } else {
            callStatus.value = "Không thể khởi tạo thiết bị.";
        }
        return false;
    }
}

async function joinChannel() {
    if (!agoraClient || !agoraConfig.value) {
        console.error("[VideoCall] Cannot join channel: client or config missing");
        return;
    }

    try {
        const token = agoraConfig.value.token || null; // Có thể null nếu dùng temporary token
        const uid = agoraConfig.value.uid || props.meId;
        const channel = agoraConfig.value.channel;

        console.log("[VideoCall] Joining channel:", {
            appId: agoraConfig.value.app_id,
            channel: channel,
            uid: uid,
            hasToken: !!token
        });

        await agoraClient.join(
            agoraConfig.value.app_id,
            channel,
            token,
            uid
        );

        console.log("[VideoCall] Successfully joined channel");

        // Publish local tracks
        const tracksToPublish = [];
        if (localAudioTrack) tracksToPublish.push(localAudioTrack);
        if (localVideoTrack) tracksToPublish.push(localVideoTrack);

        if (tracksToPublish.length > 0) {
            console.log("[VideoCall] Publishing tracks:", tracksToPublish.length);
            await agoraClient.publish(tracksToPublish);
            console.log("[VideoCall] Tracks published successfully");
        } else {
            console.warn("[VideoCall] No tracks to publish");
        }

        callStatus.value = "Đang kết nối...";
    } catch (error) {
        console.error("[VideoCall] Error joining channel:", error);
        callStatus.value = "Không thể tham gia kênh: " + (error.message || error);
    }
}


function subscribeToCallChannel() {
    if (!window.Echo) {
        console.error("[VideoCall] Echo is not available");
        return;
    }

    console.log("[VideoCall] Subscribing to call channel:", `call.${props.callId}`);
    channelName.value = `call.${props.callId}`;
    callChannel = window.Echo.join(channelName.value);

    // Lắng nghe khi call được accept (không cần WebRTC signaling nữa vì Agora tự xử lý)
    callChannel.listen('.call.accepted', (data) => {
        if (isCleaningUp.value) return;
        console.log("[VideoCall] Call accepted:", data);
        callStatus.value = "Đang kết nối...";
    });

    // Lắng nghe khi call kết thúc
    callChannel.listen('.call.ended', (data) => {
        if (isCleaningUp.value) return;
        console.log("[VideoCall] Call ended:", data);
        if (data.byUserId !== props.meId) {
            callStatus.value = "Cuộc gọi đã kết thúc";
            setTimeout(() => {
                if (!isCleaningUp.value) {
                    endCall();
                }
            }, 1000);
        }
    });

    console.log("[VideoCall] Successfully subscribed to call channel");
}

const toggleScreenShare = async () => {
    if (!agoraClient) return;

    try {
        if (!isScreenSharing.value) {
            // Bắt đầu chia sẻ màn hình
            localScreenTrack = await AgoraRTC.createScreenVideoTrack({
                encoderConfig: "1080p_1",
            });

            // Unpublish camera track
            if (localVideoTrack) {
                await agoraClient.unpublish([localVideoTrack]);
            }

            // Publish screen track
            await agoraClient.publish([localScreenTrack]);

            // Xử lý khi người dùng dừng chia sẻ màn hình
            localScreenTrack.on("track-ended", () => {
                if (isScreenSharing.value) {
                    toggleScreenShare();
                }
            });

            isScreenSharing.value = true;
            callStatus.value = "Đang chia sẻ màn hình";
        } else {
            // Dừng chia sẻ màn hình
            if (localScreenTrack) {
                await agoraClient.unpublish([localScreenTrack]);
                localScreenTrack.close();
                localScreenTrack = null;
            }

            // Quay lại camera
            if (localVideoTrack) {
                await agoraClient.publish([localVideoTrack]);
            }

            isScreenSharing.value = false;
            callStatus.value = isCallActive.value ? "Đang trò chuyện" : "Đang kết nối...";
        }
    } catch (error) {
        console.error("Error toggling screen share:", error);
        if (error.name === "NotAllowedError") {
            callStatus.value = "Bạn đã từ chối chia sẻ màn hình";
        }
    }
};

const toggleMute = () => {
    if (!localAudioTrack) return;
    isMuted.value = !isMuted.value;
    localAudioTrack.setEnabled(!isMuted.value);
};

const toggleCamera = () => {
    if (!localVideoTrack) return;
    isCameraOff.value = !isCameraOff.value;
    localVideoTrack.setEnabled(!isCameraOff.value);
};

const toggleLayout = () => {
    isTheaterMode.value = !isTheaterMode.value;
};

const toggleSidebar = () => {
    showSidebar.value = !showSidebar.value;
};

const startCallTimer = () => {
    if (timerHandle.value || !isCallActive.value) return;
    timerHandle.value = setInterval(() => {
        callDuration.value += 1;
    }, 1000);
};

const stopCallTimer = () => {
    if (timerHandle.value) {
        clearInterval(timerHandle.value);
        timerHandle.value = null;
    }
};

const cleanup = () => {
    // Tránh cleanup nhiều lần
    if (isCleaningUp.value) {
        return;
    }
    isCleaningUp.value = true;

    stopCallTimer();

    // Rời khỏi Agora channel
    if (agoraClient) {
        agoraClient.leave().catch(console.error);
        agoraClient = null;
    }

    // Đóng local tracks
    if (localAudioTrack) {
        localAudioTrack.close();
        localAudioTrack = null;
    }
    if (localVideoTrack) {
        localVideoTrack.close();
        localVideoTrack = null;
    }
    if (localScreenTrack) {
        localScreenTrack.close();
        localScreenTrack = null;
    }

    // Đóng remote tracks
    if (remoteAudioTrack) {
        remoteAudioTrack.close();
        remoteAudioTrack = null;
    }
    if (remoteVideoTrack) {
        remoteVideoTrack.close();
        remoteVideoTrack = null;
    }

    // Rời khỏi Echo channel
    if (window.Echo && channelName.value) {
        try {
            window.Echo.leave(channelName.value);
        } catch (error) {
            console.error("Error leaving channel:", error);
        }
    }

    callChannel = null;
    channelName.value = null;
};

const endCall = async () => {
    // Tránh gọi endCall nhiều lần
    if (isCleaningUp.value) {
        return;
    }

    try {
        await axios.post('/calls/end', { id: props.callId });
    } catch (error) {
        console.error("Error ending call:", error);
    } finally {
        cleanup();
        emit("end-call");
    }
};

const userInitials = computed(() => {
    if (!props.user?.name) return "U";
    const parts = props.user.name.trim().split(" ").filter(Boolean);
    const initials = parts.slice(0, 2).map(chunk => chunk[0]).join("");
    return initials.toUpperCase();
});

const formattedDuration = computed(() => {
    const minutes = String(Math.floor(callDuration.value / 60)).padStart(2, "0");
    const seconds = String(callDuration.value % 60).padStart(2, "0");
    return `${minutes}:${seconds}`;
});

const connectionLabel = computed(() => {
    if (!localVideoTrack && !localAudioTrack) return "Đang chuẩn bị thiết bị";
    if (!remoteVideoTrack && !remoteAudioTrack) return "Chờ phản hồi";
    if (agoraClient && agoraClient.connectionState === "CONNECTED") return "Kết nối ổn định";
    return "Đang kết nối...";
});

const connectionPillClass = computed(() => {
    if (!localVideoTrack && !localAudioTrack) {
        return "call-pill--idle";
    }
    if (agoraClient && agoraClient.connectionState === "CONNECTED") {
        return "call-pill--good";
    }
    return "call-pill--waiting";
});

watch(isCallActive, (active) => {
    if (active) {
        startCallTimer();
    } else {
        stopCallTimer();
    }
});

onMounted(async () => {
    console.log("[VideoCall] Component mounted, callId:", props.callId, "meId:", props.meId);

    try {
        // Xác định vai trò caller/callee
        console.log("[VideoCall] Determining call role...");
        await determineCallRole();
        console.log("[VideoCall] Call role determined, isCaller:", isCallerFlag.value);

        // Khởi tạo Agora client
        console.log("[VideoCall] Initializing Agora client...");
        const clientReady = await initializeAgoraClient();
        if (!clientReady) {
            console.error("[VideoCall] Failed to initialize Agora client");
            emit("end-call");
            return;
        }

        // Tạo local tracks
        console.log("[VideoCall] Creating local tracks...");
        const tracksReady = await createLocalTracks();
        if (!tracksReady) {
            console.error("[VideoCall] Failed to create local tracks");
            emit("end-call");
            return;
        }

        // Join channel
        console.log("[VideoCall] Joining channel...");
        await joinChannel();

        // Subscribe to call channel để lắng nghe events
        console.log("[VideoCall] Subscribing to call channel...");
        subscribeToCallChannel();

        console.log("[VideoCall] Initialization completed");
    } catch (error) {
        console.error("[VideoCall] Error during initialization:", error);
        callStatus.value = "Lỗi khởi tạo: " + (error.message || error);
        emit("end-call");
    }
});

onBeforeUnmount(() => {
    cleanup();
});

onUnmounted(() => {
    cleanup();
});

</script>

<style scoped>
.font-sans {
    font-family: 'Inter', sans-serif;
}

.call-overlay {
    position: fixed;
    inset: 0;
    z-index: 50;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    color: #f8fafc;
}

.call-overlay__shade {
    position: absolute;
    inset: 0;
    background: rgba(5, 8, 18, 0.85);
    backdrop-filter: blur(10px);
}

.call-popup {
    position: relative;
    width: min(900px, 95vw);
    height: min(620px, 90vh);
    background: linear-gradient(140deg, #0f172a, #111c3d);
    border-radius: 24px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.55);
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    z-index: 1;
}

.call-popup--wide {
    width: min(1080px, 96vw);
}

.call-top {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
    align-items: center;
}

.call-top__info {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.call-avatar {
    width: 56px;
    height: 56px;
    border-radius: 18px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 1.1rem;
    letter-spacing: 0.08em;
}

.call-username {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.call-meta {
    margin: 0.2rem 0 0;
    font-size: 0.9rem;
    color: rgba(248, 250, 252, 0.7);
}

.call-top__actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.call-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.35rem 0.85rem;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    font-size: 0.85rem;
    font-weight: 600;
}

.call-pill__dot {
    width: 8px;
    height: 8px;
    border-radius: 999px;
    background: currentColor;
    display: inline-block;
    animation: pulse 1.4s ease infinite;
}

.call-pill--idle {
    background: rgba(255, 255, 255, 0.08);
    color: rgba(248, 250, 252, 0.9);
    border-color: rgba(255, 255, 255, 0.12);
}

.call-pill--waiting {
    background: rgba(245, 184, 84, 0.15);
    color: #facc15;
    border-color: rgba(250, 204, 21, 0.35);
}

.call-pill--good {
    background: rgba(45, 212, 191, 0.15);
    color: #5eead4;
    border-color: rgba(45, 212, 191, 0.35);
}

.call-icon {
    width: 42px;
    height: 42px;
    border-radius: 999px;
    border: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(255, 255, 255, 0.08);
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    cursor: pointer;
    transition: background-color 0.2s ease;
}

.call-icon:hover {
    background: rgba(255, 255, 255, 0.18);
}

.call-layout {
    flex: 1;
    display: flex;
    gap: 1.5rem;
    overflow: hidden;
}

.call-stage {
    position: relative;
    flex: 1;
    border-radius: 22px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: #050505;
    overflow: hidden;
}

.call-stage--sharing {
    box-shadow: 0 0 0 2px rgba(56, 189, 248, 0.45);
}

.call-stage__video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: opacity 0.3s ease, transform 0.3s ease;
}

.call-stage__video--hidden {
    opacity: 0;
    transform: scale(0.98);
}

.call-stage__placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    background: radial-gradient(circle at top, rgba(255, 255, 255, 0.06), transparent);
    text-align: center;
}

.call-stage__avatar {
    width: 110px;
    height: 110px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: rgba(255, 255, 255, 0.5);
}

.call-stage__avatar i {
    font-size: 3rem;
}

.call-stage__name {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.call-stage__status {
    margin: 0;
    font-size: 0.95rem;
    color: rgba(248, 250, 252, 0.7);
}

.call-stage__pip {
    position: absolute;
    bottom: 16px;
    right: 16px;
    width: 180px;
    height: 120px;
    border-radius: 18px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background: rgba(0, 0, 0, 0.4);
    overflow: hidden;
    backdrop-filter: blur(8px);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.45);
}

.call-stage__pip-placeholder,
.call-stage__pip-video {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.call-stage__pip-placeholder {
    display: flex;
    align-items: center;
    justify-content: center;
    color: rgba(255, 255, 255, 0.5);
    font-size: 1.8rem;
}

.call-stage__pip-video--hidden {
    opacity: 0;
}

.call-stage__pip-meta {
    position: absolute;
    bottom: 6px;
    left: 8px;
    right: 8px;
    display: flex;
    justify-content: space-between;
    font-size: 0.65rem;
    color: rgba(255, 255, 255, 0.85);
}

.call-sidebar {
    width: 260px;
    flex-shrink: 0;
    border-radius: 20px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.05);
    padding: 1.1rem;
    display: flex;
    flex-direction: column;
    gap: 1rem;
    backdrop-filter: blur(10px);
}

.call-sidebar__title {
    margin: 0 0 0.6rem;
    text-transform: uppercase;
    letter-spacing: 0.2em;
    font-size: 0.68rem;
    color: rgba(248, 250, 252, 0.65);
}

.call-sidebar__list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.device-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0.55rem 0.75rem;
    border-radius: 1.25rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: rgba(255, 255, 255, 0.04);
    gap: 0.5rem;
}

.device-card__info {
    display: flex;
    align-items: center;
    gap: 0.7rem;
}

.device-card__icon {
    width: 38px;
    height: 38px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.08);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
}

.device-card__label {
    margin: 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.device-card__caption {
    margin: 0;
    font-size: 0.75rem;
    color: rgba(248, 250, 252, 0.6);
}

.device-card__status {
    font-size: 0.75rem;
    font-weight: 600;
}

.device-card__status.is-on {
    color: #5eead4;
}

.device-card__status.is-off {
    color: #f87171;
}

.device-card__status.is-share {
    color: #38bdf8;
}

.device-card__status.is-idle {
    color: rgba(248, 250, 252, 0.6);
}

.call-sidebar__tips {
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.08);
    background: rgba(255, 255, 255, 0.04);
    padding: 0.9rem;
}

.call-sidebar__tips-title {
    margin: 0 0 0.45rem;
    font-weight: 600;
    font-size: 0.95rem;
}

.call-sidebar__tips ul {
    margin: 0;
    padding-left: 1rem;
    display: flex;
    flex-direction: column;
    gap: 0.35rem;
    font-size: 0.85rem;
    color: rgba(248, 250, 252, 0.7);
}

.call-controls {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    flex-wrap: wrap;
}

.call-controls__group {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.65rem 1rem;
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.35);
    border: 1px solid rgba(255, 255, 255, 0.08);
}

.control-btn {
    width: 3.5rem;
    height: 3.5rem;
    border-radius: 9999px;
    border: 1px solid rgba(255, 255, 255, 0.15);
    background-color: rgba(255, 255, 255, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background-color 0.2s ease, transform 0.2s ease;
}

.control-btn:hover {
    background-color: rgba(255, 255, 255, 0.2);
}

.control-btn--danger {
    background: rgba(239, 68, 68, 0.9);
    border-color: rgba(239, 68, 68, 0.5);
}

.control-btn--share {
    background: rgba(56, 189, 248, 0.25);
    border-color: rgba(56, 189, 248, 0.4);
}

.call-end-btn {
    width: 4.2rem;
    height: 4.2rem;
    border-radius: 999px;
    border: 1px solid rgba(239, 68, 68, 0.5);
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    box-shadow: 0 15px 35px rgba(220, 38, 38, 0.45);
}

.call-end-btn:hover {
    transform: translateY(-2px);
}

@media (max-width: 1024px) {
    .call-layout {
        flex-direction: column;
    }

    .call-sidebar {
        width: 100%;
    }
}

@keyframes pulse {
    0% {
        opacity: 1;
        transform: scale(1);
    }

    50% {
        opacity: 0.5;
        transform: scale(0.7);
    }

    100% {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
