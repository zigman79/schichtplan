<template>
    <Head title="Email Verification" />

    <div class="mb-4 text-sm text-gray-600">
      Vielen Dank für die Anmeldung! Bevor Sie beginnen, könnten Sie Ihre E-Mail-Adresse überprüfen, indem Sie auf den Link klicken, den wir Ihnen gerade per E-Mail geschickt haben? Wenn Sie die E-Mail nicht erhalten haben, schicken wir Ihnen gerne eine neue zu.
    </div>

    <div class="mb-4 font-medium text-sm text-tenant-600" v-if="verificationLinkSent" >
      Ein neuer Verifizierungslink wurde an die E-Mail-Adresse geschickt, die Sie bei der Registrierung angegeben haben.
    </div>

    <form @submit.prevent="submit">
        <div class="mt-4 flex items-center justify-between">
            <BreezeButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Bestätigungs-E-Mail erneut senden
            </BreezeButton>

            <Link :href="route('logout')" method="post" as="button" class="underline text-sm text-gray-600 hover:text-gray-900">Abmelden</Link>
        </div>
    </form>
</template>

<script>
import BreezeButton from '@/Components/Button.vue'
import BreezeGuestLayout from '@/Layouts/Guest.vue'
import { Head, Link } from '@inertiajs/inertia-vue3';

export default {
    layout: BreezeGuestLayout,

    components: {
        BreezeButton,
        Head,
        Link,
    },

    props: {
        status: String,
    },

    data() {
        return {
            form: this.$inertia.form()
        }
    },

    methods: {
        submit() {
            this.form.post(this.route('verification.send'))
        },
    },

    computed: {
        verificationLinkSent() {
            return this.status === 'verification-link-sent';
        }
    }
}
</script>
