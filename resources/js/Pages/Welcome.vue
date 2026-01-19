<template>
    <Head title="Willkommen"/>

    <div class="w-screen h-screen flex justify-center items-center p-12 bg-gray-50">

        <div :style="{backgroundImage: backgroundImage}"
             class="relative w-full h-full rounded-lg shadow-2xl bg-center bg-cover bg-no-repeat">

            <div v-if="canLogin" class="hidden absolute top-0 right-0 px-6 py-4 sm:block">

                <Link v-if="$page.props.auth.user" href="/dashboard" class="text-white font-bold">
                    Arbeitsplan
                </Link>

                <template v-else>
                    <Link :href="route('login')" class="text-white font-bold">
                        Anmelden
                    </Link>

                    <Link v-if="canRegister" :href="route('register')" class="ml-4 text-white font-bold">
                        Registrieren
                    </Link>
                </template>

            </div>
        </div>
    </div>
</template>

<script>
import {Head, Link} from '@inertiajs/inertia-vue3';

export default {
    setup(props, {attrs}) {

        const {canLogin, canRegister, tenant} = attrs;

        // Computed Background Image
        const backgroundImage =
            `url('/static/background-${tenant}.jpeg')`;


        return {
            canLogin,
            canRegister,
            backgroundImage,
        };
    },
    components: {
        Head,
        Link,
    },
}

</script>

