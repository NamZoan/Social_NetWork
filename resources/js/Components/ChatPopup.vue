<template>
    <div class="chat-popup shadow" id="hide-in-mobile">
        <div class="row chat-window col-xs-5 col-md-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="top-bar shadow-sm d-flex align-items-center">
                        <div class="col-md-6 col-xs-6">
                            <a href="profile.html">
                                <img src="assets/images/users/user-2.jpg" class="mr-2 chatbox-user-img" alt="Chat user image">
                                <span class="panel-title">Karen Minas</span>
                            </a>
                        </div>
                        <div class="col-md-6 col-xs-6 d-flex align-items-center justify-content-between">
                            <a href="#">
                                <img src="assets/images/icons/messenger/video-call.png" class="chatbox-call" alt="Chatbox contact types">
                            </a>
                            <a href="#" data-toggle="modal" data-target="#callModal">
                                <img src="assets/images/icons/messenger/call.png" class="chatbox-call" alt="Chatbox contact types">
                            </a>
                            <a href="javascript:void(0)"><i id="minimize-chat-window" class="bx bx-minus icon_minim"></i></a>
                            <a href="javascript:void(0)" id="close-chatbox"><i class="bx bx-x"></i></a>
                        </div>
                    </div>
                    <div id="messagebody" class="msg_container_base">
                        <div class="row msg_container base_sent">
                            <div class="col-md-10 col-xs-10">
                                <div class="messages message-reply bg-primary shadow-sm">
                                    <p>Are you going to the party on Saturday?</p>
                                </div>
                            </div>
                        </div>
                        <div class="row msg_container base_receive">
                            <div class="col-md-10 col-xs-10">
                                <div class="messages message-receive shadow-sm">
                                    <p>I was thinking about it. Are you?</p>
                                </div>
                            </div>
                        </div>
                        <div class="row msg_container base_receive">
                            <div class="col-xs-10 col-md-10">
                                <div class="messages message-receive shadow-sm">
                                    <p>Really? Well, what time does it start?</p>
                                </div>
                            </div>
                        </div>
                        <div class="row msg_container base_sent">
                            <div class="col-xs-10 col-md-10">
                                <div class="messages message-reply bg-primary shadow-sm">
                                    <p>It starts at 8:00 pm, and I really think you should go.</p>
                                </div>
                            </div>
                        </div>
                        <div class="row msg_container base_receive">
                            <div class="col-xs-10 col-md-10">
                                <div class="messages message-receive shadow-sm">
                                    <p>Well, who else is going to be there?</p>
                                </div>
                            </div>
                        </div>
                        <div class="row msg_container base_sent">
                            <div class="col-md-10 col-xs-10">
                                <div class="messages message-reply bg-primary shadow-sm">
                                    <p>Everybody from school.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer chat-inputs">
                        <div class="col-md-12 message-box">
                            <input type="text" class="w-100 search-input type-message" placeholder="Type a message..." />
                            <div class="chat-tools">
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/theme/post-image.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/messenger/gif.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/messenger/smile.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/messenger/console.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/messenger/attach-file.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                                <a href="#" class="chatbox-tools">
                                    <img src="assets/images/icons/messenger/photo-camera.png" class="chatbox-tools-img" alt="Chatbox tool">
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
