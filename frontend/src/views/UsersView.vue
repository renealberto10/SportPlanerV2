<template>
  <div class="space-y-6">

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h2 class="text-lg font-bold text-slate-900">Usuarios del sistema</h2>
        <p class="text-xs text-slate-500 mt-0.5">Gestión de acceso y roles</p>
      </div>
      <button @click="openCreate" class="btn btn-primary flex items-center gap-2">
        <PlusIcon class="w-4 h-4" />
        Nuevo usuario
      </button>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden p-0">
      <div v-if="loading" class="p-8 text-center text-slate-400 text-sm">Cargando…</div>

      <table v-else class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 text-xs font-semibold text-slate-500 uppercase tracking-wider">
            <th class="text-left px-5 py-3">Nombre</th>
            <th class="text-left px-5 py-3">Correo</th>
            <th class="text-left px-5 py-3">Rol</th>
            <th class="text-left px-5 py-3">Estado</th>
            <th class="px-5 py-3"></th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="u in users" :key="u.id"
              class="border-b border-slate-50 last:border-0 hover:bg-slate-50 transition-colors">
            <td class="px-5 py-3 font-medium text-slate-900">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                     :style="{ background: rolColor(u.rol) }">
                  {{ initials(u.name) }}
                </div>
                {{ u.name }}
              </div>
            </td>
            <td class="px-5 py-3 text-slate-500">{{ u.email }}</td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                    :class="rolBadge(u.rol)">
                {{ rolLabel(u.rol) }}
              </span>
            </td>
            <td class="px-5 py-3">
              <span class="inline-flex items-center gap-1 text-xs font-medium"
                    :class="u.activo ? 'text-emerald-600' : 'text-slate-400'">
                <span class="w-1.5 h-1.5 rounded-full"
                      :class="u.activo ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                {{ u.activo ? 'Activo' : 'Inactivo' }}
              </span>
            </td>
            <td class="px-5 py-3 text-right">
              <div class="flex items-center justify-end gap-2">
                <button @click="openEdit(u)"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors">
                  <PencilSquareIcon class="w-4 h-4" />
                </button>
                <button @click="confirmDelete(u)"
                        :disabled="u.id === auth.user?.id"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors
                               disabled:opacity-30 disabled:cursor-not-allowed">
                  <TrashIcon class="w-4 h-4" />
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar usuario' : 'Nuevo usuario'">
      <form @submit.prevent="save" class="space-y-4">

        <div class="grid grid-cols-2 gap-3">
          <div class="col-span-2">
            <label class="label">Nombre completo</label>
            <input v-model="form.name" required class="input" placeholder="Ej. Juan Pérez" />
          </div>
          <div class="col-span-2">
            <label class="label">Correo electrónico</label>
            <input v-model="form.email" type="email" required class="input" placeholder="usuario@correo.com" />
          </div>
          <div class="col-span-2">
            <label class="label">
              Contraseña
              <span v-if="editing" class="text-slate-400 font-normal">(dejar vacío para no cambiar)</span>
            </label>
            <input v-model="form.password" type="password" :required="!editing" class="input"
                   placeholder="Mínimo 8 caracteres" autocomplete="new-password" />
          </div>
          <div>
            <label class="label">Rol</label>
            <select v-model="form.rol" required class="input">
              <option value="admin">Administrador</option>
              <option value="tecnico">Técnico</option>
              <option value="bodega">Bodega</option>
              <option value="viewer">Solo lectura</option>
            </select>
          </div>
          <div class="flex items-end pb-1">
            <label class="flex items-center gap-2 cursor-pointer select-none">
              <input v-model="form.activo" type="checkbox"
                     class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500" />
              <span class="text-sm font-medium text-slate-700">Usuario activo</span>
            </label>
          </div>
        </div>

        <div v-if="formError" class="bg-red-50 border border-red-200 rounded-xl px-3.5 py-2.5 text-xs text-red-700">
          {{ formError }}
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="button" @click="closeModal" class="btn btn-ghost">Cancelar</button>
          <button type="submit" :disabled="saving" class="btn btn-primary">
            {{ saving ? 'Guardando…' : (editing ? 'Actualizar' : 'Crear usuario') }}
          </button>
        </div>
      </form>
    </AppModal>

    <!-- Confirm Delete -->
    <ConfirmDialog
      v-model="showConfirmDelete"
      :message="deleteTarget ? `¿Eliminar a ${deleteTarget.name}? Esta acción no se puede deshacer.` : ''"
      @confirm="doDelete"
    />
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { PlusIcon, PencilSquareIcon, TrashIcon } from '@heroicons/vue/24/outline'
import { userApi } from '@/api'
import { useAuthStore } from '@/stores/auth'
import { useToastStore } from '@/stores/toast'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import type { User } from '@/types'
import type { AxiosError } from 'axios'

const auth  = useAuthStore()
const toast = useToastStore()

const users             = ref<User[]>([])
const loading           = ref(true)
const showModal         = ref(false)
const showConfirmDelete = ref(false)
const editing           = ref<User | null>(null)
const saving            = ref(false)
const formError         = ref('')
const deleteTarget      = ref<User | null>(null)

const emptyForm = () => ({ name: '', email: '', password: '', rol: 'viewer' as User['rol'], activo: true })
const form = ref(emptyForm())

async function load() {
  loading.value = true
  try {
    const res = await userApi.list()
    users.value = res.data
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editing.value  = null
  form.value     = emptyForm()
  formError.value = ''
  showModal.value = true
}

function openEdit(u: User) {
  editing.value   = u
  form.value      = { name: u.name, email: u.email, password: '', rol: u.rol, activo: u.activo }
  formError.value = ''
  showModal.value = true
}

function closeModal() { showModal.value = false; formError.value = '' }

async function save() {
  formError.value = ''
  saving.value    = true
  try {
    if (editing.value) {
      const payload: Record<string, unknown> = { name: form.value.name, email: form.value.email, rol: form.value.rol, activo: form.value.activo }
      if (form.value.password) payload.password = form.value.password
      await userApi.update(editing.value.id, payload as Parameters<typeof userApi.update>[1])
      toast.add('Usuario actualizado')
    } else {
      await userApi.create(form.value as Parameters<typeof userApi.create>[0])
      toast.add('Usuario creado')
    }
    closeModal()
    await load()
  } catch (e) {
    const err = e as AxiosError<{ errors?: Record<string, string[]>; message?: string }>
    const msgs = err.response?.data?.errors
    formError.value = msgs ? Object.values(msgs).flat().join(' · ') : (err.response?.data?.message ?? 'Error al guardar')
  } finally {
    saving.value = false
  }
}

function confirmDelete(u: User) {
  deleteTarget.value = u
  showConfirmDelete.value = true
}

async function doDelete() {
  if (!deleteTarget.value) return
  showConfirmDelete.value = false
  try {
    await userApi.remove(deleteTarget.value.id)
    toast.add('Usuario eliminado')
    deleteTarget.value = null
    await load()
  } catch {
    toast.add('No se pudo eliminar el usuario', 'error')
  }
}

// ── Helpers ───────────────────────────────────────────────
function initials(name: string) {
  return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
}
function rolLabel(rol: string) {
  return { admin: 'Administrador', tecnico: 'Técnico', bodega: 'Bodega', viewer: 'Solo lectura' }[rol] ?? rol
}
function rolColor(rol: string) {
  return { admin: '#1d4ed8', tecnico: '#0369a1', bodega: '#0f766e', viewer: '#64748b' }[rol] ?? '#64748b'
}
function rolBadge(rol: string) {
  return {
    admin:   'bg-blue-100 text-blue-700',
    tecnico: 'bg-sky-100 text-sky-700',
    bodega:  'bg-teal-100 text-teal-700',
    viewer:  'bg-slate-100 text-slate-600',
  }[rol] ?? 'bg-slate-100 text-slate-600'
}

onMounted(load)
</script>
