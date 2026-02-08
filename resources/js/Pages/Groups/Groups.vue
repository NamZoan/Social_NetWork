<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <div>
                <div class="btn-group d-flex top-links-fg">
                    <Link href="/groups" class="btn btn-quick-links mr-3 ql-active">
                        <img :src="'/images/web/icons/theme/group-white.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Nhóm Của Tôi</span>
                    </Link>
                    <Link href="/groups/discover" class="btn btn-quick-links mr-3">
                        <i class='bx bx-search-alt mr-2'></i>
                        <span class="fs-8">Khám Phá Nhóm</span>
                    </Link>
                    <button type="button" class="btn btn-quick-links" data-toggle="modal" data-target=".bd-example-modal-lg">
                        <img :src="'/images/web/icons/theme/create.png'" class="mr-2" alt="quick links icon">
                        <span class="fs-8">Tạo Nhóm</span>
                    </button>
                </div>
            </div>

            <!-- Search Section -->
            <div class="card mt-4 mb-4">
                <div class="card-body">
                    <form @submit.prevent="searchGroups" class="row g-3">
                        <div class="col-md-10">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class='bx bx-search'></i>
                                </span>
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Tìm kiếm nhóm của bạn..."
                                >
                            </div>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="groups groups-section">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Nhóm bạn đã tạo</h5>
                </div>
                <div class="row">
                    <template v-if="createdGroups && createdGroups.length > 0">
                        <div v-for="group in createdGroups" :key="group.id" class="col-md-6 col-sm-6">
                            <div class="group-card">
                                <img :src="groupCover(group)" class="group-card-img" alt="Group image">
                                <div class="group-card-body">
                                    <div class="group-card-title limit-2-lines">
                                        {{ group.name || 'Nhóm không có tên' }}
                                    </div>
                                    <div class="group-card-meta">
                                        <i class="bx bx-user"></i>
                                        <span>{{ group.members_count || 0 }} thành viên</span>
                                    </div>
                                    <p class="group-card-desc limit-2-lines">{{ group.description || 'Không có mô tả' }}</p>
                                    <div class="group-card-actions">
                                        <Link v-if="group.id" :href="`/groups/${group.id}`" class="btn btn-quick-link btn-view">Xem</Link>
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
            <!-- Nhóm bạn đã tham gia -->
            <div class="groups groups-section groups-joined">
                <div class="card-head d-flex justify-content-between">
                    <h5 class="mb-4">Nhóm bạn đã tham gia</h5>
                </div>
                <div class="row">
                    <template v-if="joinedGroups && joinedGroups.length > 0">
                        <div v-for="group in joinedGroups" :key="group.id" class="col-md-6 col-sm-6">
                            <div class="group-card">
                                <img :src="groupCover(group)" class="group-card-img" alt="Group image">
                                <div class="group-card-body">
                                    <div class="group-card-title limit-2-lines">
                                        {{ group.name || 'Nhóm không có tên' }}
                                    </div>
                                    <div class="group-card-meta">
                                        <i class="bx bx-user"></i>
                                        <span>{{ group.members_count || 0 }} thành viên</span>
                                    </div>
                                    <p class="group-card-desc limit-2-lines">{{ group.description || 'Không có mô tả' }}</p>
                                    <div class="group-card-actions">
                                        <Link v-if="group.id" :href="`/groups/${group.id}`" class="btn btn-quick-link btn-view primary">Xem</Link>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div v-else class="col-12 text-center">
                        <p>Bạn chưa tham gia nhóm nào.</p>
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
import { onMounted, ref } from 'vue'
import { useForm, Link, router } from '@inertiajs/vue3'
import Index from "./Index.vue";
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import $ from 'jquery';

const props = defineProps({
    createdGroups: {
        type: Array,
        default: () => []
    },
    joinedGroups: {
        type: Array,
        default: () => []
    },
    search: {
        type: String,
        default: ''
    }
});

const searchQuery = ref(props.search || '');

const defaultGroupCover = '/images/web/groups/group.webp';
const groupCover = (group) => {
    const cover = group?.cover_photo_url;
    if (!cover) return defaultGroupCover;
    if (cover.startsWith('http')) return cover;
    if (cover.startsWith('/')) return cover;
    return `/images/client/group/thumbnail/${cover}`;
};

const form = useForm({
    name: '',
    description: '',
    privacy_setting: true,
    post_approval_required: false,
    cover_photo_url: null
})

const searchGroups = () => {
    router.get('/groups', {
        search: searchQuery.value
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

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

<style scoped>
.groups-section {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.groups-joined {
    margin-top: 12px;
}

.group-card {
    background: #ffffff;
    border: 1px solid #eef2f7;
    border-radius: 14px;
    overflow: hidden;
    display: flex;
    gap: 14px;
    padding: 12px;
    margin-bottom: 16px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.group-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 24px rgba(15, 23, 42, 0.12);
}

.group-card-img {
    width: 90px;
    height: 90px;
    border-radius: 12px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
}

.group-card-body {
    display: flex;
    flex-direction: column;
    gap: 6px;
    flex: 1;
}

.group-card-title {
    font-size: 16px;
    font-weight: 700;
    color: #0f172a;
}

.group-card-meta {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #64748b;
    font-size: 13px;
}

.group-card-desc {
    color: #6b7280;
    font-size: 13px;
    margin-bottom: 2px;
}

.group-card-actions {
    margin-top: auto;
}

.btn-view {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #1e293b;
    font-weight: 600;
    padding: 6px 14px;
    border-radius: 999px;
}

.btn-view.primary {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.btn-view:hover {
    background: #e2e8f0;
}

.btn-view.primary:hover {
    background: #1d4ed8;
}

@media (max-width: 768px) {
    .group-card {
        flex-direction: column;
        align-items: flex-start;
    }

    .group-card-img {
        width: 100%;
        height: 160px;
    }
}
</style>



