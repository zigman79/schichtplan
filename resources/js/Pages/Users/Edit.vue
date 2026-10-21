<template>
    <Head title="Benutzer bearbeiten"/>

    <BreezeAuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ title }} / <span class="text-tenant-600">{{ user.name }}</span>
                </h2>
                <a :href="route('users.trinkgeldliste', user.id)" target="_blank">
                    <button type="button" class="inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-xs font-semibold rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500">
                        Trinkgeldliste erstellen
                    </button>
                </a>
            </div>
        </template>


        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

                <div class="bg-white rounded-md shadow-sm overflow-x-auto">
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
                                    <span class="grow flex flex-col">
                                        <SwitchLabel as="span" class="text-sm font-bold text-gray-900"
                                                     passive>Keine Arbeitszeiten
                                        </SwitchLabel>
                                        <SwitchDescription as="span" class="text-sm text-gray-500">Wird beim Arbeitsplan
                                            und den PDF's nicht angezeigt.
                                        </SwitchDescription>
                                    </span>
                                    <Switch v-model="form.keine_arbeitszeit"
                                            :class="[form.keine_arbeitszeit ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                                        <span aria-hidden="true"
                                              :class="[form.keine_arbeitszeit ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200']"/>
                                    </Switch>
                                </SwitchGroup>

                                <SwitchGroup as="div" class="flex items-center justify-between">
                                    <span class="grow flex flex-col">
                                        <SwitchLabel as="span" class="text-sm font-bold text-gray-900" passive>Minijob
                                        </SwitchLabel>
                                        <SwitchDescription as="span" class="text-sm text-gray-500">Es werden die
                                            Minijob-Zeiten für die Pläne verwendet.
                                        </SwitchDescription>
                                    </span>
                                    <Switch v-model="form.minijob"
                                            :class="[form.minijob ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                                        <span aria-hidden="true"
                                              :class="[form.minijob ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-sm transform ring-0 transition ease-in-out duration-200']"/>
                                    </Switch>
                                </SwitchGroup>
                            </div>

                            <template v-if="form.minijob">
                                <div class="lg:col-span-2">
                                    <Label class="text-sm font-bold text-gray-900">
                                        Minijob-Gruppe
                                    </Label>
                                    <select v-model="form.minijob_group"
                                            class="border-gray-300 focus:border-tenant-300 focus:ring-3 focus:ring-tenant-200/50 rounded-md shadow-xs w-full">
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
                                                class="h-4 w-4 text-tenant-600 focus:ring-tenant-500 border-gray-300 rounded-sm"
                                            />
                                            <label :for="`jobgroup-${jobGroup.id}`" class="ml-3 text-sm font-medium text-gray-700">
                                                {{ jobGroup.name }}
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <InputError :message="form.errors.job_groups" class="mt-2"/>
                                </div>
                            </div>

                            <div v-if="minijobUebertraege.length > 0" class="lg:col-span-2 space-y-4">
                                <div class="relative">
                                    <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"/>
                                    </div>
                                    <div class="relative flex justify-center">
                                        <span class="px-2 bg-white text-sm text-gray-500">
                                            Minijob-Übertrag (Standard)
                                        </span>
                                    </div>
                                </div>

                                <p class="text-sm text-gray-500">
                                    Abgerechnet werden höchstens die Monatsstunden der Gruppe, Mehrstunden gehen in den nächsten Monat.
                                    Der Übertrag zum 1.1. wird aus dem Vorjahr berechnet. Trägt man einen Wert ein, gilt dieser;
                                    leer speichern rechnet wieder automatisch.
                                </p>

                                <table class="w-full text-sm">
                                    <thead>
                                    <tr class="text-left text-gray-500">
                                        <th class="py-1 pr-4 font-medium">Jahr</th>
                                        <th class="py-1 pr-4 font-medium">Übertrag zum 1.1.</th>
                                        <th class="py-1 pr-4 font-medium">berechnet</th>
                                        <th class="py-1 font-medium text-right">Stand</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr v-for="jahr in minijobUebertraege" :key="jahr.year" class="border-t border-gray-100 align-top">
                                        <td class="py-2 pr-4 font-medium text-gray-900">{{ jahr.year }}</td>
                                        <td class="py-2 pr-4">
                                            <div class="flex items-center gap-2">
                                                <input v-model="uebertragEingaben[jahr.year]"
                                                       type="text"
                                                       :placeholder="jahr.berechnet_nice"
                                                       @keydown.enter.prevent="saveUebertrag(jahr.year)"
                                                       class="w-28 border-gray-300 focus:border-tenant-300 focus:ring-3 focus:ring-tenant-200/50 rounded-md shadow-xs text-sm"/>
                                                <button type="button" @click="saveUebertrag(jahr.year)"
                                                        class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50">
                                                    Speichern
                                                </button>
                                            </div>
                                            <p v-if="jahr.manuell !== null" class="text-xs text-gray-500 mt-1">von Hand eingetragen</p>
                                            <InputError v-if="uebertragJahr === jahr.year" :message="usePage().props.errors.stunden"/>
                                        </td>
                                        <td class="py-2 pr-4 text-gray-500">{{ jahr.berechnet_nice }}</td>
                                        <td class="py-2 text-right text-gray-900">
                                            <template v-if="jahr.stand_nice !== null">
                                                {{ jahr.stand_nice }}
                                                <span class="block text-xs text-gray-500">Ende {{ jahr.stand_monat }}</span>
                                            </template>
                                            <span v-else class="text-gray-400">–</span>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
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
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                            >
                                                Token generieren
                                            </button>
                                        </div>

                                        <div v-if="qrData" class="border rounded-lg p-6 bg-gray-50">
                                            <div class="flex flex-col items-center space-y-4">
                                                <div class="bg-white p-4 rounded-lg shadow-sm">
                                                    <img :src="qrCodeUrl" alt="QR Code" class="w-64 h-64" />
                                                </div>
                                                <div class="w-full space-y-3">
                                                    <p class="text-sm font-medium text-gray-700">API-Token (geheim halten!):</p>
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="text"
                                                            :value="tokenDisplay"
                                                            readonly
                                                            class="flex-1 border-gray-300 rounded-md shadow-xs text-sm bg-white"
                                                        />
                                                        <button
                                                            @click="copyToken"
                                                            type="button"
                                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50"
                                                        >
                                                            {{ copiedToken ? 'Kopiert!' : 'Kopieren' }}
                                                        </button>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <input
                                                            type="text"
                                                            :value="qrData"
                                                            readonly
                                                            class="flex-1 border-gray-300 rounded-md shadow-xs bg-white text-gray-500 font-mono text-xs"
                                                        />
                                                        <button
                                                            @click="copyQrData"
                                                            type="button"
                                                            class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50 whitespace-nowrap"
                                                        >
                                                            {{ copiedQrData ? 'Kopiert!' : 'JSON kopieren' }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
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
                                                Permalink Wochenplan
                                            </span>
                                        </div>
                                    </div>

                                    <div class="pt-4 space-y-4">
                                        <div class="flex items-center justify-between gap-4">
                                            <div class="text-sm text-gray-700">
                                                <p class="font-medium">Wochenansicht fürs Handy, ohne Login</p>
                                                <p class="text-gray-500 mt-1">
                                                    Zeigt die Arbeitszeiten, die {{ user.name }} sehen darf. Nur lesen, Änderungen sind darüber nicht möglich.
                                                    Wer den Link hat, sieht den Plan.
                                                </p>
                                            </div>
                                            <button
                                                v-if="!permalinkUrl"
                                                @click="createPermalink"
                                                type="button"
                                                class="shrink-0 inline-flex items-center px-4 py-2 border border-gray-300 shadow-xs text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                                            >
                                                Permalink erstellen
                                            </button>
                                        </div>

                                        <div v-if="permalinkUrl" class="border rounded-lg p-4 bg-gray-50 space-y-3">
                                            <div class="flex items-center space-x-2">
                                                <input
                                                    type="text"
                                                    :value="permalinkUrl"
                                                    readonly
                                                    @focus="$event.target.select()"
                                                    class="flex-1 min-w-0 border-gray-300 rounded-md shadow-xs bg-white text-gray-600 font-mono text-xs"
                                                />
                                                <button
                                                    @click="copyPermalink"
                                                    type="button"
                                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50 whitespace-nowrap"
                                                >
                                                    {{ copiedPermalink ? 'Kopiert!' : 'Kopieren' }}
                                                </button>
                                                <a
                                                    :href="permalinkUrl"
                                                    target="_blank"
                                                    class="px-3 py-2 border border-gray-300 rounded-md text-sm bg-white hover:bg-gray-50 whitespace-nowrap"
                                                >
                                                    Öffnen
                                                </a>
                                            </div>
                                            <div class="flex items-center justify-between text-sm">
                                                <button type="button" class="text-gray-600 hover:underline" @click="createPermalink">
                                                    Neuen Link erzeugen
                                                </button>
                                                <button type="button" class="text-red-600 hover:underline" @click="deletePermalink">
                                                    Link deaktivieren
                                                </button>
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
                                                       class="focus:ring-tenant-500 h-4 w-4 text-tenant-600 border-gray-300 rounded-sm"/>
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
                                class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-xs font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
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

import {ChevronRightIcon, PlusIcon} from '@heroicons/vue/24/solid'
import { Head, Link, usePage, useForm, router } from '@inertiajs/vue3'
import Input from '@/Components/Input.vue'
import Label from '@/Components/Label.vue'
import SpinningLoader from '@/Components/SpinningLoader.vue'
import SelectBox from '@/Components/SelectBox.vue'
import {arbeitszeit} from '@/utils/roles.js'
import InputError from '@/Components/InputError.vue'
import {Switch, SwitchDescription, SwitchGroup, SwitchLabel} from '@headlessui/vue'
import Button from "@/Components/Button.vue";
import {directive} from 'vue-tippy'
import {computed, reactive, ref, watch} from "vue";
export default {
    directives: {
        tippy: directive,
    },
    props: {
        user: Object,
        other_users: Array,
        minijobGroups: Array,
        jobGroups: Array,
        permalinkUrl: String,
        minijobUebertraege: {type: Array, default: () => []},
    },
    setup(props, {attrs}) {

        const genderLanguage = usePage().props.genderLanguage || false

        const title = `Benutzer${genderLanguage ? ':in' : ''}`

        const roles = arbeitszeit(genderLanguage)

        const isAdmin = usePage().props.auth.user.arbeitszeit_admin

        const {user, other_users} = props

        const qrData = ref(user.api_qr_data)
        const parsedQrData = user.api_qr_data ? JSON.parse(user.api_qr_data) : null
        const tokenDisplay = ref(parsedQrData?.token || null)
        const copiedToken = ref(false)
        const copiedQrData = ref(false)

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
            router.post(route('users.generate-token', user.id), {}, {
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
                copiedToken.value = true
                setTimeout(() => {
                    copiedToken.value = false
                }, 2000)
            }
        }

        const copiedPermalink = ref(false)

        // preserveState: ungespeicherte Eingaben im Formular bleiben erhalten,
        // die neue permalinkUrl kommt trotzdem als Prop an
        const createPermalink = () => {
            if (props.permalinkUrl && !confirm('Der bisherige Link funktioniert danach nicht mehr. Neuen Link erzeugen?')) {
                return
            }

            router.post(route('users.permalink.store', user.id), {}, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const deletePermalink = () => {
            if (!confirm('Link deaktivieren? Er funktioniert danach nicht mehr.')) {
                return
            }

            router.delete(route('users.permalink.destroy', user.id), {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const copyPermalink = () => {
            navigator.clipboard.writeText(props.permalinkUrl)
            copiedPermalink.value = true
            setTimeout(() => {
                copiedPermalink.value = false
            }, 2000)
        }

        const uebertragEingaben = reactive({})
        const uebertragJahr = ref(null)

        // Nach dem Speichern kommen die Werte neu vom Server
        watch(() => props.minijobUebertraege, (jahre) => {
            jahre.forEach(jahr => {
                uebertragEingaben[jahr.year] = jahr.manuell ?? ''
            })
        }, {immediate: true})

        const saveUebertrag = (year) => {
            uebertragJahr.value = year
            router.put(route('users.minijob-uebertrag.update', [user.id, year]), {
                stunden: uebertragEingaben[year],
            }, {
                preserveState: true,
                preserveScroll: true,
            })
        }

        const copyQrData = () => {
            if (qrData.value) {
                const encoder = new TextEncoder()
                const data = encoder.encode(qrData.value)
                const base64Encoded = window.btoa(String.fromCharCode(...data))
                navigator.clipboard.writeText(base64Encoded)
                copiedQrData.value = true
                setTimeout(() => {
                    copiedQrData.value = false
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
            copyQrData,
            copiedToken,
            copiedQrData,
            createPermalink,
            deletePermalink,
            copyPermalink,
            copiedPermalink,
            uebertragEingaben,
            uebertragJahr,
            saveUebertrag,
            usePage,
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
