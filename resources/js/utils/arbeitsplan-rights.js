import {computed} from "vue";
import {usePage} from "@inertiajs/inertia-vue3";

export function slotRights (props) {
    // Computed Boolean if user is admin
    const isAdmin = computed(() => usePage().props.value.auth.user.arbeitszeit_admin || usePage().props.value.auth.superTeamleaders.includes(usePage().props.value.auth.user.name));
    // Computed Boolean if user is Teamleader
    const isTeamleader = computed(() => usePage().props.value.auth.user.arbeitszeit_teamleader)
    // Computed Boolean if user is current User
    const isCurrentUser = computed(() => usePage().props.value.auth.user.id === props.user.id)

    // Computed property if admin or teamleader or currentuser
    const hasEditRights = computed(() => isAdmin.value || isTeamleader.value || isCurrentUser.value)

    // Computed property to check if date is today or older
    const isOld = computed(() => {
        const today = new Date()
        const date = new Date(props.date)
        return date < today
    })

    // Computed property to check if the date is today
    const isToday = computed(() => {
        const today = new Date()
        const date = new Date(props.date)
        return date.getDate() === today.getDate() && date.getMonth() === today.getMonth() && date.getFullYear() === today.getFullYear()
    })

    // Computed property to check if the date is in the future
    const isFuture = computed(() => {
        const today = new Date()
        const date = new Date(props.date)
        return date > today
    })

    // Computed combine of isOld and !isToday and !isAdmin
    const isOldAndNotToday = computed(() => {
        return isOld.value && !isToday.value && !isAdmin.value
    })

    // Computed combine of (has no edit rights or is today) and is not Admin
    const isNotEditable = computed(() => {
        return (!hasEditRights.value || isToday.value) && !isAdmin.value
    })

    // Computed combine of !hasEditRights && !isAdmin
    const isNotEditableAndNotAdmin = computed(() => {
        return !hasEditRights.value && !isAdmin.value
    })

    return {
        isAdmin,
        isTeamleader,
        isCurrentUser,
        hasEditRights,
        isOld,
        isToday,
        isOldAndNotToday,
        isNotEditable,
        isNotEditableAndNotAdmin,
        isFuture
    }

}
