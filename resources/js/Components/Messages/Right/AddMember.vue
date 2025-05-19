<template>
    <div>
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
        <div class="selected-members" v-if="selectedMembers.length > 0">
            <div class="selected-member" v-for="member in selectedMembers" :key="member.id">
                <img :src="member.avatar ? `/images/client/avatar/${member.avatar}` : '/images/web/users/avatar.jpg'"
                    :alt="member.name">
                <span>{{ member.name }}</span>
                <button @click="removeMember(member)" class="remove-member">&times;</button>
            </div>
        </div>
        <div class="modal-footer new-msg-footer">
            <button type="button" class="btn btn-primary btn-sm" @click="addGroup" :disabled="selectedMembers.length === 0">
                Thêm Thành Viên
            </button>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    conversationId: {
        type: [String, Number],
        required: true
    },
    conversation: {
        type: Object,
        required: true
    }
});
const emit = defineEmits(['close']);

const searchQuery = ref('');
const showResults = ref(false);
const friends = ref([]);
const filteredFriends = ref([]);
const selectedMembers = ref([]);
const currentMembers = ref([]);

// Lấy danh sách bạn bè và thành viên hiện tại của nhóm
const loadFriendsAndMembers = async () => {
    try {
        // Lấy danh sách bạn bè
        const friendsRes = await axios.get('/message/getFriends');
        friends.value = friendsRes.data;

        // Lấy danh sách thành viên hiện tại của nhóm
        const membersRes = await axios.get(`/conversations/${props.conversationId}/members`);
        currentMembers.value = membersRes.data.map(m => m.id);
    } catch (error) {
        console.error('Không thể lấy danh sách bạn bè hoặc thành viên nhóm', error);
    }
};

onMounted(() => {
    loadFriendsAndMembers();
});

// Lọc bạn bè chưa có trong nhóm
const searchFriends = () => {
    if (!searchQuery.value.trim()) {
        filteredFriends.value = [];
        showResults.value = false;
        return;
    }
    const query = searchQuery.value.toLowerCase();
    filteredFriends.value = friends.value.filter(friend =>
        friend.name.toLowerCase().includes(query) &&
        !currentMembers.value.includes(friend.id) && // loại người đã trong nhóm
        !selectedMembers.value.some(member => member.id === friend.id) // loại người đã chọn
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

const addGroup = async () => {
    if (selectedMembers.value.length === 0) return;
    try {
        await axios.post(`conversations/${props.conversationId}/add-members`, {
            member_ids: selectedMembers.value.map(m => m.id)
        });
        alert('Đã thêm thành viên vào nhóm!');
        emit('close');
    } catch (error) {
        alert('Thêm thành viên thất bại!');
    }
};

// Lấy tất cả bạn bè chưa trong nhóm
const availableFriends = computed(() =>
    friends.value.filter(friend =>
        !currentMembers.value.includes(friend.id)
    )
);


// Nếu conversationId thay đổi thì reload lại danh sách
watch(() => props.conversationId, () => {
    selectedMembers.value = [];
    searchQuery.value = '';
    showResults.value = false;
    loadFriendsAndMembers();
});
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
