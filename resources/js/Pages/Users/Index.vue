<template>
    <Head title="Benutzer"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{title}}
                </h2>
                <div class="flex-shrink-0">
                    <Link :href="route('users.create')">
                        <Button>
                            {{title}} hinzufügen
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
                            <th class="hidden lg:table-cell pl-4 pt-6 pb-4"></th>
                            <th class="px-6 pt-6 pb-4">
                                <button @click="sortBy('name')" class="hover:underline">
                                    Name
                                </button>
                            </th>
                            <th class="px-6 pt-6 pb-4">Eingestellt am</th>
                            <th class="px-6 pt-6 pb-4">
                                <button @click="sortBy('druck_sort')" class="hover:underline">
                                    Druck Sort
                                </button>
                            </th>
                            <th class="px-6 pt-6 pb-4" colspan="2">Status</th>
                        </tr>
                        </thead>

                        <tbody v-if="users.length === 0">
                        <tr>
                            <td class="border-t px-6 py-4" colspan="5">No users found.</td>
                        </tr>
                        </tbody>

                        <template v-else>
                            <draggable
                                tag="tbody"
                                v-model="users"
                                group="users"
                                @change="dragged"
                                :disabled="sending"
                                item-key="id"
                            >
                                <template #item="{element}">
                                    <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                                        <td class="hidden lg:table-cell border-t">
                                            <div class="pl-4 py-4">
                                                <button class="opacity-25 hover:opacity-100">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="border-t">
                                            <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                                  :href="route('users.edit', element.id)">
                                                <img v-if="element.photo" class="block w-5 h-5 rounded-full mr-2 -my-2"
                                                     :src="element.photo"/>
                                                {{ element.name }}
                                            </Link>
                                        </td>
                                        <td class="border-t">
                                            <Link class="px-6 py-4 flex items-center"
                                                  :href="route('users.edit', element.id)"
                                                  tabindex="-1">
                                                {{ element.eingestellt_am || '-' }}
                                            </Link>
                                        </td>
                                        <td class="border-t">
                                            <Link class="px-6 py-4 flex items-center"
                                                  :href="route('users.edit', element.id)"
                                                  tabindex="-1">
                                                {{ element.druck_sort || '-' }}
                                            </Link>
                                        </td>
                                        <td class="border-t">
                                            <Link class="px-6 py-4 flex items-center"
                                                  :href="route('users.edit', element.id)"
                                                  tabindex="-1">
                                                {{  element.role }}
                                            </Link>
                                        </td>
                                        <td class="border-t w-px">
                                            <div class="flex items-center justify-end space-x-2">
                                                <Link title="Als User anmelden" v-if="isAdmin && element.role !== 'Administrator'" :href="route('switch-user', element.id)" class="px-4 flex items-center">
                                                    <FingerPrintIcon class="block w-6 h-6 text-gray-400 hover:text-gray-600"/>
                                                </Link>

                                                <Link class="px-4 flex items-center" :href="route('users.edit', element.id)"
                                                      tabindex="-1">
                                                    <ChevronRightIcon class="block w-6 h-6 text-gray-400 hover:text-gray-600"/>
                                                </Link>

                                            </div>
                                        </td>
                                    </tr>
                                </template>
                            </draggable>
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
        users: Object,
    },
    setup(props, {attrs}) {

        const isAdmin = usePage().props.value.auth.user.arbeitszeit_admin
        const genderLanguage = usePage().props.value.genderLanguage || false

        const title = `Benutzer${genderLanguage ? ':in' : ''}`

        const sending = ref(false)
        const users = ref(props.users)

        const sortBy = (field) => {
            users.value = [...users.value].sort((a, b) => {
                if (field === 'name') {
                    return a.name.localeCompare(b.name)
                } else if (field === 'druck_sort') {
                    return (a.druck_sort || 0) - (b.druck_sort || 0)
                }
                return 0
            })
        }

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
                const ids = users.value.map(user => user.id)

                submitOrder(ids)

            }

        }

        return {
            dragged,
            isAdmin,
            sending,
            title,
            users,
            sortBy,
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
