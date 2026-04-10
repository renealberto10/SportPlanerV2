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
                  <div class="flex gap-1 justify-end">
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-blue-600" title="Editar" @click="openForm(s)">✏</button>
                    <button v-if="auth.isAdmin || s.estado === 'pendiente'" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-red-600" title="Eliminar" @click="confirmDelete(s)">✕</button>
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
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showConfirm" message="¿Eliminar esta solicitud?" @confirm="doDelete" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
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

const emptyForm = (): Partial<Solicitud> => ({
  fecha_solicitud: today(), actividad: '', escenario_id: null, escenario_texto: '',
  solicita: '', fecha_calendarizada: null, hora: null, tecnico_id: null,
  seguimiento: '', prioridad: 'medio', estado: 'pendiente', notas: '',
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

async function save() {
  if (!form.value.actividad) { toast.add('La actividad es requerida', 'error'); return }
  if (!form.value.solicita)  { toast.add('El solicitante es requerido', 'error'); return }
  saving.value = true
  try {
    if (editing.value) await solicitudApi.update(editing.value.id, form.value as Solicitud)
    else               await solicitudApi.create(form.value as Solicitud)
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

onMounted(async () => {
  const [esc, tec] = await Promise.all([escenarioApi.list(), tecnicoApi.list()])
  escenarios.value = esc.data.data
  tecnicos.value   = tec.data.data
  await load()
})
</script>
