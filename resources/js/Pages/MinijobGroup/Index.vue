<template>
    <Head title="Minijob-Gruppen"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Minijob-Gruppen
                </h2>
                <div class="flex-shrink-0">
                    <Link :href="route('minijobGroups.create')">
                        <Button>
                            Minijob-Gruppe hinzufügen
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
                            <th class="px-6 pt-6 pb-4">Zugewiesen</th>
                            <th class="px-6 pt-6 pb-4"></th>
                        </tr>
                        </thead>

                        <tbody v-if="groups.length === 0">
                        <tr>
                            <td class="border-t px-6 py-4" colspan="4">No Groups found.</td>
                        </tr>
                        </tbody>

                        <template v-else>
                            <tbody>
                            <template v-for="(group, index) in groups" :key="group.index">
                                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('minijobGroups.edit', group.id)">
                                            {{ group.name }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('minijobGroups.edit', group.id)">
                                            {{ group.users_count }}
                                        </Link>
                                    </td>

                                    <td class="border-t w-px">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link class="px-4 flex items-center"
                                                  :href="route('minijobGroups.edit', group.id)"
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
import draggable from 'vuedraggable'
import {ref} from "vue";
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon, FingerPrintIcon} from '@heroicons/vue/solid'
import {Head, Link, usePage} from '@inertiajs/inertia-vue3'
import Button from '@/Components/Button'
import NavLink from '@/Components/NavLink'
import {Inertia} from '@inertiajs/inertia'

export default {
    props: {
        groups: Object,
    },
    setup(props, {attrs}) {

        const sending = ref(false)

        const submitOrder = (ids) => {

            if (sending.value) {
                return
            }

            sending.value = true

            Inertia.put(route('order.users'), {
                ids,
            }, {
                onFinish: () => {
                    sending.value = false
                },
            })
        }

        const dragged = (type) => {

            if (type?.moved) {

                // get ids of users
                const ids = props.groups.map(user => user.id)

                submitOrder(ids)

            }

        }

        return {
            dragged,
            sending,
        }

    },
    components: {
        NavLink,
        Button,
        BreezeAuthenticatedLayout,
        ChevronRightIcon,
        FingerPrintIcon,
        Head,
        Link,
        draggable
    },
}
</script>
