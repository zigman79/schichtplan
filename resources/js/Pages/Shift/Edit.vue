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
                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
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
                                    class="w-full border-gray-300 focus:border-tenant-300 focus:ring-3 focus:ring-tenant-200/50 rounded-md shadow-xs"
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
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-xs font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                type="submit"
                            >
                                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                                <span>{{title}} aktualisieren</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Enrolled Users -->
                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
                    <div class="p-8">
                        <h3 class="text-lg font-semibold mb-4">Angemeldete Mitarbeiter ({{ enrolledUsers.length }})</h3>

                        <!-- Add User Form -->
                        <div v-if="availableUsers.length > 0" class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Mitarbeiter hinzufügen</h4>
                            <form @submit.prevent="addUser" class="flex flex-wrap gap-3 items-end">
                                <div class="flex-1 min-w-[200px]">
                                    <Label class="text-sm">Mitarbeiter</Label>
                                    <SelectBox
                                        v-model="addUserForm.user_id"
                                        :options="availableUserOptions"
                                        :error="addUserForm.errors.user_id"
                                        placeholder="Mitarbeiter wählen..."
                                    />
                                </div>
                                <div class="flex-1 min-w-[200px]">
                                    <Label class="text-sm">Kommentar (optional)</Label>
                                    <Input
                                        class="w-full"
                                        type="text"
                                        v-model="addUserForm.user_comment"
                                        placeholder="z.B. Früher Feierabend"
                                    />
                                </div>
                                <button
                                    type="submit"
                                    :disabled="!addUserForm.user_id || addUserForm.processing"
                                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
                                >
                                    <SpinningLoader tailwind="w-4 h-4 text-white mr-2" v-if="addUserForm.processing"/>
                                    Hinzufügen
                                </button>
                            </form>
                        </div>
                        <div v-else class="mb-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                            <p class="text-sm text-yellow-700">Alle Mitarbeiter dieser Jobgruppe sind bereits eingetragen.</p>
                        </div>

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
                                    <button
                                        @click="removeUser(user)"
                                        class="text-red-600 hover:text-red-800 text-sm hover:underline"
                                    >
                                        Entfernen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unavailable Users (Absagen) -->
                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
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
                                    <button
                                        @click="removeDecline(user)"
                                        class="text-red-600 hover:text-red-800 text-sm hover:underline"
                                    >
                                        Entfernen
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- No Response Users (Keine Antwort) -->
                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
                    <div class="p-8">
                        <h3 class="text-lg font-semibold mb-4">Keine Antwort ({{ noResponseUsers.length }})</h3>

                        <div v-if="noResponseUsers.length === 0" class="text-gray-500 italic">
                            Alle Mitarbeiter haben geantwortet.
                        </div>

                        <div v-else class="space-y-2">
                            <div v-for="user in noResponseUsers"
                                 :key="user.id"
                                 class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="font-medium text-gray-800">{{ user.name }}</span>
                                <div class="flex gap-2">
                                    <button
                                        @click="quickAddUser(user)"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-600 hover:bg-green-200 transition-colors"
                                        title="Zur Schicht hinzufügen"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <button
                                        @click="quickDeclineUser(user)"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 text-red-600 hover:bg-red-200 transition-colors"
                                        title="Absage eintragen"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
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
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import Input from '@/Components/Input.vue'
import Label from '@/Components/Label.vue'
import SpinningLoader from '@/Components/SpinningLoader.vue'
import SelectBox from '@/Components/SelectBox.vue'
import InputError from '@/Components/InputError.vue'
export default {
    props: {
        shift: Object,
        jobGroups: Array,
        enrolledUsers: Array,
        unavailableUsers: Array,
        noResponseUsers: Array,
        availableUsers: Array,
    },
    setup(props) {

        const title = 'Schicht'

        const {shift, jobGroups, availableUsers} = props

        const jobGroupOptions = jobGroups.map(g => ({
            value: g.id,
            label: g.name
        }))

        const availableUserOptions = availableUsers.map(u => ({
            value: u.id,
            label: u.name
        }))

        const form = useForm({
            job_group_id: shift.job_group_id,
            shift_date: shift.shift_date,
            start_time: shift.start_time,
            end_time: shift.end_time,
            admin_comment: shift.admin_comment,
            required_employees: shift.required_employees,
        })

        const addUserForm = useForm({
            user_id: null,
            user_comment: '',
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

        const addUser = () => {
            addUserForm.post(route('shifts.addUser', shift.id), {
                preserveScroll: true,
                onSuccess: () => {
                    addUserForm.reset()
                },
            })
        }

        const removeUser = (user) => {
            if (confirm(`Möchten Sie ${user.name} wirklich aus dieser Schicht entfernen?`)) {
                router.delete(route('shifts.removeUser', [shift.id, user.id]), {
                    preserveScroll: true,
                })
            }
        }

        const quickAddUser = (user) => {
            router.post(route('shifts.addUser', shift.id), {
                user_id: user.id,
                user_comment: null,
            }, {
                preserveScroll: true,
            })
        }

        const quickDeclineUser = (user) => {
            router.post(route('shifts.declineUser', shift.id), {
                user_id: user.id,
                reason: null,
            }, {
                preserveScroll: true,
            })
        }

        const removeDecline = (user) => {
            if (confirm(`Möchten Sie die Absage von ${user.name} wirklich entfernen?`)) {
                router.delete(route('shifts.removeDecline', [shift.id, user.id]), {
                    preserveScroll: true,
                })
            }
        }

        return {
            title,
            form,
            addUserForm,
            destroy,
            update,
            addUser,
            removeUser,
            quickAddUser,
            quickDeclineUser,
            removeDecline,
            jobGroupOptions,
            availableUserOptions,
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