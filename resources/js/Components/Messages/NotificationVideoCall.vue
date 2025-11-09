<template>
    <transition name="fade">
        <div v-if="visible" class="call-notify">
            <div class="call-notify__backdrop"></div>
            <div class="call-notify__card">
                <header class="call-notify__header">
                    <p class="call-notify__label">Cuộc gọi đến</p>
                    <button class="call-notify__close" @click="handleReject" aria-label="Đóng">
                        <i class="bx bx-x"></i>
                    </button>
                </header>

                <div class="call-notify__body">
                    <div class="call-notify__avatar">
                        <img v-if="caller.avatar" :src="caller.avatar" alt="avatar" />
                        <span v-else>{{ callerInitials }}</span>
                    </div>
                    <div>
                        <p class="call-notify__name">{{ caller.name }}</p>
                        <p class="call-notify__subtitle">{{ subtitle }}</p>
                    </div>
                </div>

                <footer class="call-notify__actions">
                    <button class="call-notify__btn call-notify__btn--decline" @click="handleReject">
                        <i class="bx bx-x-circle"></i>
                        Từ chối
                    </button>
                    <button class="call-notify__btn call-notify__btn--accept" @click="handleAccept">
                        <i class="bx bx-video"></i>
                        Chấp nhận
                    </button>
                </footer>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { computed, onMounted, onBeforeUnmount } from "vue";

const props = defineProps({
    visible: {
        type: Boolean,
        default: false,
    },
    caller: {
        type: Object,
        default: () => ({
            name: "Người gọi",
            avatar: null,
        }),
    },
    subtitle: {
        type: String,
        default: "Đang gọi video...",
    },
});

const emit = defineEmits(["accept", "reject"]);

const callerInitials = computed(() => {
    const name = props.caller?.name || "";
    return name
        .split(" ")
        .filter(Boolean)
        .map((chunk) => chunk[0])
        .join("")
        .toUpperCase()
        .slice(0, 2) || "U";
});

const handleAccept = () => emit("accept");
const handleReject = () => emit("reject");

const handleKey = (event) => {
    if (!props.visible) return;
    if (event.key === "Escape") handleReject();
    if (event.key === "Enter") handleAccept();
};

onMounted(() => window.addEventListener("keyup", handleKey));
onBeforeUnmount(() => window.removeEventListener("keyup", handleKey));
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.call-notify {
    position: fixed;
    inset: 0;
    z-index: 60;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: "Inter", sans-serif;
}
.call-notify__backdrop {
    position: absolute;
    inset: 0;
    background: rgba(2, 6, 23, 0.75);
    backdrop-filter: blur(4px);
}
.call-notify__card {
    position: relative;
    width: min(420px, 90vw);
    border-radius: 1.5rem;
    border: 1px solid rgba(255, 255, 255, 0.1);
    background: linear-gradient(135deg, #111c3d, #0f172a);
    padding: 1.5rem;
    color: #f8fafc;
    box-shadow: 0 25px 70px rgba(0, 0, 0, 0.55);
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
    z-index: 1;
}
.call-notify__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.call-notify__label {
    margin: 0;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.3em;
    color: rgba(248, 250, 252, 0.6);
}
.call-notify__close {
    border: none;
    background: rgba(255, 255, 255, 0.08);
    color: inherit;
    width: 38px;
    height: 38px;
    border-radius: 999px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s ease;
}
.call-notify__close:hover {
    background: rgba(255, 255, 255, 0.18);
}
.call-notify__body {
    display: flex;
    align-items: center;
    gap: 1rem;
}
.call-notify__avatar {
    width: 82px;
    height: 82px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.08);
    border: 1px solid rgba(255, 255, 255, 0.12);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
    font-weight: 600;
    overflow: hidden;
}
.call-notify__avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.call-notify__name {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
}
.call-notify__subtitle {
    margin: 0.25rem 0 0;
    font-size: 0.95rem;
    color: rgba(248, 250, 252, 0.75);
}
.call-notify__actions {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 0.75rem;
}
.call-notify__btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.35rem;
    border-radius: 999px;
    padding: 0.85rem 1rem;
    border: 1px solid transparent;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.call-notify__btn i {
    font-size: 1.2rem;
}
.call-notify__btn:hover {
    transform: translateY(-1px);
}
.call-notify__btn--decline {
    background: rgba(239, 68, 68, 0.12);
    border-color: rgba(239, 68, 68, 0.4);
    color: #fecaca;
}
.call-notify__btn--accept {
    background: rgba(34, 197, 94, 0.15);
    border-color: rgba(34, 197, 94, 0.4);
    color: #bbf7d0;
}
@media (max-width: 560px) {
    .call-notify__card {
        padding: 1.25rem;
    }
    .call-notify__body {
        flex-direction: column;
        text-align: center;
    }
    .call-notify__actions {
        grid-template-columns: 1fr;
    }
}
</style>
