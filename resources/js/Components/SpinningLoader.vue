<template>
    <RefreshIcon :class="[tailwind, 'animate-spin direction-reverse']" />
</template>

<script>

import {RefreshIcon} from '@heroicons/vue/solid'

export default {
    name: "SpinningLoader",
    components: {
        RefreshIcon,
    },
    props: {
        tailwind: {
            type: String,
            default: 'w-8 h-8 text-gray-500',
        },
    },
}
</script>

<style scoped>
.direction-reverse {
    animation-direction: reverse;
}
</style>
