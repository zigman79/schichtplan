<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav class="bg-white border-b border-gray-100">
                <!-- Primary Navigation Menu -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex-shrink-0 flex items-center">
                                <Link :href="route('dashboard')">
                                    <BreezeApplicationLogo class="block h-9 w-auto"/>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <template
                                    v-for="item in navigation"
                                    :key="`nav-${item.name}`"
                                >
                                    <BreezeNavLink
                                        v-if="item.if"
                                        :href="route(item.route)"
                                        :active="route().current(item.active)"
                                    >
                                        {{ item.name }}
                                    </BreezeNavLink>
                                </template>

                            </div>
                        </div>

                        <div class="hidden sm:flex sm:items-center sm:ml-6">
                            <!-- Settings Dropdown -->
                            <div class="ml-3 relative">
                                <BreezeDropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button type="button"
                                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                                {{ $page.props.auth.user.name }}

                                                <svg class="ml-2 -mr-0.5 h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                                     viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                          d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                          clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <BreezeDropdownLink v-if="switched" :href="route('switch-back')" as="button">
                                            Zurück zu meinem Account
                                        </BreezeDropdownLink>
                                        <BreezeDropdownLink :href="route('logout')" method="post" as="button">
                                            Abmelden
                                        </BreezeDropdownLink>
                                    </template>
                                </BreezeDropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-mr-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = ! showingNavigationDropdown"
                                    class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                    <path
                                        :class="{'hidden': showingNavigationDropdown, 'inline-flex': ! showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"/>
                                    <path
                                        :class="{'hidden': ! showingNavigationDropdown, 'inline-flex': showingNavigationDropdown }"
                                        stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div :class="{'block': showingNavigationDropdown, 'hidden': ! showingNavigationDropdown}"
                     class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <template v-for="item in navigation" :key="`nav-mobile-${item.name}`">
                            <BreezeResponsiveNavLink
                                v-if="item.if"
                                :href="route(item.route)"
                                :active="route().current(item.active)"
                            >
                                {{ item.name }}
                            </BreezeResponsiveNavLink>
                        </template>

                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-gray-200">
                        <div class="px-4">
                            <div class="font-medium text-base text-gray-800">{{ $page.props.auth.user.name }}</div>
                            <div class="font-medium text-sm text-gray-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <BreezeResponsiveNavLink v-if="switched" :href="route('switch-back')" as="button">
                                Zurück zu meinem Account
                            </BreezeResponsiveNavLink>
                            <BreezeResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Abmelden
                            </BreezeResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white" v-if="$slots.header" :class="[{'shadow' : !$slots.subHeader}]">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    <slot name="header"/>
                </div>
            </header>

            <header class="bg-white shadow -mt-px" v-if="$slots.subHeader">
                <div class="max-w-7xl mx-auto pb-4 px-4 sm:px-6 lg:px-8">
                    <div class="border-t border-gray-200 mb-4"></div>
                    <slot name="subHeader"/>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <FlashMessages/>
                <slot/>
            </main>
        </div>
    </div>
</template>

<script>
import BreezeApplicationLogo from '@/Components/ApplicationLogo.vue'
import BreezeDropdown from '@/Components/Dropdown.vue'
import BreezeDropdownLink from '@/Components/DropdownLink.vue'
import BreezeNavLink from '@/Components/NavLink.vue'
import BreezeResponsiveNavLink from '@/Components/ResponsiveNavLink.vue'
import {Link, usePage} from '@inertiajs/inertia-vue3'
import FlashMessages from '@/Components/FlashMessages'
import {ref} from 'vue'

export default {
    components: {
        FlashMessages,
        BreezeApplicationLogo,
        BreezeDropdown,
        BreezeDropdownLink,
        BreezeNavLink,
        BreezeResponsiveNavLink,
        Link,
    },

    setup() {

        const isAdmin = usePage().props.value.auth.user.arbeitszeit_admin
        const isTeamLeader = usePage().props.value.auth.user.arbeitszeit_teamleader || usePage().props.value.auth.user.arbeitszeit_admin
        const switched = usePage().props.value.auth.switched
        const genderLanguage = usePage().props.value.genderLanguage || false

        const showingNavigationDropdown = ref(false)

        const navigation = [
            {
                'name': 'Arbeitsplan',
                'route': 'dashboard',
                'active': 'dashboard',
                'if': true,
            },
            {
                'name': `Benutzer${genderLanguage ? ':innen' : ''}`,
                'route': 'users.index',
                'active': 'users.index',
                'if': isAdmin,
            },
            {
                'name': 'Minijob-Zeiten',
                'route': 'minijobvorgabe.index',
                'active': 'minijobvorgabe.index',
                'if': isAdmin,
            },
            {
                'name': 'Minijob-Gruppen',
                'route': 'minijobGroups.index',
                'active': 'minijobGroups.index',
                'if': isAdmin,
            },
            {
                'name': 'Jobgruppen',
                'route': 'jobGroups.index',
                'active': 'jobGroups.*',
                'if': isTeamLeader,
            },
            {
                'name': 'Schichten',
                'route': 'shifts.index',
                'active': 'shifts.*',
                'if': isTeamLeader,
            },
            {
                'name': 'Wochenplan',
                'route': 'wochenplan',
                'active': 'wochenplan',
                'if': true,
            },
        ]

        return {
            showingNavigationDropdown,
            isAdmin,
            switched,
            navigation,
        }
    },
}
</script>
