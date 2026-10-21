<template>
    <div class="space-y-6">
        <div class="bg-white shadow-sm rounded-lg divide-y divide-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    class="p-2 rounded-md border border-gray-300 text-gray-700"
                                    aria-label="Vorheriger Tag"
                                    @click="shiftDay(-1)">
                                &lsaquo;
                            </button>

                            <!-- Unsichtbares Datumsfeld über dem Knopf: Antippen öffnet den Kalender des Geräts -->
                            <label class="relative flex-1 px-3 py-2 rounded-md border border-gray-300 text-center cursor-pointer">
                                <strong>{{ $date(day).format('dddd') }} {{ $date(day).format('L') }}</strong>
                                <span class="ml-1 text-gray-500">&#128197;</span>
                                <input type="date"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       aria-label="Tag auswählen"
                                       :value="day"
                                       @change="selectDay($event.target.value)"/>
                            </label>

                            <button type="button"
                                    class="p-2 rounded-md border border-gray-300 text-gray-700"
                                    aria-label="Nächster Tag"
                                    @click="shiftDay(1)">
                                &rsaquo;
                            </button>
                        </div>

                        <div v-if="!isToday" class="mt-2 text-center">
                            <button type="button" class="text-sm text-tenant-600 underline" @click="selectDay(todayString)">
                                Zurück zu heute
                            </button>
                        </div>

                        <div v-if="findTournament(day) !== ''" class="mt-2 text-yellow-600 leading-none truncate">
                          <small>
                            {{ findTournament(day) }}
                          </small>
                        </div>
                    </div>
                    <div
                        v-for="user in users"
                        class="space-y-2"
                        :key="`dailyView--${user.id}`"
                    >
                        <div>
                            {{ user.name }}
                        </div>

                        <ArbeitsplanSlot
                            :date="day"
                            :user="user"
                            :arbeitszeit="findArbeitszeit(user, day)"
                        />

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {computed, ref} from 'vue'
import ArbeitsplanSlot from '@/Components/ArbeitsplanSlot.vue'

export default {

    components: {
        ArbeitsplanSlot,
    },

    props: {
        // Angezeigter Tag im Format YYYY-MM-DD
        day: {
            type: String,
            required: true
        },
        users: {
            type: Array,
            default: () => [],
        },
      tournaments : {
        type: Object,
        default: () => {},
      }
    },

    emits: ['select-day'],

    setup(props, {emit}) {

        const loading = ref(false)

        const todayString = dayjs().format('YYYY-MM-DD')
        const isToday = computed(() => props.day === todayString)

        const selectDay = (value) => {
            if (value && value !== props.day) {
                emit('select-day', value)
            }
        }

        const shiftDay = (days) => selectDay(dayjs(props.day).add(days, 'day').format('YYYY-MM-DD'))

        // function to find tournaments in tournaments by date
        const findTournament = (date) => {
          return props.tournaments[date] ? props.tournaments[date][0].turniername : ''
        }

      // Funcion to find arbeitszeit in user by comparing two date strings
      const findArbeitszeit = (user, date) => {
        return user.arbeitszeiten.find(arbeitszeit => {
          return arbeitszeit.tag === date
        })
      }


        return {
            loading, todayString, isToday, selectDay, shiftDay, findTournament, findArbeitszeit
        }

    },
}
</script>
