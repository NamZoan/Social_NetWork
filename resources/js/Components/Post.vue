<template>
    <ul class="list-unstyled" data-toggle="modal" data-target="#exampleModal">
        <li class="media post-form w-shadow">
            <div class="media-body">
                <div class="form-group post-input">
                    <textarea v-model="form.content" class="form-control" id="postForm" rows="2"
                        :placeholder="placeholder"></textarea>
                </div>
                <div class="row post-form-group">
                    <div class="col-md-9">
                        <button type="button" class="btn btn-link post-form-btn btn-sm" @click="triggerFileInput">
                            <img :src="'/images/web/icons/theme/post-image.png'" alt="post form icon" />
                            <span>Photo/Video</span>
                        </button>
                        <input type="file" ref="fileInput" @change="handleFileChange" multiple style="display: none" accept="image/*,video/*">
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
                        <button type="button" class="btn btn-primary btn-sm" @click="submitPost" :disabled="form.processing">
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
                        <div v-if="!props.group_id">
                        <select v-model="form.privacy_setting" class="form-control" aria-label="Default select example">
                            <option value="public">Công Khai</option>
                            <option value="friends">Bạn Bè</option>
                            <option value="private">Chỉ Mình Tôi</option>
                        </select>
                        </div>
                        <div v-else class="alert" :class="props.group?.post_approval_required ? 'alert-info' : 'alert-warning'">
                            {{ props.group?.post_approval_required ? 'Đăng bài tự do' : 'Cần quản trị viên duyệt bài viết' }}
                        </div>

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
import { onMounted, defineProps, ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import $ from 'jquery';
import axios from 'axios';

const props = defineProps({
    group_id: {
        type: Number,
        default: null
    },
    user: {
        type: Object,
        required: true
    },
    group: {
        type: Object,
        default: null
    },
    createdGroups: {
        type: Array,
        default: () => []
    },
    joinedGroups: {
        type: Array,
        default: () => []
    }
});

const fileInput = ref(null);
const form = useForm({
    content: '',
    privacy_setting: 'public',
    files: [],
    group_id: props.group_id || null
});

const placeholder = props.user ? `Bạn đang nghĩ gì..., ${props.user.name}?` : "Bạn đang nghĩ gì...?";

const triggerFileInput = () => {
    fileInput.value.click();
};

const handleFileChange = (event) => {
    form.files = Array.from(event.target.files);
};

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
    if (!form.content.trim()) return;

    form.post('/posts', {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            if (fileInput.value) {
                fileInput.value.value = '';
            }
            // Reload the page to show the new post
            window.location.reload();
        }
    });
};

</script>
<style scoped>
.alert {
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 4px;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.alert-warning {
    background-color: #fff3cd;
    border-color: #ffeeba;
    color: #856404;
}
</style>

