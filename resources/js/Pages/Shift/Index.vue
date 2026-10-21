<template>
    <Head title="Schichten"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Schichten
                </h2>
                <div class="flex items-center gap-3 shrink-0">
                    <template v-if="deleteMode">
                        <span class="text-sm text-gray-500">{{ selectedIds.length }} ausgewählt</span>
                        <Button variant="secondary" @click="cancelDeleteMode">
                            Abbrechen
                        </Button>
                        <Button
                            class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
                            :disabled="selectedIds.length === 0"
                            @click="confirmDelete"
                        >
                            {{ selectedIds.length }} löschen
                        </Button>
                    </template>
                    <template v-else>
                        <Button variant="secondary" @click="enterDeleteMode">
                            Löschen
                        </Button>
                        <Link :href="route('shifts.create')">
                            <Button>
                                Schicht hinzufügen
                            </Button>
                        </Link>
                    </template>
                </div>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <!-- Filter -->
                <div class="bg-white rounded-md shadow-sm mb-6 p-6">
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

                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
                    <table class="w-full whitespace-nowrap">
                        <thead>
                        <tr class="text-left font-bold">
                            <th v-if="deleteMode" class="px-4 pt-6 pb-4 w-px">
                                <input
                                    type="checkbox"
                                    class="rounded-sm border-gray-300 text-tenant-600 focus:ring-tenant-500"
                                    :checked="selectedIds.length === shifts.length && shifts.length > 0"
                                    :indeterminate="selectedIds.length > 0 && selectedIds.length < shifts.length"
                                    @change="toggleAll"
                                />
                            </th>
                            <th class="px-6 pt-6 pb-4">Datum</th>
                            <th class="px-6 pt-6 pb-4">Tag</th>
                            <th class="px-6 pt-6 pb-4">Zeit</th>
                            <th class="px-6 pt-6 pb-4">Jobgruppe</th>
                            <th class="px-6 pt-6 pb-4">Benötigt</th>
                            <th class="px-6 pt-6 pb-4">Angemeldet</th>
                            <th class="px-6 pt-6 pb-4">Absagen</th>
                            <th class="px-6 pt-6 pb-4">Status</th>
                            <th v-if="!deleteMode" class="px-6 pt-6 pb-4"></th>
                        </tr>
                        </thead>

                        <tbody v-if="shifts.length === 0">
                        <tr>
                            <td class="border-t px-6 py-4" colspan="9">Keine Schichten vorhanden.</td>
                        </tr>
                        </tbody>

                        <template v-else>
                            <tbody>
                            <template v-for="shift in shifts" :key="shift.id">
                                <tr
                                    class="hover:bg-gray-100 focus-within:bg-gray-100"
                                    :class="{ 'bg-red-50': deleteMode && selectedIds.includes(shift.id) }"
                                    @click="deleteMode ? toggleSelect(shift.id) : null"
                                    :style="deleteMode ? 'cursor: pointer' : ''"
                                >
                                    <td v-if="deleteMode" class="border-t px-4 py-4 w-px" @click.stop>
                                        <input
                                            type="checkbox"
                                            class="rounded-sm border-gray-300 text-tenant-600 focus:ring-tenant-500"
                                            :checked="selectedIds.includes(shift.id)"
                                            @change="toggleSelect(shift.id)"
                                        />
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ shift.shift_date }}
                                        </Link>
                                    </td>
                                    <td class="border-t">
                                        <Link class="px-6 py-4 flex items-center focus:text-tenant-500"
                                              :href="route('shifts.edit', shift.id)">
                                            {{ getWeekday(shift.shift_date) }}
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

                                    <td v-if="!deleteMode" class="border-t w-px">
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

                <!-- Letzte App-Nutzung -->
                <div class="bg-white rounded-md shadow-sm mt-6 p-6">
                    <h3 class="text-lg font-semibold mb-4">Letzte App-Nutzung</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        <div v-for="user in usersWithLastUsage" :key="user.id"
                             class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                            <span class="font-medium">{{ user.name }}</span>
                            <span v-if="user.never_used" class="text-red-500 text-sm">Nie</span>
                            <span v-else class="text-gray-500 text-sm">{{ user.last_used_at }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import { reactive, ref } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon} from '@heroicons/vue/24/solid'
import { Head, Link, router } from '@inertiajs/vue3'
import Button from '@/Components/Button.vue'
import Label from '@/Components/Label.vue'
import Input from '@/Components/Input.vue'
import SelectBox from '@/Components/SelectBox.vue'
export default {
    props: {
        shifts: Array,
        jobGroups: Array,
        filters: Object,
        usersWithLastUsage: Array,
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
            router.get(route('shifts.index'), filterForm, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const weekdays = ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa']

        const getWeekday = (dateStr) => {
            const [day, month, year] = dateStr.split('.')
            const date = new Date(year, month - 1, day)
            return weekdays[date.getDay()]
        }

        // Löschmodus
        const deleteMode = ref(false)
        const selectedIds = ref([])

        const enterDeleteMode = () => {
            deleteMode.value = true
            selectedIds.value = []
        }

        const cancelDeleteMode = () => {
            deleteMode.value = false
            selectedIds.value = []
        }

        const toggleSelect = (id) => {
            const idx = selectedIds.value.indexOf(id)
            if (idx === -1) {
                selectedIds.value.push(id)
            } else {
                selectedIds.value.splice(idx, 1)
            }
        }

        const toggleAll = () => {
            if (selectedIds.value.length === props.shifts.length) {
                selectedIds.value = []
            } else {
                selectedIds.value = props.shifts.map(s => s.id)
            }
        }

        const confirmDelete = () => {
            if (selectedIds.value.length === 0) return
            if (!confirm(`${selectedIds.value.length} Schicht(en) wirklich löschen?`)) return

            router.delete(route('shifts.destroyMultiple'), {
                data: { ids: selectedIds.value },
                onSuccess: () => {
                    deleteMode.value = false
                    selectedIds.value = []
                },
            })
        }

        return {
            jobGroupOptions,
            filterForm,
            applyFilter,
            getWeekday,
            deleteMode,
            selectedIds,
            enterDeleteMode,
            cancelDeleteMode,
            toggleSelect,
            toggleAll,
            confirmDelete,
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