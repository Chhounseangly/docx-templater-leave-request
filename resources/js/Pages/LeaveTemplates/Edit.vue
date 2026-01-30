<script setup>
import { useForm } from '@inertiajs/vue3'

const props = defineProps({
  template: Object,
  place_holders: Array,
  db_mapping: Object,
})

// form stores placeholder → db field mapping
const form = useForm({
  mapping: props.template.mapping || {},
})

const save = () => {
  form.put(route('leave_template.update', props.template.id))
}
</script>

<template>
  <div class="p-6 max-w-4xl mx-auto">
    <h1 class="text-xl font-bold mb-4">
      Edit Leave Template Mapping
    </h1>

    <!-- Template info -->
    <div class="mb-6 text-sm text-gray-600">
      Template: <strong>{{ template.name }}</strong>
    </div>

    <!-- Mapping table -->
    <div class="border rounded-lg overflow-hidden">
      <table class="w-full border-collapse">
        <thead class="bg-gray-100">
          <tr>
            <th class="p-3 text-left">Placeholder</th>
            <th class="p-3 text-left">Map to DB Field</th>
          </tr>
        </thead>

        <tbody>
          <tr
            v-for="ph in place_holders"
            :key="ph"
            class="border-t"
          >
            <td class="p-3 font-mono text-sm">
              {{ ph }}
            </td>

            <td class="p-3">
              <select
                v-model="form.mapping[ph]"
                class="w-full border rounded px-3 py-2"
              >
                <option value="">— Select field —</option>

                <option
                  v-for="(value, label) in db_mapping"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </td>
          </tr>

          <tr v-if="place_holders.length === 0">
            <td colspan="2" class="p-4 text-center text-gray-500">
              No placeholders found in this DOCX
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Actions -->
    <div class="mt-6 flex justify-end">
      <button
        @click="save"
        :disabled="form.processing"
        class="px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 disabled:opacity-50"
      >
        Save Mapping
      </button>
    </div>
  </div>
</template>
