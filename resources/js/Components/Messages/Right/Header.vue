<template>
    <div class="col-md-12">
        <div class="message-header d-flex justify-content-between align-items-center">
            <div class="wrap d-flex align-items-center">
                <span class="contact-status online"></span>
                <img :src="getOtherUserAvatar" alt="Conversation user" />
                <div class="meta">
                    <p class="name">{{ getOtherUserName }}</p>
                    <p class="preview">Active now</p>
                </div>
            </div>
            <div>
                <!-- Nếu là cá nhân: hiện nút xóa -->
                <button v-if="props.conversation && props.conversation.conversation_type === 'individual'"
                    class="btn btn-link p-0" @click="deleteConversation" title="Xóa cuộc trò chuyện">
                    <i class="bx bx-trash-alt" style="font-size: 24px;"></i>
                </button>
                <!-- Nếu là nhóm: hiện nút thêm thành viên và rời nhóm -->
                <template v-if="props.conversation && props.conversation.conversation_type === 'group'">
                    <button class="btn btn-link p-0 ms-2" @click="showMembers = true" title="Xem danh sách thành viên">
                        <i class="bx bx-group" style="font-size: 22px;"></i>
                    </button>
                    <button class="btn btn-link p-0" @click="showAddMember = true" title="Thêm thành viên">
                        <i class="bx bx-plus-circle" style="font-size: 24px;"></i>
                    </button>
                    <button class="btn btn-link p-0" @click="leaveGroup" title="Rời nhóm">
                        <i class="bx bx-log-out" style="font-size: 24px;"></i>
                    </button>
                    <button v-if="isCreator" class="btn btn-link p-0" title="Xóa nhóm" @click="deleteGroup">
                        <i class="bx bx-trash" style="font-size: 22px;"></i>
                    </button>
                    <!-- Thêm vào phần nút, chỉ hiện với creator -->
                    <button v-if="isCreator" class="btn btn-link p-0" title="Cập nhật nhóm" @click="showEditGroup = true">
                        <i class="bx bx-edit" style="font-size: 22px;"></i>
                    </button>
                </template>
            </div>
        </div>
        <!-- Modal thêm thành viên -->
        <div v-if="showAddMember">
            <div class="modal-backdrop fade show"></div>
            <div class="modal d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Thêm thành viên</h5>
                            <button type="button" class="close" @click="showAddMember = false">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <AddMember :conversationId="props.conversation.id" @close="showAddMember = false" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal danh sách thành viên -->
        <div v-if="showMembers">
            <div class="modal-backdrop fade show"></div>
            <div class="modal d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Danh sách thành viên</h5>
                            <button type="button" class="close" @click="showMembers = false">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <ul class="list-group">
                                <li v-for="member in props.conversation.members" :key="member.id"
                                    class="list-group-item d-flex align-items-center">
                                    <img :src="member.avatar ? `/images/client/avatar/${member.avatar}` : '/images/web/users/avatar.jpg'"
                                        alt="avatar"
                                        style="width:32px;height:32px;border-radius:50%;margin-right:10px;">
                                    <span>{{ member.name }}</span>
                                    <!-- Nút xóa thành viên, chỉ hiển thị với người tạo nhóm -->
                                    <button v-if="isCreator" class="btn btn-link p-0 ms-auto"
                                        @click="removeMember(member.id)" title="Xóa thành viên khỏi nhóm">
                                        <i class="bx bx-trash" style="font-size: 18px;color:#dc3545;"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal cập nhật nhóm -->
        <div v-if="showEditGroup">
            <div class="modal-backdrop fade show"></div>
            <div class="modal d-block" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cập nhật nhóm</h5>
                            <button type="button" class="close" @click="showEditGroup = false">
                                <span>&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form @submit.prevent="updateGroup">
                                <div class="form-group mb-2">
                                    <label>Tên nhóm</label>
                                    <input v-model="editGroupName" class="form-control" />
                                </div>
                                <div class="form-group mb-2">
                                    <label>Ảnh nhóm</label>
                                    <input type="file" @change="onImageChange" class="form-control" />
                                    <img v-if="previewImage" :src="previewImage" alt="preview"
                                        style="max-width:100px;margin-top:10px;" />
                                </div>
                                <button class="btn btn-primary btn-sm" type="submit">Lưu thay đổi</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed,watch } from 'vue';
import axios from 'axios';
import AddMember from '../Right/AddMember.vue'; // Đường dẫn đúng tới AddMember.vue
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const user = computed(() => page.props.auth.user);
const props = defineProps({
    conversation: {
        type: Object,
        required: false,
        default: () => ({
            members: []
        })
    }
});

const emit = defineEmits(['conversation-deleted', 'left-group', 'add-member', 'member-removed']);

const showAddMember = ref(false);
const showMembers = ref(false);
const showEditGroup = ref(false);

const editGroupName = ref('');
const previewImage = ref(null);

const getOtherUserAvatar = computed(() => {
    if (!props.conversation) return '/images/web/users/avatar.jpg';
    if (props.conversation.conversation_type === 'group') {
        return props.conversation.image
            ? `/images/client/group/conversation/${props.conversation.image}`
            : '/images/web/groups/group.webp';
    }
    // Cá nhân
    const otherUser = props.conversation.members[0];
    return otherUser?.avatar
        ? `/images/client/avatar/${otherUser.avatar}`
        : '/images/web/users/avatar.jpg';
});

const getOtherUserName = computed(() => {
    if (!props.conversation) return 'Unknown';
    if (props.conversation.conversation_type === 'group') {
        return props.conversation.name;
    }
    return props.conversation.members[0]?.name || 'Unknown User';
});


// Kiểm tra xem người dùng có phải là người tạo nhóm không
const isCreator = computed(() => {
    return props.conversation && props.conversation.creator_id === user.value.id;
});

console.log('isCreator:', isCreator.value);

// Xóa cuộc trò chuyện cá nhân
const deleteConversation = async () => {
    if (!props.conversation) return;
    if (!confirm('Bạn có chắc muốn xóa cuộc trò chuyện này?')) return;
    try {
        await axios.post(`/conversations/${props.conversation.id}/delete`);
        emit('conversation-deleted', props.conversation.id);
        window.location.reload(); // Thêm dòng này để reload lại trang
    } catch (e) {
        alert('Xóa cuộc trò chuyện thất bại!');
    }
};

// Rời nhóm
const leaveGroup = async () => {
    if (!props.conversation) return;
    if (!confirm('Bạn có chắc muốn rời nhóm này?')) return;
    try {
        await axios.post(`/conversations/${props.conversation.id}/leave`);
        emit('left-group', props.conversation.id);
    } catch (e) {
        alert('Rời nhóm thất bại!');
    }
};

// Xóa thành viên khỏi nhóm
const removeMember = async (memberId) => {
    if (!confirm('Bạn có chắc muốn xóa thành viên này khỏi nhóm?')) return;
    try {
        await axios.delete(`/conversations/${props.conversation.id}/members/${memberId}`);
        // Xóa thành viên khỏi danh sách trên giao diện
        const idx = props.conversation.members.findIndex(m => m.id === memberId);
        if (idx !== -1) props.conversation.members.splice(idx, 1);
        emit('member-removed', memberId);
    } catch (e) {
        alert('Xóa thành viên thất bại!');
    }
};
const deleteGroup = async () => {
    if (!props.conversation) return;
    if (!confirm('Bạn có chắc muốn xóa nhóm này?')) return;
    try {
        await axios.post(`/conversations/${props.conversation.id}/delete`);
        emit('conversation-deleted', props.conversation.id); // Xóa khỏi giao diện
        // window.location.reload(); // Nếu muốn reload toàn trang thì bỏ comment dòng này
    } catch (e) {
        alert('Xóa nhóm thất bại!');
    }
};

// Lấy danh sách thành viên nhóm
const fetchGroupMembers = async () => {
    if (!props.conversation) return;
    try {
        await axios.get(`/conversations/${props.conversation.id}/members`);
        // Xử lý dữ liệu thành viên nếu cần
    } catch (e) {
        console.error('Lỗi khi lấy danh sách thành viên nhóm:', e);
    }
};

// Gọi hàm lấy danh sách thành viên khi component được khởi tạo
fetchGroupMembers();

// Cập nhật nhóm
const updateGroup = async () => {
    if (!props.conversation) return;
    try {
        const formData = new FormData();
        formData.append('name', editGroupName.value);
        if (previewImage.value) {
            formData.append('image', previewImage.value);
        }
        await axios.post(`/conversations/${props.conversation.id}/update`, formData);
        emit('conversation-updated', { id: props.conversation.id, name: editGroupName.value });
        showEditGroup.value = false;
        // Cập nhật lại tên nhóm trên giao diện
        props.conversation.name = editGroupName.value;
    } catch (e) {
        alert('Cập nhật nhóm thất bại!');
    }
};

// Xử lý thay đổi ảnh nhóm
const onImageChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        previewImage.value = URL.createObjectURL(file);
    } else {
        previewImage.value = null;
    }
};

// Khởi tạo giá trị cho form sửa nhóm
const initEditGroup = () => {
    if (props.conversation) {
        editGroupName.value = props.conversation.name;
        previewImage.value = null;
    }
};

// Theo dõi sự thay đổi của showEditGroup để khởi tạo lại form khi mở modal
watch(showEditGroup, (newVal) => {
    if (newVal) {
        initEditGroup();
    }
});
</script>

<style scoped>
.message-header {
    padding: 15px;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.message-header .wrap {
    display: flex;
    align-items: center;
}

.message-header img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    margin-right: 15px;
}

.message-header .meta {
    flex: 1;
}

.message-header .name {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.message-header .preview {
    margin: 0;
    font-size: 12px;
    color: #666;
}

.contact-status {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 10px;
}

.contact-status.online {
    background: #2ecc71;
}

.group-members-list {
    display: flex;
    flex-wrap: wrap;
}

.group-members-list .badge {
    font-size: 12px;
    padding: 5px 10px;
    margin-right: 5px;
    margin-bottom: 5px;
}
</style>
