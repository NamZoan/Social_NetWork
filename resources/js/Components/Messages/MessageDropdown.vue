<template>
    <li class="nav-item s-nav dropdown message-drop-li">
        <a href="#" class="nav-link nav-links message-drop drop-w-tooltip" data-toggle="dropdown"
            data-placement="bottom" data-title="Messages" role="button" aria-haspopup="true" aria-expanded="false">
            <img :src="'/images/web/icons/navbar/message.png'" class="message-dropdown" alt="navbar icon"> <span
                class="badge badge-pill badge-primary">1</span>
        </a>

        <ul class="dropdown-menu notify-drop dropdown-menu-right nav-drop shadow-sm">
            <div class="notify-drop-title">
                <div class="row">
                    <div class="col-md-6 col-sm-6 col-xs-6 fs-8">Messages | <a href="#">Requests</a></div>
                    <div class="col-md-6 col-sm-6 col-xs-6 text-right">
                        <a href="#" class="notify-right-icon">
                            Mark All as Read
                        </a>
                    </div>
                </div>
            </div>
            <div class="drop-content">
                <li>
                    <div class="col-md-2 col-sm-2 col-xs-2">
                        <div class="notify-img">
                            <img src="/images/web/users/avatar.jpg" alt="notification user image">
                        </div>
                    </div>
                    <div class="col-md-10 col-sm-10 col-xs-10">
                        <a href="#" class="notification-user">Susan P. Jarvis</a>
                        <a href="#" class="notify-right-icon">
                            <i class='bx bx-radio-circle-marked'></i>
                        </a>
                        <p class="time">
                            <i class='bx bx-check'></i> This party is going to have a DJ, food, and drinks.
                        </p>
                    </div>
                </li>
            </div>
            <div class="notify-drop-footer text-center">
                <a href="#">See More</a>
            </div>
        </ul>
    </li>
</template>

<script setup>


</script>