<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Técnicos Registrados</h2>
        <p class="text-xs text-slate-500 mt-0.5">Personal técnico asignado al contrato</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Agregar Técnico
      </button>
    </div>

    <!-- Stats bar -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <div class="card card-hover">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</div>
        <div class="text-2xl font-bold text-slate-900 mt-1.5">{{ items.length }}</div>
        <div class="text-xs text-slate-400 mt-0.5">Técnicos</div>
      </div>
      <div class="card card-hover">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Activos</div>
        <div class="text-2xl font-bold text-emerald-600 mt-1.5">{{ items.filter(t => t.activo).length }}</div>
        <div class="text-xs text-slate-400 mt-0.5">Disponibles</div>
      </div>
      <div class="card card-hover">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Video</div>
        <div class="text-2xl font-bold text-purple-600 mt-1.5">{{ items.filter(t => t.especialidad === 'video').length }}</div>
        <div class="text-xs text-slate-400 mt-0.5">Especialistas</div>
      </div>
      <div class="card card-hover">
        <div class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Audio</div>
        <div class="text-2xl font-bold text-amber-600 mt-1.5">{{ items.filter(t => t.especialidad === 'audio').length }}</div>
        <div class="text-xs text-slate-400 mt-0.5">Especialistas</div>
      </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="flex flex-wrap gap-3 items-center">
        <input v-model="search" class="input max-w-xs" placeholder="Buscar por nombre o correo…" @input="load" />
        <select v-model="filtroActivo" class="input w-40" @change="load">
          <option value="">Todos</option>
          <option value="1">Solo activos</option>
          <option value="0">Inactivos</option>
        </select>
      </div>
    </div>

    <!-- Table -->
    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="◉" title="No hay técnicos registrados" subtitle="Agrega técnicos para asignarlos a los mantenimientos" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">Técnico</th>
                <th class="th">Especialidad</th>
                <th class="th">Teléfono</th>
                <th class="th">Correo</th>
                <th class="th text-center">Mantenimientos</th>
                <th class="th">Estado</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="t in items" :key="t.id">
                <td class="td">
                  <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="`background:${avatarColor(t.nombre)}`">
                      {{ initials(t) }}
                    </div>
                    <div>
                      <div class="font-semibold text-slate-900 text-sm">{{ t.nombre_completo }}</div>
                      <div v-if="t.notas" class="text-xs text-slate-400 truncate max-w-[200px]">{{ t.notas }}</div>
                    </div>
                  </div>
                </td>
                <td class="td">
                  <span :class="TECNICO_ESPECIALIDAD_BADGE[t.especialidad] || 'badge badge-gray'">
                    {{ TECNICO_ESPECIALIDADES[t.especialidad] || t.especialidad }}
                  </span>
                </td>
                <td class="td text-slate-600 text-sm">{{ t.telefono || '—' }}</td>
                <td class="td text-sm text-blue-600">{{ t.email || '—' }}</td>
                <td class="td text-center">
                  <span class="badge badge-gray">{{ t.mantenimientos_count ?? 0 }}</span>
                </td>
                <td class="td">
                  <span :class="t.activo ? 'badge badge-green' : 'badge badge-red'">
                    {{ t.activo ? 'Activo' : 'Inactivo' }}
                  </span>
                </td>
                <td class="td text-right">
                  <div class="flex gap-1 justify-end">
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-blue-600" title="Editar" @click="openForm(t)">✏</button>
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-red-600" title="Eliminar" @click="confirmDelete(t)">✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Form Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Técnico' : 'Nuevo Técnico'" maxWidth="560px">
      <div class="space-y-4">
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Nombre *</label>
            <input v-model="form.nombre" class="input" placeholder="Nombre(s)" />
          </div>
          <div>
            <label class="label">Apellido *</label>
            <input v-model="form.apellido" class="input" placeholder="Apellido(s)" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Teléfono</label>
            <input v-model="form.telefono" class="input" placeholder="7777-1234" />
          </div>
          <div>
            <label class="label">Correo electrónico</label>
            <input type="email" v-model="form.email" class="input" placeholder="tecnico@ejemplo.com" />
          </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="label">Especialidad *</label>
            <select v-model="form.especialidad" class="input">
              <option value="audio">Audio</option>
              <option value="video">Video / Pantallas</option>
              <option value="electronica">Electrónica</option>
              <option value="redes">Redes / IT</option>
              <option value="general">General</option>
            </select>
          </div>
          <div>
            <label class="label">Estado</label>
            <select v-model="form.activo" class="input">
              <option :value="true">Activo</option>
              <option :value="false">Inactivo</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Notas</label>
          <textarea v-model="form.notas" class="input" rows="2" placeholder="Certificaciones, observaciones…" />
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
      message="¿Eliminar este técnico? Los mantenimientos asociados quedarán sin técnico asignado."
      @confirm="doDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { tecnicoApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { TECNICO_ESPECIALIDADES, TECNICO_ESPECIALIDAD_BADGE, avatarColor } from '@/constants'
import type { Tecnico } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

const items       = ref<Tecnico[]>([])
const loading     = ref(true)
const saving      = ref(false)
const showModal   = ref(false)
const showConfirm = ref(false)
const editing     = ref<Tecnico | null>(null)
const toDelete    = ref<Tecnico | null>(null)
const search        = ref('')
const filtroActivo  = ref('')

const emptyForm = (): Partial<Tecnico> => ({ nombre: '', apellido: '', telefono: '', email: '', especialidad: 'general', activo: true, notas: '' })
const form = ref(emptyForm())

const initials = (t: Tecnico) => `${t.nombre?.[0] ?? ''}${t.apellido?.[0] ?? ''}`.toUpperCase()

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (search.value) params.search = search.value
    if (filtroActivo.value !== '') params.activo = filtroActivo.value
    const { data } = await tecnicoApi.list(params)
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar técnicos')
  } finally {
    loading.value = false
  }
}

function openForm(t?: Tecnico) {
  editing.value = t || null
  form.value = t ? { ...t } : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.nombre?.trim() || !form.value.apellido?.trim()) {
    toast.add('Nombre y apellido son requeridos', 'error'); return
  }
  saving.value = true
  try {
    if (editing.value) await tecnicoApi.update(editing.value.id, form.value as Tecnico)
    else               await tecnicoApi.create(form.value as Tecnico)
    toast.add('Técnico guardado correctamente')
    showModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar el técnico')
  } finally {
    saving.value = false
  }
}

function confirmDelete(t: Tecnico) { toDelete.value = t; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await tecnicoApi.remove(toDelete.value.id)
    toast.add('Técnico eliminado')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar el técnico')
  }
}

onMounted(load)
</script>
