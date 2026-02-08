<template>
    <App>
        <div class="row newsfeed-right-side-content mt-3">
            <Left />
            <div class="col-md-9 main-content">
                <Request :received-requests="receivedRequests" :sent-requests="sentRequests" />
            <div class="col-md-12 second-section">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bx bx-user-check me-2"></i>Danh sách bạn bè
                        </h5>
                        <span class="badge bg-primary">{{ friends.total }} bạn</span>
                    </div>
                    <div class="card-body">
                        <!-- Search Friends -->
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="bx bx-search"></i>
                                </span>
                                <input 
                                    v-model="searchQuery" 
                                    type="text" 
                                    class="form-control" 
                                    placeholder="Tìm kiếm bạn bè..."
                                />
                            </div>
                        </div>

                        <!-- Friends List -->
                        <div v-if="filteredFriends.length > 0" class="friends-grid">
                            <div 
                                v-for="friend in filteredFriends" 
                                :key="friend.id" 
                                class="friend-card"
                            >
                                <div class="friend-header">
                                    <Link 
                                        :href="`/${friend.username}`"
                                        class="friend-avatar-link"
                                    >
                                        <img 
                                            :src="getAvatarUrl(friend.avatar)"
                                            :alt="friend.name"
                                            class="friend-avatar"
                                        />
                                    </Link>
                                </div>
                                <div class="friend-info">
                                    <Link 
                                        :href="`/${friend.username}`"
                                        class="friend-name-link"
                                    >
                                        <h6 class="friend-name">{{ friend.name }}</h6>
                                    </Link>
                                    <p class="friend-username">@{{ friend.username }}</p>
                                </div>
                                <div class="friend-actions">
                                    <button 
                                        @click="sendMessage(friend)"
                                        class="btn btn-sm btn-outline-primary"
                                        title="Gửi tin nhắn"
                                    >
                                        <i class="bx bx-message-dots me-1"></i>Nhắn tin
                                    </button>
                                    <button 
                                        @click="unfriend(friend.id)"
                                        class="btn btn-sm btn-outline-danger"
                                        title="Hủy kết bạn"
                                        :disabled="loadingStates[friend.id]"
                                    >
                                        <span v-if="loadingStates[friend.id]" class="spinner-border spinner-border-sm me-1"></span>
                                        <i v-else class="bx bx-user-minus me-1"></i>{{ loadingStates[friend.id] ? 'Đang...' : 'Hủy' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-else class="text-center py-5">
                            <div class="empty-state">
                                <i class="bx bx-user-off empty-icon"></i>
                                <p class="text-muted mt-3">
                                    {{ searchQuery ? 'Không tìm thấy bạn bè nào' : 'Bạn chưa có bạn bè nào' }}
                                </p>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <nav v-if="friends.last_page > 1" aria-label="Page navigation" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <li 
                                    :class="['page-item', { disabled: !friends.prev_page_url }]"
                                >
                                    <Link 
                                        v-if="friends.prev_page_url"
                                        :href="friends.prev_page_url"
                                        class="page-link"
                                    >
                                        <i class="bx bx-chevron-left"></i> Trước
                                    </Link>
                                    <span v-else class="page-link">
                                        <i class="bx bx-chevron-left"></i> Trước
                                    </span>
                                </li>

                                <li 
                                    v-for="page in generatePageNumbers()"
                                    :key="page"
                                    :class="['page-item', { active: page === friends.current_page }]"
                                >
                                    <Link 
                                        v-if="page !== friends.current_page"
                                        :href="`/friends?page=${page}`"
                                        class="page-link"
                                    >
                                        {{ page }}
                                    </Link>
                                    <span v-else class="page-link">
                                        {{ page }}
                                    </span>
                                </li>

                                <li 
                                    :class="['page-item', { disabled: !friends.next_page_url }]"
                                >
                                    <Link 
                                        v-if="friends.next_page_url"
                                        :href="friends.next_page_url"
                                        class="page-link"
                                    >
                                        Sau <i class="bx bx-chevron-right"></i>
                                    </Link>
                                    <span v-else class="page-link">
                                        Sau <i class="bx bx-chevron-right"></i>
                                    </span>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div></div>
            
        </div>
    </App>
</template>

<script setup>
import { ref, computed, defineAsyncComponent, reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import axios from 'axios';
import App from '../../Layouts/App.vue';
import Left from '../../Components/Left.vue';

// Lazy load Request component
const Request = defineAsyncComponent(() => 
    import('./Request.vue')
);

const props = defineProps({
    friends: {
        type: Object,
        default: () => ({
            data: [],
            total: 0,
            per_page: 15,
            current_page: 1,
            last_page: 1,
            next_page_url: null,
            prev_page_url: null
        })
    },
    receivedRequests: {
        type: Array,
        default: () => []
    },
    sentRequests: {
        type: Array,
        default: () => []
    }
});

const searchQuery = ref('');
const friendsList = reactive(props.friends.data || []);
const loadingStates = reactive({});

// Lọc bạn bè dựa trên tìm kiếm
const filteredFriends = computed(() => {
    if (!searchQuery.value.trim()) {
        return friendsList || [];
    }

    const query = searchQuery.value.toLowerCase();
    return (friendsList || []).filter(friend =>
        friend.name.toLowerCase().includes(query) ||
        friend.username.toLowerCase().includes(query)
    );
});

// Hàm lấy URL avatar với mặc định
const getAvatarUrl = (avatar) => {
    if (!avatar) return '/images/web/users/avatar.jpg';
    if (avatar.startsWith('http')) return avatar;
    if (avatar.startsWith('/')) return avatar;
    return `/images/client/avatar/${avatar}`;
};

// Hủy kết bạn
const unfriend = async (userId) => {
    if (confirm('Bạn có chắc muốn hủy kết bạn với người này?')) {
        loadingStates[userId] = true;
        try {
            await axios.post('/unfriend', { user_id: userId });
            // Xóa khỏi danh sách bạn bè
            const index = friendsList.findIndex(f => f.id === userId);
            if (index > -1) {
                friendsList.splice(index, 1);
            }
        } catch (error) {
            const message = error.response?.data?.message || 'Hủy kết bạn thất bại!';
            alert(message);
        } finally {
            loadingStates[userId] = false;
        }
    }
};

// Gửi tin nhắn
const sendMessage = (friend) => {
    router.visit(`/messages?user=${friend.id}`);
};

// Tạo danh sách số trang
const generatePageNumbers = () => {
    const pages = [];
    const maxPages = Math.min(props.friends.last_page, 5);
    const currentPage = props.friends.current_page;

    if (maxPages <= 5) {
        for (let i = 1; i <= maxPages; i++) {
            pages.push(i);
        }
    } else {
        if (currentPage <= 3) {
            for (let i = 1; i <= 5; i++) {
                pages.push(i);
            }
        } else if (currentPage >= props.friends.last_page - 2) {
            for (let i = props.friends.last_page - 4; i <= props.friends.last_page; i++) {
                pages.push(i);
            }
        } else {
            for (let i = currentPage - 2; i <= currentPage + 2; i++) {
                pages.push(i);
            }
        }
    }

    return pages;
};
</script>

<style scoped>
.friends-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
    margin-top: 20px;
}

.friend-card {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 15px;
    text-align: center;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
}

.friend-card:hover {
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    border-color: #dee2e6;
}

.friend-header {
    margin-bottom: 12px;
}

.friend-avatar-link {
    text-decoration: none;
    display: inline-block;
}

.friend-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid white;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease;
}

.friend-avatar-link:hover .friend-avatar {
    transform: scale(1.1);
}

.friend-info {
    flex: 1;
    margin-bottom: 12px;
}

.friend-name-link {
    text-decoration: none;
    color: inherit;
}

.friend-name {
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 4px 0;
    color: #212529;
}

.friend-username {
    font-size: 12px;
    color: #6c757d;
    margin: 0;
}

.friend-actions {
    display: flex;
    gap: 6px;
    justify-content: center;
}

.friend-actions .btn {
    font-size: 12px;
    padding: 4px 8px;
    flex: 1;
}

.empty-icon {
    font-size: 64px;
    color: #dee2e6;
}

.pagination {
    margin-top: 20px;
}

.input-group {
    margin-bottom: 20px;
}

.card-header {
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

@media (max-width: 768px) {
    .friends-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .friend-card {
        padding: 12px;
    }

    .friend-avatar {
        width: 60px;
        height: 60px;
    }

    .friend-actions {
        flex-direction: column;
    }

    .friend-actions .btn {
        width: 100%;
    }
}
</style>
