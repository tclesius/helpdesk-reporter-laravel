<script setup>
import BreezeButton from '@/Components/Button.vue'
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import BreezeInput from '@/Components/Input.vue'
import BreezeLabel from '@/Components/Label.vue'
import BreezeValidationErrors from '@/Components/ValidationErrors.vue'
import { Head, useForm } from '@inertiajs/vue3'

const props = defineProps({
  email: String,
  token: String,
})

const form = useForm({
  token: props.token,
  email: props.email,
  password: '',
  password_confirmation: '',
})

const submit = () => {
  // eslint-disable-next-line no-undef
  form.post(route('password.update'), {
    onFinish: () => form.reset('password', 'password_confirmation'),
  })
}
</script>

<template>
  <BreezeGuestLayout>
    <Head><title>Reset Password</title></Head>

    <BreezeValidationErrors class="mb-4" />

    <form @submit.prevent="submit">
      <div>
        <BreezeLabel
          for="email"
          value="Email"
        />
        <BreezeInput
          id="email"
          v-model="form.email"
          type="email"
          class="mt-1 block w-full"
          required
          autofocus
          autocomplete="username"
        />
      </div>

      <div class="mt-4">
        <BreezeLabel
          for="password"
          value="Password"
        />
        <BreezeInput
          id="password"
          v-model="form.password"
          type="password"
          class="mt-1 block w-full"
          required
          autocomplete="new-password"
        />
      </div>

      <div class="mt-4">
        <BreezeLabel
          for="password_confirmation"
          value="Confirm Password"
        />
        <BreezeInput
          id="password_confirmation"
          v-model="form.password_confirmation"
          type="password"
          class="mt-1 block w-full"
          required
          autocomplete="new-password"
        />
      </div>

      <div class="flex items-center justify-end mt-4">
        <BreezeButton
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
        >
          Reset Password
        </BreezeButton>
      </div>
    </form>
  </BreezeGuestLayout>
</template>
