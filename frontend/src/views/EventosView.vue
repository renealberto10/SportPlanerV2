<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Bitácora de Eventos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Registro de eventos y producción técnica</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Registrar Evento
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="flex flex-wrap gap-3 items-center">
        <select v-model="filters.escenario_id" class="input w-52" @change="load">
          <option value="">Todos los escenarios</option>
          <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <select v-model="filters.estado" class="input w-40" @change="load">
          <option value="">Todos los estados</option>
          <option value="programado">Programado</option>
          <option value="en_curso">En Curso</option>
          <option value="realizado">Realizado</option>
          <option value="cancelado">Cancelado</option>
        </select>
      </div>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="◎" title="No hay eventos registrados" subtitle="Registra el primer evento técnico" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">Fecha</th>
                <th class="th">Hora</th>
                <th class="th">Evento</th>
                <th class="th">Escenario</th>
                <th class="th">Tipo</th>
                <th class="th">Personal</th>
                <th class="th">Estado</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in items" :key="e.id">
                <td class="td font-medium text-emerald-600 whitespace-nowrap">{{ fmtDate(e.fecha) }}</td>
                <td class="td text-slate-500 text-sm">{{ e.hora || '—' }}</td>
                <td class="td">
                  <span class="font-semibold text-slate-900">{{ e.nombre }}</span>
                  <div v-if="e.descripcion" class="text-xs text-slate-400 truncate max-w-[160px] mt-0.5">{{ e.descripcion }}</div>
                </td>
                <td class="td text-slate-600 text-sm max-w-[140px] truncate">{{ e.escenario?.nombre || '—' }}</td>
                <td class="td"><span class="badge badge-blue">{{ EVENTO_TIPOS[e.tipo] || e.tipo }}</span></td>
                <td class="td text-xs text-slate-500 max-w-xs truncate">{{ e.personal || '—' }}</td>
                <td class="td">
                  <span :class="EVENTO_ESTADO_BADGE[e.estado] || 'badge badge-gray'">
                    {{ EVENTO_ESTADOS[e.estado] || e.estado }}
                  </span>
                </td>
                <td class="td text-right">
                  <div class="flex gap-1 justify-end">
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-blue-600" title="Editar" @click="openForm(e)">✏</button>
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-red-600" title="Eliminar" @click="confirmDelete(e)">✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Evento' : 'Nuevo Evento'" maxWidth="600px">
      <div class="space-y-4">
        <div>
          <label class="label">Nombre del Evento *</label>
          <input v-model="form.nombre" class="input" placeholder="Ej: Final Nacional Fútbol" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Fecha *</label>
            <input type="date" v-model="form.fecha" class="input" />
          </div>
          <div>
            <label class="label">Hora</label>
            <input type="time" v-model="form.hora" class="input" />
          </div>
        </div>
        <div>
          <label class="label">Escenario *</label>
          <select v-model="form.escenario_id" class="input">
            <option :value="null">Seleccionar escenario…</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Tipo de Evento</label>
            <select v-model="form.tipo" class="input">
              <option value="deportivo">Deportivo</option>
              <option value="cultural">Cultural</option>
              <option value="produccion">Producción Técnica</option>
              <option value="otro">Otro</option>
            </select>
          </div>
          <div>
            <label class="label">Estado</label>
            <select v-model="form.estado" class="input">
              <option value="programado">Programado</option>
              <option value="en_curso">En Curso</option>
              <option value="realizado">Realizado</option>
              <option value="cancelado">Cancelado</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Descripción</label>
          <textarea v-model="form.descripcion" class="input" rows="2" placeholder="Detalles del evento…" />
        </div>
        <div>
          <label class="label">Personal Técnico</label>
          <input v-model="form.personal" class="input" placeholder="Nombres separados por coma" />
        </div>
        <div>
          <label class="label">Equipos Utilizados / Notas Técnicas</label>
          <textarea v-model="form.equipos_notas" class="input" rows="2" placeholder="Lista de equipos y configuraciones…" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showConfirm" message="¿Eliminar este evento de la bitácora?" @confirm="doDelete" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { eventoApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { EVENTO_TIPOS, EVENTO_ESTADOS, EVENTO_ESTADO_BADGE, fmtDate, today } from '@/constants'
import type { Evento, Escenario } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

const items      = ref<Evento[]>([])
const escenarios = ref<Escenario[]>([])
const loading    = ref(true)
const saving     = ref(false)
const showModal  = ref(false)
const showConfirm = ref(false)
const editing    = ref<Evento | null>(null)
const toDelete   = ref<Evento | null>(null)
const filters    = reactive({ escenario_id: '', estado: '' })

const emptyForm = (): Partial<Evento> => ({
  nombre: '', fecha: today(), hora: '10:00', escenario_id: undefined,
  tipo: 'deportivo', estado: 'programado', descripcion: '', personal: '', equipos_notas: '',
})
const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (filters.escenario_id) params.escenario_id = filters.escenario_id
    if (filters.estado)       params.estado = filters.estado
    const { data } = await eventoApi.list(params)
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar eventos')
  } finally {
    loading.value = false
  }
}

function openForm(e?: Evento) {
  editing.value = e || null
  form.value = e ? { ...e } : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.nombre?.trim()) { toast.add('El nombre es requerido', 'error'); return }
  if (!form.value.escenario_id)   { toast.add('Selecciona un escenario', 'error'); return }
  saving.value = true
  try {
    if (editing.value) await eventoApi.update(editing.value.id, form.value as Evento)
    else               await eventoApi.create(form.value as Evento)
    toast.add('Evento guardado correctamente')
    showModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar el evento')
  } finally {
    saving.value = false
  }
}

function confirmDelete(e: Evento) { toDelete.value = e; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await eventoApi.remove(toDelete.value.id)
    toast.add('Evento eliminado')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar el evento')
  }
}

onMounted(async () => {
  const { data } = await escenarioApi.list()
  escenarios.value = data.data
  await load()
})
</script>
