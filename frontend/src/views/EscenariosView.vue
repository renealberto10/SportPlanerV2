<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Escenarios Deportivos</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de recintos deportivos</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Agregar Escenario
      </button>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="⬡" title="No hay escenarios registrados" subtitle="Agrega el primer escenario deportivo" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">#</th>
                <th class="th">Nombre</th>
                <th class="th">Descripción</th>
                <th class="th text-center">Equipos</th>
                <th class="th text-center">Mantenimientos</th>
                <th class="th text-center">Colosseo</th>
                <th class="th">Estado</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(e, i) in items" :key="e.id">
                <td class="td text-slate-400 text-xs font-mono">{{ String(i + 1).padStart(2, '0') }}</td>
                <td class="td">
                  <span class="font-semibold text-slate-900">{{ e.nombre }}</span>
                </td>
                <td class="td text-slate-500 max-w-[220px] truncate">{{ e.descripcion || '—' }}</td>
                <td class="td text-center">
                  <span class="badge badge-blue">{{ e.equipos_count ?? 0 }}</span>
                </td>
                <td class="td text-center">
                  <span class="badge badge-gray">{{ e.mantenimientos_count ?? 0 }}</span>
                </td>
                <td class="td text-center">
                  <span :class="e.tiene_colosseo ? 'badge badge-purple' : 'badge badge-gray'">
                    {{ e.tiene_colosseo ? 'Sí' : 'No' }}
                  </span>
                </td>
                <td class="td">
                  <span :class="e.activo ? 'badge badge-green' : 'badge badge-red'">
                    {{ e.activo ? 'Activo' : 'Inactivo' }}
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

    <!-- Form Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Escenario' : 'Nuevo Escenario'" maxWidth="520px">
      <div class="space-y-4">
        <div>
          <label class="label">Nombre del Escenario *</label>
          <input v-model="form.nombre" class="input" placeholder="Nombre oficial del escenario" />
        </div>
        <div>
          <label class="label">Descripción</label>
          <textarea v-model="form.descripcion" class="input" rows="2" placeholder="Descripción breve del recinto" />
        </div>
        <div>
          <label class="label">Dirección</label>
          <input v-model="form.direccion" class="input" placeholder="Dirección del escenario" />
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Estado</label>
            <select v-model="form.activo" class="input">
              <option :value="true">Activo</option>
              <option :value="false">Inactivo</option>
            </select>
          </div>
          <div>
            <label class="label">Sistema Colosseo</label>
            <select v-model="form.tiene_colosseo" class="input">
              <option :value="true">Sí</option>
              <option :value="false">No</option>
            </select>
          </div>
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog
      v-model="showConfirm"
      message="¿Eliminar este escenario? Se eliminarán también todos sus equipos, mantenimientos y eventos asociados."
      @confirm="doDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import type { Escenario } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

const items       = ref<Escenario[]>([])
const loading     = ref(true)
const saving      = ref(false)
const showModal   = ref(false)
const showConfirm = ref(false)
const editing     = ref<Escenario | null>(null)
const toDelete    = ref<Escenario | null>(null)

const emptyForm = () => ({ nombre: '', descripcion: '', direccion: '', activo: true, tiene_colosseo: true })
const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const { data } = await escenarioApi.list()
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar escenarios')
  } finally {
    loading.value = false
  }
}

function openForm(e?: Escenario) {
  editing.value = e || null
  form.value = e ? { ...e } : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.nombre.trim()) { toast.add('El nombre es requerido', 'error'); return }
  saving.value = true
  try {
    if (editing.value) await escenarioApi.update(editing.value.id, form.value)
    else               await escenarioApi.create(form.value)
    toast.add('Escenario guardado correctamente')
    showModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar el escenario')
  } finally {
    saving.value = false
  }
}

function confirmDelete(e: Escenario) { toDelete.value = e; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await escenarioApi.remove(toDelete.value.id)
    toast.add('Escenario eliminado')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar el escenario')
  }
}

onMounted(load)
</script>
