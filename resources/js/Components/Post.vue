<template>
    <ul class="list-unstyled" data-toggle="modal" data-target="#exampleModal">
        <li class="media post-form w-shadow">
            <div class="media-body">
                <div class="form-group post-input">
                    <textarea class="form-control" id="postForm" rows="2"
                        placeholder="What's on your mind, Arthur?"></textarea>
                </div>
                <div class="row post-form-group">
                    <div class="col-md-9">
                        <button type="file" class="btn btn-link post-form-btn btn-sm">
                            <img :src="'/images/web/icons/theme/post-image.png'" alt="post form icon" />
                            <span>Photo/Video</span>
                        </button>
                        <button type="button" class="btn btn-link post-form-btn btn-sm">
                            <img :src="'/images/web/icons/theme/tag-friend.png'" alt="post form icon" />
                            <span>Tag Friends</span>
                        </button>
                        <button type="button" class="btn btn-link post-form-btn btn-sm">
                            <img :src="'/images/web/icons/theme/check-in.png'" alt="post form icon" />
                            <span>Check In</span>
                        </button>
                    </div>
                    <div class="col-md-3 text-right">
                        <button type="button" class="btn btn-primary btn-sm">
                            Publish
                        </button>
                    </div>
                </div>
            </div>
        </li>
    </ul>

    <div class="modal fade bd-example-modal-lg" id="exampleModal" tabindex="-1" role="dialog"
        aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content ">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Tạo bài viết</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="submitPost">
                        <!-- Chọn quyền riêng tư -->
                        <select v-model="form.privacy_setting" class="form-control" aria-label="Default select example">
                            <option value="public">Công Khai</option>
                            <option value="friends">Bạn Bè</option>
                            <option value="private">Chỉ Mình Tôi</option>
                        </select>

                        <!-- Nội dung bài viết -->
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Bạn đang nghĩ gì:</label>
                            <textarea v-model="form.content" class="form-control" id="message-text"></textarea>
                        </div>

                        <!-- Upload file -->
                        <input id="input-b3" type="file" class="file" multiple @change="handleFileUpload">

                        <div class="modal-footer p-0 mt-5">
                            <button type="submit" class="btn btn-primary">Đăng Tin</button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
</template>


<script setup>
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import { onMounted,defineProps } from 'vue';
import { useForm,router  } from '@inertiajs/vue3';
import $ from 'jquery';
const props = defineProps({
    user: Object,
});


const form = useForm({
    privacy_setting: 'public',
    content: '',
    files: []
});

// Gán file vào form khi người dùng chọn file
const handleFileUpload = (event) => {
    form.files = Array.from(event.target.files);
};

// Khởi tạo bootstrap-fileinput
onMounted(() => {
    $('#input-b3').fileinput({
        showUpload: false,
        showPreview: true,
        allowedFileExtensions: ['jpg', 'png', 'gif'],
    });
});


// Gửi form lên server Laravel
const submitPost = () => {
    const formData = new FormData();
    formData.append('privacy_setting', form.privacy_setting);
    formData.append('content', form.content);

    form.files.forEach(file => {
        formData.append('files[]', file);
    });

    form.post('/posts', {
        headers: { 'Content-Type': 'multipart/form-data' },
        onSuccess: () => {
            alert('✅ Bài viết đã được đăng thành công!');
            form.reset();

            // Chuyển hướng về trang profile của user
            router.visit(`/profile/${props.user.username}`);
        },
        onError: (errors) => {
            alert('❌ Đăng bài thất bại! Vui lòng thử lại.');
            console.error(errors);
        }
    });
};

</script>
