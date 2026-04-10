<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Inventario de Equipos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Control de equipos audiovisuales y tecnológicos</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Agregar Equipo
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 sm:items-center">
        <input
          v-model="filters.search"
          class="input col-span-2"
          placeholder="Buscar nombre, modelo, serie…"
          @input="load"
        />
        <select v-model="filters.escenario_id" class="input" @change="load">
          <option value="">Todos los escenarios</option>
          <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <select v-model="filters.tipo" class="input" @change="load">
          <option value="">Todos los tipos</option>
          <option value="pantalla">Pantalla COLOSSEO</option>
          <option value="bocina">Bocina</option>
          <option value="consola">Consola de Audio</option>
          <option value="servidor">Servidor</option>
          <option value="otro">Otro</option>
        </select>
        <select v-model="filters.estado" class="input" @change="load">
          <option value="">Todos los estados</option>
          <option value="operativo">Operativo</option>
          <option value="mantenimiento">En Mantenimiento</option>
          <option value="falla">Con Falla</option>
          <option value="baja">Baja</option>
        </select>
        <button v-if="hasActiveFilters" class="btn btn-ghost btn-sm text-slate-500 col-span-2 sm:col-span-1" @click="clearFilters">
          ✕ Limpiar filtros
        </button>
      </div>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="▣" title="No hay equipos registrados" subtitle="Agrega equipos al inventario" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">Nombre</th>
                <th class="th">Tipo</th>
                <th class="th">Modelo / Serie</th>
                <th class="th">Escenario</th>
                <th class="th">Estado</th>
                <th class="th">F. Instalación</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in items" :key="e.id">
                <td class="td">
                  <span class="font-semibold text-slate-900">{{ e.nombre }}</span>
                  <div v-if="e.notas" class="text-xs text-slate-400 truncate max-w-[180px] mt-0.5">{{ e.notas }}</div>
                </td>
                <td class="td">
                  <span class="badge badge-blue">{{ EQUIPO_TIPOS[e.tipo] || e.tipo }}</span>
                </td>
                <td class="td">
                  <span class="text-slate-700 text-xs">{{ e.modelo || '—' }}</span>
                  <span v-if="e.serie" class="text-slate-400 text-xs"> / {{ e.serie }}</span>
                </td>
                <td class="td text-slate-600 text-sm">{{ e.escenario?.nombre || '—' }}</td>
                <td class="td">
                  <span :class="EQUIPO_ESTADO_BADGE[e.estado] || 'badge badge-gray'">
                    {{ EQUIPO_ESTADOS[e.estado] || e.estado }}
                  </span>
                </td>
                <td class="td text-slate-500 text-xs whitespace-nowrap">{{ e.fecha_instalacion ? fmtDate(e.fecha_instalacion) : '—' }}</td>
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

    <!-- Form Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Equipo' : 'Nuevo Equipo'" maxWidth="600px">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="col-span-2">
          <label class="label">Nombre del Equipo *</label>
          <input v-model="form.nombre" class="input" placeholder="Nombre descriptivo del equipo" />
        </div>
        <div>
          <label class="label">Tipo *</label>
          <select v-model="form.tipo" class="input">
            <option value="pantalla">Pantalla COLOSSEO</option>
            <option value="bocina">Bocina</option>
            <option value="consola">Consola de Audio</option>
            <option value="servidor">Servidor</option>
            <option value="otro">Otro</option>
          </select>
        </div>
        <div>
          <label class="label">Estado *</label>
          <select v-model="form.estado" class="input">
            <option value="operativo">Operativo</option>
            <option value="mantenimiento">En Mantenimiento</option>
            <option value="falla">Con Falla</option>
            <option value="baja">Baja</option>
          </select>
        </div>
        <div>
          <label class="label">Modelo</label>
          <input v-model="form.modelo" class="input" placeholder="Ej: Samsung QN85B" />
        </div>
        <div>
          <label class="label">Número de Serie</label>
          <input v-model="form.serie" class="input" placeholder="Número de serie" />
        </div>
        <div class="col-span-2">
          <label class="label">Escenario</label>
          <select v-model="form.escenario_id" class="input">
            <option :value="null">Sin asignar</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="label">Fecha de Instalación</label>
          <input type="date" v-model="form.fecha_instalacion" class="input" />
        </div>
        <div class="col-span-2">
          <label class="label">Notas</label>
          <textarea v-model="form.notas" class="input" rows="2" placeholder="Observaciones técnicas…" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showConfirm" message="¿Eliminar este equipo del inventario? Esta acción no se puede deshacer." @confirm="doDelete" />
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { equipoApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { EQUIPO_TIPOS, EQUIPO_ESTADOS, EQUIPO_ESTADO_BADGE, fmtDate } from '@/constants'
import type { Equipo, Escenario } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

const items      = ref<Equipo[]>([])
const escenarios = ref<Escenario[]>([])
const loading    = ref(true)
const saving     = ref(false)
const showModal  = ref(false)
const showConfirm = ref(false)
const editing    = ref<Equipo | null>(null)
const toDelete   = ref<Equipo | null>(null)
const filters    = reactive({ search: '', escenario_id: '', tipo: '', estado: '' })

const hasActiveFilters = computed(() => filters.search || filters.escenario_id || filters.tipo || filters.estado)

const emptyForm = (): Partial<Equipo> => ({
  nombre: '', tipo: 'pantalla', modelo: '', serie: '',
  escenario_id: null, estado: 'operativo', fecha_instalacion: '', notas: '',
})
const form = ref(emptyForm())

function clearFilters() {
  filters.search = ''; filters.escenario_id = ''; filters.tipo = ''; filters.estado = ''
  load()
}

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (filters.search)       params.search = filters.search
    if (filters.escenario_id) params.escenario_id = filters.escenario_id
    if (filters.tipo)         params.tipo = filters.tipo
    if (filters.estado)       params.estado = filters.estado
    const { data } = await equipoApi.list(params)
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar equipos')
  } finally {
    loading.value = false
  }
}

function openForm(e?: Equipo) {
  editing.value = e || null
  form.value = e ? { ...e } : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.nombre?.trim()) { toast.add('El nombre es requerido', 'error'); return }
  saving.value = true
  try {
    if (editing.value) await equipoApi.update(editing.value.id, form.value as Equipo)
    else               await equipoApi.create(form.value as Equipo)
    toast.add('Equipo guardado correctamente')
    showModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar el equipo')
  } finally {
    saving.value = false
  }
}

function confirmDelete(e: Equipo) { toDelete.value = e; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await equipoApi.remove(toDelete.value.id)
    toast.add('Equipo eliminado')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar el equipo')
  }
}

onMounted(async () => {
  const { data } = await escenarioApi.list()
  escenarios.value = data.data
  await load()
})
</script>
