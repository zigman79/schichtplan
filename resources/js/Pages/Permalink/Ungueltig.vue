<template>
  <Head title="Link nicht mehr gültig"/>

  <div class="min-h-dvh bg-gray-100 flex items-center justify-center px-6">
    <div class="max-w-sm bg-white rounded-xl shadow-sm border-t-4 border-tenant-500 p-8 text-center">
      <h1 class="text-lg font-bold text-gray-900">Link nicht mehr gültig</h1>
      <p class="mt-2 text-sm text-gray-500">
        Dieser Link wurde deaktiviert oder erneuert. Bitte in der Verwaltung einen neuen Link anfragen.
      </p>
    </div>
  </div>
</template>

<script>
import { Head } from '@inertiajs/vue3'

export default {
  components: { Head },
}
</script>
