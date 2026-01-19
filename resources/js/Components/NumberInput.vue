<template>
  <input
      ref="inputRef"
      type="text"
      :value="formattedValue"
  >
</template>

<script>
import { useCurrencyInput } from 'vue-currency-input'
import {toRefs} from 'vue'

export default {
  name: 'NumberInput',

  props: {
    modelValue: Number,
    precision: {
      type: Number,
      default: 2
    },
  },

  setup (props) {

    const { precision } = toRefs(props)

    const { formattedValue, inputRef } = useCurrencyInput({
      currency: 'EUR',
      currencyDisplay: 'hidden',
      locale: 'de-DE',
      precision: precision.value,
    })

    return { inputRef, formattedValue }
  },
}
</script>
