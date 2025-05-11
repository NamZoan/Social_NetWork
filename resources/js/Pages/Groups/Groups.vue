<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <div>
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
            <div class="groups py-3 px-4">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Nhóm của bạn</h5>
                </div>
                <div class="row">
                    <template v-if="allGroups && allGroups.length > 0">
                        <div v-for="group in allGroups" :key="group?.id" class="col-md-6 col-sm-6">
                        <div class="card group-card bg-transparent group-card-inline mb-3">
                                <div class="row no-gutters d-flex align-items-center">
                                    <div class="col-md-3">
                                        <img :src="group?.cover_photo_url ? `/images/client/group/thumbnail/${group.cover_photo_url}` : 'images/web/groups/group-5.jpg'" 
                                             class="card-img group-card-inline-img" alt="Group image">
                                </div>
                                <div class="col-md-6">
                                    <div class="card-body">
                                            <h4 class="card-title limit-2-lines">{{ group?.name || 'Nhóm không có tên' }}</h4>
                                            <h5 class="card-text">{{ group?.members_count || 0 }} thành viên</h5>
                                            <p class="card-text limit-2-lines">{{ group?.description || 'Không có mô tả' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <Link v-if="group?.id" :href="`/groups/${group.id}`" class="btn btn-quick-link bg-white">Xem</Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="col-12 text-center">
                        <p>Bạn chưa tạo nhóm nào.</p>
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
                        <h5 class="modal-title">Tạo nhóm</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <!-- Trạng thái nhóm -->
                        <label class="col-form-label">Trạng Thái Nhóm</label>
                        <select v-model="form.privacy_setting" class="form-control">
                            <option :value="true">Công Khai</option>
                            <option :value="false">Riêng Tư</option>
                        </select>

                        <!-- Tên nhóm -->
                        <div class="form-group">
                            <label class="col-form-label">Tên Nhóm</label>
                            <input v-model="form.name" type="text" class="form-control" />
                        </div>

                        <!-- Mô tả -->
                        <div class="form-group">
                            <label class="col-form-label">Mô Tả</label>
                            <textarea v-model="form.description" class="form-control"></textarea>
                        </div>

                        <!-- Ảnh -->
                        <div class="form-group">
                            <label class="col-form-label">Ảnh</label>
                            <input id="input-b1" name="input-b1" type="file" class="file" data-browse-on-zone-click="true" @change="handleFileChange" />
                        </div>

                        <!-- Cho phép đăng bài -->
                        <div class="form-group">
                            <label class="col-form-label">Cho phép người dùng đăng bài</label>
                            <select v-model="form.post_approval_required" class="form-control">
                                <option :value="false">Không</option>
                                <option :value="true">Có</option>
                            </select>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary" :disabled="form.processing">Tạo Nhóm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</template>
<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm, usePage, Link } from '@inertiajs/vue3'
import Index from "./Index.vue";
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import $ from 'jquery';

const page = usePage();
const user_auth = computed(() => page.props.auth.user || {});

const props = defineProps({
    createdGroups: {
        type: Array,
        default: () => []
    },
    joinedGroups: {
        type: Array,
        default: () => []
    }
});

const allGroups = computed(() => {
    if (!props.createdGroups || !props.joinedGroups) return [];
    return [...props.createdGroups, ...props.joinedGroups].filter(group => group !== null && group !== undefined);
});

const { errors } = usePage()

const form = useForm({
    name: '',
    description: '',
    privacy_setting: true,
    post_approval_required: false,
    cover_photo_url: null
})


onMounted(() => {
        $('#input-b1').fileinput({
            showUpload: false,
            showPreview: true,
            allowedFileExtensions: ['jpg', 'png', 'gif'],
        })
})

const handleFileChange = (event) => {
    form.cover_photo_url = event.target.files[0];
};

const submit = () => {
    form.post('/groups', {
        preserveScroll: true,
        onSuccess: () => {
            $('.bd-example-modal-lg').modal('hide');
            form.reset();
        }
    });
};
</script>


