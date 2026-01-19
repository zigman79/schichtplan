<template>
    <div class="space-y-6">
        <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
            <div class="px-4 py-5 sm:px-6">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <strong> {{ $date(today).format('dddd') }} {{ $date(today).format('L') }} </strong>
                        <div v-if="findTournament(today) !== ''" class="text-yellow-600 leading-none truncate">
                          <small>
                            {{ findTournament(today) }}
                          </small>
                        </div>
                    </div>
                    <div
                        v-for="(user, userIndex) in users"
                        class="space-y-2"
                        :key="`dailyView--${userIndex}`"
                    >
                        <div>
                            {{ user.name }}
                        </div>

                        <ArbeitsplanSlot
                            :date="date()"
                            :user="user"
                            :arbeitszeit="findArbeitszeit(user, date())"
                        />

                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>
import {ref} from 'vue'
import ArbeitsplanSlot from '@/Components/ArbeitsplanSlot'

export default {

    components: {
        ArbeitsplanSlot,
    },

    props: {
        today: {
            type: Date,
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

    setup(props) {

        const loading = ref(false)

        const {today} = props

        // today in format YYYY-MM-DD
        const date = () => dayjs(today).format('YYYY-MM-DD')

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
            loading, date, findTournament, findArbeitszeit
        }

    },
}
</script>
