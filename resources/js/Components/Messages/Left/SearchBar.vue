<template>
    <div class="message-search">
        <div class="wrap">
            <input type="text" placeholder="Tìm kiếm cuộc trò chuyện ..." v-model="searchQuery" @input="search" />
            <button type="button" class="btn btn-create-conversation" data-toggle="modal"
                data-target="#newConversation">
                <i class='bx bx-pencil'></i>
            </button>

            <button type="button" class="btn btn-create-conversation" data-toggle="modal"
                data-target="#newGroupConversation">
                <i class='bx bxs-group'></i> </button>
        </div>
    </div>
    <NewMessage />
</template>

<script setup>
import { ref } from 'vue';
import NewMessage from './NewMessage.vue';

const searchQuery = ref('');

const search = () => {
    // Implement search functionality here
    console.log('Searching for:', searchQuery.value);
};
</script>

<style scoped>
.message-search {
    padding: 10px;
    background: #f5f5f5;
}

.message-search .wrap {
    position: relative;
    display: flex;
    align-items: center;
}

.message-search input {
    flex: 1;
    padding: 10px 35px 10px 15px;
    border: none;
    border-radius: 20px;
    background: #fff;
    font-size: 14px;
}

.search-icon {
    position: absolute;
    right: 50px;
    top: 50%;
    transform: translateY(-50%);
    color: #666;
}

.btn-create-conversation {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    background: #007bff;
    color: white;
    border: none;
    margin-left: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-create-conversation:hover {
    background: #0056b3;
}

.btn-create-conversation i {
    font-size: 18px;
}
</style>
