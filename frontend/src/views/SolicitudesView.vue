<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Solicitudes de Mantenimiento</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de solicitudes de mantenimiento</p>
      </div>
      <button class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Nueva Solicitud
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 sm:items-center">
        <input v-model="filters.search" class="input col-span-2" placeholder="Buscar actividad, solicitante…" @input="load" />
        <select v-model="filters.prioridad" class="input" @change="load">
          <option value="">Todas las prioridades</option>
          <option value="alto">🔴 Alta</option>
          <option value="medio">🟡 Media</option>
          <option value="bajo">🟢 Baja</option>
        </select>
        <select v-model="filters.estado" class="input" @change="load">
          <option value="">Todos los estados</option>
          <option value="pendiente">Pendiente</option>
          <option value="en_proceso">En Proceso</option>
          <option value="completado">Completado</option>
          <option value="cancelado">Cancelado</option>
        </select>
        <select v-model="filters.tecnico_id" class="input col-span-2 sm:col-span-1" @change="load">
          <option value="">Todos los técnicos</option>
          <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
        </select>
      </div>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="📋" title="Sin solicitudes" subtitle="Agrega la primera solicitud de mantenimiento" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">#</th>
                <th class="th">Fecha</th>
                <th class="th">Actividad</th>
                <th class="th">Escenario</th>
                <th class="th">Solicita</th>
                <th class="th">Calendarizada</th>
                <th class="th">Técnico</th>
                <th class="th">Prioridad</th>
                <th class="th">Estado</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="s in items" :key="s.id">
                <td class="td text-slate-400 text-xs font-mono">{{ s.id }}</td>
                <td class="td whitespace-nowrap text-sm">{{ fmtDate(s.fecha_solicitud) }}</td>
                <td class="td max-w-[200px]">
                  <p class="text-sm text-slate-800 line-clamp-2 leading-snug">{{ s.actividad }}</p>
                </td>
                <td class="td text-slate-600 text-sm max-w-[140px] truncate">
                  {{ s.escenario?.nombre || s.escenario_texto || '—' }}
                </td>
                <td class="td text-slate-600 text-sm whitespace-nowrap">{{ s.solicita }}</td>
                <td class="td whitespace-nowrap text-sm">
                  <span v-if="s.fecha_calendarizada" class="font-medium text-blue-600">{{ fmtDate(s.fecha_calendarizada) }}</span>
                  <span v-else class="text-slate-300">—</span>
                </td>
                <td class="td">
                  <div v-if="s.tecnico" class="flex items-center gap-1.5">
                    <div class="w-6 h-6 rounded-md flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="`background:${avatarColor(s.tecnico.nombre_completo)}`">
                      {{ s.tecnico.nombre_completo[0] }}
                    </div>
                    <span class="text-xs text-slate-700">{{ s.tecnico.nombre_completo }}</span>
                  </div>
                  <button v-else class="text-xs text-blue-500 hover:text-blue-700 font-medium" @click="openForm(s)">
                    + Asignar
                  </button>
                </td>
                <td class="td">
                  <span :class="SOLICITUD_PRIORIDAD_BADGE[s.prioridad]">{{ s.prioridad.toUpperCase() }}</span>
                </td>
                <td class="td">
                  <span :class="SOLICITUD_ESTADO_BADGE[s.estado]">{{ SOLICITUD_ESTADOS[s.estado] }}</span>
                </td>
                <td class="td text-right">
                  <div class="flex gap-1 justify-end items-center">
                    <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-amber-600 relative"
                            title="Fotos" @click="openFotos(s)">
                      <CameraIcon class="w-4 h-4" />
                      <span v-if="s.fotos?.length"
                            class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ s.fotos.length }}
                      </span>
                    </button>
                    <button v-if="auth.isAdmin || auth.isTecnico" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-blue-600" title="Editar" @click="openForm(s)">✏</button>
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-red-600" title="Eliminar" @click="confirmDelete(s)">✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
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
        <div>
          <label class="label">Estado</label>
          <select v-model="form.estado" class="input">
            <option value="pendiente">Pendiente</option>
            <option value="en_proceso">En Proceso</option>
            <option value="completado">Completado</option>
            <option value="cancelado">Cancelado</option>
          </select>
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
import { ref, reactive, computed, onMounted } from 'vue'
import { CameraIcon } from '@heroicons/vue/24/outline'
import { solicitudApi, escenarioApi, tecnicoApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useAuthStore } from '@/stores/auth'
import { useApiError } from '@/composables/useApiError'
import {
  SOLICITUD_PRIORIDAD_BADGE, SOLICITUD_ESTADO_BADGE, SOLICITUD_ESTADOS,
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

const items      = ref<Solicitud[]>([])
const escenarios = ref<Escenario[]>([])
const tecnicos   = ref<Tecnico[]>([])
const loading    = ref(true)
const saving     = ref(false)
const showModal  = ref(false)
const showConfirm = ref(false)
const editing    = ref<Solicitud | null>(null)
const toDelete   = ref<Solicitud | null>(null)
const filters    = reactive({ search: '', prioridad: '', estado: '', tecnico_id: '' })

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

const emptyForm = (): Partial<Solicitud> => ({
  fecha_solicitud: today(), actividad: '', escenario_id: null, escenario_texto: '',
  solicita: '', fecha_calendarizada: null, hora: null, tecnico_id: null,
  seguimiento: '', prioridad: 'medio', estado: 'pendiente', notas: '', emails_invitar: '',
})
const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (filters.search)     params.search = filters.search
    if (filters.prioridad)  params.prioridad = filters.prioridad
    if (filters.estado)     params.estado = filters.estado
    if (filters.tecnico_id) params.tecnico_id = filters.tecnico_id
    const { data } = await solicitudApi.list(params)
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
  return {
    fecha_solicitud:     f.fecha_solicitud,
    actividad:           f.actividad,
    escenario_id:        f.escenario_id   || null,
    escenario_texto:     f.escenario_texto || null,
    solicita:            f.solicita,
    fecha_calendarizada: f.fecha_calendarizada || null,
    hora:                f.hora || null,
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
    if (editing.value) await solicitudApi.update(editing.value.id, payload as Solicitud)
    else               await solicitudApi.create(payload as Solicitud)
    toast.add('Solicitud guardada correctamente')
    showModal.value = false
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
