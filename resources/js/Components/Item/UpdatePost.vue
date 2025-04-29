<template>
    <div class="modal fade" :id="'modal-update' + post.id" tabindex="-1" role="dialog" aria-labelledby="updatePostModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updatePostModalLabel">Chỉnh sửa bài viết</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="updatePost">
                        <div class="form-group">
                            <textarea 
                                v-model="content" 
                                class="form-control" 
                                rows="5" 
                                placeholder="Bạn đang nghĩ gì?"
                            ></textarea>
                        </div>
                        
                        <!-- Hiển thị ảnh hiện tại -->
                        <div v-if="currentImages.length > 0" class="current-images mb-3">
                            <div class="row">
                                <div v-for="(image, index) in currentImages" :key="index" class="col-md-4 mb-2">
                                    <div class="position-relative">
                                        <img :src="'/images/client/post/' + image" class="img-fluid rounded" />
                                        <button 
                                            type="button" 
                                            class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1"
                                            @click="removeImage(index)"
                                        >
                                            <i class="bx bx-x"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload ảnh mới sử dụng fileinput -->
                        <div class="form-group">
                            <label for="newImages">Thêm ảnh mới</label>
                            <input 
                                id="newImages" 
                                name="new_images[]" 
                                type="file" 
                                multiple 
                                class="file" 
                                data-show-upload="false"
                                data-show-caption="true"
                                data-msg-placeholder="Chọn ảnh để tải lên..."
                                accept="image/*"
                            >
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="button" class="btn btn-primary" @click="updatePost" :disabled="isUpdating">
                        {{ isUpdating ? 'Đang cập nhật...' : 'Cập nhật' }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, defineProps, defineEmits, onMounted } from 'vue';
import axios from 'axios';
import 'bootstrap-fileinput/css/fileinput.min.css';
import 'bootstrap-fileinput/js/fileinput.min.js';
import $ from 'jquery';

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
});

const emit = defineEmits(['updated']);

const content = ref(props.post.content);
const currentImages = ref(props.post.media ? props.post.media.filter(m => m.media_type === 'image').map(m => m.media_url) : []);
const isUpdating = ref(false);

const removeImage = (index) => {
    currentImages.value.splice(index, 1);
};

const updatePost = async () => {
    if (isUpdating.value) return;
    
    isUpdating.value = true;
    
    try {
        const formData = new FormData();
        formData.append('content', content.value);
        
        // Thêm danh sách ảnh hiện tại
        currentImages.value.forEach(image => {
            formData.append('current_images[]', image);
        });

        // Lấy các file đã chọn từ fileinput
        const fileInput = document.getElementById('newImages');
        if (fileInput.files.length > 0) {
            for (let i = 0; i < fileInput.files.length; i++) {
                formData.append('new_images[]', fileInput.files[i]);
            }
        }
        
        const response = await axios.post(`/posts/${props.post.id}/update`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        
        if (response.status === 200) {
            emit('updated', response.data.post);
            // Đóng modal
            $(`#modal-update${props.post.id}`).modal('hide');
        }
    } catch (error) {
        console.error('Error updating post:', error);
    } finally {
        isUpdating.value = false;
    }
};

onMounted(() => {
    // Khởi tạo fileinput
    $('#newImages').fileinput({
        theme: 'fa',
        language: 'vi',
        showUpload: false,
        showCaption: true,
        showRemove: true,
        showPreview: true,
        allowedFileExtensions: ['jpg', 'jpeg', 'png', 'gif'],
        maxFileSize: 5000, // 5MB
        maxFilesNum: 10,
        msgPlaceholder: 'Chọn ảnh để tải lên...',
        browseClass: 'btn btn-primary',
        removeClass: 'btn btn-danger',
        mainClass: 'input-group-lg',
        uploadUrl: false, // Không upload tự động
        initialPreview: currentImages.value.map(image => '/images/client/post/' + image),
        initialPreviewConfig: currentImages.value.map(image => ({
            caption: image.split('/').pop(),
            key: image,
            url: `/posts/${props.post.id}/media/delete`,
            extra: {
                id: image
            }
        }))
    });

    // Xử lý sự kiện khi xóa ảnh
    $('#newImages').on('filebeforedelete', function(event, key, jqXHR, data) {
        if (currentImages.value.includes(key)) {
            // Nếu là ảnh hiện tại, xóa khỏi danh sách
            const index = currentImages.value.indexOf(key);
            if (index > -1) {
                currentImages.value.splice(index, 1);
            }
        }
    });
});
</script>

<style scoped>
.current-images img {
    max-height: 200px;
    object-fit: cover;
    width: 100%;
}

.position-relative {
    position: relative;
}

.position-absolute {
    position: absolute;
}

.top-0 {
    top: 0;
}

.end-0 {
    right: 0;
}

.m-1 {
    margin: 0.25rem;
}
</style> 