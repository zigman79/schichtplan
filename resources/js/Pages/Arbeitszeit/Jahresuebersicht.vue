<template>
    <Head title="Jahresübersicht"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div
                class="flex sm:items-center sm:justify-between flex-wrap flex-col sm:flex-row sm:flex-nowrap space-y-4 sm:space-y-0">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Jahresübersicht {{ year }}
                </h2>

                <div class="shrink-0 flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2 sm:items-center">

                    <a target="_blank" :href="route('print.jahresuebersicht', [ year ])">
                        <Button type="button" class="w-full">
                            <div class="mx-auto">Als PDF runterladen</div>
                        </Button>
                    </a>

                    <div class="flex items-center justify-start space-x-2">
                        <label for="year" class="font-semibold w-20 sm:w-auto">Jahr:</label>
                        <select
                            id="year"
                            v-model="selectedYear"
                            class="block w-full sm:w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-hidden focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm rounded-md">
                            <option
                                v-for="(y, key) in years"
                                :key="`year-${key}`"
                                :value="y"
                            >
                                {{ y }}
                            </option>
                        </select>
                    </div>

                </div>

            </div>
        </template>

        <div class="py-6">
            <div class="w-full sm:px-6 lg:px-8">

                <div v-if="loading" class="flex justify-center py-12">
                    <SpinningLoader tailwind="w-8 h-8 text-tenant-500"/>
                </div>

                <div v-else class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">

                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="sticky left-0 bg-gray-50 px-4 py-3 text-left font-semibold text-gray-700 whitespace-nowrap">
                                {{ genderLanguage ? 'Mitarbeiter:in' : 'Mitarbeiter' }}
                            </th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">
                                Übertrag {{ year - 1 }}
                            </th>
                            <th
                                v-for="(monat, key) in monate"
                                :key="`head-${key}`"
                                class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap"
                            >
                                {{ monat.substring(0, 3) }}
                            </th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">
                                Überstunden
                            </th>
                            <th class="px-3 py-3 text-right font-semibold text-gray-700 whitespace-nowrap">
                                Resturlaub
                            </th>
                            <th class="px-3 py-3"></th>
                        </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200">
                        <tr v-for="user in users" :key="`user-${user.id}`" class="hover:bg-gray-50">
                            <td class="sticky left-0 bg-white px-4 py-2 font-medium text-gray-900 whitespace-nowrap">
                                {{ user.name }}
                                <span v-if="user.minijob" class="block text-xs font-normal text-gray-500">Minijob: Übertrag</span>
                            </td>
                            <td class="px-3 py-2 text-right text-gray-500 whitespace-nowrap">
                                {{ user.uebertrag }} h
                            </td>
                            <td
                                v-for="(monat, key) in user.monate"
                                :key="`user-${user.id}-monat-${key}`"
                                class="px-3 py-2 text-right whitespace-nowrap"
                                :class="monat.diff < 0 ? 'text-red-600' : 'text-gray-700'"
                            >
                                {{ monat.diff_nice }}
                            </td>
                            <td class="px-3 py-2 text-right font-semibold whitespace-nowrap"
                                :class="user.summe < 0 ? 'text-red-600' : 'text-gray-900'">
                                {{ user.summe_nice }} h
                            </td>
                            <td class="px-3 py-2 text-right text-gray-700 whitespace-nowrap">
                                {{ user.urlaub }}
                            </td>
                            <td class="px-3 py-2 text-right whitespace-nowrap">
                                <a target="_blank" :href="route('print.overview', [ year, user.id])"
                                   class="text-tenant-600 hover:text-tenant-900">
                                    PDF
                                </a>
                            </td>
                        </tr>
                        <tr v-if="!users.length">
                            <td class="px-4 py-6 text-center text-gray-500" :colspan="monate.length + 5">
                                Keine Mitarbeiter vorhanden.
                            </td>
                        </tr>
                        </tbody>
                    </table>

                </div>

            </div>
        </div>

    </BreezeAuthenticatedLayout>
</template>

<script>

import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import Button from '@/Components/Button.vue'
import SpinningLoader from '@/Components/SpinningLoader.vue'
import {ref, watch} from 'vue'

export default {
    props: {
        year: Number,
        years: Array,
        monate: Array,
        users: Array,
    },
    setup(props) {

        const selectedYear = ref(props.year)
        const loading = ref(false)
        const genderLanguage = usePage().props.genderLanguage || false

        watch(selectedYear, (value) => {
            if (value === props.year) {
                return
            }

            loading.value = true

            router.get(route('jahresuebersicht', [value]), {}, {
                onFinish: () => {
                    loading.value = false
                },
            })
        })

        return {selectedYear, loading, genderLanguage}
    },
    components: {
        BreezeAuthenticatedLayout,
        Button,
        SpinningLoader,
        Head,
    },
}
</script>
