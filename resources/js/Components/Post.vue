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
                        <div class="input-group mb-3">
                            <select v-model="status" class="form-select">
                                <option disabled>Trạng thái</option>
                                <option value="1">Công Khai</option>
                                <option value="2">Bạn Bè</option>
                                <option value="3">Chỉ Mình Tôi</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Message:</label>
                            <textarea v-model="message" class="form-control" id="message-text"></textarea>
                        </div>

                        <input id="input-b3" name="input-b3[]" type="file" data-show-upload="false"
                            class="file form-control" multiple @change="handleFileUpload" />

                        <div class="modal-footer p-0 mt-5">
                            <button type="button" class="btn btn-secondary">Hủy</button>
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

const form = useForm({
    status: '',
    message: '',
    files: []
});

const submitPost = () => {
    form.post('/posts', {
        onSuccess: () => {
            alert('Đăng bài thành công!');
        }
    });
};

</script>
