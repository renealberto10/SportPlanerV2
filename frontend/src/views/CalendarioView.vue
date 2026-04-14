<template>
  <div>
    <!-- Header -->
    <div class="page-header">
      <div>
        <h2 class="page-title">Calendario</h2>
        <p class="text-xs text-slate-500 mt-0.5">Vista mensual de mantenimientos y eventos</p>
      </div>
      <div class="flex items-center gap-2">
        <button class="btn btn-outline btn-sm" @click="prevMonth">← Anterior</button>
        <span class="font-semibold text-slate-800 text-sm w-44 text-center">
          {{ mesLabel }} {{ year }}
        </span>
        <button class="btn btn-outline btn-sm" @click="nextMonth">Siguiente →</button>
      </div>
    </div>

    <!-- Legend -->
    <div class="flex gap-5 mb-4 text-xs">
      <span class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-blue-500 inline-block"></span>
        <span class="text-slate-500 font-medium">Mantenimiento</span>
      </span>
      <span class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-emerald-500 inline-block"></span>
        <span class="text-slate-500 font-medium">Evento</span>
      </span>
      <span class="flex items-center gap-2">
        <span class="w-2.5 h-2.5 rounded-sm bg-amber-400 inline-block"></span>
        <span class="text-slate-500 font-medium">Solicitud</span>
      </span>
    </div>

    <div class="card p-0 overflow-hidden">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <!-- Day headers -->
        <div class="grid grid-cols-7 border-b border-slate-100">
          <div v-for="d in days" :key="d"
               class="py-2.5 text-center text-xs font-semibold text-slate-500 uppercase tracking-wider bg-slate-50/80">
            {{ d }}
          </div>
        </div>

        <!-- Calendar grid -->
        <div class="grid grid-cols-7">
          <div
            v-for="(day, i) in calDays"
            :key="i"
            class="min-h-28 border-b border-r border-slate-100 p-2 transition-colors last:border-r-0"
            :class="[
              day.current
                ? day.isToday
                  ? 'bg-blue-50/60 cursor-pointer hover:bg-blue-50'
                  : 'bg-white cursor-pointer hover:bg-slate-50/60'
                : 'bg-slate-50/40',
            ]"
            @click="dayClick(day)"
          >
            <!-- Day number -->
            <div class="mb-1.5">
              <span
                class="inline-flex w-6 h-6 items-center justify-center rounded-full text-xs font-semibold"
                :class="
                  day.isToday
                    ? 'bg-blue-600 text-white'
                    : day.current
                    ? 'text-slate-700'
                    : 'text-slate-300'
                "
              >{{ day.n }}</span>
            </div>

            <!-- Chips -->
            <div
              v-for="m in day.mants"
              :key="'m' + m.id"
              class="text-xs bg-blue-100 text-blue-700 rounded px-1.5 py-0.5 mb-0.5 truncate leading-tight font-medium"
              :title="m.escenario?.nombre"
            >⚙ {{ m.escenario?.nombre?.substring(0, 16) || 'Mant.' }}</div>

            <div
              v-for="e in day.eventos"
              :key="'e' + e.id"
              class="text-xs bg-emerald-100 text-emerald-700 rounded px-1.5 py-0.5 mb-0.5 truncate leading-tight font-medium"
              :title="e.nombre"
            >◎ {{ e.nombre?.substring(0, 16) }}</div>

            <div
              v-for="s in day.solicitudes"
              :key="'s' + s.id"
              class="text-xs bg-amber-100 text-amber-700 rounded px-1.5 py-0.5 mb-0.5 truncate leading-tight font-medium"
              :title="s.actividad"
            >📋 {{ s.actividad?.substring(0, 14) }}</div>
          </div>
        </div>
      </template>
    </div>

    <!-- Day Detail Modal -->
    <AppModal v-model="showDayModal" :title="`${selectedDate}`" maxWidth="520px">
      <div class="space-y-4">
        <!-- Mantenimientos -->
        <div v-if="selectedMants.length">
          <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">⚙ Mantenimientos</h4>
          <div v-for="m in selectedMants" :key="m.id" class="rounded-xl border border-blue-100 bg-blue-50/50 p-3.5 mb-2">
            <div class="flex items-start justify-between gap-2">
              <div class="font-semibold text-slate-900 text-sm">{{ m.escenario?.nombre }}</div>
              <button class="flex-shrink-0 text-[11px] text-blue-600 hover:text-blue-800 font-medium flex items-center gap-1 bg-blue-100 hover:bg-blue-200 rounded-lg px-2 py-1 transition-colors"
                      @click="openGcal(m.fecha, m.hora, `Mantenimiento — ${m.escenario?.nombre}`, m.actividades, m.escenario?.nombre)">
                📅 Google Cal
              </button>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              <span class="font-medium">Técnico:</span> {{ m.tecnico || 'N/A' }} ·
              <span class="font-medium">Tipo:</span> {{ m.tipo }} ·
              <span :class="MANTENIMIENTO_ESTADO_BADGE[m.estado] || 'badge badge-gray'" class="ml-1">{{ m.estado }}</span>
            </div>
            <div v-if="m.actividades" class="text-xs text-slate-600 mt-2 leading-relaxed">{{ m.actividades }}</div>
          </div>
        </div>

        <!-- Eventos -->
        <div v-if="selectedEventos.length">
          <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">◎ Eventos</h4>
          <div v-for="e in selectedEventos" :key="e.id" class="rounded-xl border border-emerald-100 bg-emerald-50/50 p-3.5 mb-2">
            <div class="flex items-start justify-between gap-2">
              <div class="font-semibold text-slate-900 text-sm">{{ e.nombre }}</div>
              <button class="flex-shrink-0 text-[11px] text-emerald-600 hover:text-emerald-800 font-medium flex items-center gap-1 bg-emerald-100 hover:bg-emerald-200 rounded-lg px-2 py-1 transition-colors"
                      @click="openGcal(e.fecha, e.hora, e.nombre, e.descripcion, e.escenario?.nombre)">
                📅 Google Cal
              </button>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              {{ e.escenario?.nombre }} · {{ e.hora || '' }} ·
              <span :class="EVENTO_ESTADO_BADGE[e.estado] || 'badge badge-gray'" class="ml-1">{{ e.estado }}</span>
            </div>
            <div v-if="e.descripcion" class="text-xs text-slate-600 mt-2 leading-relaxed">{{ e.descripcion }}</div>
          </div>
        </div>

        <!-- Solicitudes -->
        <div v-if="selectedSolicitudes.length">
          <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">📋 Solicitudes</h4>
          <div v-for="s in selectedSolicitudes" :key="s.id" class="rounded-xl border border-amber-100 bg-amber-50/50 p-3.5 mb-2">
            <div class="flex items-start justify-between gap-2">
              <div class="font-semibold text-slate-900 text-sm">{{ s.actividad }}</div>
              <span :class="SOLICITUD_ESTADO_BADGE[s.estado] || 'badge badge-gray'" class="flex-shrink-0 text-[10px]">{{ SOLICITUD_ESTADOS[s.estado] }}</span>
            </div>
            <div class="text-xs text-slate-500 mt-1">
              <span class="font-medium">Solicita:</span> {{ s.solicita }}
              <span v-if="s.hora"> · {{ s.hora }}</span>
              <span v-if="s.escenario"> · {{ s.escenario.nombre }}</span>
            </div>
            <div v-if="s.tecnico" class="text-xs text-slate-500 mt-1">
              <span class="font-medium">Técnico:</span> {{ s.tecnico.nombre_completo }}
            </div>
            <!-- Google Calendar for solicitudes -->
            <div class="mt-2.5 pt-2.5 border-t border-amber-100">
              <div class="text-[11px] text-amber-700 font-medium mb-1.5">Invitar por correo (separados por coma):</div>
              <div class="flex gap-2">
                <input
                  v-model="gcalEmails[s.id]"
                  class="input text-xs py-1 flex-1"
                  placeholder="correo1@indes.gob.sv, correo2@..."
                />
                <button
                  class="flex-shrink-0 text-[11px] text-amber-700 font-medium flex items-center gap-1 bg-amber-200 hover:bg-amber-300 rounded-lg px-2.5 py-1 transition-colors"
                  @click="openGcal(s.fecha_calendarizada || '', s.hora, s.actividad, `Solicita: ${s.solicita}`, s.escenario?.nombre, gcalEmails[s.id])">
                  📅 Google Cal
                </button>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!selectedMants.length && !selectedEventos.length && !selectedSolicitudes.length"
             class="text-center py-10 text-slate-400 text-sm">
          Sin actividades registradas para este día
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline btn-sm" @click="showDayModal = false">Cerrar</button>
        <RouterLink to="/mantenimiento" class="btn btn-outline btn-sm" @click="showDayModal = false">+ Mantenimiento</RouterLink>
        <RouterLink to="/eventos" class="btn btn-success btn-sm" @click="showDayModal = false">+ Evento</RouterLink>
      </template>
    </AppModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { mantenimientoApi, eventoApi, solicitudApi } from '@/api'
import { useApiError } from '@/composables/useApiError'
import { MANTENIMIENTO_ESTADO_BADGE, EVENTO_ESTADO_BADGE, SOLICITUD_ESTADO_BADGE, SOLICITUD_ESTADOS, MESES } from '@/constants'
import type { Mantenimiento, Evento, Solicitud } from '@/types'
import AppModal from '@/components/AppModal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const { handleError } = useApiError()
const loading      = ref(true)
const allMants       = ref<Mantenimiento[]>([])
const allEventos     = ref<Evento[]>([])
const allSolicitudes = ref<Solicitud[]>([])
const now          = new Date()
const month        = ref(now.getMonth())
const year         = ref(now.getFullYear())
const showDayModal = ref(false)
const selectedDate       = ref('')
const selectedMants      = ref<Mantenimiento[]>([])
const selectedEventos    = ref<Evento[]>([])
const selectedSolicitudes = ref<Solicitud[]>([])
const gcalEmails = ref<Record<number, string>>({})

const days     = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb']
const mesLabel = computed(() => MESES[month.value])

const calDays = computed(() => {
  const first = new Date(year.value, month.value, 1)
  const last  = new Date(year.value, month.value + 1, 0)
  const today = new Date()
  const cells: {
    n: number | string; current: boolean; isToday: boolean; dateStr: string
    mants: Mantenimiento[]; eventos: Evento[]; solicitudes: Solicitud[]
  }[] = []

  for (let i = 0; i < first.getDay(); i++) {
    cells.push({ n: '', current: false, isToday: false, dateStr: '', mants: [], eventos: [], solicitudes: [] })
  }
  for (let d = 1; d <= last.getDate(); d++) {
    const dateStr = `${year.value}-${String(month.value + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`
    cells.push({
      n: d, current: true, dateStr,
      isToday: today.getDate() === d && today.getMonth() === month.value && today.getFullYear() === year.value,
      mants:       allMants.value.filter(m => m.fecha === dateStr),
      eventos:     allEventos.value.filter(e => e.fecha === dateStr),
      solicitudes: allSolicitudes.value.filter(s => s.fecha_calendarizada === dateStr),
    })
  }
  return cells
})

function dayClick(day: typeof calDays.value[0]) {
  if (!day.current) return
  selectedDate.value        = day.dateStr
  selectedMants.value       = day.mants
  selectedEventos.value     = day.eventos
  selectedSolicitudes.value = day.solicitudes
  showDayModal.value        = true
}

async function loadData() {
  loading.value = true
  try {
    const [m, e, s] = await Promise.all([
      mantenimientoApi.list({ mes: month.value + 1, anio: year.value }),
      eventoApi.list({ mes: month.value + 1, anio: year.value }),
      solicitudApi.list(),
    ])
    allMants.value       = m.data.data
    allEventos.value     = e.data.data
    allSolicitudes.value = s.data.data.filter(
      (sol: Solicitud) => sol.fecha_calendarizada !== null,
    )
  } catch (e) {
    handleError(e, 'Error al cargar el calendario')
  } finally {
    loading.value = false
  }
}

function openGcal(
  fecha: string,
  hora: string | null | undefined,
  title: string,
  details: string | null | undefined,
  location: string | null | undefined,
  emails?: string,
) {
  const dateStr = fecha.replace(/-/g, '')
  let dates: string
  if (hora) {
    const [h, m] = hora.split(':')
    const start = `${dateStr}T${h}${m}00`
    const end   = `${dateStr}T${String(Number(h) + 1).padStart(2, '0')}${m}00`
    dates = `${start}/${end}`
  } else {
    dates = `${dateStr}/${dateStr}`
  }
  const url = new URL('https://calendar.google.com/calendar/render')
  url.searchParams.set('action', 'TEMPLATE')
  url.searchParams.set('text', title)
  url.searchParams.set('dates', dates)
  if (details)  url.searchParams.set('details', details)
  if (location) url.searchParams.set('location', location)
  if (emails)   url.searchParams.set('add', emails)
  window.open(url.toString(), '_blank')
}

function prevMonth() {
  if (month.value === 0) { month.value = 11; year.value-- } else month.value--
  loadData()
}
function nextMonth() {
  if (month.value === 11) { month.value = 0; year.value++ } else month.value++
  loadData()
}

onMounted(loadData)
</script>
