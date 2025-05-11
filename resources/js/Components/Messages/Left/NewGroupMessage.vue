<template>
    <div class="modal fade" id="newGroupConversation" tabindex="-1" role="dialog"
        aria-labelledby="newGroupConversationLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header new-msg-header">
                    <h5 class="modal-title" id="newGroupConversationLabel">Tạo cuộc trò chuyện nhóm</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body new-msg-body">
                    <div class="form-group">
                        <label for="group-name" class="col-form-label">Tên nhóm</label>
                        <input type="text" class="form-control search-input" id="group-name" v-model="groupName"
                            placeholder="Nhập tên nhóm...">
                    </div>

                    <div class="form-group">
                        <label class="col-form-label">Chọn thành viên</label>
                        <div class="search-container">
                            <input type="text" class="form-control search-input" v-model="searchQuery"
                                @input="searchFriends" placeholder="Tìm kiếm bạn bè...">
                            <div class="search-results" v-if="showResults && filteredFriends.length > 0">
                                <div v-for="friend in filteredFriends" :key="friend.id" class="search-item"
                                    @click="addMember(friend)">
                                    <img :src="friend.avatar ? `/images/client/avatar/${friend.avatar}` : '/images/web/users/avatar.jpg'"
                                        :alt="friend.name">
                                    <span>{{ friend.name }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="selected-members" v-if="selectedMembers.length > 0">
                        <div class="selected-member" v-for="member in selectedMembers" :key="member.id">
                            <img :src="member.avatar ? `/images/client/avatar/${member.avatar}` : '/images/web/users/avatar.jpg'"
                                :alt="member.name">
                            <span>{{ member.name }}</span>
                            <button @click="removeMember(member)" class="remove-member">&times;</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer new-msg-footer">
                    <button type="button" class="btn btn-primary btn-sm" @click="createGroup">Tạo nhóm</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const groupName = ref('');
const searchQuery = ref('');
const showResults = ref(false);
const friends = ref([]);
const filteredFriends = ref([]);
const selectedMembers = ref([]);



const searchFriends = () => {
    if (!searchQuery.value.trim()) {
        filteredFriends.value = [];
        showResults.value = false;
        return;
    }

    const query = searchQuery.value.toLowerCase();
    filteredFriends.value = friends.value.filter(friend =>
        friend.name.toLowerCase().includes(query) &&
        !selectedMembers.value.some(member => member.id === friend.id)
    );
    showResults.value = true;
};

const addMember = (friend) => {
    if (!selectedMembers.value.some(member => member.id === friend.id)) {
        selectedMembers.value.push(friend);
    }
    searchQuery.value = '';
    showResults.value = false;
};

const removeMember = (member) => {
    selectedMembers.value = selectedMembers.value.filter(m => m.id !== member.id);
};

const createGroup = async () => {
    if (!groupName.value.trim()) {
        alert('Vui lòng nhập tên nhóm');
        return;
    }

    if (selectedMembers.value.length < 2) {
        alert('Vui lòng chọn ít nhất 2 thành viên');
        return;
    }

    try {
        const response = await axios.post('/messages/createGroup', {
            name: groupName.value.trim(),
            members: selectedMembers.value.map(member => member.id)
        });

        if (response.data.success) {
            // Reset form
            groupName.value = '';
            selectedMembers.value = [];
            searchQuery.value = '';

            // Close modal using jQuery
            $('#newGroupConversation').modal('hide');

            // Emit event to parent
            emit('group-created', response.data.conversation);
        } else {
            alert(response.data.message || 'Có lỗi xảy ra khi tạo nhóm');
        }
    } catch (error) {
        console.error('Error creating group:', error);
        alert(error.response?.data?.message || 'Có lỗi xảy ra khi tạo nhóm. Vui lòng thử lại.');
    }
};

const loadFriends = async () => {
    try {
        const response = await axios.get('/message/getFriends');
        friends.value = response.data;
    } catch (error) {
        console.error('Error loading friends:', error);
    }
};

onMounted(() => {
    loadFriends();
});

const emit = defineEmits(['group-created']);
</script>

<style scoped>
.search-container {
    position: relative;
    margin-bottom: 15px;
}

.search-input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
}

.search-input:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.search-results {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #ced4da;
    border-radius: 4px;
    margin-top: 4px;
    max-height: 200px;
    overflow-y: auto;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #eee;
}

.search-item:last-child {
    border-bottom: none;
}

.search-item:hover {
    background-color: #f8f9fa;
}

.search-item img {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
}

.search-item span {
    font-size: 14px;
    color: #333;
}

.selected-members {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin: 10px 0;
    padding: 5px;
    border: 1px solid #eee;
    border-radius: 4px;
    min-height: 40px;
}

.selected-member {
    display: flex;
    align-items: center;
    gap: 5px;
    background: #e9ecef;
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 13px;
}

.selected-member img {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    object-fit: cover;
}

.selected-member span {
    color: #495057;
}

.remove-member {
    background: none;
    border: none;
    color: #dc3545;
    cursor: pointer;
    padding: 0 5px;
    font-size: 16px;
    line-height: 1;
}

.remove-member:hover {
    color: #c82333;
}
</style>