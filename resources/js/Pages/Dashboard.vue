<template>
  <Head title="Arbeitsplan"/>

  <BreezeAuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          Arbeitsplan
        </h2>
            <!-- Filter Section -->
            <input
              id="userFilter"
              v-model="userFilter"
              type="text"
              placeholder="Nach Mitarbeitern filtern..."
              class="block w-64 px-3 py-2 border border-gray-300 rounded-md shadow-xs focus:outline-hidden focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm"
            />

        <div class="shrink-0 mt-2 sm:mt-0 flex-col sm:flex-row flex space-y-2 sm:space-y-0 sm:space-x-2 sm:items-center">

          <template v-if="isAdmin || isTeamLeader || true">
            <a target="_blank" :href="route('print.uebersicht', [ year, month])">
              <Button>
                Monatsübersicht
              </Button>
            </a>
          </template>

          <a target="_blank" :href="route('print.arbeitszeit', [ year, month])">
            <Button>
              Monat als PDF runterladen
            </Button>
          </a>

          <a v-if="isTeamLeader" target="_blank" :href="route('users.trinkgeldliste', authUser.id)">
            <Button>
              Trinkgeldliste erstellen
            </Button>
          </a>

          <div class="shrink-0 hidden lg:flex space-x-2 w-auto">


            <select
                id="month"
                v-model="month"
                name="month"
                class="block w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-hidden focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm rounded-md">
              <option
                  v-for="(month, key) in months"
                  :key="`month-${key}`"
                  :value="key + 1"
              >
                {{ month }}
              </option>
            </select>

            <select
                id="year"
                v-model="year"
                name="year"
                class="block w-32 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-hidden focus:ring-tenant-500 focus:border-tenant-500 sm:text-sm rounded-md">
              <option
                  v-for="(year, key) in years"
                  :key="`year-${key}`"
                  :value="year"
              >
                {{ year }}
              </option>
            </select>

          </div>
        </div>
      </div>
    </template>


    <div class="py-12" v-if="!loading">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="hidden lg:block">
          <Arbeitsplan
              :month="month"
              :year="year"
              :users="filteredUsers"
              :tournaments="tournaments"
              :feiertage="feiertage"
              :shifts="shifts"
              :key="`arbeitsplan-${month}-${year}`"
          />
        </div>
        <div class="lg:hidden">
          <Tagesplan
              :day="selectedDay"
              :users="filteredUsers"
              :key="`tagesplan-${selectedDay}`"
              @select-day="selectDay"
              :tournaments="tournaments"
              :feiertage="feiertage"
          />
        </div>
      </div>
    </div>
    <div v-else class="flex items-center justify-center py-12">
      <div class="flex justify-center items-center space-x-1 text-sm text-gray-700">

        <svg fill='none' class="w-6 h-6 animate-spin" viewBox="0 0 32 32" xmlns='http://www.w3.org/2000/svg'>
          <path clip-rule='evenodd'
                d='M15.165 8.53a.5.5 0 01-.404.58A7 7 0 1023 16a.5.5 0 011 0 8 8 0 11-9.416-7.874.5.5 0 01.58.404z'
                fill='currentColor' fill-rule='evenodd'/>
        </svg>


        <div>Lade ...</div>
      </div>
    </div>
  </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import Button from '@/Components/Button.vue'
import Arbeitsplan from '@/Partials/Arbeitsplan.vue'
import Tagesplan from '@/Partials/Tagesplan.vue'
import { computed, ref, watchEffect, watch } from 'vue'
import dayjs from 'dayjs'

export default {
  props: {
    users: Array,
    month: String,
    year: String,
    tournaments: Object,
    feiertage: Object,
    shifts: Object,
  },
  setup (props, { attrs }) {

    const months = [
      'Januar',
      'Februar',
      'März',
      'April',
      'Mai',
      'Juni',
      'Juli',
      'August',
      'September',
      'Oktober',
      'November',
      'Dezember',
    ]

    const today = new Date()

    const month = ref(parseInt(props.month) || today.getMonth() + 1)
    const year = ref(parseInt(props.year) || today.getFullYear())
    const loading = ref(false)
    const userFilter = ref('')
    const debouncedUserFilter = ref('')

    // Watcher für userFilter mit kurzer Verzögerung
    let filterTimeout = null
    watch(userFilter, (newValue) => {
      // Lösche vorherigen Timeout falls vorhanden
      if (filterTimeout) {
        clearTimeout(filterTimeout)
      }

      filterTimeout = setTimeout(() => {
        debouncedUserFilter.value = newValue
      }, 100)
    })

    // Bewusst computed und kein ref: so wirken sich neue Props (z.B. nach dem
    // Löschen einer Arbeitszeit) sofort aus, ohne den Filter zurückzusetzen
    const filteredUsers = computed(() => {
      const search = debouncedUserFilter.value.trim().toLowerCase()

      if (!search) {
        return props.users
      }

      return props.users.filter(user => user.name.toLowerCase().includes(search))
    })

    // Funktion zum Löschen des Filters
    const clearFilter = () => {
      userFilter.value = ''
    }

    const getYears = () => {
      const currentYear = new Date().getFullYear()
      const years = []
      for (let i = -1; i < 2; i++) {
        years.push(currentYear + i)
      }
      return years
    }

    const years = ref(getYears())

    watchEffect(() => {
      if ((parseInt(props.month) !== month.value) || (parseInt(props.year) !== year.value)) {
        loading.value = true

        router.get(`/dashboard/${year.value}/${month.value}`, {}, {
          onSuccess: () => {
            loading.value = false
          },
        })
      }
    })

    // Mobile Tagesansicht: angezeigter Tag kommt aus ?tag=YYYY-MM-DD, sonst heute
    // bzw. der Monatserste, wenn ein anderer Monat geladen ist
    const isInLoadedMonth = (day) => dayjs(day).month() + 1 === month.value && dayjs(day).year() === year.value
    const urlTag = new URLSearchParams(window.location.search).get('tag')
    const selectedDay = ref(
        urlTag && /^\d{4}-\d{2}-\d{2}$/.test(urlTag) && isInLoadedMonth(urlTag)
            ? urlTag
            : isInLoadedMonth(today)
                ? dayjs(today).format('YYYY-MM-DD')
                : dayjs(new Date(year.value, month.value - 1, 1)).format('YYYY-MM-DD')
    )

    const selectDay = (day) => {
      const url = `/dashboard/${dayjs(day).year()}/${dayjs(day).month() + 1}`

      if (isInLoadedMonth(day)) {
        // Daten sind schon da: nur Tag wechseln und in der URL merken, damit
        // nach dem Speichern (Redirect::back) derselbe Tag wieder erscheint
        selectedDay.value = day
        window.history.replaceState(window.history.state, '', `${url}?tag=${day}`)
        return
      }

      // Anderer Monat: Monatsdaten neu laden
      loading.value = true
      router.get(url, { tag: day }, {
        onFinish: () => {
          loading.value = false
        },
      })
    }

    const authUser = usePage().props.auth.user
    const isAdmin = authUser.arbeitszeit_admin
    const isTeamLeader = authUser.arbeitszeit_teamleader || authUser.arbeitszeit_admin

    return {
      months,
      month,
      year,
      years,
      today,
      selectedDay,
      selectDay,
      loading,
      authUser,
      isAdmin,
      isTeamLeader,
      userFilter,
      filteredUsers,
      clearFilter
    }
  },
  components: {
    Arbeitsplan,
    Tagesplan,
    BreezeAuthenticatedLayout,
    Head,
    Button
  },
}
</script>
