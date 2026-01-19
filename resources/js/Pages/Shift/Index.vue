<template>
    <Head title="Schichten"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Schichten
                </h2>
                <div class="flex-shrink-0">
                    <Link :href="route('shifts.create')">
                        <Button>
                            Schicht hinzufügen
                        </Button>
                    </Link>

                </div>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Filter -->
                <div class="bg-white rounded-md shadow mb-6 p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <Label>Jobgruppe</Label>
                            <SelectBox
                                v-model="filterForm.job_group_id"
                                :options="jobGroupOptions"
                                @change="applyFilter"
                            />
                        </div>
                        <div>
                            <Label>Von Datum</Label>
                            <Input
                                type="date"
                                v-model="filterForm.date_from"
                                @change="applyFilter"
                                class="w-full"
                            />
                        </div>
                        <div>
                            <Label>Bis Datum</Label>
                            <Input
                                type="date"
                                v-model="filterForm.date_to"
                                @change="applyFilter"
                                class="w-full"
                            />
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                        <tr class="text-left font-bold">
                            <th class="px-6 pt-6 pb-4">Datum</th>
                            <th class="px-6 pt-6 pb-4">Zeit</th>
                            <th class="px-6 pt-6 pb-4">Jobgruppe</th>
                            <th class="px-6 pt-6 pb-4">Benötigt</th>
                            <th class="px-6 pt-6 pb-4">Angemeldet</th>
                            <th class="px-6 pt-6 pb-4">Absagen</th>
                            <th class="px-6 pt-6 pb-4">Status</th>
                            <th class="px-6 pt-6 pb-4"></th>
                        </tr>
                        </thead>

                        <tbody v-if="shifts.length === 0">
                        <tr>
                            <td class="border-t px-6 py-4" colspan="8">Keine Schichten vorhanden.</td>
                        </tr>
                        </tbody>

                        <template v-else>
                            <tbody>
                            <template v-for="shift in shifts" :key="shift.id">
                                <tr class="hover:bg-gray-100 focus-within:bg-gray-100">
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.shift_date }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.start_time }} - {{ shift.end_time }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.job_group_name }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.required_employees }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.enrolled_count }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            <span v-if="shift.unavailable_count > 0" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                {{ shift.unavailable_count }}
                                            </span>
                                            <span v-else class="text-gray-400">
                                                0
                                            </span>
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center"
                                              :href="route('shifts.edit', shift.id)">
                                            <span v-if="shift.is_full" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                Voll
                                            </span>
                                            <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ shift.available_slots }} frei
                                            </span>
                                        </Link>
                                    </td>

                                    <td class="border-t w-px">
                                        <div class="flex items-center justify-end space-x-2">
                                            <Link class="px-4 flex items-center"
                                                  :href="route('shifts.edit', shift.id)"
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
import { reactive } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon} from '@heroicons/vue/solid'
import {Head, Link} from '@inertiajs/inertia-vue3'
import Button from '@/Components/Button'
import Label from '@/Components/Label'
import Input from '@/Components/Input'
import SelectBox from '@/Components/SelectBox'
import { Inertia } from '@inertiajs/inertia'

export default {
    props: {
        shifts: Array,
        jobGroups: Array,
        filters: Object,
    },
    setup(props) {
        
        const jobGroupOptions = [
            { value: '', label: 'Alle Jobgruppen' },
            ...props.jobGroups.map(g => ({ value: g.id, label: g.name }))
        ]

        const filterForm = reactive({
            job_group_id: props.filters?.job_group_id || '',
            date_from: props.filters?.date_from || '',
            date_to: props.filters?.date_to || '',
        })

        const applyFilter = () => {
            Inertia.get(route('shifts.index'), filterForm, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        return {
            jobGroupOptions,
            filterForm,
            applyFilter,
        }
    },
    components: {
        Button,
        BreezeAuthenticatedLayout,
        ChevronRightIcon,
        Head,
        Link,
        Label,
        Input,
        SelectBox,
    },
}
</script>