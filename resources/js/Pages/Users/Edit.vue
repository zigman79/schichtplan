<template>
    <Head title="Benutzer bearbeiten"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ title }} / <span class="text-tenant-600">{{ user.name }}</span>
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
                            <div>
                                <Label>
                                    E-Mail
                                </Label>
                                <Input
                                    class="w-full"
                                    type="email"
                                    v-model="form.email"
                                />
                                <InputError :message="form.errors.email"/>
                            </div>
                            <div>
                                <Label>
                                    Telegram
                                </Label>
                                <Input
                                    class="w-full"
                                    type="text"
                                    v-model="form.telegram_id"
                                />
                                <InputError :message="form.errors.telegram_id"/>
                            </div>
                            <div>
                                <Label>
                                    Chip Id
                                </Label>
                                <Input
                                    class="w-full"
                                    type="text"
                                    v-model="form.chip_id"
                                />
                                <InputError :message="form.errors.chip_id"/>
                            </div>
                            <div>
                                <Label>
                                    Eingestellt am
                                </Label>
                                <Input
                                    class="w-full"
                                    type="date"
                                    v-model="form.eingestellt_am"
                                />
                                <InputError :message="form.errors.eingestellt_am"/>
                            </div>
                            <div>
                                <Label>
                                    Druck Sort
                                </Label>
                                <Input
                                    class="w-full"
                                    type="number"
                                    v-model="form.druck_sort"
                                />
                                <InputError :message="form.errors.druck_sort"/>
                            </div>
                            <div>
                                <Label>
                                    Passwort
                                </Label>
                                <Input
                                    class="w-full"
                                    v-model="form.password"
                                    type="password"
                                    autocomplete="new-password"
                                />
                                <InputError :message="form.errors.password"/>
                            </div>
                            <div>
                                <Label>
                                    Rechte
                                </Label>
                                <SelectBox
                                    class="w-full"
                                    :options="roles"
                                    v-model="form.role"
                                />
                                <InputError :message="form.errors.role"/>
                            </div>

                            <div class="lg:col-span-2 space-y-8">
                                <SwitchGroup as="div" class="flex items-center justify-between">
                                    <span class="flex-grow flex flex-col">
                                        <SwitchLabel as="span" class="text-sm font-bold text-gray-900"
                                                     passive>Keine Arbeitszeiten
                                        </SwitchLabel>
                                        <SwitchDescription as="span" class="text-sm text-gray-500">Wird beim Arbeitsplan
                                            und den PDF's nicht angezeigt.
                                        </SwitchDescription>
                                    </span>
                                    <Switch v-model="form.keine_arbeitszeit"
                                            :class="[form.keine_arbeitszeit ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                                        <span aria-hidden="true"
                                              :class="[form.keine_arbeitszeit ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']"/>
                                    </Switch>
                                </SwitchGroup>

                                <SwitchGroup as="div" class="flex items-center justify-between">
                                    <span class="flex-grow flex flex-col">
                                        <SwitchLabel as="span" class="text-sm font-bold text-gray-900" passive>Minijob
                                        </SwitchLabel>
                                        <SwitchDescription as="span" class="text-sm text-gray-500">Es werden die
                                            Minijob-Zeiten für die Pläne verwendet.
                                        </SwitchDescription>
                                    </span>
                                    <Switch v-model="form.minijob"
                                            :class="[form.minijob ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                                        <span aria-hidden="true"
                                              :class="[form.minijob ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']"/>
                                    </Switch>
                                </SwitchGroup>
                            </div>

                            <template v-if="form.minijob">
                                <div class="lg:col-span-2">
                                    <Label class="text-sm font-bold text-gray-900">
                                        Minijob-Gruppe
                                    </Label>
                                    <select v-model="form.minijob_group"
                                            class="border-gray-300 focus:border-tenant-300 focus:ring focus:ring-tenant-200 focus:ring-opacity-50 rounded-md shadow-sm w-full">
                                        <template v-for="group in minijobGroupSelects" :key="group.value ?? 'none'">
                                            <option :value="group.value">
                                                {{ group.label }}
                                            </option>
                                        </template>
                                    </select>

                                    <InputError :message="form.errors.minijob_group"/>
                                </div>
                            </template>

                            <div v-if="jobGroups.length > 0" class="lg:col-span-2 space-y-4">
                                <div class="space-y-2">
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t border-gray-300"/>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-2 bg-white text-sm text-gray-500">
                                                Jobgruppen (für Schichtplanung)
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="space-y-2 pt-4">
                                        <div v-for="jobGroup in jobGroups" :key="jobGroup.id" class="flex items-center">
                                            <input
                                                :id="`jobgroup-${jobGroup.id}`"
                                                type="checkbox"
                                                :value="jobGroup.id"
                                                v-model="form.job_groups"
                                                class="h-4 w-4 text-tenant-600 focus:ring-tenant-500 border-gray-300 rounded"
                                            />
                                            <label :for="`jobgroup-${jobGroup.id}`" class="ml-3 text-sm font-medium text-gray-700">
                                                {{ jobGroup.name }}
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <InputError :message="form.errors.job_groups" class="mt-2"/>
                                </div>
                            </div>

                            <div class="lg:col-span-2 space-y-4">
                                <div class="space-y-2">
                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t border-gray-300"/>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-2 bg-white text-sm text-gray-500">
                                                API-Token für mobile App
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="pt-4 space-y-4">
                                        <div class="flex items-center justify-between">
                                            <div class="text-sm text-gray-700">
                                                <p class="font-medium">API-Zugang für Schicht-Anmeldungen</p>
                                                <p class="text-gray-500 mt-1">Generieren Sie einen QR-Code für die mobile App</p>
                                            </div>
                                            <button
                                                @click="generateToken"
                                                type="button"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                            >
                                                Token generieren
                                            </button>
                                        </div>

                                        <div v-if="qrData" class="border rounded-lg p-6 bg-gray-50">
                                            <div class="flex flex-col items-center space-y-4">
                                                <div class="bg-white p-4 rounded-lg shadow">
                                                    <img :src="qrCodeUrl" alt="QR Code" class="w-64 h-64" />
                                                </div>
                                                <div class="w-full space-y-2">
                                                    <p class="text-sm font-medium text-gray-700">API-Token (geheim halten!):</p>
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="text"
                                                            :value="tokenDisplay"
                                                            readonly
                                                            class="flex-1 border-gray-300 rounded-md shadow-sm text-sm bg-white"
                                                        />
                                                        <button
                                                            @click="copyToken"
                                                            type="button"
                                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
                                                        >
                                                            {{ copied ? 'Kopiert!' : 'Kopieren' }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <template v-if="isAdmin">
                                <a v-for="year in years" :key="`overview-button-${year.value}`" target="_blank"
                                   :href="route('print.overview', [ year.value, form.id])">
                                    <Button
                                        v-tippy="{content: year.value === currentYear && isJanuary ? 'Im aktuellen Jahr erst nach dem Januar verfügbar': undefined}"
                                        :disabled="year.value === currentYear && isJanuary" type="button"
                                        class="w-full">
                                        <div class="mx-auto">{{ year.label }}</div>
                                    </Button>
                                </a>
                            </template>

                            <template v-if="form.role !== 'administrator'">

                                <div class="space-y-6 lg:col-span-2">

                                    <div class="relative">
                                        <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                            <div class="w-full border-t border-gray-300"/>
                                        </div>
                                        <div class="relative flex justify-center">
                                            <span class="px-2 bg-white text-sm text-gray-500">
                                                Teammitglieder
                                            </span>
                                        </div>
                                    </div>

                                    <div class="divide-y divide-gray-200">
                                        <div v-for="(person, personIdx) in other_users_selected" :key="personIdx"
                                             class="relative flex items-center py-4">
                                            <div class="min-w-0 flex-1 text-sm flex flex-col">
                                                <label :for="`person-${person.id}`"
                                                       class="font-medium text-gray-700 select-none">
                                                    {{ person.name }}<br/>
                                                    <span class="text-sm text-gray-500">{{ person.role }}</span>
                                                </label>
                                            </div>
                                            <div class="ml-3 flex items-center h-5">
                                                <input :id="`person-${person.id}`" :name="`person-${person.id}`"
                                                       v-model="person.selected"
                                                       type="checkbox"
                                                       class="focus:ring-tenant-500 h-4 w-4 text-tenant-600 border-gray-300 rounded"/>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                            </template>

                        </div>


                        <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">

                            <button class="text-red-600 hover:underline" tabindex="-1" type="button" @click="destroy">
                                {{ title }} löschen
                            </button>

                            <button
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                type="submit"
                            >
                                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                                <span>{{ title }} aktualisieren</span>
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
import {Head, Link, usePage} from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import {useForm} from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import SelectBox from '@/Components/SelectBox'
import {arbeitszeit} from '@/utils/roles'
import InputError from '@/Components/InputError'
import {Switch, SwitchDescription, SwitchGroup, SwitchLabel} from '@headlessui/vue'
import Button from "@/Components/Button";
import {directive} from 'vue-tippy'
import {computed, ref} from "vue";
import {Inertia} from '@inertiajs/inertia'

export default {
    directives: {
        tippy: directive,
    },
    props: {
        user: Object,
        other_users: Array,
        minijobGroups: Array,
        jobGroups: Array,
    },
    setup(props, {attrs}) {

        const genderLanguage = usePage().props.value.genderLanguage || false

        const title = `Benutzer${genderLanguage ? ':in' : ''}`

        const roles = arbeitszeit(genderLanguage)

        const isAdmin = usePage().props.value.auth.user.arbeitszeit_admin

        const {user, other_users} = props

        const qrData = ref(user.api_qr_data)
        const tokenDisplay = ref(user.api_qr_data ? JSON.parse(user.api_qr_data).token : null)
        const copied = ref(false)

        const other_users_selected = other_users.map(other_user => {
            return {
                ...other_user,
                selected: user.arbeitszeiten_team.some(x => x.id === other_user.id),
            }
        })

        const minijobGroupSelects = computed(() => {
            const selects = [
                {
                    label: 'Keine Gruppe',
                    value: null,
                }
            ];
            props.minijobGroups.forEach(group => {
                selects.push({
                    label: group.name,
                    value: group.id,
                })
            })
            return selects;
        })

        const getMinijobGroupLabel = (id) => {
            console.log(id)
            const group = minijobGroupSelects.value.find(group => group.value === id) || null;
            return group ? group.label : null;
        }

        const form = useForm({
            id: user.id,
            name: user.name,
            email: user.email,
            telegram_id: user.telegram_id,
            password: null,
            chip_id: user.chip_id,
            eingestellt_am: user.eingestellt_am ? new Date(user.eingestellt_am).toISOString().split('T')[0] : null,
            druck_sort: user.druck_sort,
            keine_arbeitszeit: user.keine_arbeitszeit,
            minijob: user.minijob,
            minijob_group: user.minijob_group ? user.minijob_group.id : null,
            job_groups: user.job_groups.map(g => g.id),
            role: roles.find(r => {
                let role = user.readable_role;
                if (role === `Minijobber${genderLanguage ? ':in' : ''}`) {
                    role = `Mitarbeiter${genderLanguage ? ':in' : ''}`
                }
                return r.label === role
            }).value,
        })

        const update = () => {
            form.transform((data) => ({
                ...data,
                arbeitszeit_admin: data.role === 'administrator',
                arbeitszeit_teamleader: data.role === 'team-leader',
                arbeitszeitenTeam: other_users_selected.filter(u => u.selected).map(u => u.id),
            }))
                .put(`/users/${user.id}`, {
                    preserveScroll: true,
                    preserveState: false,
                })
        }

        const destroy = () => {
            confirm('Sind Sie sicher?') && form.delete(`/users/${user.id}`)
        }

        // computed this and last year
        const years = [
            {
                label: 'Jahresauswertung Dieses Jahr',
                value: new Date().getFullYear(),
            },
            {
                label: 'Jahresauswertung Letztes Jahr',
                value: new Date().getFullYear() - 1,
            },
        ]

        const currentYear = new Date().getFullYear()

        const isJanuary = new Date().getMonth() === 0

        const qrCodeUrl = computed(() => {
            if (!qrData.value) return null
            return `https://api.qrserver.com/v1/create-qr-code/?size=256x256&data=${encodeURIComponent(qrData.value)}`
        })

        const generateToken = () => {
            Inertia.post(route('users.generate-token', user.id), {}, {
                preserveState: false,
                preserveScroll: true,
                onSuccess: () => {
                    // Page reloads and shows QR code from database
                }
            })
        }

        const copyToken = () => {
            if (tokenDisplay.value) {
                navigator.clipboard.writeText(tokenDisplay.value)
                copied.value = true
                setTimeout(() => {
                    copied.value = false
                }, 2000)
            }
        }

        return {
            roles,
            form,
            destroy,
            update,
            other_users_selected,
            isAdmin,
            currentYear,
            isJanuary,
            years,
            title,
            minijobGroupSelects,
            qrData,
            tokenDisplay,
            qrCodeUrl,
            generateToken,
            copyToken,
            copied,
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
