<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Upload DOCX Template</h1>
    <form @submit.prevent="submit" class="flex flex-col gap-4">
      <input v-model="form.name" type="text" class="w-full border rounded px-4 py-2" placeholder="Template Name" required />
      <input type="file" @change="onFileChange" accept=".docx" class="border p-2 mb-4" />
      <br>
      <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded">Upload</button>
    </form>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  name: null,
  file: null
})

const onFileChange = (e) => {
  form.file = e.target.files[0]
}

const submit = () => {
  form.post('/templates/store')
}
</script>
