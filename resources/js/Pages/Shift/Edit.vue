<template>
    <Head :title="`${title} bearbeiten`"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{title}} / <span class="text-tenant-600">{{ shift.shift_date }}</span>
                </h2>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Shift Details -->
                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <form @submit.prevent="update">
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

                            <div>
                                <Label>
                                    Status
                                </Label>
                                <div class="mt-2">
                                    <span v-if="shift.enrolled_count >= shift.required_employees" 
                                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        Voll belegt ({{ shift.enrolled_count }}/{{ shift.required_employees }})
                                    </span>
                                    <span v-else 
                                          class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        {{ shift.available_slots }} Plätze frei ({{ shift.enrolled_count }}/{{ shift.required_employees }})
                                    </span>
                                </div>
                            </div>

                            <div class="lg:col-span-2">
                                <Label>
                                    Kommentar (Admin)
                                </Label>
                                <textarea
                                    class="w-full border-gray-300 focus:border-tenant-300 focus:ring focus:ring-tenant-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                    rows="3"
                                    v-model="form.admin_comment"
                                    :class="{ 'border-red-500': form.errors.admin_comment }"
                                ></textarea>
                                <InputError :message="form.errors.admin_comment"/>
                            </div>

                        </div>


                        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">

                            <button class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
                                {{title}} löschen
                            </button>

                            <button
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                type="submit"
                            >
                                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                                <span>{{title}} aktualisieren</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Enrolled Users -->
                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <div class="p-8">
                        <h3 class="text-lg font-semibold mb-4">Angemeldete Mitarbeiter ({{ enrolledUsers.length }})</h3>
                        
                        <div v-if="enrolledUsers.length === 0" class="text-gray-500 italic">
                            Noch keine Anmeldungen vorhanden.
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="user in enrolledUsers"
                                 :key="user.id"
                                 class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold">{{ user.name }}</p>
                                        <p class="text-sm text-gray-500">Angemeldet am: {{ user.enrolled_at }}</p>
                                        <p v-if="user.user_comment" class="mt-2 text-sm">
                                            <span class="font-medium">Kommentar:</span> {{ user.user_comment }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unavailable Users (Absagen) -->
                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <div class="p-8">
                        <h3 class="text-lg font-semibold mb-4">Absagen ({{ unavailableUsers.length }})</h3>
                        
                        <div v-if="unavailableUsers.length === 0" class="text-gray-500 italic">
                            Keine Absagen vorhanden.
                        </div>

                        <div v-else class="space-y-4">
                            <div v-for="user in unavailableUsers"
                                 :key="user.id"
                                 class="border border-red-200 bg-red-50 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-semibold text-red-900">{{ user.name }}</p>
                                        <p class="text-sm text-red-700">Abgesagt am: {{ user.declined_at }}</p>
                                        <p v-if="user.reason" class="mt-2 text-sm text-red-800">
                                            <span class="font-medium">Grund:</span> {{ user.reason }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {Head, Link} from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import {useForm} from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import SelectBox from '@/Components/SelectBox'
import InputError from '@/Components/InputError'

export default {
    props: {
        shift: Object,
        jobGroups: Array,
        enrolledUsers: Array,
        unavailableUsers: Array,
    },
    setup(props) {

        const title = 'Schicht'

        const {shift, jobGroups} = props

        const jobGroupOptions = jobGroups.map(g => ({ 
            value: g.id, 
            label: g.name 
        }))

        const form = useForm({
            job_group_id: shift.job_group_id,
            shift_date: shift.shift_date,
            start_time: shift.start_time,
            end_time: shift.end_time,
            admin_comment: shift.admin_comment,
            required_employees: shift.required_employees,
        })

        const update = () => {
            form.transform((data) => ({
                ...data,
            }))
                .put(route('shifts.update', shift.id), {
                    preserveScroll: true,
                    preserveState: false,
                })
        }

        const destroy = () => {
            const message = props.enrolledUsers.length > 0 
                ? 'Sind Sie sicher? Es gibt bereits ' + props.enrolledUsers.length + ' Anmeldung(en) für diese Schicht.'
                : 'Sind Sie sicher?'
                
            confirm(message) && form.delete(route('shifts.destroy', shift.id))
        }

        return {
            title,
            form,
            destroy,
            update,
            jobGroupOptions,
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