<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-4">DOCX Templates</h1>

    <a href="/templates/create" class="bg-blue-500 text-white px-4 py-2 rounded mb-4 inline-block">
      Upload New Template
    </a>

    <table class="w-full border">
      <thead>
        <tr class="bg-gray-200">
          <th class="border p-2">Name</th>
          <th class="border p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="t in templates" :key="t.id">
          <td class="border p-2">{{ t.name }}</td>
          <td class="border p-2 flex gap-3">
            <Link :href="`/templates/edit/${t.id}`" class="text-blue-500">Edit</Link>
            <!-- <button class="px-4 py-2 bg-amber-200" @click="generate(t.id)">Generate</button> -->
            <a :href="route('leave_template.generate.pdf', t.id)"
              class="px-5 py-2 bg-green-600 text-white rounded hover:bg-green-700" target="_blank"
              rel="noopener noreferrer">
              Download Generated File
            </a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const props = defineProps({
  templates: Array
})
const form = useForm({
  id: null
});
function generate(id) {
  form.id = id;
  form.post('/templates/generate-pdf');
}
</script>
