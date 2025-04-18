<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <div class="mb-3">
                <div class="btn-group d-flex top-links-fg">
                    <a href="index.html" class="btn btn-quick-links mr-3 ql-active">
                        <img :src="'images/web/icons/theme/group-white.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Nhóm Của Tôi</span>
                    </a>
                    <a href="messages.html" class="btn btn-quick-links mr-3">
                        <img :src="'images/web/icons/theme/rocket.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Discover</span>
                    </a>
                    <a href="#" class="btn btn-quick-links" data-toggle="modal" data-target=".bd-example-modal-lg">
                        <img :src="'images/web/icons/theme/create.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Tạo Nhóm</span>
                    </a>
                </div>
            </div>
            <div class="groups py-3 px-4 mt-3">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Nhóm của bạn</h5>
                </div>
                <div class="row">
                    <div class="col-md-6 col-sm-6">
                        <div class="card group-card bg-transparent group-card-inline mb-3">
                            <div class="row no-gutters  d-flex align-items-center">
                                <div class="col-md-4">
                                    <img :src="'images/web/groups/group-5.jpg'" class="card-img group-card-inline-img"
                                        alt="Group image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title">Source Code Viewer</h5>
                                        <p class="card-text">8.6k Members 29+ post a day</p>
                                        <div class="like-user-img">
                                            <ul class="list-unstyled liked-users">
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-7.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-6.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-5.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <span class="liked-info fs-8">9 friends are members</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-quick-link bg-white">Join</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-6">
                        <div class="card group-card bg-transparent group-card-inline mb-3">
                            <div class="row no-gutters  d-flex align-items-center">
                                <div class="col-md-4">
                                    <img :src="'images/web/groups/group-6.jpg'" class="card-img group-card-inline-img"
                                        alt="Group image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                        <h5 class="card-title">T-Shirt</h5>
                                        <p class="card-text">2.4k Members 5+ post a day</p>
                                        <div class="like-user-img">
                                            <ul class="list-unstyled liked-users">
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-7.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-6.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <a href="#"><img :src="'images/web/users/user-5.png'"
                                                            alt="Like user image"></a>
                                                </li>
                                                <li>
                                                    <span class="liked-info fs-8">4 friends are members</span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="#" class="btn btn-quick-link bg-white">Join</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <hr class="my-5">
            <!-- Groups -->
            <div class="groups bg-white py-3 px-4 shadow-sm">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Nhóm bạn đã tham gia</h5>
                </div>
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-1.png'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Argon </h5>
                                <p class="card-text">10k Members 20+ post a week</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Xem</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-2.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Tourism</h5>
                                <p class="card-text">2.5k Members 35+ post a week</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-3.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Reading Books</h5>
                                <p class="card-text">1.3k Members 10+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-4.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Capture The Best</h5>
                                <p class="card-text">2.8k Members 8+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-4.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Capture The Best</h5>
                                <p class="card-text">2.8k Members 8+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card group-card shadow-sm">
                            <img :src="'images/web/groups/group-4.jpg'" class="card-img-top group-card-image"
                                alt="Group image">
                            <div class="card-body">
                                <h5 class="card-title">Capture The Best</h5>
                                <p class="card-text">2.8k Members 8+ post a day</p>
                                <a href="#" class="btn btn-quick-link join-group-btn border w-100">Join</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Index>

    <div class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form @submit.prevent="submit">
                    <div class="modal-header">
                        <h5 class="modal-title">Tạo nhóm </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <label for="recipient-status" class="col-form-label">Trạng Thái Nhóm</label>

                        <select class="form-control" aria-label="Default select example">
                            <option value="public">Công Khai</option>
                            <option value="friends">Riêng Tư</option>
                        </select>
                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Tên Nhóm</label>
                            <input type="text" class="form-control" id="recipient-name">
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Mô Tả</label>
                            <textarea class="form-control" id="message-text"></textarea>
                        </div>
                        <div>
                            <label for="cover_photo_url" class="col-form-label">Ảnh</label>
                            <input id="input-b3" type="file" class="file" data-browse-on-zone-click="true" @change="handleFileChange">
                        </div>
                        <div>
                            <label for="post_approval_required" class="col-form-label">Cho Phép Người Dùng Đăng
                                Bài</label>
                            <select class="form-control" aria-label="Default select example">
                                <option value="friends">Không</option>
                                <option value="public">Có</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Tạo Nhóm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>
<script setup>
import { ref } from 'vue'
import { Inertia } from '@inertiajs/inertia'
import Index from "./Index.vue";
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';

const form = ref({
  name: '',
  description: '',
  privacy_setting: 0,
  post_approval_required: 0,
  cover_photo_url: null,
})

const handleFileChange = (e) => {
  form.value.cover_photo_url = e.target.files[0]
}

const submit = () => {
  const formData = new FormData()
  formData.append('name', form.value.name)
  formData.append('description', form.value.description)
  formData.append('privacy_setting', form.value.privacy_setting)
  formData.append('post_approval_required', form.value.post_approval_required)
  if (form.value.cover_photo_url) {
    formData.append('cover_photo_url', form.value.cover_photo_url)
  }

  Inertia.post('/groups', formData)
}
</script>
