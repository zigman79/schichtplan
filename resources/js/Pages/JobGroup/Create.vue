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
                                    Name
                                </Label>
                                <Input
                                    class="w-full"
                                    type="text"
                                    v-model="form.name"
                                    :error="form.errors.name"
                                />
                                <InputError :message="form.errors.name"/>
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
import { ChevronRightIcon } from '@heroicons/vue/solid'
import { Head, Link } from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import { useForm } from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import InputError from '@/Components/InputError'

export default {
    setup () {


        const title = 'Jobgruppe'

        const form = useForm({
            name: null
        })

        const create = () => {
            form
                .transform((data) => ({
                    ...data,
                }))
                .post(route('jobGroups.store'), {
                    preserveScroll: true,
                })
        }

        return {
            form,
            create,
            title
        }
    },
    components: {
        InputError,
        BreezeAuthenticatedLayout,
        SpinningLoader,
        ChevronRightIcon,
        Head,
        Link,
        Input,
        Label,
    },
}
</script>