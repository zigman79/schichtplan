<template>
  <Head title="Wochenplan"/>

  <!-- pan-y: senkrecht scrollt der Browser, waagerecht wischen wir selbst -->
  <div
      ref="flaeche"
      class="min-h-dvh bg-gray-100 text-gray-900"
      style="touch-action: pan-y pinch-zoom"
      @pointerdown="gesteStart"
      @pointermove="gesteBewegung"
      @pointerup="gesteEnde"
      @pointercancel="gesteAbbruch"
  >
    <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-sm border-t-4 border-tenant-500 shadow-sm">
      <div class="max-w-5xl mx-auto sm:px-4">
        <div class="flex items-center gap-1 px-1.5 pt-2 sm:px-0">
          <button type="button" :class="pfeil" aria-label="Vorherige Woche" @click="wechsle(-1)">
            <ChevronLeftIcon class="w-6 h-6"/>
          </button>

          <div class="flex-1 min-w-0 text-center">
            <div class="flex items-center justify-center gap-2 text-[11px] font-bold uppercase tracking-[0.14em] text-gray-500">
              <span>KW {{ plan.kw }}</span>
              <!-- Gleicher Rahmen wie "Zu heute", damit der Kopf beim Wechsel nicht springt -->
              <span v-if="istAktuelleWoche" class="rounded-full border border-transparent bg-tenant-100 px-2 py-0.5 tracking-wider text-tenant-700">
                Diese Woche
              </span>
              <button v-else type="button" @click="zuHeute"
                      class="rounded-full border border-tenant-300 px-2 py-0.5 tracking-wider text-tenant-700 hover:bg-tenant-50">
                Zu heute
              </button>
            </div>
            <h1 class="mt-0.5 truncate text-[17px] sm:text-xl font-bold leading-tight">
              {{ zeitraum }}
            </h1>
          </div>

          <button type="button" :class="pfeil" aria-label="Nächste Woche" @click="wechsle(1)">
            <ChevronRightIcon class="w-6 h-6"/>
          </button>
        </div>

        <!-- Beim Wischen gleiten nur die Tage, die Namensspalte bleibt stehen -->
        <div :class="zeile">
          <div></div>
          <div class="overflow-x-clip">
            <div class="schiebt grid grid-cols-7">
              <div v-for="tag in tage" :key="`kopf-${tag.datum}`" class="flex flex-col items-center pt-1.5 pb-1.5">
                <span class="text-[10px] sm:text-xs font-bold uppercase tracking-wider"
                      :class="tag.heute ? 'text-tenant-700' : 'text-gray-400'">
                  {{ tag.kurz }}
                </span>
                <span class="mt-0.5 grid h-7 w-7 place-items-center rounded-full text-[15px] font-bold tabular-nums"
                      :class="tag.heute ? 'bg-tenant-600 text-white' : tag.rot ? 'text-red-600' : 'text-gray-800'">
                  {{ tag.nummer }}
                </span>
                <span class="mt-0.5 flex h-1.5 gap-0.5">
                  <span v-if="tag.feiertag" class="h-1.5 w-1.5 rounded-full bg-red-500"/>
                  <span v-if="tag.turniere.length" class="h-1.5 w-1.5 rounded-full bg-yellow-500"/>
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="relative max-w-5xl mx-auto sm:px-4 sm:pt-4">
      <div class="bg-white shadow-sm sm:rounded-xl sm:overflow-hidden">
        <div v-if="plan.users.length === 0" class="px-4 py-10 text-center text-sm text-gray-500">
          Keine Arbeitszeiten sichtbar.
        </div>

        <div
            v-for="mitarbeiter in plan.users"
            :key="mitarbeiter.id"
            :class="zeile"
            class="border-b border-gray-100"
        >
          <div class="flex items-center min-w-0 pl-2.5 pr-1 sm:pl-3 py-2" :title="mitarbeiter.name">
            <span class="line-clamp-2 hyphens-auto break-words text-[13px] sm:text-sm font-semibold leading-tight text-gray-800">
              <span class="sm:hidden">{{ mitarbeiter.kurz }}</span>
              <span class="hidden sm:inline">{{ mitarbeiter.name }}</span>
            </span>
          </div>

          <div class="overflow-x-clip">
            <div class="schiebt grid h-full grid-cols-7">
              <div
                  v-for="(eintrag, index) in mitarbeiter.tage"
                  :key="`${mitarbeiter.id}-${index}`"
                  class="flex items-center justify-center border-l border-gray-100 px-px py-2 text-center"
                  :class="tage[index].heute ? 'bg-tenant-50' : tage[index].wochenende ? 'bg-gray-50/70' : ''"
              >
                <template v-if="eintrag">
                  <span v-if="eintrag.status === 'frei'" class="text-[11px] sm:text-xs text-gray-400">frei</span>

                  <span v-else-if="eintrag.status"
                        class="inline-flex items-center justify-center rounded-md px-1.5 py-1 text-[11px] sm:text-xs font-bold leading-none"
                        :class="status[eintrag.status].farbe">
                    <span class="sm:hidden">{{ status[eintrag.status].kurz }}</span>
                    <span class="hidden sm:inline">{{ status[eintrag.status].label }}</span>
                  </span>

                  <!-- Passt die Zeit nicht in eine Zeile, bricht sie nach dem Strich um -->
                  <span v-else class="flex flex-wrap justify-center text-[12.5px] sm:text-sm font-bold leading-[1.2] tracking-[-0.01em] tabular-nums text-gray-900">
                    <template v-if="eintrag.von && eintrag.bis">
                      <span class="whitespace-nowrap"><Uhrzeit :wert="eintrag.von"/>–</span>
                      <span class="whitespace-nowrap"><Uhrzeit :wert="eintrag.bis"/></span>
                    </template>
                    <span v-else-if="eintrag.von" class="whitespace-nowrap">
                      <span class="font-normal text-gray-500">ab </span><Uhrzeit :wert="eintrag.von"/>
                    </span>
                    <span v-else class="whitespace-nowrap">
                      <span class="font-normal text-gray-500">bis </span><Uhrzeit :wert="eintrag.bis"/>
                    </span>
                  </span>
                </template>
              </div>
            </div>
          </div>
        </div>

        <div v-if="plan.users.length" :class="zeile" class="bg-gray-50 text-[11px] sm:text-xs text-gray-500">
          <div class="pl-2.5 sm:pl-3 py-2 font-semibold">Im Dienst</div>
          <div class="overflow-x-clip">
            <div class="schiebt grid grid-cols-7">
              <div v-for="(anzahl, index) in anwesend" :key="`anwesend-${index}`"
                   class="py-2 text-center font-bold tabular-nums"
                   :class="tage[index].heute ? 'text-tenant-700' : ''">
                {{ anzahl }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="laedt" class="absolute inset-x-0 top-16 flex justify-center">
        <SpinningLoader tailwind="w-7 h-7 text-tenant-600"/>
      </div>
    </main>

    <div class="max-w-5xl mx-auto px-4 pt-5 pb-10 space-y-5">
      <section v-if="termine.length">
        <h2 class="mb-2 text-[11px] font-bold uppercase tracking-[0.14em] text-gray-500">Termine</h2>
        <ul class="bg-white rounded-xl shadow-sm divide-y divide-gray-100">
          <li v-for="(termin, index) in termine" :key="`termin-${index}`" class="flex items-center gap-3 px-3 py-2 text-sm">
            <span class="w-16 shrink-0 font-bold tabular-nums">{{ termin.tag }}</span>
            <span class="h-2 w-2 shrink-0 rounded-full" :class="termin.feiertag ? 'bg-red-500' : 'bg-yellow-500'"/>
            <span class="min-w-0">{{ termin.name }}</span>
          </li>
        </ul>
      </section>

      <section class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-gray-500">
        <span v-for="(eintrag, schluessel) in status" :key="`legende-${schluessel}`" class="flex items-center gap-1.5 sm:hidden">
          <span class="inline-flex items-center justify-center rounded-md px-1.5 py-1 text-[11px] font-bold leading-none" :class="eintrag.farbe">
            {{ eintrag.kurz }}
          </span>
          {{ eintrag.label }}
        </span>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-yellow-500"/>Turnier</span>
        <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"/>Feiertag</span>
      </section>

      <footer class="space-y-1 text-center text-xs text-gray-400">
        <p>Ansicht {{ plan.besitzer }} · nur lesen · Stand {{ plan.stand }} Uhr</p>
        <p class="sm:hidden">Nach links oder rechts wischen, um die Woche zu wechseln.</p>
        <p class="hidden sm:block">Mit den Pfeiltasten ← → die Woche wechseln.</p>
      </footer>
    </div>

    <div v-if="fehler" class="fixed inset-x-4 bottom-4 z-30 mx-auto max-w-sm rounded-lg bg-gray-900 px-4 py-3 text-center text-sm text-white shadow-lg">
      {{ fehler }}
    </div>
  </div>
</template>

<script>
import { computed, h, onBeforeUnmount, onMounted, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import dayjs from 'dayjs'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import SpinningLoader from '@/Components/SpinningLoader.vue'

// Kompakte Uhrzeit: "10:00" -> 10, "09:30" -> 9³⁰
const Uhrzeit = {
  props: { wert: { type: String, required: true } },
  setup (props) {
    return () => {
      const [stunde, minute] = props.wert.split(':')

      return h('span', [
        String(Number(stunde)),
        minute && minute !== '00' ? h('sup', { class: 'ml-px text-[0.62em]' }, minute) : null,
      ])
    }
  },
}

const status = {
  urlaub: { kurz: 'U', label: 'Urlaub', farbe: 'bg-sky-100 text-sky-800' },
  krank: { kurz: 'K', label: 'Krank', farbe: 'bg-rose-100 text-rose-700' },
  schule: { kurz: 'S', label: 'Schule', farbe: 'bg-violet-100 text-violet-700' },
}

// Namensspalte so schmal, dass "12–21" auch bei 360px Displaybreite in eine Zeile passt
const zeile = 'grid grid-cols-[5rem_minmax(0,1fr)] sm:grid-cols-[10rem_minmax(0,1fr)] lg:grid-cols-[13rem_minmax(0,1fr)]'

const pfeil = 'grid h-11 w-11 shrink-0 place-items-center rounded-full text-gray-500 hover:bg-gray-100 hover:text-gray-800 active:bg-gray-200'

// Muss zur transition-duration von .schiebt passen
const DAUER = 220

export default {
  components: { Head, ChevronLeftIcon, ChevronRightIcon, SpinningLoader, Uhrzeit },

  props: {
    token: { type: String, required: true },
    woche: { type: Object, required: true },
  },

  setup (props) {
    const plan = ref(props.woche)
    const flaeche = ref(null)
    const laedt = ref(false)
    const fehler = ref(null)

    const tage = computed(() => plan.value.tage.map(tag => {
      const datum = dayjs(tag.datum)

      return {
        ...tag,
        kurz: datum.format('dd'),
        nummer: datum.format('D'),
        heute: tag.datum === plan.value.heute,
        wochenende: datum.day() === 0 || datum.day() === 6,
        rot: datum.day() === 0 || !!tag.feiertag,
      }
    }))

    const istAktuelleWoche = computed(() => plan.value.tage.some(tag => tag.datum === plan.value.heute))

    const zeitraum = computed(() => {
      const von = dayjs(plan.value.tage[0].datum)
      const bis = dayjs(plan.value.tage[6].datum)

      if (von.year() !== bis.year()) {
        return `${von.format('D. MMM YYYY')} – ${bis.format('D. MMM YYYY')}`
      }
      if (von.month() !== bis.month()) {
        return `${von.format('D. MMM')} – ${bis.format('D. MMM YYYY')}`
      }
      return `${von.format('D.')}–${bis.format('D. MMMM YYYY')}`
    })

    const anwesend = computed(() => plan.value.tage.map((_, index) =>
      plan.value.users.filter(mitarbeiter => mitarbeiter.tage[index] && !mitarbeiter.tage[index].status).length,
    ))

    const termine = computed(() => tage.value.flatMap(tag => [
      ...(tag.feiertag ? [{ feiertag: true, name: tag.feiertag }] : []),
      ...tag.turniere.map(name => ({ feiertag: false, name })),
    ].map(termin => ({ ...termin, tag: `${tag.kurz} ${dayjs(tag.datum).format('D.M.')}` }))))

    /*
     | Wochen laden: einmal geladene Wochen bleiben im Speicher, die
     | Nachbarwochen werden vorab geholt, damit das Wischen sofort reagiert.
     */
    const cache = new Map([[plan.value.start, Promise.resolve(plan.value)]])
    let geladenUm = Date.now()

    const verschiebe = (start, wochen) => dayjs(start).add(wochen, 'week').format('YYYY-MM-DD')

    const ladeWoche = (start) => {
      if (!cache.has(start)) {
        cache.set(start, axios.get(route('permalink.woche', { token: props.token, montag: start }))
            .then(antwort => antwort.data)
            .catch(grund => {
              cache.delete(start)
              throw grund
            }))
      }

      return cache.get(start)
    }

    const ladeNachbarn = () => {
      [-1, 1].forEach(richtung => ladeWoche(verschiebe(plan.value.start, richtung)).catch(() => {}))
    }

    let fehlerTimer = null
    const zeigeFehler = (grund) => {
      // Link inzwischen deaktiviert: Neu laden zeigt den Hinweis vom Server
      if (grund?.response?.status === 404) {
        window.location.reload()
        return
      }

      fehler.value = 'Die Woche konnte nicht geladen werden.'
      clearTimeout(fehlerTimer)
      fehlerTimer = setTimeout(() => { fehler.value = null }, 4000)
    }

    /*
     | Schieben: alle Tagesspalten hängen an derselben CSS-Variable. Die wird
     | direkt am Element gesetzt, damit Vue nicht bei jeder Fingerbewegung
     | den ganzen Plan neu rendert.
     */
    const breite = () => window.innerWidth || 400
    const warte = (ms) => new Promise(resolve => setTimeout(resolve, ms))
    const naechsterFrame = () => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))

    const setzeVersatz = (px, animiert) => {
      if (!flaeche.value) {
        return
      }

      flaeche.value.classList.toggle('animiert', animiert)
      flaeche.value.style.setProperty('--versatz', `${px}px`)
      flaeche.value.style.setProperty('--deckkraft', String(1 - Math.min(Math.abs(px) / breite(), 1) * 0.6))
    }

    const zurueckschnappen = () => setzeVersatz(0, true)

    let wechselLaeuft = false

    // Alte Woche fährt raus, neue kommt von der Gegenseite rein
    const zeigeWoche = async (start, richtung) => {
      if (wechselLaeuft) {
        return
      }
      wechselLaeuft = true

      const anfrage = ladeWoche(start)
      const mitAnimation = !window.matchMedia('(prefers-reduced-motion: reduce)').matches

      if (mitAnimation) {
        setzeVersatz(-richtung * breite(), true)
        await warte(DAUER)
      }

      let geklappt = false
      const ladeHinweis = setTimeout(() => { laedt.value = true }, 150)

      try {
        plan.value = await anfrage
        geklappt = true
      } catch (grund) {
        zeigeFehler(grund)
      } finally {
        clearTimeout(ladeHinweis)
        laedt.value = false
      }

      if (mitAnimation) {
        // Bei einem Fehler kommt die alte Woche von dort zurück, wo sie hin ist
        setzeVersatz((geklappt ? richtung : -richtung) * breite(), false)
        await naechsterFrame()
        setzeVersatz(0, true)
        await warte(DAUER)
      }

      setzeVersatz(0, false)
      wechselLaeuft = false

      if (geklappt) {
        ladeNachbarn()
      }
    }

    const wechsle = (richtung) => zeigeWoche(verschiebe(plan.value.start, richtung), richtung)

    const zuHeute = () => {
      const heute = dayjs(plan.value.heute)
      const montag = heute.subtract((heute.day() + 6) % 7, 'day').format('YYYY-MM-DD')

      zeigeWoche(montag, montag < plan.value.start ? -1 : 1)
    }

    /*
     | Wischgeste mit Pointer-Events (nur Touch/Stift, keine Maus).
     | Erst wenn klar ist, dass waagerecht gewischt wird, folgt der Plan dem Finger.
     */
    let geste = null

    const gesteStart = (event) => {
      if (event.pointerType === 'mouse' || wechselLaeuft) {
        return
      }

      // Weiterer Finger vor der Richtungsentscheidung = Zoomen, kein Wischen.
      // Während des Wischens (z.B. Handballen am Rand) einfach ignorieren.
      if (geste) {
        if (!geste.achse) {
          geste = null
        }
        return
      }

      geste = { id: event.pointerId, x: event.clientX, y: event.clientY, zeit: event.timeStamp, achse: null }
    }

    const gesteBewegung = (event) => {
      if (!geste || event.pointerId !== geste.id) {
        return
      }

      const dx = event.clientX - geste.x
      const dy = event.clientY - geste.y

      if (!geste.achse) {
        if (Math.hypot(dx, dy) < 10) {
          return
        }
        geste.achse = Math.abs(dx) > Math.abs(dy) * 1.2 ? 'x' : 'y'
      }

      if (geste.achse === 'x') {
        setzeVersatz(dx, false)
      }
    }

    const gesteEnde = (event) => {
      if (!geste || event.pointerId !== geste.id) {
        return
      }

      const { achse, x, zeit } = geste
      geste = null

      if (achse !== 'x') {
        return
      }

      const dx = event.clientX - x
      const tempo = Math.abs(dx) / Math.max(event.timeStamp - zeit, 1)

      if (Math.abs(dx) > breite() * 0.25 || (Math.abs(dx) > 40 && tempo > 0.5)) {
        wechsle(dx < 0 ? 1 : -1)
      } else {
        zurueckschnappen()
      }
    }

    const gesteAbbruch = () => {
      if (geste?.achse === 'x') {
        zurueckschnappen()
      }
      geste = null
    }

    /*
     | Safari (iOS) scrollt trotz touch-action: pan-y senkrecht mit, sobald der
     | Finger beim Wischen etwas nach oben/unten abweicht, und bricht die Geste
     | dann mit pointercancel ab. Bei langen Wischern passiert das fast immer.
     | Steht fest, dass waagerecht gewischt wird, daher das Scrollen unterbinden.
     */
    const scrollenBlockieren = (event) => {
      if (geste?.achse === 'x' && event.cancelable) {
        event.preventDefault()
      }
    }

    const taste = (event) => {
      if (event.altKey || event.metaKey || event.ctrlKey) {
        return
      }
      if (event.key === 'ArrowLeft') {
        wechsle(-1)
      }
      if (event.key === 'ArrowRight') {
        wechsle(1)
      }
    }

    // Zurück aufs Handy nach längerer Pause: frische Daten statt altem Stand
    const wiederSichtbar = () => {
      if (document.visibilityState !== 'visible' || Date.now() - geladenUm < 2 * 60 * 1000 || wechselLaeuft) {
        return
      }

      cache.clear()
      geladenUm = Date.now()

      ladeWoche(plan.value.start)
          .then(daten => {
            plan.value = daten
            ladeNachbarn()
          })
          .catch(zeigeFehler)
    }

    onMounted(() => {
      ladeNachbarn()
      // passive: false, sonst ignoriert der Browser preventDefault()
      flaeche.value.addEventListener('touchmove', scrollenBlockieren, { passive: false })
      window.addEventListener('keydown', taste)
      document.addEventListener('visibilitychange', wiederSichtbar)
    })

    onBeforeUnmount(() => {
      flaeche.value?.removeEventListener('touchmove', scrollenBlockieren)
      window.removeEventListener('keydown', taste)
      document.removeEventListener('visibilitychange', wiederSichtbar)
      clearTimeout(fehlerTimer)
    })

    return {
      plan, flaeche, tage, istAktuelleWoche, zeitraum, anwesend, termine,
      status, zeile, pfeil, laedt, fehler,
      wechsle, zuHeute, gesteStart, gesteBewegung, gesteEnde, gesteAbbruch,
    }
  },
}
</script>

<style scoped>
.schiebt {
    transform: translate3d(var(--versatz, 0px), 0, 0);
    opacity: var(--deckkraft, 1);
}

.animiert .schiebt {
    transition: transform 220ms cubic-bezier(.2, .8, .2, 1), opacity 220ms;
}
</style>
