<template>

    <div class="modal fade" id="newConversation" tabindex="-1" role="dialog" aria-labelledby="newConversationLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header new-msg-header">
                    <h5 class="modal-title" id="newConversationLabel">Cuộc hội thoại mới</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="" method="" class="new-msg-form">

                    <div class="modal-body new-msg-body">

                        <div class="form-group">
                            <label for="recipient-name" class="col-form-label">Người nhận</label>
                            <div class="input-group" ref="inputRef">
                                <input type="text" class="form-control search-input" id="recipient-name"
                                    v-model="searchQuery" @input="searchFriends" @focus="handleFocus"
                                    @click="handleFocus" placeholder="Tên người nhận">
                                <div class="dropdown-menu notify-drop nav-drop shadow-sm w-100" v-show="showFriendList"
                                    style="display: block; position: absolute; top: 100%; left: 0; width: 100%;">
                                    <div class="notify-drop-title">
                                        <div class="row">
                                            <div class="col-md-6 col-sm-6 col-xs-6 fs-8">
                                                Danh sách bạn bè <span class="badge badge-pill badge-primary ml-2">{{
                                                    filteredFriends.length }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="drop-content">
                                        <div v-if="isLoading" class="dropdown-item text-center">
                                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                <span class="sr-only">Loading...</span>
                                            </div>
                                            Đang tải danh sách bạn bè...
                                        </div>
                                        <div v-else-if="filteredFriends.length === 0" class="dropdown-item text-center">
                                            Không tìm thấy bạn bè
                                        </div>
                                        <div v-else v-for="friend in filteredFriends" :key="friend.id"
                                            class="dropdown-item" @click="selectFriend(friend)">
                                            <div class="row">
                                                <div class="col-md-2 col-sm-2 col-xs-2">
                                                    <div class="notify-img">
                                                        <img :src="friend.avatar ? `/images/client/avatar/${friend.avatar}` : '/images/web/users/avatar.jpg'"
                                                            alt="Friend avatar" @error="handleImageError">
                                                    </div>
                                                </div>
                                                <div class="col-md-10 col-sm-10 col-xs-10">
                                                    <a href="#" class="notification-user">{{ friend.name }}</a>
                                                    <p class="time">
                                                        {{ friend.last_active ? `Hoạt động ${friend.last_active}` :
                                                            'Không hoạt động' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="message-text" class="col-form-label">Tin nhắn</label>
                            <textarea class="form-control search-input" rows="5" id="message-text" v-model="messageText"
                                placeholder="Nhập tin nhắn..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer new-msg-footer">
                        <button type="button" class="btn btn-primary btn-sm" @click="sendMessage"
                            :disabled="!selectedFriend || !messageText.trim()">Gửi tin nhắn</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const searchQuery = ref('');
const friends = ref([]);
const filteredFriends = ref([]);
const showFriendList = ref(false);
const selectedFriend = ref(null);
const inputRef = ref(null);
const messageText = ref('');
const isLoading = ref(false);



function removeVietnameseTones(str) {
    return str.normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '') // loại bỏ dấu
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .toLowerCase();
}

const searchFriends = () => {
    const query = removeVietnameseTones(searchQuery.value.trim());

    if (query === '') {
        filteredFriends.value = friends.value;
        console.log(filteredFriends.value);
    } else {
        filteredFriends.value = friends.value.filter(friend => {
            const name = removeVietnameseTones(friend.name);
            return name.includes(query);
        });
        console.log(filteredFriends.value);

    }

    showFriendList.value = true;
};



const selectFriend = (friend) => {
    selectedFriend.value = friend;
    searchQuery.value = friend.name;
    showFriendList.value = false;
};

const handleClickOutside = (event) => {
    console.log('Click outside event triggered');
    if (inputRef.value && !inputRef.value.contains(event.target)) {
        showFriendList.value = false;
    }
};

const handleFocus = () => {
    console.log('Focus event triggered');
    if (friends.value.length > 0) {
        showFriendList.value = true;
        searchFriends();
    }
};




const fetchFriends = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('message/getFriends');
        if (Array.isArray(response.data)) {
            friends.value = response.data;
            filteredFriends.value = response.data;
        } else {
            console.error('Invalid response format:', response.data);
        }
    } catch (error) {
        console.error('Error fetching friends:', error.response || error);
    } finally {
        isLoading.value = false;
    }
};

const sendMessage = async () => {
    if (!selectedFriend.value || !messageText.value.trim()) return;

    try {
        const response = await axios.post('/messages/send', {
            recipient_id: selectedFriend.value.id,
            content: messageText.value,
        });

        // Emit event to parent to update conversation list
        window.dispatchEvent(new CustomEvent('new-conversation', {
            detail: response.data.conversation
        }));

        // Close modal and reset form
        $('#newConversation').modal('hide');
        messageText.value = '';
        searchQuery.value = '';
        selectedFriend.value = null;
    } catch (error) {
        console.error('Error sending message:', error);
        alert('Lỗi khi gửi tin nhắn. Vui lòng thử lại.');
    }
};

const handleImageError = (e) => {
    e.target.src = '/images/web/users/avatar.jpg';
};

onMounted(() => {
    fetchFriends();
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Thêm watch để tự động lọc khi searchQuery thay đổi
watch(searchQuery, () => {
    searchFriends();
});

</script>

<style scoped>
.input-group {
    position: relative;
}

.dropdown-menu {
    max-height: 300px;
    overflow-y: auto;
    position: absolute;
    width: 100%;
    z-index: 1000;
    background: white;
    border: 1px solid rgba(0, 0, 0, .15);
    border-radius: 0.25rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, .175);
    margin-top: 0.5rem;
}

.notify-img img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
}

.dropdown-item {
    cursor: pointer;
    padding: 0.5rem 1rem;
    clear: both;
    font-weight: 400;
    color: #212529;
    text-align: inherit;
    white-space: nowrap;
    background-color: transparent;
    border: 0;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
}

.notify-drop-title {
    padding: 0.5rem 1rem;
    border-bottom: 1px solid #dee2e6;
}

.drop-content {
    padding: 0.5rem 0;
}
</style>
