<template>
    <Head title="Wochenplan"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Wochenplan
                </h2>
                <div class="flex-shrink-0 mt-1 sm:mt-0">
                    <a target="_blank" :href="route('print.wochenplan')" download>
                        <Button>
                            Wochenplan als PDF runterladen
                        </Button>
                    </a>
                </div>
            </div>
        </template>


        <div class="py-12 hidden md:block">
            <div class="max-w-[100rem] mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-md shadow  p-4">
                    <div class="rounded shadow ring-1 ring-black ring-opacity-5 overflow-x-auto">
                        <table class="w-full whitespace-nowrap divide-y divide-gray-300">
                            <thead class="bg-gray-800">
                            <tr class="text-left font-bold divide-x divide-gray-700 text-white">
                                <th class="pl-4 pt-6 pb-4"></th>
                                <template v-for="(tag, tagIdx) in wochenplan.tag" :key="tag">
                                    <th class="px-6 pt-6 pb-4">{{ tag }}</th>
                                </template>
                            </tr>
                            <tr></tr>
                            </thead>

                            <tbody>
                            <tr class="divide-x divide-gray-200">
                                <td class="border-t">
                                    <div class="p-4 font-bold">
                                        Anwesend
                                    </div>
                                </td>

                                <template v-for="(tag, tagIdx) in wochenplan.arbeit" :key="`anwesend-${tagIdx}`">
                                    <td class="border-t">
                                        <div class="p-4">
                                            <template v-for="(slot, slotIdx) in tag"
                                                      :key="`anwesend-${tagIdx}-slot-${slotIdx}`">
                                                {{ userName(slot.user_id) }}<br/>
                                            </template>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr class="bg-gray-50 divide-x divide-gray-200">
                                <td class="border-t">
                                    <div class="p-4 font-bold">
                                        Krank
                                    </div>
                                </td>
                                <template v-for="(tag, tagIdx) in wochenplan.krank" :key="`krank-${tagIdx}`">
                                    <td class="border-t">
                                        <div class="p-4">
                                            <template v-for="(slot, slotIdx) in tag"
                                                      :key="`krank-${tagIdx}-slot-${slotIdx}`">
                                                {{ userName(slot.user_id) }}<br/>
                                            </template>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr class="divide-x divide-gray-200">
                                <td class="border-t">
                                    <div class="p-4 font-bold">
                                        Frei
                                    </div>
                                </td>
                                <template v-for="(tag, tagIdx) in wochenplan.frei" :key="`frei-${tagIdx}`">
                                    <td class="border-t">
                                        <div class="p-4">
                                            <template v-for="(slot, slotIdx) in tag"
                                                      :key="`frei-${tagIdx}-slot-${slotIdx}`">
                                                {{ userName(slot.user_id) }}<br/>
                                            </template>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            <tr class="bg-gray-50 divide-x divide-gray-200">
                                <td class="border-t">
                                    <div class="p-4 font-bold">
                                        Urlaub
                                    </div>
                                </td>
                                <template v-for="(tag, tagIdx) in wochenplan.urlaub" :key="`urlaub-${tagIdx}`">
                                    <td class="border-t">
                                        <div class="p-4">
                                            <template v-for="(slot, slotIdx) in tag"
                                                      :key="`urlaub-${tagIdx}-slot-${slotIdx}`">
                                                {{ userName(slot.user_id) }}<br/>
                                            </template>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="py-4 md:hidden space-y-6 p-2">
                <template v-for="(tag, tagIdx) in wochenplan.tag">
            <div class="bg-white rounded-md shadow p-4 space-y-6 overflow-hidden">
                    <div>
                        <div class="-mx-4 -mt-4 bg-gray-900" style="width: calc(100% + 2rem)">
                            <h3 class="text-lg font-bold leading-6 text-white p-4">{{ tag }}</h3>
                        </div>

                        <div class="mt-4">
                            <dl>
                                    <dt class="text-base font-bold leading-6 text-gray-900">Anwesend</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700">
                                        <template v-if="wochenplan.arbeit[tagIdx].length === 0">
                                            -
                                        </template>
                                        <template v-else  v-for="(slot, slotIdx) in wochenplan.arbeit[tagIdx]"
                                                  :key="`arbeit-${tagIdx}-slot-${slotIdx}`">
                                            {{ userName(slot.user_id) }}<br/>
                                        </template>
                                    </dd>
                                    <dt class="text-base font-bold leading-6 text-gray-900 mt-4">Krank</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700">
                                        <template v-if="wochenplan.krank[tagIdx].length === 0">
                                            -
                                        </template>
                                        <template v-else  v-for="(slot, slotIdx) in wochenplan.krank[tagIdx]"
                                                  :key="`krank-${tagIdx}-slot-${slotIdx}`">
                                            {{ userName(slot.user_id) }}<br/>
                                        </template>
                                    </dd>
                                    <dt class="text-base font-bold leading-6 text-gray-900 mt-4">Frei</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700">
                                        <template v-if="wochenplan.frei[tagIdx].length === 0">
                                            -
                                        </template>
                                        <template v-else v-for="(slot, slotIdx) in wochenplan.frei[tagIdx]"
                                                  :key="`frei-${tagIdx}-slot-${slotIdx}`">
                                            {{ userName(slot.user_id) }}<br/>
                                        </template>
                                    </dd>
                                    <dt class="text-base font-bold leading-6 text-gray-900 mt-4">Urlaub</dt>
                                    <dd class="mt-1 text-sm leading-6 text-gray-700">
                                        <template v-if="wochenplan.urlaub[tagIdx].length === 0">
                                            -
                                        </template>
                                        <template v-else  v-for="(slot, slotIdx) in wochenplan.urlaub[tagIdx]"
                                                  :key="`urlaub-${tagIdx}-slot-${slotIdx}`">
                                            {{ userName(slot.user_id) }}<br/>
                                        </template>
                                    </dd>
                            </dl>
                        </div>
                    </div>
            </div>
                </template>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import {ref} from "vue";
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon, FingerPrintIcon} from '@heroicons/vue/solid'
import {Head, Link, usePage} from '@inertiajs/inertia-vue3'
import Button from '@/Components/Button'
import NavLink from '@/Components/NavLink'
import {Inertia} from '@inertiajs/inertia'

export default {
    props: {
        wochenplan: {
            type: Array,
            default: []
        },
        users: {
            type: Array,
            default: []
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
    },
    methods: {
        userName(userID) {
            return this.users.find(user => user.id === userID).name
        },
    },
}
</script>
