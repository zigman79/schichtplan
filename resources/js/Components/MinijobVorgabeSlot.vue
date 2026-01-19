<template>
    <form
        @submit.prevent="submitOrUpdate"
        class="p-4 border-gray-200 border rounded"
        :class="[{'bg-gray-50' : altBackground}]"
    >
        <label v-if="label" :for="`hours-${form.month}`" class="font-semibold">{{ label }}</label>
        <div class="grid sm:grid-cols-2 gap-2">
            <div>
                <label :for="`hours-${form.month}`"
                       class="block text-sm font-semibold text-gray-700">Arbeitszeit</label>
                <number-input
                    required
                    v-model="form.hours"
                    class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    :id="`hours-${month}`"
                    :precision="3"
                />
            </div>
            <div>
                <label :for="`hours-${form.away}`" class="block text-sm font-semibold text-gray-700">Krank /
                    Urlaub</label>
                <number-input
                    required
                    v-model="form.away"
                    class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                    :id="`hours-${form.away}`"
                    :precision="3"
                />
            </div>
        </div>
        <div class="w-full mt-2" :class="[{'opacity-50 pointer-events-none' : !isDirty}]">
            <button
                class="w-full bg-tenant-600 hover:bg-tenant-700  focus:ring-tenant-500 flex justify-center items-center px-4 py-2 border border-transparent shadow-sm text-white text-sm font-semibold rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2"
                type="submit"
            >
                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing & saving"/>
                <span>Speichern</span>
            </button>
        </div>
    </form>
</template>

<script>
import NumberInput from '@/Components/NumberInput'
import SpinningLoader from '@/Components/SpinningLoader'
import {useForm} from '@inertiajs/inertia-vue3'
import {computed, ref, watch} from 'vue'

export default {
    name: 'MinijobVorgabeSlot',
    props: {
        label: {
            type: String,
            required: false,
        },
        month: {
            type: Number,
            required: true,
        },
        year: {
            type: Number,
            required: true,
        },
        altBackground: {
            type: Boolean,
            default: false,
        },
        vorgabe: {
            type: Object,
            default: () => ({}),
        },
        groupId: {
            type: Number,
            required: true,
        },
    },
    components: {
        NumberInput,
        SpinningLoader,
    },
    setup(props) {

        const {month, year, vorgabe} = props

        /**
         * Form Functions
         */

        const form = useForm({
            id: null,
            month: month,
            year: year,
            hours: null,
            away: null,
            group_id: null,
        })

        const saving = ref(false)

        const setFormFromProps = () => {

            if (vorgabe) {
                form.id = vorgabe.id
                form.month = month
                form.year = year
                form.hours = vorgabe.hours
                form.away = vorgabe.away
                form.group_id = props.groupId
            }
        }

        // if props arbeitszeit changes set form values
        watch(() => props.vorgabe, () => {
            setFormFromProps()
        })

        if (props.vorgabe) {
            setFormFromProps()
        }

        const submit = () => {
            saving.value = true
            form.post(`/minijobvorgaben`, {
                preserveScroll: true, onSuccess: () => saving.value = false,
            })
        }

        const update = () => {
            saving.value = true
            form.put(`/minijobvorgaben/${form.id}`, {
                preserveScroll: true, onSuccess: () => saving.value = false,
            })
        }

        // computed isDirty
        const isDirty = computed(() => {
            return form.hours !== vorgabe.hours || form.away !== vorgabe.away
        })

        const submitOrUpdate = () => {
            if (form.id) {
                update()
            } else {
                submit()
            }
        }

        return {
            vorgabe,
            form,
            submitOrUpdate,
            saving,
            isDirty,
        }
    },
}
</script>
