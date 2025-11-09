<template>
  <div class="message-contacts">
    <ul class="conversations">
      <li
        v-for="conversation in filteredConversations"
        :key="conversation.id"
        class="contact"
        :class="{ 'messenger-user-active': selectedConversationId == conversation.id }"
        @click="open(conversation)"
      >
        <div class="wrap">
          <span class="contact-status" :class="getStatusClass(conversation)"></span>
          <img :src="getConversationAvatar(conversation)" alt="Conversation" />
          <div class="meta">
            <p class="name">{{ getConversationName(conversation) }}</p>
            <p class="preview">{{ getLastMessage(conversation) }}</p>
          </div>
        </div>
      </li>

      <li v-if="filteredConversations.length === 0" class="no-results">
        Không tìm thấy cuộc trò chuyện nào
      </li>
    </ul>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { usePage, router } from '@inertiajs/vue3'

const props = defineProps({
  conversations: { type: Array, required: true },
  // cho phép Number hoặc String (phòng khi ID là uuid)
  selectedConversationId: { type: [Number, String], default: null },
  searchQuery: { type: String, default: '' },
})

const emit = defineEmits(['select-conversation'])

const page = usePage()
const currentUserId = page.props.user?.id || page.props.auth?.user?.id

// ✅ Hàm click: điều hướng SPA + emit cho cha
function open(conversation) {
  // đổi URL sang /messages/:id mà không reload
  router.visit(`/messages/${conversation.id}`, {
    preserveState: true,
    preserveScroll: true,
    // chỉ xin lại props cần thiết từ server (nếu bạn có dùng partial reload)
    // only: ['messages', 'selectedConversationId'],
  })
  emit('select-conversation', conversation)
}

const filteredConversations = computed(() => {
  if (!props.searchQuery) return props.conversations
  const q = props.searchQuery.toLowerCase()
  return props.conversations.filter(c => {
    const name = getConversationName(c).toLowerCase()
    const last = getLastMessage(c).toLowerCase()
    return name.includes(q) || last.includes(q)
  })
})

const getStatusClass = (conversation) => {
  if (conversation.conversation_type === 'group') return 'group'
  return isUserOnline(conversation) ? 'online' : 'offline'
}

const getConversationAvatar = (conversation) => {
  if (conversation.conversation_type === 'group') {
    return conversation.image
      ? `/images/client/group/conversation/${conversation.image}`
      : '/images/web/groups/group.webp'
  } else {
    const other = conversation.members?.find(m => m.id !== currentUserId)
    return other?.avatar
      ? `/images/client/avatar/${other.avatar}`
      : '/images/web/users/avatar.jpg'
  }
}

const getConversationName = (conversation) => {
  if (conversation.conversation_type === 'group') return conversation.name
  const other = conversation.members?.find(m => m.id !== currentUserId)
  return other?.name || 'Unknown User'
}

const getLastMessage = (conversation) => {
  // ưu tiên lastMessage backend trả; fallback phần tử cuối trong messages
  const last = conversation.last_message ?? conversation.lastMessage ?? (
    Array.isArray(conversation.messages) && conversation.messages.length
      ? conversation.messages[conversation.messages.length - 1]
      : null
  )
  if (!last) return 'Chưa có tin nhắn nào'

  if (conversation.conversation_type === 'group') {
    const sender = conversation.members?.find(m => m.id === last.sender_id)
    const senderName = sender?.id === currentUserId ? 'Bạn' : (sender?.name || 'Ai đó')
    return `${senderName}: ${last.content ?? '[Tệp đính kèm]'}`
  }
  return last.content ?? '[Tệp đính kèm]'
}

const isUserOnline = (conversation) => {
  if (conversation.conversation_type === 'group') return false
  const other = conversation.members?.find(m => m.id !== currentUserId)
  return other?.is_online || false
}
</script>

<style scoped>
@import '../../../../css/messenger.css';

.contact{cursor:pointer;transition:background-color .3s;padding:10px;border-bottom:1px solid #eee}
.contact:hover{background:#f5f5f5}
.messenger-user-active{background:#e9ecef}
.wrap{display:flex;align-items:center;gap:10px}
.contact-status{width:10px;height:10px;border-radius:50%;margin-right:5px}
.contact-status.online{background:#28a745}
.contact-status.offline{background:#dc3545}
.contact-status.group{background:#007bff}
.meta{flex:1;min-width:0}
.name{font-weight:600;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.preview{color:#666;margin:0;font-size:.9em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
img{width:40px;height:40px;border-radius:50%;object-fit:cover}
.no-results{padding:20px;text-align:center;color:#666;font-style:italic}
</style>
