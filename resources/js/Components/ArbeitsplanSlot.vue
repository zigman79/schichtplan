<template>
  <Popover class="relative -mt-px w-full" v-slot="{ open, close }">

    <PopoverButton
        class="flex w-full rounded-md relative shadow-sm group -space-x-px items-stretch focus-within:outline-none focus-within:ring-2 focus-within:ring-tenant-500 focus-within:ring-opacity-50">
      <div class="relative flex items-stretch flex-grow focus-within:z-10">
        <input
            type="text"
            readonly
            tabindex="-1"
            :value="isToday && !isOldAndNotToday || isFuture ? readableFutureTime : readableTime"
            class="cursor-pointer block w-full sm:text-sm border-gray-300 rounded-l-md focus:ring-transparent focus:outline-none focus:shadow-none px-1 text-center"
        />
      </div>
      <div
          :class="[{'bg-tenant-500 border-tenant-600 text-white' : open}, {'bg-gray-50 text-gray-400 group-hover:bg-gray-100 border-gray-300' : !open }]"
          class="inline-flex justify-center items-center p-2 border text-sm font-semibold rounded-r-md focus:ring-transparent focus:outline-none focus:shadow-none">
        <ChevronDownIcon class="h-4 w-4" :class="[{'transform rotate-180' : open}]" aria-hidden="true"/>
      </div>
    </PopoverButton>

    <transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="translate-y-1 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition duration-150 ease-in"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-1 opacity-0"
    >
      <PopoverPanel
          class="absolute z-10 w-screen sm:max-w-sm px-4 mt-2 transform -translate-x-1/2 left-1/2 sm:px-0"
          :class="[hasBreaks ? 'lg:max-w-2xl' : 'lg:max-w-lg']">


        <div v-if="user.arbeitszeit_admin && !isAdmin || isOldAndNotToday"
             class="absolute inset-0 z-10 rounded-lg overflow-hidden">
          <div class="absolute inset-0 bg-white opacity-75"/>
          <div class="absolute inset-0 z-5 flex justify-center items-center">
            <span class="font-bold text-gray-700 text-xl" v-if="!isTeamleader">
                Bei Änderungen bitte an Teamleiter wenden.
            </span>
            <span class="font-bold text-gray-700 text-xl" v-if="isTeamleader">
                Bei Änderungen bitte an Administrator wenden.
            </span>
          </div>
        </div>


        <form
            @submit.prevent="submitOrUpdate(close)"
            class="overflow-hidden relative rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 bg-white"
        >
          <div
              :class="[{'opacity-50 pointer-events-none filter blur-sm' : isOldAndNotToday || user.arbeitszeit_admin && !isAdmin}]">
            <div :class="[{'grid grid-cols-2 items-start' : hasBreaks}]" class="bg-white">
              <div class="relative grid gap-4 p-7 lg:grid-cols-2">
                <div :class="[{'opacity-50 pointer-events-none' : isNotEditable}]">
                  <label for="begin"
                         class="block text-sm font-semibold text-gray-700">Arbeitsbegin</label>
                  <div class="mt-1">
                    <input v-model="form.beginn"
                           type="time"
                           name="begin"
                           id="begin"
                           @change="resetOther"
                           class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                           placeholder="08:00"
                    />
                  </div>
                  <div v-if="form.errors.beginn">
                    {{ form.errors.beginn }}
                  </div>
                </div>
                <div :class="[{'opacity-50 pointer-events-none' : isNotEditable}]">
                  <label for="end" class="block text-sm font-semibold text-gray-700">Ende</label>
                  <div class="mt-1">
                    <input v-model="form.ende"
                           type="time"
                           name="end"
                           id="end"
                           @change="resetOther"
                           class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                           placeholder="20:00"
                    />
                  </div>
                  <div v-if="form.errors.ende">
                    {{ form.errors.ende }}
                  </div>
                </div>
                <div class="col-span-2"
                     :class="[{'opacity-50 pointer-events-none' : isNotEditableAndNotAdmin}]">
                  <button type="button"
                          @click="addBreak"
                          class="inline-flex w-full justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-semibold rounded-md text-gray-600 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    <PlusIcon class="-ml-1 mr-1 h-4 w-4" aria-hidden="true"/>
                    <span>Pause hinzufügen</span>
                  </button>
                </div>
                <div class="col-span-2"
                     :class="[{'opacity-50 pointer-events-none' : isNotEditable}]">
                  <RadioGroup
                      v-model="form.frei_urlaub_krank"
                      @update:modelValue="resetTimesAndBreaks"
                  >
                    <RadioGroupLabel class="block text-sm font-semibold text-gray-700 sr-only">
                      Andere
                    </RadioGroupLabel>
                    <div class="relative z-0 inline-flex shadow-sm rounded-md w-full -space-x-px">
                      <RadioGroupOption
                          v-for="(option, optionIndex) in otherOptionen"
                          :key="`option--${optionIndex}--${date}--${user.name}`"
                          v-slot="{ checked }"
                          :value="option.value"
                          class="flex-1 relative focus-within:outline-none focus-within:ring-1 focus-within:ring-tenant-500 focus-within:border-tenant-500 focus:z-10"
                      >
                                            <span
                                                class="cursor-pointer w-full relative inline-flex items-center justify-center px-4 py-2 border text-sm font-semibold focus:border-tenant-500"
                                                :class="[
                                                    {'bg-tenant-600 border-tenant-600 text-white ' : checked},
                                                    {'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' : !checked},
                                                    {'rounded-l-md' : optionIndex === 0},
                                                    {'rounded-r-md' : optionIndex === otherOptionen.length -1}
                                                 ]"
                                            >
                                                <span>{{ option.label }}</span>
                                            </span>
                      </RadioGroupOption>
                    </div>
                  </RadioGroup>

                  <div v-if="form.errors.frei_urlaub_krank">
                    {{ form.errors.frei_urlaub_krank }}
                  </div>
                </div>

                <div class="col-span-2"
                     :class="[{'opacity-50 pointer-events-none' : isNotEditableAndNotAdmin}]">

                  <button
                      type="submit"
                      class="flex w-full justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                      :disabled="form.processing"
                  >
                    <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing & saving"/>
                    <span v-if="form.id">Aktualisieren</span>
                    <span v-else>Speichern</span>
                  </button>

                  <button
                      type="button"
                      class="flex mt-2 w-full justify-center items-center px-4 py-2 border border-transparent shadow-sm text-sm font-semibold rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                      :disabled="form.processing"
                      @click="resetForm(close)"
                  >
                    <SpinningLoader tailwind="w-4 h-4 text-whit mr-1" v-if="form.processing & deleting"/>
                    <TrashIcon v-else class="w-5 h-5 text-white mr-1"/>
                    <span v-if="form.id">Löschen</span>
                    <span v-else>Zurücksetzten</span>
                  </button>

                </div>
              </div>
              <div class="bg-gray-50 h-full px-4 py-3 divide-y divide-gray-200"
                   v-if="hasBreaks"
              >
                <div v-for="(pause, pauseIndex) in form.pausen" class="py-4">
                  <div class="flex justify-between items-center">
                    <div class="text-sm font-semibold text-gray-900">
                      {{ pauseIndex + 1 }}. Pause
                    </div>
                    <button type="button" class="text-sm font-semibold text-red-500"
                            @click="removeBreak(pauseIndex)">Löschen
                    </button>
                  </div>
                  <div class="lg:grid-cols-2 grid gap-2">
                    <div>
                      <label :for="`pause_begin__${pauseIndex}`"
                             class="block text-sm font-semibold text-gray-700"
                      >
                        Beginn
                      </label>
                      <div class="mt-1">
                        <input v-model="pause.beginn" type="time"
                               :name="`pause_beginn__${pauseIndex}`"
                               :id="`pause_beginn__${pauseIndex}`"
                               class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="11:00"
                               @change="resetOther"
                        />
                      </div>
                    </div>
                    <div>
                      <label :for="`pause_end__${pauseIndex}`"
                             class="block text-sm font-semibold text-gray-700"
                      >
                        Ende
                      </label>
                      <div class="mt-1">
                        <input v-model="pause.ende" type="time"
                               :name="`pause_ende__${pauseIndex}`"
                               :id="`pause_ende__${pauseIndex}`"
                               class="shadow-sm focus:ring-tenant-500 focus:border-tenant-500 block w-full sm:text-sm border-gray-300 rounded-md"
                               placeholder="12:00"
                               @change="resetOther"
                        />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>


        </form>
      </PopoverPanel>
    </transition>
  </Popover>
</template>

<script>
import { Popover, PopoverButton, PopoverPanel, RadioGroup, RadioGroupLabel, RadioGroupOption } from '@headlessui/vue'
import { ChevronDownIcon, PlusIcon, TrashIcon } from '@heroicons/vue/solid'
import { computed, nextTick, ref, watch } from 'vue'
import { useForm } from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import { slotRights } from '@/utils/arbeitsplan-rights'

export default {
  components: {
    SpinningLoader,
    Popover, PopoverButton, PopoverPanel,
    RadioGroup, RadioGroupLabel, RadioGroupOption,
    ChevronDownIcon, PlusIcon, TrashIcon,
  },
  props: {
    user: {
      type: Object,
      required: true,
    },
    date: {
      type: String,
      required: true,
    },
    arbeitszeit: {
      type: Object,
      required: false,
      default: null,
    },
  },
  setup (props) {

    const otherOptionen = [
        { value: 'frei', label: 'Frei' },
        { value: 'urlaub', label: 'Urlaub' },
        { value: 'krank', label: 'Krank' },
        { value: 'schule', label: 'Schule' },
    ]

    /**
     * Form Functions
     * @type {InertiaForm<{ende: null, frei_urlaub_krank: null, id: null, tag: String, beginn: null}>}
     */

    const form = useForm({
      id: null,
      tag: props.date,
      beginn: null,
      ende: null,
      frei_urlaub_krank: null,
      pausen: [],
    })

    const deleting = ref(false)
    const saving = ref(false)

    // Show frei_urlaub_krank or arbeitszeit
    const readableTime = ref(null)

    // Show frei_urlaub_krank or arbeitszeit
    const readableFutureTime = ref(null)

    const addSeconds = (data) => (
        {
          // return data but add seconds to beginn and ende
          ...data,

          // if beginn length is 5 characters long and has no seconds add them
          beginn: data.beginn ? data.beginn.length === 5 ? data.beginn + ':00' : data.beginn : null,

          // if ende length is 5 characters long and has no seconds add them
          ende: data.ende ? data.ende.length === 5 ? data.ende + ':00' : data.ende : null,

          // if beginn or ende in all pausen length is 5 characters long and has no seconds add them
          pausen: data.pausen.map(pause => {
            return {
              ...pause,
              beginn: pause.beginn ? pause.beginn.length === 5 ? pause.beginn + ':00' : pause.beginn : null,
              ende: pause.ende ? pause.ende.length === 5 ? pause.ende + ':00' : pause.ende : null,
            }
          }),
        }
    )

    const submit = (close) => {
      saving.value = true
      form.transform(addSeconds)
          .post(`/arbeitszeiten/${props.user.id}`, {
            preserveScroll: true, onSuccess: () => {
              close()
              saving.value = false
            },
          })
    }

    const update = (close) => {
      saving.value = true
      form.transform(addSeconds)
          .put(`/arbeitszeiten/${form.id}`, {
            preserveScroll: true, onSuccess: () => {
              close()
              saving.value = false
            },
          })
    }

    const resetForm = (close) => {
      if (form.id) {
        deleting.value = true
        if (confirm('Sicher diese Arbeitszeit zu löschen?')) {
          form.delete(`/arbeitszeiten/${form.id}`, {
            preserveScroll: true,
            preserveState: false,
            onSuccess: () => {
              nextTick(() => form.reset())
              deleting.value = false
              close()
            },
          })
        }
      } else {
        form.reset()
      }
    }

    const setFormFromProps = () => {

      const { arbeitszeit } = props

      if (arbeitszeit) {
        form.id = arbeitszeit.id
        form.tag = props.date
        form.beginn = arbeitszeit.beginn
        form.ende = arbeitszeit.ende
        form.frei_urlaub_krank = arbeitszeit.frei_urlaub_krank
        form.pausen = arbeitszeit.pausen

        readableTime.value = arbeitszeit.readable_time
        readableFutureTime.value = arbeitszeit.readable_future_time

      }
    }

    // if props arbeitszeit changes set form values
    watch(() => props.arbeitszeit, () => {
      setFormFromProps()
    })

    if (props.arbeitszeit) {
      setFormFromProps()
    }

    const submitOrUpdate = (close) => {
      if (form.id) {
        update(close)
      } else {
        submit(close)
      }
    }

    // reset begin and end and pausen when frei_urlaub_krank changes
    const resetTimesAndBreaks = () => {
      if (form.frei_urlaub_krank) {
        form.beginn = null
        form.ende = null
        form.pausen = []
      }
    }

    // reset frei_urlaub_krank when begin or end changes
    const resetOther = () => {
      form.frei_urlaub_krank = null
    }

    // Function to add a start and end time to the pausen array
    const addBreak = (reset = true) => {
      if (reset) {
        resetOther()
      }

      form.pausen.push({
        beginn: null,
        ende: null,
      })
    }

    // Computed property to check if the user has pausen
    const hasBreaks = computed(() => {
      return form.pausen.length > 0
    })

    // Function to remove a break from the pausen array
    const removeBreak = (index) => {
      form.pausen.splice(index, 1)
    }

    return {
      form,
      submitOrUpdate,
      otherOptionen,
      addBreak,
      removeBreak,
      resetForm,
      hasBreaks,
      resetOther,
      resetTimesAndBreaks,
      readableTime,
      readableFutureTime,
      deleting,
      saving,
      ...slotRights(props),
    }

  },
}
</script>
