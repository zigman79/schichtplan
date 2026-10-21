<template>
    <Head :title="`${title} hinzufügen`"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{title}} / <span class="text-tenant-600">Hinzufügen</span>
                </h2>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
                    <form @submit.prevent="create">
                        <div class="p-8 gap-8 grid grid-cols-1 lg:grid-cols-2">
                            <div>
                                <Label>
                                    Jobgruppe
                                </Label>
                                <SelectBox
                                    v-model="form.job_group_id"
                                    :options="jobGroupOptions"
                                    :error="form.errors.job_group_id"
                                />
                                <InputError :message="form.errors.job_group_id"/>
                            </div>

                            <div>
                                <Label>
                                    Datum
                                </Label>
                                <Input
                                    class="w-full"
                                    type="date"
                                    v-model="form.shift_date"
                                    :error="form.errors.shift_date"
                                />
                                <InputError :message="form.errors.shift_date"/>
                            </div>

                            <div>
                                <Label>
                                    Startzeit
                                </Label>
                                <Input
                                    class="w-full"
                                    type="time"
                                    v-model="form.start_time"
                                    :error="form.errors.start_time"
                                />
                                <InputError :message="form.errors.start_time"/>
                            </div>

                            <div>
                                <Label>
                                    Endzeit
                                </Label>
                                <Input
                                    class="w-full"
                                    type="time"
                                    v-model="form.end_time"
                                    :error="form.errors.end_time"
                                />
                                <InputError :message="form.errors.end_time"/>
                            </div>

                            <div>
                                <Label>
                                    Benötigte Mitarbeiter
                                </Label>
                                <Input
                                    class="w-full"
                                    type="number"
                                    min="1"
                                    v-model="form.required_employees"
                                    :error="form.errors.required_employees"
                                />
                                <InputError :message="form.errors.required_employees"/>
                            </div>

                            <div class="lg:col-span-2">
                                <Label>
                                    Kommentar (Admin)
                                </Label>
                                <textarea
                                    class="w-full border-gray-300 focus:border-tenant-300 focus:ring-3 focus:ring-tenant-200/50 rounded-md shadow-xs"
                                    rows="3"
                                    v-model="form.admin_comment"
                                    :class="{ 'border-red-500': form.errors.admin_comment }"
                                ></textarea>
                                <InputError :message="form.errors.admin_comment"/>
                            </div>

                            <!-- Mehrfach-Erstellung -->
                            <div class="lg:col-span-2 border-t pt-6 mt-2">
                                <div class="flex items-center space-x-3 mb-4">
                                    <input
                                        id="multi-create"
                                        type="checkbox"
                                        v-model="multiCreate"
                                        class="h-4 w-4 text-tenant-600 focus:ring-tenant-500 border-gray-300 rounded-sm"
                                    />
                                    <label for="multi-create" class="text-sm font-medium text-gray-700">
                                        Mehrere Schichten auf einmal anlegen
                                    </label>
                                </div>

                                <div v-if="multiCreate" class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                    <div>
                                        <Label>
                                            Bis Datum
                                        </Label>
                                        <Input
                                            class="w-full"
                                            type="date"
                                            v-model="form.shift_date_end"
                                            :min="form.shift_date"
                                        />
                                        <InputError :message="form.errors.shift_date_end"/>
                                    </div>
                                    <div>
                                        <Label>
                                            Wochentage
                                        </Label>
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <label v-for="(day, index) in weekdays" :key="index"
                                                   class="inline-flex items-center">
                                                <input
                                                    type="checkbox"
                                                    :value="index"
                                                    v-model="form.weekdays"
                                                    class="h-4 w-4 text-tenant-600 focus:ring-tenant-500 border-gray-300 rounded-sm"
                                                />
                                                <span class="ml-1 mr-3 text-sm text-gray-700">{{ day }}</span>
                                            </label>
                                        </div>
                                        <InputError :message="form.errors.weekdays"/>
                                    </div>
                                </div>

                                <p v-if="multiCreate && shiftsToCreate > 0" class="mt-3 text-sm text-gray-600">
                                    Es werden <span class="font-semibold text-tenant-600">{{ shiftsToCreate }}</span> Schichten erstellt.
                                </p>
                            </div>

                        </div>



                        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">

                            <button
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-xs font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                type="submit"
                            >
                                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                                <span v-if="multiCreate && shiftsToCreate > 1">{{ shiftsToCreate }} Schichten hinzufügen</span>
                                <span v-else>{{title}} hinzufügen</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import Input from '@/Components/Input.vue'
import Label from '@/Components/Label.vue'
import SpinningLoader from '@/Components/SpinningLoader.vue'
import SelectBox from '@/Components/SelectBox.vue'
import InputError from '@/Components/InputError.vue'
import { ref, computed } from 'vue'

export default {
    props: {
        jobGroups: Array,
        defaultDate: String,
    },
    setup (props) {

        const title = 'Schicht'
        const multiCreate = ref(false)

        const weekdays = ['Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So']

        const jobGroupOptions = props.jobGroups.map(g => ({
            value: g.id,
            label: g.name
        }))

        const form = useForm({
            job_group_id: jobGroupOptions[0]?.value || null,
            shift_date: props.defaultDate || null,
            shift_date_end: null,
            start_time: '12:00',
            end_time: '21:00',
            admin_comment: null,
            required_employees: 1,
            weekdays: [0, 1, 2, 3, 4, 5, 6], // Alle Tage standardmäßig ausgewählt
        })

        const shiftsToCreate = computed(() => {
            if (!multiCreate.value || !form.shift_date || !form.shift_date_end) {
                return 1
            }

            const start = new Date(form.shift_date)
            const end = new Date(form.shift_date_end)

            if (end < start) return 0

            let count = 0
            const current = new Date(start)

            while (current <= end) {
                // getDay() gibt 0 für Sonntag zurück, wir wollen 0 für Montag
                const dayIndex = (current.getDay() + 6) % 7
                if (form.weekdays.includes(dayIndex)) {
                    count++
                }
                current.setDate(current.getDate() + 1)
            }

            return count
        })

        const create = () => {
            if (multiCreate.value && form.shift_date_end) {
                form.post(route('shifts.storeMultiple'), {
                    preserveScroll: true,
                })
            } else {
                form
                    .transform((data) => ({
                        job_group_id: data.job_group_id,
                        shift_date: data.shift_date,
                        start_time: data.start_time,
                        end_time: data.end_time,
                        admin_comment: data.admin_comment,
                        required_employees: data.required_employees,
                    }))
                    .post(route('shifts.store'), {
                        preserveScroll: true,
                    })
            }
        }

        return {
            form,
            create,
            title,
            jobGroupOptions,
            multiCreate,
            weekdays,
            shiftsToCreate,
        }
    },
    components: {
        InputError,
        SelectBox,
        BreezeAuthenticatedLayout,
        SpinningLoader,
        Head,
        Link,
        Input,
        Label,
    },
}
</script>