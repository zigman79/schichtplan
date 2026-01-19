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

                <div class="bg-white rounded-md shadow overflow-x-auto">
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
                                    class="w-full border-gray-300 focus:border-tenant-300 focus:ring focus:ring-tenant-200 focus:ring-opacity-50 rounded-md shadow-sm"
                                    rows="3"
                                    v-model="form.admin_comment"
                                    :class="{ 'border-red-500': form.errors.admin_comment }"
                                ></textarea>
                                <InputError :message="form.errors.admin_comment"/>
                            </div>

                        </div>



                        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">

                            <button
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                type="submit"
                            >
                                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                                <span>{{title}} hinzufügen</span>
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
import { Head, Link } from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import { useForm } from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import SelectBox from '@/Components/SelectBox'
import InputError from '@/Components/InputError'

export default {
    props: {
        jobGroups: Array,
    },
    setup (props) {

        const title = 'Schicht'

        const jobGroupOptions = props.jobGroups.map(g => ({ 
            value: g.id, 
            label: g.name 
        }))

        const form = useForm({
            job_group_id: jobGroupOptions[0]?.value || null,
            shift_date: null,
            start_time: '08:00',
            end_time: '17:00',
            admin_comment: null,
            required_employees: 1,
        })

        const create = () => {
            form
                .transform((data) => ({
                    ...data,
                }))
                .post(route('shifts.store'), {
                    preserveScroll: true,
                })
        }

        return {
            form,
            create,
            title,
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