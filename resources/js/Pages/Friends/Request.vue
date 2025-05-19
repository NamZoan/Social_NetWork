<template>
    <Index>
        <div class="col-md-9 second-section" id="page-content-wrapper">
            <div class="card-body">
                <ul class="request-list list-inline m-0 p-0 d-flex flex-wrap">
                    <li v-for="user in requests" :key="user.id" class="d-flex align-items-center flex-wrap col-6">
                        <div class="user-img img-fluid flex-shrink-0">
                            <Link :href="`/${user.username}`" class="btn btn-quick-link join-group-btn border w-100">
                            <img
                                :src="user.avatar ? `/images/client/avatar/${user.avatar}` : '/images/web/users/avatar.jpg'"
                                alt="avatar"
                                class="rounded-circle avatar-40"
                            >
                            </Link>

                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6>{{ user.name }}</h6>
                            <p class="mb-0">@{{ user.username }}</p>
                        </div>
                        <div class="d-flex align-items-center mt-2 mt-md-0">
                            <a href="#" class="me-3 btn btn-primary rounded"
                               @click.prevent="acceptRequest(user.id)">Xác Nhận</a>
                            <a href="#" class="btn btn-secondary rounded"
                               @click.prevent="rejectRequest(user.id)">Từ Chối</a>
                        </div>
                    </li>
                    <li v-if="!requests || requests.length === 0" class="d-block text-center mb-0 pb-0 text-muted">
                        Không có lời mời kết bạn nào.
                    </li>
                </ul>
            </div>
        </div>
    </Index>
</template>

<script setup>
import Index from './Index.vue';
import { router,Link } from '@inertiajs/vue3';

const props = defineProps({
    requests: {
        type: Array,
        default: () => []
    }
});

function acceptRequest(userId) {
    router.post('/accept-friend-request', { user_id: userId }, {
        onSuccess: () => window.location.reload()
    });
}

function rejectRequest(userId) {
    router.post('/unfriend', { user_id: userId }, {
        onSuccess: () => window.location.reload()
    });
}
</script>
<style scoped>
@import '../../../css/socialv.css';
</style>
