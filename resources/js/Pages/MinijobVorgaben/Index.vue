<template>
    <Head title="Minijob-Zeiten"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div
                class="flex sm:items-center sm:justify-between flex-wrap flex-col sm:flex-row sm:flex-nowrap space-y-4 sm:space-y-0">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Minijob-Zeiten
                </h2>

                <div class="flex items-center justify-start space-x-2">
                    <label for="group" class="font-semibold w-20 sm:w-auto">Jahr:</label>
                    <select
                        id="year"
                        v-model="year"
                        class="block w-full sm:w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm rounded-md">
                        <option
                            v-for="(year, key) in years"
                            :key="`year-${key}`"
                            :value="year"
                        >
                            {{ year }}
                        </option>
                    </select>
                </div>

            </div>
        </template>

        <template #subHeader>
            <div class="flex items-center justify-start space-x-2 sm:hidden">
                <label for="group" class="font-semibold w-20 sm:w-auto">Gruppe:</label>
                <select
                    id="group"
                    v-model="currentGroupId"
                    class="block w-full sm:w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm rounded-md">
                    <option
                        v-for="(group, key) in groups"
                        :key="`group-${key}`"
                        :value="group.id"
                    >
                        {{ group.name }}
                    </option>
                </select>
            </div>
            <div class="hidden sm:flex justify-between">
                <nav class="flex md:space-x-4" aria-label="Global">
                    <template v-for="(group, key) in groups" :key="`group-${key}`">
                        <button
                            :class="{'bg-gray-100 text-gray-900 font-semibold': group.id === currentGroupId, 'font-medium text-gray-900 hover:bg-gray-50 hover:text-gray-900': group.id !== currentGroupId}"
                            class="inline-flex items-center rounded-md py-2 px-3 text-sm "
                            @click="currentGroupId = group.id"
                        >
                            {{ group.name }}
                        </button>
                    </template>
                </nav>
                <Link :href="route('minijobGroups.index')">
                    <Button class="ml-auto">
                        Gruppen bearbeiten
                    </Button>
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white rounded-md shadow overflow-x-auto p-6">
                    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                        <template v-for="(month, key) in months" :key="`${currentGroupId}-${year}-month-${key}`">
                            <minijob-vorgabe-slot
                                :label="month"
                                :month="key + 1"
                                :year="year"
                                :alt-background="key < 6"
                                :vorgabe="currentGroup.vorgaben[key]"
                                :group-id="currentGroupId"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>

</template>

<script>

import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon} from '@heroicons/vue/solid'
import {Head, Link} from '@inertiajs/inertia-vue3'
import Button from '@/Components/Button'
import NavLink from '@/Components/NavLink'
import {ref, watchEffect} from 'vue'
import {Inertia} from '@inertiajs/inertia'
import MinijobVorgabeSlot from '@/Components/MinijobVorgabeSlot'

export default {
    props: {
        year: String,
        groups: Object,
    },
    setup(props, {attrs}) {

        const months = [
            'Januar',
            'Februar',
            'März',
            'April',
            'Mai',
            'Juni',
            'Juli',
            'August',
            'September',
            'Oktober',
            'November',
            'Dezember',
        ]

        const year = ref(parseInt(props.year) || today.getFullYear())
        const loading = ref(false)

        const currentGroupId = ref(props.groups[0].id)
        const currentGroup = ref(props.groups[0])

        watchEffect(() => {
            currentGroup.value = props.groups.find(group => group.id === currentGroupId.value)
        })

        const getYears = () => {
            const currentYear = new Date().getFullYear()
            const years = []
            for (let i = -1; i < 2; i++) {
                years.push(currentYear + i)
            }
            return years
        }

        const years = ref(getYears())

        watchEffect(() => {
            if (parseInt(props.year) !== year.value) {
                loading.value = true

                Inertia.get(`/minijobvorgaben/${year.value}`, {}, {
                    onSuccess: () => {
                        loading.value = false
                    },
                })
            }
        })

        return {months, year, years, loading, currentGroupId, currentGroup}

    },
    components: {
        MinijobVorgabeSlot,
        NavLink,
        Button,
        BreezeAuthenticatedLayout,
        ChevronRightIcon,
        Head,
        Link,
    },
}
</script>
