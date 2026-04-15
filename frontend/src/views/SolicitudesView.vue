<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Solicitudes de Mantenimiento</h2>
        <p class="text-xs text-slate-500 mt-0.5">Flujo de trabajo por estado</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Nueva Solicitud
      </button>
    </div>

    <!-- Controls: search + filters (no card wrapper) -->
    <div class="flex flex-col sm:flex-row gap-2 mb-3">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" class="input pl-9" placeholder="Buscar actividad, solicitante, escenario…" />
      </div>
      <select v-model="filterPrioridad" class="input sm:w-44">
        <option value="">Todas las prioridades</option>
        <option value="alto">🔴 Alta</option>
        <option value="medio">🟡 Media</option>
        <option value="bajo">🟢 Baja</option>
      </select>
      <select v-model="filterTecnico" class="input sm:w-48">
        <option :value="null">Todos los técnicos</option>
        <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
      </select>
    </div>

    <!-- Status tabs -->
    <div class="flex mb-4 bg-slate-100 rounded-xl p-1 gap-1">
      <button v-for="tab in TABS" :key="tab.estado"
              class="flex-1 flex items-center justify-center gap-1.5 py-2 px-2 sm:px-3 rounded-lg text-xs sm:text-sm font-semibold transition-all"
              :class="activeTab === tab.estado
                ? 'bg-white shadow-sm ' + tab.textClass
                : 'text-slate-400 hover:text-slate-600'"
              @click="activeTab = tab.estado">
        <span class="hidden sm:inline">{{ tab.label }}</span>
        <span class="sm:hidden">{{ tab.shortLabel }}</span>
        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-[10px] font-bold"
              :class="activeTab === tab.estado ? tab.countClass : 'bg-slate-200 text-slate-500'">
          {{ tabCount(tab.estado) }}
        </span>
      </button>
    </div>

    <!-- Content area -->
    <div>
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!filteredItems.length"
                    icon="📋"
                    :title="emptyTitle"
                    subtitle="Edita una solicitud y cambia su estado para verla aquí" />
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3">
          <div v-for="s in filteredItems" :key="s.id"
               class="bg-white rounded-xl border border-slate-200 hover:border-slate-300 hover:shadow-md transition-all cursor-pointer overflow-hidden flex flex-col"
               :style="`border-left: 4px solid ${PRIORITY_COLOR[s.prioridad]}`"
               @click="openForm(s)">
            <!-- Card body -->
            <div class="p-4 flex-1">
              <div class="flex items-start justify-between gap-2 mb-2">
                <p class="text-sm font-semibold text-slate-800 leading-snug line-clamp-2 flex-1">{{ s.actividad }}</p>
                <span :class="SOLICITUD_PRIORIDAD_BADGE[s.prioridad]" class="flex-shrink-0 text-[10px]">{{ s.prioridad.toUpperCase() }}</span>
              </div>
              <p class="text-xs text-slate-400 mb-3">
                <svg class="inline w-3 h-3 mr-0.5 -mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ s.escenario?.nombre || s.escenario_texto || 'Sin escenario' }}
              </p>

              <div class="space-y-1.5 text-xs text-slate-500">
                <div class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  <span>{{ s.solicita }}</span>
                </div>

                <div v-if="s.tecnico" class="flex items-center gap-1.5">
                  <div class="w-4 h-4 rounded-full flex-shrink-0 flex items-center justify-center text-white text-[9px] font-bold"
                       :style="`background:${avatarColor(s.tecnico.nombre_completo)}`">
                    {{ s.tecnico.nombre_completo[0] }}
                  </div>
                  <span class="font-medium text-slate-600">{{ s.tecnico.nombre_completo }}</span>
                </div>
                <div v-else class="flex items-center gap-1.5 text-amber-500 font-medium">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>Sin técnico asignado</span>
                </div>

                <div v-if="s.fecha_calendarizada" class="flex items-center gap-1.5 text-blue-600 font-medium">
                  <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <span>{{ fmtDate(s.fecha_calendarizada) }}<span v-if="s.hora"> · {{ s.hora }}</span></span>
                </div>
              </div>
            </div>

            <!-- Card footer -->
            <div class="flex items-center justify-between px-4 py-2.5 bg-slate-50 border-t border-slate-100" @click.stop>
              <span class="text-[11px] text-slate-400">{{ fmtDate(s.fecha_solicitud) }} · #{{ s.id }}</span>
              <div class="flex gap-0.5 items-center">
                <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-amber-600 relative"
                        title="Fotos" @click.stop="openFotos(s)">
                  <CameraIcon class="w-4 h-4" />
                  <span v-if="s.fotos?.length"
                        class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                    {{ s.fotos.length }}
                  </span>
                </button>
                <button v-if="auth.isAdmin || auth.isTecnico"
                        class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-blue-600"
                        title="Editar" @click.stop="openForm(s)">✏</button>
                <button v-if="auth.isAdmin"
                        class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-red-600"
                        title="Eliminar" @click.stop="confirmDelete(s)">✕</button>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Form Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Solicitud' : 'Nueva Solicitud'" maxWidth="660px">
      <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Fecha de Solicitud *</label>
            <input type="date" v-model="form.fecha_solicitud" class="input" />
          </div>
          <div>
            <label class="label">Prioridad *</label>
            <select v-model="form.prioridad" class="input">
              <option value="alto">🔴 Alta</option>
              <option value="medio">🟡 Media</option>
              <option value="bajo">🟢 Baja</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Actividad / Descripción *</label>
          <textarea v-model="form.actividad" class="input" rows="3" placeholder="Describe la actividad de mantenimiento requerida…" />
        </div>
        <div>
          <label class="label">Escenario</label>
          <select v-model="form.escenario_id" class="input">
            <option :value="null">— Seleccionar —</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="label">Escenarios adicionales (texto libre)</label>
          <input v-model="form.escenario_texto" class="input" placeholder="Ej: Las Delicias, Gimnasio Nacional…" />
        </div>
        <div>
          <label class="label">Solicita *</label>
          <input v-model="form.solicita" class="input" placeholder="Nombre del solicitante — ej: Luis Villeda" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Fecha Calendarizada</label>
            <input type="date" v-model="form.fecha_calendarizada" class="input" />
          </div>
          <div>
            <label class="label">Hora</label>
            <input type="time" v-model="form.hora" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Técnico Asignado</label>
          <select v-model="form.tecnico_id" class="input">
            <option :value="null">— Sin asignar —</option>
            <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
          </select>
        </div>

        <!-- Estado — highlighted so it's clear this moves the card -->
        <div class="rounded-xl border-2 border-slate-200 p-3 bg-slate-50/50">
          <label class="label font-semibold text-slate-700">Estado de la Solicitud</label>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 mt-1">
            <label v-for="tab in TABS" :key="tab.estado"
                   class="flex items-center gap-2 rounded-lg border-2 px-3 py-2 cursor-pointer transition-all text-sm font-medium"
                   :class="form.estado === tab.estado
                     ? tab.selectedClass
                     : 'border-slate-200 text-slate-500 hover:border-slate-300 bg-white'">
              <input type="radio" v-model="form.estado" :value="tab.estado" class="sr-only" />
              <span class="text-base leading-none">{{ tab.icon }}</span>
              <span>{{ tab.label }}</span>
            </label>
          </div>
          <p class="text-[11px] text-slate-400 mt-2">Al guardar, la solicitud aparecerá en la sección correspondiente.</p>
        </div>

        <div>
          <label class="label">Seguimiento del Técnico</label>
          <textarea v-model="form.seguimiento" class="input" rows="2" placeholder="Notas de seguimiento, avances…" />
        </div>
        <div>
          <label class="label">Notas adicionales</label>
          <textarea v-model="form.notas" class="input" rows="2" placeholder="Información extra…" />
        </div>

        <!-- Google Calendar section — visible when fecha_calendarizada is set -->
        <div v-if="form.fecha_calendarizada" class="rounded-xl border border-blue-100 bg-blue-50/60 p-4 space-y-3">
          <div class="flex items-center gap-2 text-sm font-semibold text-blue-700">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11z"/>
            </svg>
            Enviar a Google Calendar
          </div>
          <div>
            <label class="label text-blue-600">Correos a invitar <span class="font-normal text-slate-400">(separados por coma)</span></label>
            <input v-model="form.emails_invitar" class="input"
              placeholder="juan@indes.gob.sv, maria@indes.gob.sv" />
          </div>
          <button type="button"
            class="btn w-full justify-center gap-2 bg-white border border-blue-200 text-blue-700 hover:bg-blue-100 hover:border-blue-300 font-semibold text-sm py-2.5 rounded-xl transition-colors"
            @click="openGoogleCalendar">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
              <path d="M19 3h-1V1h-2v2H8V1H6v2H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
            </svg>
            Abrir en Google Calendar
          </button>
          <p class="text-[11px] text-blue-500 leading-snug">
            Se abrirá Google Calendar con el evento pre-llenado. Guarda el evento ahí para enviar invitaciones por correo.
          </p>
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showConfirm" message="¿Eliminar esta solicitud?" @confirm="doDelete" />

    <!-- Photo Viewer Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showFotos" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)"
             @click.self="showFotos = false">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col" style="max-height:90vh">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
              <div>
                <div class="font-semibold text-slate-900 text-sm">Evidencia fotográfica</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ fotosTarget?.actividad?.substring(0, 50) }}</div>
              </div>
              <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-slate-700" @click="showFotos = false">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
              <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-200 rounded-xl p-6 cursor-pointer hover:border-amber-400 hover:bg-amber-50/40 transition-all mb-5"
                     :class="{ 'opacity-50 pointer-events-none': uploadingFoto }">
                <CameraIcon class="w-8 h-8 text-slate-300" />
                <span class="text-sm text-slate-500 font-medium">{{ uploadingFoto ? 'Subiendo foto…' : 'Clic o arrastra una foto aquí' }}</span>
                <span class="text-xs text-slate-400">JPG, PNG, WebP — máx. 10 MB</span>
                <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" class="hidden"
                       :disabled="uploadingFoto" @change="handleFotoUpload" />
              </label>
              <div v-if="currentFotos.length" class="grid grid-cols-3 gap-3">
                <div v-for="(foto, i) in currentFotos" :key="i"
                     class="relative group rounded-xl overflow-hidden bg-slate-100 aspect-[4/3]">
                  <img :src="foto.url" class="w-full h-full object-cover" />
                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center">
                    <button v-if="auth.isAdmin"
                            class="opacity-0 group-hover:opacity-100 transition-all btn btn-danger btn-sm"
                            @click="deleteFoto(foto.path)">Eliminar</button>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-slate-400 text-sm">
                Sin fotos adjuntas
              </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400 text-right">
              {{ currentFotos.length }} foto{{ currentFotos.length !== 1 ? 's' : '' }}
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { CameraIcon } from '@heroicons/vue/24/outline'
import { solicitudApi, escenarioApi, tecnicoApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useAuthStore } from '@/stores/auth'
import { useApiError } from '@/composables/useApiError'
import {
  SOLICITUD_PRIORIDAD_BADGE,
  avatarColor, fmtDate, today,
} from '@/constants'
import type { Solicitud, Escenario, Tecnico } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

// ── Status tab definitions ─────────────────────────────────
const TABS = [
  {
    estado: 'pendiente',
    label: 'Pendiente',
    shortLabel: 'Pend.',
    icon: '🟡',
    textClass: 'text-amber-600',
    activeClass: 'bg-white shadow-sm text-amber-600',
    countClass: 'bg-amber-100 text-amber-700',
    selectedClass: 'border-amber-400 bg-amber-50 text-amber-700',
  },
  {
    estado: 'en_proceso',
    label: 'En Proceso',
    shortLabel: 'Proc.',
    icon: '🔵',
    textClass: 'text-blue-600',
    activeClass: 'bg-white shadow-sm text-blue-600',
    countClass: 'bg-blue-100 text-blue-700',
    selectedClass: 'border-blue-400 bg-blue-50 text-blue-700',
  },
  {
    estado: 'completado',
    label: 'Completado',
    shortLabel: 'Comp.',
    icon: '🟢',
    textClass: 'text-emerald-600',
    activeClass: 'bg-white shadow-sm text-emerald-600',
    countClass: 'bg-emerald-100 text-emerald-700',
    selectedClass: 'border-emerald-400 bg-emerald-50 text-emerald-700',
  },
  {
    estado: 'cancelado',
    label: 'Cancelado',
    shortLabel: 'Canc.',
    icon: '🔴',
    textClass: 'text-red-500',
    activeClass: 'bg-white shadow-sm text-red-500',
    countClass: 'bg-red-100 text-red-600',
    selectedClass: 'border-red-400 bg-red-50 text-red-700',
  },
] as const

// Left-border priority color for cards
const PRIORITY_COLOR: Record<string, string> = {
  alto:  '#ef4444',
  medio: '#f59e0b',
  bajo:  '#22c55e',
}

type TabEstado = typeof TABS[number]['estado']

const activeTab       = ref<TabEstado>('pendiente')
const search          = ref('')
const filterPrioridad = ref('')
const filterTecnico   = ref<number | null>(null)

const items      = ref<Solicitud[]>([])
const escenarios = ref<Escenario[]>([])
const tecnicos   = ref<Tecnico[]>([])
const loading    = ref(true)
const saving     = ref(false)
const showModal  = ref(false)
const showConfirm = ref(false)
const editing    = ref<Solicitud | null>(null)
const toDelete   = ref<Solicitud | null>(null)

// ── Derived counts (uses all items, not filtered) ──────────
function tabCount(estado: TabEstado) {
  return items.value.filter(s => s.estado === estado).length
}

// ── Filtered items for active tab ──────────────────────────
const filteredItems = computed(() => {
  let list = items.value.filter(s => s.estado === activeTab.value)
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(s =>
      s.actividad?.toLowerCase().includes(q) ||
      s.solicita?.toLowerCase().includes(q) ||
      s.escenario?.nombre?.toLowerCase().includes(q) ||
      s.escenario_texto?.toLowerCase().includes(q)
    )
  }
  if (filterPrioridad.value) list = list.filter(s => s.prioridad === filterPrioridad.value)
  if (filterTecnico.value)   list = list.filter(s => s.tecnico_id === filterTecnico.value)
  return list
})

const emptyTitle = computed(() => {
  const tab = TABS.find(t => t.estado === activeTab.value)
  return `Sin solicitudes ${tab?.label.toLowerCase() ?? ''}`
})

// ── Photos ─────────────────────────────────────────────────
const showFotos     = ref(false)
const fotosTarget   = ref<Solicitud | null>(null)
const uploadingFoto = ref(false)
const currentFotos  = computed(() =>
  (fotosTarget.value?.fotos ?? []).map(p => ({ path: p, url: `/storage/${p}` }))
)
function openFotos(s: Solicitud) { fotosTarget.value = s; showFotos.value = true }
async function handleFotoUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file || !fotosTarget.value) return
  uploadingFoto.value = true
  try {
    const res = await solicitudApi.uploadFoto(fotosTarget.value.id, file)
    fotosTarget.value.fotos = res.data.fotos.map((f: { path: string }) => f.path)
    const idx = items.value.findIndex(s => s.id === fotosTarget.value!.id)
    if (idx !== -1) items.value[idx].fotos = fotosTarget.value.fotos
  } catch (err) { handleError(err, 'Error al subir la foto') }
  finally { uploadingFoto.value = false; (e.target as HTMLInputElement).value = '' }
}
async function deleteFoto(path: string) {
  if (!fotosTarget.value) return
  try {
    await solicitudApi.removeFoto(fotosTarget.value.id, path)
    fotosTarget.value.fotos = fotosTarget.value.fotos.filter(f => f !== path)
    const idx = items.value.findIndex(s => s.id === fotosTarget.value!.id)
    if (idx !== -1) items.value[idx].fotos = fotosTarget.value.fotos
  } catch (err) { handleError(err, 'Error al eliminar la foto') }
}

// ── Form ───────────────────────────────────────────────────
const emptyForm = (): Partial<Solicitud> => ({
  fecha_solicitud: today(), actividad: '', escenario_id: null, escenario_texto: '',
  solicita: '', fecha_calendarizada: null, hora: null, tecnico_id: null,
  seguimiento: '', prioridad: 'medio', estado: activeTab.value, notas: '', emails_invitar: '',
})
const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const { data } = await solicitudApi.list()
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar solicitudes')
  } finally {
    loading.value = false
  }
}

function openForm(s?: Solicitud) {
  editing.value = s || null
  form.value = s ? { ...s } : emptyForm()
  showModal.value = true
}

function buildPayload() {
  const f = form.value
  // Strip seconds from hora ("09:00:00" → "09:00") to satisfy date_format:H:i validation
  const hora = f.hora ? f.hora.substring(0, 5) : null
  return {
    fecha_solicitud:     f.fecha_solicitud,
    actividad:           f.actividad,
    escenario_id:        f.escenario_id   || null,
    escenario_texto:     f.escenario_texto || null,
    solicita:            f.solicita,
    fecha_calendarizada: f.fecha_calendarizada || null,
    hora,
    tecnico_id:          f.tecnico_id || null,
    seguimiento:         f.seguimiento || null,
    prioridad:           f.prioridad,
    estado:              f.estado,
    notas:               f.notas || null,
    emails_invitar:      f.emails_invitar || null,
  }
}

async function save() {
  if (!form.value.actividad) { toast.add('La actividad es requerida', 'error'); return }
  if (!form.value.solicita)  { toast.add('El solicitante es requerido', 'error'); return }
  saving.value = true
  try {
    const payload = buildPayload()
    const newEstado = payload.estado as TabEstado
    if (editing.value) {
      await solicitudApi.update(editing.value.id, payload as Solicitud)
    } else {
      await solicitudApi.create(payload as Solicitud)
    }
    toast.add('Solicitud guardada correctamente')
    showModal.value = false
    // Switch to the tab matching the saved estado so user sees the card
    activeTab.value = newEstado
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar la solicitud')
  } finally {
    saving.value = false
  }
}

function confirmDelete(s: Solicitud) { toDelete.value = s; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await solicitudApi.remove(toDelete.value.id)
    toast.add('Solicitud eliminada')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar la solicitud')
  }
}

function openGoogleCalendar() {
  const f = form.value
  if (!f.fecha_calendarizada) return

  const dateStr = f.fecha_calendarizada.replace(/-/g, '')
  let dates: string
  if (f.hora) {
    const [h, m] = f.hora.split(':')
    const startTime = `${dateStr}T${h}${m}00`
    const endHour   = String(Number(h) + 1).padStart(2, '0')
    const endTime   = `${dateStr}T${endHour}${m}00`
    dates = `${startTime}/${endTime}`
  } else {
    dates = `${dateStr}/${dateStr}`
  }

  const escNombre = escenarios.value.find((e: Escenario) => e.id === f.escenario_id)?.nombre
    ?? f.escenario_texto ?? ''

  const details = [
    `Solicita: ${f.solicita ?? ''}`,
    f.seguimiento ? `Seguimiento: ${f.seguimiento}` : '',
    f.notas       ? `Notas: ${f.notas}`              : '',
  ].filter(Boolean).join('\n')

  const emails = (f.emails_invitar ?? '').split(',').map(e => e.trim()).filter(Boolean)

  const url = new URL('https://calendar.google.com/calendar/render')
  url.searchParams.set('action', 'TEMPLATE')
  url.searchParams.set('text', f.actividad || 'Solicitud de Servicio')
  url.searchParams.set('dates', dates)
  if (details)    url.searchParams.set('details', details)
  if (escNombre)  url.searchParams.set('location', escNombre)
  if (emails.length) url.searchParams.set('add', emails.join(','))

  window.open(url.toString(), '_blank')
}

onMounted(async () => {
  const [esc, tec] = await Promise.all([escenarioApi.list(), tecnicoApi.list()])
  escenarios.value = esc.data.data
  tecnicos.value   = tec.data.data
  await load()
})
</script>
