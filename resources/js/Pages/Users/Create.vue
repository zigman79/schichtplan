<template>
  <Head title="Benutzer hinzufügen"/>

  <BreezeAuthenticatedLayout>
    <template #header>
      <div class="flex items-center justify-between flex-wrap sm:flex-nowrap">

        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
          {{title}} / <span class="text-tenant-600">Hinzufügen</span>
        </h2>
      </div>
    </template>


    <div class="py-12">
      <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">

        <div class="bg-white rounded-md shadow overflow-x-auto">
          <form @submit.prevent="create">
            <div class="p-8 gap-8 grid grid-cols-1 lg:grid-cols-2">
              <div>
                <Label>
                  Name
                </Label>
                <Input
                    class="w-full"
                    type="text"
                    v-model="form.name"
                    :error="form.errors.name"
                />
                <InputError :message="form.errors.name"/>
              </div>
              <div>
                <Label>
                  E-Mail
                </Label>
                <Input
                    class="w-full"
                    type="email"
                    v-model="form.email"
                />
                <InputError :message="form.errors.email"/>
              </div>
              <div>
                <Label>
                  Telegram
                </Label>
                <Input
                    class="w-full"
                    type="text"
                    v-model="form.telegram_id"
                />
                <InputError :message="form.errors.telegram_id"/>
              </div>
              <div>
                <Label>
                  Chip Id
                </Label>
                <Input
                    class="w-full"
                    type="text"
                    v-model="form.chip_id"
                />
                <InputError :message="form.errors.chip_id"/>
              </div>
              <div>
                <Label>
                  Eingestellt am
                </Label>
                <Input
                    class="w-full"
                    type="date"
                    v-model="form.eingestellt_am"
                />
                <InputError :message="form.errors.eingestellt_am"/>
              </div>
              <div>
                <Label>
                  Druck Sort
                </Label>
                <Input
                    class="w-full"
                    type="number"
                    v-model="form.druck_sort"
                />
                <InputError :message="form.errors.druck_sort"/>
              </div>
              <div>
                <Label>
                  Passwort
                </Label>
                <Input
                    class="w-full"
                    v-model="form.password"
                    type="password"
                    autocomplete="new-password"
                />
                <InputError :message="form.errors.password"/>
              </div>
              <div>
                <Label>
                  Rechte
                </Label>
                <SelectBox
                    class="w-full"
                    :options="roles"
                    v-model="form.role"
                />
                <InputError :message="form.errors.role"/>
              </div>


              <div class="col-span-2 space-y-8">
                <SwitchGroup as="div" class="flex items-center justify-between">
                  <span class="flex-grow flex flex-col">
                    <SwitchLabel as="span" class="text-sm font-bold text-gray-900" passive>Keine Arbeitszeiten</SwitchLabel>
                    <SwitchDescription as="span" class="text-sm text-gray-500">Wird beim Arbeitsplan und den PDF's nicht angezeigt.</SwitchDescription>
                  </span>
                  <Switch v-model="form.keine_arbeitszeit"
                          :class="[form.keine_arbeitszeit ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                    <span aria-hidden="true"
                          :class="[form.keine_arbeitszeit ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']"/>
                  </Switch>
                </SwitchGroup>

                <SwitchGroup as="div" class="flex items-center justify-between">
                  <span class="flex-grow flex flex-col">
                    <SwitchLabel as="span" class="text-sm font-bold text-gray-900" passive>Minijob</SwitchLabel>
                    <SwitchDescription as="span" class="text-sm text-gray-500">Es werden die Minijob-Zeiten für die Pläne verwendet.</SwitchDescription>
                  </span>
                  <Switch v-model="form.minijob"
                          :class="[form.minijob ? 'bg-tenant-600' : 'bg-gray-200', 'relative inline-flex flex-shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500']">
                    <span aria-hidden="true"
                          :class="[form.minijob ? 'translate-x-5' : 'translate-x-0', 'pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 transition ease-in-out duration-200']"/>
                  </Switch>
                </SwitchGroup>

              </div>


            </div>



            <div class="px-8 py-4 bg-gray-50 border-t border-gray-100 flex items-center">

              <button
                  class="ml-auto inline-flex justify-center items-center px-4 py-2 border border-transparent shadow-sm font-semibold rounded-md text-white bg-tenant-600 hover:bg-tenant-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-tenant-500"
                  type="submit"
              >
                <SpinningLoader tailwind="w-4 h-4 text-white" v-if="form.processing"/>
                <span>{{title}} hinzufügen</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>


  </BreezeAuthenticatedLayout>
</template>

<script>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue'
import { ChevronRightIcon } from '@heroicons/vue/solid'
import { Head, Link, usePage } from '@inertiajs/inertia-vue3'
import Input from '@/Components/Input'
import Label from '@/Components/Label'
import { useForm } from '@inertiajs/inertia-vue3'
import SpinningLoader from '@/Components/SpinningLoader'
import SelectBox from '@/Components/SelectBox'
import { arbeitszeit } from '@/utils/roles'
import InputError from '@/Components/InputError'
import { Switch, SwitchDescription, SwitchGroup, SwitchLabel } from '@headlessui/vue'

export default {
  setup ({ attrs }) {

  const genderLanguage = usePage().props.value.genderLanguage || false

  const title = `Benutzer${genderLanguage ? ':in' : ''}`

    const form = useForm({
      name: null,
      email: null,
      password: null,
      telegram_id: null,
      chip_id: null,
      eingestellt_am: null,
      druck_sort: 99,
      role: 'user',
      keine_arbeitszeit: false,
      minijob: false,
    })

  const roles = arbeitszeit(genderLanguage)

    const create = () => {
      form
          .transform((data) => ({
            ...data,
            arbeitszeit_admin: data.role === 'administrator',
            arbeitszeit_teamleader: data.role === 'team-leader',
          }))
          .post('/users', {
            preserveScroll: true,
          })
    }

    const destroy = () => {}

    return {
      roles,
      form,
      destroy,
      create,
        title
    }
  },
  components: {
    InputError,
    SelectBox,
    BreezeAuthenticatedLayout,
    SpinningLoader,
    ChevronRightIcon,
    Head,
    Link,
    Input,
    Label,
    Switch,
    SwitchDescription,
    SwitchGroup,
    SwitchLabel,
  },
}
</script>
