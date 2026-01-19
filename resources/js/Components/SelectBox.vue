<template>
    <select class="border-gray-300 focus:border-tenant-300 focus:ring focus:ring-tenant-200 focus:ring-opacity-50 rounded-md shadow-sm" :value="modelValue" @change="$emit('update:modelValue', $event.target.value)" ref="select">
      <option v-for="opt in options" :key="opt.value" :value="opt.value">
        {{ opt.label }}
      </option>
    </select>
</template>

<script>
export default {
    props: ['modelValue', 'options'],

    emits: ['update:modelValue'],

    methods: {
        focus() {
            this.$refs.select.focus()
        }
    }
}
</script>
