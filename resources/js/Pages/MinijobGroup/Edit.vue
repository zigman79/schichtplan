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
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

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
            </div>
        </div>


    </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'

import {ChevronRightIcon, PlusIcon} from '@heroicons/vue/solid'
import {Head, Link} from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import {useForm} from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import SelectBox from '@/Components/SelectBox'
import InputError from '@/Components/InputError'
import {Switch, SwitchDescription, SwitchGroup, SwitchLabel} from '@headlessui/vue'
import Button from "@/Components/Button";
import { directive } from 'vue-tippy'

export default {
    directives: {
        tippy: directive,
    },
    props: {
        group: Object,
    },
    setup(props, {attrs}) {


        const title = 'Minijob-Gruppe'

        const {group} = props

        const form = useForm({
            id: group.id,
            name: group.name,
        })

        const update = () => {
            form.transform((data) => ({
                ...data,
            }))
                .put(route('minijobGroups.update', group.id), {
                    preserveScroll: true,
                    preserveState: false,
                })
        }

        const destroy = () => {
            confirm('Sind Sie sicher?') && form.delete(route('minijobGroups.destroy', group.id))
        }

        return {
            title,
            form,
            destroy,
            update,
        }
    },
    components: {
        Button,
        InputError,
        SelectBox,
        BreezeAuthenticatedLayout,
        BreezeResponsiveNavLink,
        SpinningLoader,
        ChevronRightIcon,
        PlusIcon,
        Head,
        Link,
        Input,
        Label,
        Switch,
        SwitchDescription,
        SwitchGroup,
        SwitchLabel,
    },
}
</script>
