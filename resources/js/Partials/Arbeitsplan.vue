<template>
  <div class="space-y-6">
    <div
        v-for="(week, weekIndex) in weeks"
        :key="`week--${weekIndex}`"
        class="bg-white shadow-sm rounded-lg divide-y divide-gray-200"
    >

      <div class="px-4 py-5 sm:px-6">
        <div class="grid grid-cols-8 gap-4">
          <div>
            <strong>{{ $date(week[0].date).format('L') }} - {{ $date(week[6].date).format('L') }} </strong>
          </div>
          <div
              v-for="(day, dayIndex) in weekDays"
              :key="`weekday-${dayIndex}-${weekIndex}`"
          >
            <div class="font-semibold">
              {{ day }}
            </div>
            <Link :href="route('shifts.create', { date: week[dayIndex].date })" class="hover:underline">
              <small>{{ $date(week[dayIndex].date).format('L') }}</small>
            </Link>
            <div v-if="findTournament(week[dayIndex].date) !== ''" class="text-yellow-600 leading-none truncate">
              <small>
                {{ findTournament(week[dayIndex].date) }}
              </small>
            </div>
            <template v-if="getShiftInfo(week[dayIndex].date)">
              <Link v-for="(shiftInfo, si) in getShiftInfo(week[dayIndex].date)"
                   :key="`shift-${dayIndex}-${weekIndex}-${si}`"
                   :href="route('shifts.edit', shiftInfo.id)"
                   v-tippy="{ content: shiftInfo.tooltip, allowHTML: true }"
                   class="text-xs cursor-pointer truncate block hover:underline" :class="{ 'mt-1': si === 0 }">
                <span class="text-gray-500">{{ shiftInfo.groupLabel }}</span>
                (<span class="text-yellow-600 font-semibold">{{ shiftInfo.required }}</span>
                 / {{ shiftInfo.enrolled }}
                 / {{ shiftInfo.noResponse }})
              </Link>
            </template>
          </div>
        </div>
      </div>

      <div class="px-4 divide-y divide-gray-200">
        <div
            v-for="user in users"
            :key="`user--${weekIndex}--${user.id}`"
            class="grid grid-cols-8 gap-4 py-4"
        >
          <div>
            {{ user.name }}
          </div>
          <div
              v-for="(day, dayIndex) in week"
              :key="`week-day--${dayIndex}--${weekIndex}`"
          >

            <ArbeitsplanSlot
                :date="day.date"
                :user="user"
                :arbeitszeit="findArbeitszeit(user, day.date)"
            />

          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref } from 'vue'
import { getCalendar } from '@/utils/arbeitsplan.js'
import ArbeitsplanSlot from '@/Components/ArbeitsplanSlot.vue'
import { Link } from '@inertiajs/vue3'
import { directive } from 'vue-tippy'

export default {

  components: {
    ArbeitsplanSlot,
    Link,
  },

  directives: {
    tippy: directive,
  },

  props: {
    month: {
      type: Number,
      required: true,
    },
    year: {
      type: Number,
      required: true,
    },
    users: {
      type: Array,
      default: () => [],
    },
    tournaments: {
      type: Object,
      default: () => {},
    },
    feiertage: {
      type: Object,
      default: () => {},
    },
    shifts: {
      type: Object,
      default: () => ({}),
    },
  },

  setup (props) {

    const weekDays = ['Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag']

    const loading = ref(false)

    const weeks = ref([])

    weeks.value = getCalendar(props.year, props.month)

    // Funcion to find arbeitszeit in user by comparing two date strings
    const findArbeitszeit = (user, date) => {
      return user.arbeitszeiten.find(arbeitszeit => {
        return arbeitszeit.tag === date
      })
    }

    // function to find tournaments in tournaments by date
    const findTournament = (date) => {
      return props.tournaments[date] ? props.tournaments[date][0].turniername : props.feiertage[date] ? props.feiertage[date].name : ''
    }

    const getShiftInfo = (date) => {
      const dayShifts = props.shifts[date]
      if (!dayShifts || dayShifts.length === 0) return null

      return dayShifts.map(shift => {
        const shiftEnrolled = shift.users ? shift.users.length : 0
        const enrolledIds = shift.users ? shift.users.map(u => u.id) : []
        const declinedIds = shift.unavailable_users ? shift.unavailable_users.map(u => u.id) : []
        const confirmedNames = shift.users ? shift.users.map(u => u.name) : []
        const declinedNames = shift.unavailable_users ? shift.unavailable_users.map(u => u.name) : []
        const noResponseNames = []

        if (shift.job_group && shift.job_group.users) {
          shift.job_group.users.forEach(u => {
            if (!enrolledIds.includes(u.id) && !declinedIds.includes(u.id)) {
              noResponseNames.push(u.name)
            }
          })
        }

        const groupLabel = shift.job_group ? shift.job_group.name : ''

        let tooltip = ''
        if (groupLabel) {
          tooltip += '<strong>Gruppe:</strong> ' + groupLabel
        }
        tooltip += (tooltip ? '<br>' : '') + '<strong>Angefragt:</strong> ' + shift.required_employees
        tooltip += '<br><strong>Zusagen:</strong> ' + shiftEnrolled
        tooltip += '<br><strong>Keine Antwort:</strong> ' + noResponseNames.length
        if (confirmedNames.length > 0) {
          tooltip += '<br><br>' + confirmedNames.join('<br>')
        }
        if (declinedNames.length > 0) {
          tooltip += '<br><br><strong>Absagen:</strong><br>' + declinedNames.join('<br>')
        }
        if (noResponseNames.length > 0) {
          tooltip += '<br><br><strong>Keine Antwort:</strong><br>' + noResponseNames.join('<br>')
        }

        return {
          id: shift.id,
          required: shift.required_employees,
          enrolled: shiftEnrolled,
          noResponse: noResponseNames.length,
          tooltip,
          groupLabel,
        }
      })
    }

    const getNoOfDays = () => {
      weeks.value = getCalendar(props.year, props.month)
    }

    return {
      weekDays, loading, weeks, findArbeitszeit, getNoOfDays, findTournament, getShiftInfo,
    }

  },

  watch: {
    month () {
      this.getNoOfDays()
    },
  },
}
</script>
