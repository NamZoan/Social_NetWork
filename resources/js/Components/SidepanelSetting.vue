<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const page = usePage();
const currentPath = computed(() => page.url);

function isActive(path: string) {
    return currentPath.value === path;
}
</script>

<template>
    <div class="message-sidepanel col-3">
        <div class="message-contacts settings-sidebar">
            <ul class="conversations">
                <h6 class="p-3">Cài Đặt Chung</h6>
                <li :class="['contact', isActive('/cai-dat') && 'setting-active']">
                    <Link :href="'/cai-dat'" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/account.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Thông Tin Cá Nhân</p>
                        </div>
                    </Link>
                </li>
                <li :class="['contact', isActive('/settings-contact.html') && 'setting-active']">
                    <a href="settings-contact.html" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/contact.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Thông Tin Liên Lạc</p>
                        </div>
                    </a>
                </li>
                <li :class="['contact', isActive('#') && 'setting-active']">
                    <a href="#" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/privacy.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Quyền Riêng Tư</p>
                        </div>
                    </a>
                </li>
                <h6 class="p-3">Bảo Mật & Đăng Nhập</h6>
                <li :class="['contact', isActive('/doi-mat-khau') && 'setting-active']">
                    <Link :href="'/doi-mat-khau'" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/account.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Mật Khẩu</p>
                        </div>
                    </Link>
                </li>
                <li :class="['contact', isActive('#') && 'setting-active']">
                    <a href="#" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/security-question.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Câu Hỏi Bảo Mật</p>
                        </div>
                    </a>
                </li>
                <li :class="['contact', isActive('settings-fingerprint.html') && 'setting-active']">
                    <a href="settings-fingerprint.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/fingerprint.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Khóa Vân Tay
                        </p>
                    </a>
                </li>
                <li :class="['contact', isActive('settings-location.html') && 'setting-active']">
                    <a href="settings-location.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/location.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Vị Trí
                        </p>
                    </a>
                </li>
                <h6 class="p-3">Thanh Toán & Hóa Đơn</h6>
                <li :class="['contact', isActive('settings-billing-method.html') && 'setting-active']">
                    <a href="settings-billing-method.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/wallet.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Phương Thức Thanh Toán
                        </p>
                    </a>
                </li>
                <li :class="['contact', isActive('#') && 'setting-active']">
                    <a href="#" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/credit-card.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Thanh Toán Tự Động
                        </p>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>
<style scoped>

@import '../../css/forms.css';
@import '../../css/settings.css';

</style>

