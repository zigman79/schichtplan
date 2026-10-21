<template>
    <ArrowPathIcon :class="[tailwind, 'animate-spin direction-reverse']" />
</template>

<script>

import {ArrowPathIcon} from '@heroicons/vue/24/solid'

export default {
    name: "SpinningLoader",
    components: {
        ArrowPathIcon,
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
