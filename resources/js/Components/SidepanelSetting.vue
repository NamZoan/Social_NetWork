<template>
    <div class="message-sidepanel col-3">
        <div class="message-contacts settings-sidebar">
            <ul class="conversations">
                <h6 class="p-3">General Settings</h6>
                <li class="contact setting-active">
                    <a href="settings.html" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/account.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Your Account</p>
                        </div>
                    </a>
                </li>
                <li class="contact">
                    <a href="settings-contact.html" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/contact.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Contact Info</p>
                        </div>
                    </a>
                </li>
                <li class="contact">
                    <a href="#" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/privacy.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Privacy</p>
                        </div>
                    </a>
                </li>
                <h6 class="p-3">Security &#38; Login</h6>
                <li class="contact">
                    <a href="settings-password.html" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/account.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Password</p>
                        </div>
                    </a>
                </li>
                <li class="contact">
                    <a href="#" class="wrap d-flex align-items-center">
                        <img :src="'images/web/icons/settings/security-question.png'" class="settings-icon"
                            alt="Settings left sidebar" />
                        <div class="meta">
                            <p>Security Question</p>
                        </div>
                    </a>
                </li>
                <li class="contact">
                    <a href="settings-fingerprint.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/fingerprint.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Fingerprint Lock
                        </p>
                    </a>
                </li>
                <li class="contact">
                    <a href="settings-location.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/location.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Location
                        </p>
                    </a>
                </li>
                <h6 class="p-3">Billing &#38; Payment</h6>
                <li class="contact">
                    <a href="settings-billing-method.html" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/wallet.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Billing Method
                        </p>
                    </a>
                </li>
                <li class="contact">
                    <a href="#" class="wrap d-flex align-items-center">
                        <div class="meta"></div>
                        <p>
                            <img :src="'images/web/icons/settings/credit-card.png'" class="settings-icon"
                                alt="Settings left sidebar" />
                            Automatic Payments
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

