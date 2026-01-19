<template>
    <Head title="Jobgruppen"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Jobgruppen
                </h2>
                <div class="flex-shrink-0">
                    <Link :href="route('jobGroups.create')">
                        <Button>
                            Jobgruppe hinzufügen
                        </Button>
                    </Link>

                </div>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                        <tr class="text-left font-bold">
                            <th class="px-6 pt-6 pb-4">Name</th>
                            <th class="px-6 pt-6 pb-4">Mitarbeiter</th>
                            <th class="px-6 pt-6 pb-4">Schichten</th>
                            <th class="px-6 pt-6 pb-4"></th>
                        </tr>
                        </thead>

                        <tbody v-if="groups.length === 0">
                        <tr>
                            <td class="border-t px-6 py-4" colspan="4">Keine Jobgruppen vorhanden.</td>
                        </tr>
                        </tbody>

                        <template v-else>
                            <tbody>
                            <template v-for="(group) in groups" :key="group.id">
                                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('jobGroups.edit', group.id)">
                                            {{ group.name }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('jobGroups.edit', group.id)">
                                            {{ group.users_count }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('jobGroups.edit', group.id)">
                                            {{ group.shifts_count }}
                                        </Link>
                                    </td>

                                    <td class="border-t w-px">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link class="px-4 flex items-center"
                                                  :href="route('jobGroups.edit', group.id)"
                                                  tabindex="-1">
                                                <ChevronRightIcon
                                                    class="block w-6 h-6 text-gray-400 hover:text-gray-600"/>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                            </tbody>
                        </template>
                    </table>
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

export default {
    props: {
        groups: Array,
    },
    components: {
        Button,
        BreezeAuthenticatedLayout,
        ChevronRightIcon,
        Head,
        Link,
    },
}
</script>