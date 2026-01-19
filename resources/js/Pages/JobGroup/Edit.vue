<template>
    <Head :title="`${title} bearbeiten`"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{title}} / <span class="text-tenant-600">{{ group.name }}</span>
                </h2>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Group Details -->
                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <form @submit.prevent="update">
                        <div class="p-8 gap-8 grid lg:grid-cols-2">
                            <div>
                                <Label>
                                    Name
                                </Label>
                                <Input
                                    class="w-full"
                                    type="text"
                                    v-model="form.name"
                                />
                                <InputError :message="form.errors.name"/>
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

                <!-- User Assignment -->
                <div class="bg-white rounded-md shadow overflow-x-auto">
                    <div class="p-8">
                        <h3 class="text-lg font-semibold mb-4">Mitarbeiter zuordnen</h3>
                        
                        <div class="space-y-2">
                            <div v-for="user in allUsers" :key="user.id" class="flex items-center">
                                <input
                                    :id="`user-${user.id}`"
                                    type="checkbox"
                                    :value="user.id"
                                    v-model="form.users"
                                    class="h-4 w-4 text-tenant-600 focus:ring-tenant-500 border-gray-300 rounded"
                                />
                                <label :for="`user-${user.id}`" class="ml-3 text-sm">
                                    {{ user.name }} <span class="text-gray-500">({{ user.role }})</span>
                                </label>
                            </div>
                        </div>
                        
                        <InputError :message="form.errors.users" class="mt-2"/>
                    </div>

                    <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
                        <button
                            @click="update"
                            type="button"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                        >
                            <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                            <span>Zuordnung speichern</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import {ChevronRightIcon} from '@heroicons/vue/solid'
import {Head, Link} from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import {useForm} from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import InputError from '@/Components/InputError'

export default {
    props: {
        group: Object,
        allUsers: Array,
    },
    setup(props) {

        const title = 'Jobgruppe'

        const {group, allUsers} = props

        const form = useForm({
            id: group.id,
            name: group.name,
            users: group.users.map(u => u.id),
        })

        const update = () => {
            form.transform((data) => ({
                ...data,
            }))
                .put(route('jobGroups.update', group.id), {
                    preserveScroll: true,
                    preserveState: false,
                })
        }

        const destroy = () => {
            confirm('Sind Sie sicher? Alle zugehörigen Schichten werden ebenfalls gelöscht.') && 
            form.delete(route('jobGroups.destroy', group.id))
        }

        return {
            title,
            form,
            destroy,
            update,
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