<template>
  <div class="space-y-6">
    <div
        v-for="(week, weekIndex) in weeks"
        :key="`week--${weekIndex}`"
        class="bg-white shadow rounded-lg divide-y divide-gray-200"
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
            <small>{{ $date(week[dayIndex].date).format('L') }}</small>
            <div v-if="findTournament(week[dayIndex].date) !== ''" class="text-yellow-600 leading-none truncate">
              <small>
                {{ findTournament(week[dayIndex].date) }}
              </small>
            </div>
          </div>
        </div>
      </div>

      <div class="px-4 divide-y divide-gray-200">
        <div
            v-for="user in users"
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
import { getCalendar } from '@/utils/arbeitsplan'
import ArbeitsplanSlot from '@/Components/ArbeitsplanSlot'

export default {

  components: {
    ArbeitsplanSlot,
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

    const getNoOfDays = () => {
      weeks.value = getCalendar(props.year, props.month)
    }

    return {
      weekDays, loading, weeks, findArbeitszeit, getNoOfDays, findTournament,
    }

  },

  watch: {
    month () {
      this.getNoOfDays()
    },
  },
}
</script>
