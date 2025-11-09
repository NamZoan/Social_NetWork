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
                    <div class="call-stage__pip">
                        <video id="localVideo" autoplay playsinline muted class="call-stage__pip-video"></video>
                    </div>
                    <div v-if="!remoteStream" class="call-stage__placeholder">
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
const localStream = ref(null);
const remoteStream = ref(null);
const screenStream = ref(null);

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

let pc = null;
let echoSub = null;
let callChannel = null;
let isCallerFlag = ref(false);
const resolvedPeerId = ref(null);
const isCleaningUp = ref(false);
const channelName = ref(null);

const iceServers = [
    { urls: 'stun:stun.l.google.com:19302' },
    {
        urls: 'turn:relay1.expressturn.com:3480',
        username: '000000002077978350',
        credential: 'Qjj9PbBlQ/+G1jqNwPDRDP54/qA='
    }
];

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

async function startLocalStream() {
    try {
        const devices = await navigator.mediaDevices.enumerateDevices();
        const hasVideo = devices.some((d) => d.kind === "videoinput");
        const hasAudio = devices.some((d) => d.kind === "audioinput");

        if (!hasVideo && !hasAudio) {
            callStatus.value = "Không tìm thấy camera hoặc micro";
            return false;
        }

        const stream = await navigator.mediaDevices.getUserMedia({
            video: hasVideo ? { facingMode: 'user' } : false,
            audio: hasAudio,
        });

        localStream.value = stream;

        await nextTick();
        if (localVideo.value) {
            localVideo.value.srcObject = stream;
        }

        callStatus.value = isCallerFlag.value ? "Đang kết nối..." : "Đang chờ đối phương...";
        return true;
    } catch (err) {
        console.error("Error accessing media devices:", err);
        isCameraOff.value = !err.name.includes("video");
        isMuted.value = !err.name.includes("audio");
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

function createPeerConnection() {
    if (pc) {
        pc.close();
    }

    pc = new RTCPeerConnection({ iceServers });

    // Thêm tracks từ local stream
    if (localStream.value) {
        localStream.value.getTracks().forEach(track => {
            pc.addTrack(track, localStream.value);
        });
    }

    // Xử lý ICE candidates
    pc.onicecandidate = (event) => {
        if (event.candidate) {
            const peerId = resolvedPeerId.value || props.peerId;
            if (peerId) {
                sendSignal('/calls/ice', {
                    type: 'ice',
                    from: props.meId,
                    to: peerId,
                    candidate: event.candidate
                });
            }
        }
    };

    // Xử lý khi nhận được remote stream
    pc.ontrack = (event) => {
        console.log("Received remote track:", event);
        if (event.streams && event.streams[0]) {
            remoteStream.value = event.streams[0];
            nextTick(() => {
                const rv = document.querySelector('#remoteVideo');
                if (rv) {
                    rv.srcObject = remoteStream.value;
                }
            });
        }
    };

    // Xử lý connection state changes
    pc.onconnectionstatechange = () => {
        console.log("Connection state:", pc.connectionState);
        if (pc.connectionState === 'connected') {
            callStatus.value = "Đang trò chuyện";
            if (!isCallActive.value) {
                isCallActive.value = true;
                startCallTimer();
            }
        } else if (pc.connectionState === 'disconnected' || pc.connectionState === 'failed') {
            callStatus.value = "Kết nối bị gián đoạn";
        }
    };

    // Xử lý ICE connection state
    pc.oniceconnectionstatechange = () => {
        console.log("ICE connection state:", pc.iceConnectionState);
        if (pc.iceConnectionState === 'failed') {
            // Retry connection
            pc.restartIce();
        }
    };
}

async function makeOffer() {
    if (!pc) return;

    const peerId = resolvedPeerId.value || props.peerId;
    if (!peerId) {
        console.error("Cannot create offer: peerId not found");
        // Thử lấy lại peerId
        await determineCallRole();
        const retryPeerId = resolvedPeerId.value || props.peerId;
        if (!retryPeerId) {
            console.error("Still cannot find peerId after retry");
            return;
        }
        // Sử dụng retryPeerId
        return makeOfferWithPeerId(retryPeerId);
    }

    return makeOfferWithPeerId(peerId);
}

async function makeOfferWithPeerId(peerId) {
    if (!pc || !peerId) return;

    try {
        const offer = await pc.createOffer({
            offerToReceiveAudio: true,
            offerToReceiveVideo: true
        });
        await pc.setLocalDescription(offer);
        await sendSignal('/calls/offer', {
            type: 'offer',
            from: props.meId,
            to: peerId,
            sdp: offer
        });
        console.log("Offer sent to peer:", peerId);
    } catch (error) {
        console.error("Error creating offer:", error);
    }
}

async function handleOffer(offerData) {
    if (!pc) return;

    try {
        await pc.setRemoteDescription(new RTCSessionDescription(offerData.sdp));
        const answer = await pc.createAnswer({
            offerToReceiveAudio: true,
            offerToReceiveVideo: true
        });
        await pc.setLocalDescription(answer);
        await sendSignal('/calls/answer', {
            type: 'answer',
            from: props.meId,
            to: offerData.from,
            sdp: answer
        });
        console.log("Answer sent");
    } catch (error) {
        console.error("Error handling offer:", error);
    }
}

async function handleAnswer(answerData) {
    if (!pc) return;

    try {
        await pc.setRemoteDescription(new RTCSessionDescription(answerData.sdp));
        console.log("Answer received and set");
    } catch (error) {
        console.error("Error handling answer:", error);
    }
}

async function handleIceCandidate(candidateData) {
    if (!pc || !candidateData.candidate) return;

    try {
        await pc.addIceCandidate(new RTCIceCandidate(candidateData.candidate));
    } catch (error) {
        console.error("Error adding ICE candidate:", error);
    }
}

async function sendSignal(url, payload) {
    try {
        await axios.post(url, {
            id: props.callId,
            payload
        });
    } catch (error) {
        console.error("Error sending signal:", error);
    }
}

function subscribeToCallChannel() {
    if (!window.Echo) {
        console.error("Echo is not available");
        return;
    }

    channelName.value = `call.${props.callId}`;
    callChannel = window.Echo.join(channelName.value);

    // Lắng nghe signaling messages
    callChannel.listen('.signal', async (data) => {
        if (isCleaningUp.value) return; // Bỏ qua nếu đang cleanup
        const { payload } = data;
        if (!payload || payload.to !== props.meId) return;

        console.log("Received signal:", payload.type);

        switch (payload.type) {
            case 'offer':
                await handleOffer(payload);
                break;
            case 'answer':
                await handleAnswer(payload);
                break;
            case 'ice':
                await handleIceCandidate(payload);
                break;
        }
    });

    // Lắng nghe khi call được accept
    callChannel.listen('.call.accepted', (data) => {
        if (isCleaningUp.value) return;
        console.log("Call accepted:", data);
        callStatus.value = "Đang kết nối...";
        // Nếu là callee và chưa có offer, đợi caller gửi offer
        // Nếu là caller, đã gửi offer rồi, chỉ cần đợi answer
    });

    // Lắng nghe khi call kết thúc
    callChannel.listen('.call.ended', (data) => {
        if (isCleaningUp.value) return;
        console.log("Call ended:", data);
        if (data.byUserId !== props.meId) {
            callStatus.value = "Cuộc gọi đã kết thúc";
            setTimeout(() => {
                if (!isCleaningUp.value) {
                    endCall();
                }
            }, 1000);
        }
    });
}

const toggleScreenShare = async () => {
    if (!pc) return;

    try {
        if (!isScreenSharing.value) {
            // Bắt đầu chia sẻ màn hình
            const stream = await navigator.mediaDevices.getDisplayMedia({
                video: true,
                audio: true
            });

            screenStream.value = stream;

            // Thay thế video track
            const videoTrack = stream.getVideoTracks()[0];
            const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
            if (sender) {
                await sender.replaceTrack(videoTrack);
            }

            // Xử lý khi người dùng dừng chia sẻ màn hình
            videoTrack.onended = () => {
                if (isScreenSharing.value) {
                    toggleScreenShare();
                }
            };

            isScreenSharing.value = true;
            callStatus.value = "Đang chia sẻ màn hình";
        } else {
            // Dừng chia sẻ màn hình và quay lại camera
            if (screenStream.value) {
                screenStream.value.getTracks().forEach(track => track.stop());
                screenStream.value = null;
            }

            // Quay lại camera
            if (localStream.value) {
                const videoTrack = localStream.value.getVideoTracks()[0];
                const sender = pc.getSenders().find(s => s.track && s.track.kind === 'video');
                if (sender && videoTrack) {
                    await sender.replaceTrack(videoTrack);
                }
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

const stopLocalStream = () => {
    if (localStream.value) {
        localStream.value.getTracks().forEach(track => track.stop());
        localStream.value = null;
    }
    if (screenStream.value) {
        screenStream.value.getTracks().forEach(track => track.stop());
        screenStream.value = null;
    }
};

const toggleMute = () => {
    if (!localStream.value) return;
    isMuted.value = !isMuted.value;
    localStream.value.getAudioTracks().forEach(track => {
        track.enabled = !isMuted.value;
    });
};

const toggleCamera = () => {
    if (!localStream.value) return;
    isCameraOff.value = !isCameraOff.value;
    localStream.value.getVideoTracks().forEach(track => {
        track.enabled = !isCameraOff.value;
    });
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

    // Đóng peer connection
    if (pc) {
        try {
            pc.close();
        } catch (error) {
            console.error("Error closing peer connection:", error);
        }
        pc = null;
    }

    // Dừng local stream
    stopLocalStream();

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
    if (!localStream.value) return "Đang chuẩn bị thiết bị";
    if (!remoteStream.value) return "Chờ phản hồi";
    if (pc && pc.connectionState === 'connected') return "Kết nối ổn định";
    return "Đang kết nối...";
});

const connectionPillClass = computed(() => {
    if (!localStream.value) {
        return "call-pill--idle";
    }
    if (pc && pc.connectionState === 'connected') {
        return "call-pill--good";
    }
    return "call-pill--waiting";
});

watch(remoteStream, (stream) => {
    if (stream) {
        callStatus.value = "Đang trò chuyện";
        if (!isCallActive.value) {
            isCallActive.value = true;
            startCallTimer();
        }
    }
});

watch(isCallActive, (active) => {
    if (active) {
        startCallTimer();
    } else {
        stopCallTimer();
    }
});

onMounted(async () => {
    // Xác định vai trò caller/callee
    await determineCallRole();

    // Khởi tạo local stream
    const ready = await startLocalStream();
    if (!ready) {
        emit("end-call");
        return;
    }

    // Tạo peer connection
    createPeerConnection();

    // Subscribe to call channel
    subscribeToCallChannel();

    // Nếu là caller, tạo offer sau một chút delay để đảm bảo callee đã join
    if (isCallerFlag.value) {
        setTimeout(() => {
            makeOffer();
        }, 1000);
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
