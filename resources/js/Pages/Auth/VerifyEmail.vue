<template>
  <div class="max-w-md mx-auto mt-10 p-6 border rounded">
    <h2 class="text-xl font-bold mb-2">Xác thực Email</h2>
    <p class="mb-4">
      Chúng tôi đã gửi một email xác thực tới <strong>{{ email }}</strong>.
      Vui lòng kiểm tra hộp thư và bấm vào liên kết để kích hoạt tài khoản.
    </p>

    <div class="space-y-3">
      <button
        class="px-4 py-2 rounded bg-blue-600 text-white"
        @click="resend"
        :disabled="form.processing"
      >
        Gửi lại email xác thực
      </button>

      <div v-if="$page.props.flash?.status" class="text-green-600">
        {{ $page.props.flash.status }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
  email: String,
});

const form = useForm({});

const resend = () => {
  form.post(route('verification.send'));
};
</script>
