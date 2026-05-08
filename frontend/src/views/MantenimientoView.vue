<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Bitácora de Mantenimiento</h2>
        <p class="text-xs text-slate-500 mt-0.5">Registro de visitas y servicios técnicos</p>
      </div>
      <button v-if="auth.isAdmin" class="btn btn-primary" @click="openForm()">
        <span class="text-base leading-none">+</span> Registrar Visita
      </button>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
      <div class="grid grid-cols-2 sm:flex sm:flex-wrap gap-2 sm:gap-3 sm:items-center">
        <input
          v-model="filters.search"
          class="input col-span-2"
          placeholder="Buscar técnico, actividades…"
          @input="load"
        />
        <select v-model="filters.escenario_id" class="input" @change="load">
          <option value="">Todos los escenarios</option>
          <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <select v-model="filters.tecnico_id" class="input" @change="load">
          <option value="">Todos los técnicos</option>
          <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
        </select>
        <select v-model="filters.tipo" class="input" @change="load">
          <option value="">Todos los tipos</option>
          <option value="preventivo">Preventivo</option>
          <option value="correctivo">Correctivo</option>
          <option value="operativo">Operativo</option>
        </select>
        <select v-model="filters.estado" class="input" @change="load">
          <option value="">Todos los estados</option>
          <option value="completado">Completado</option>
          <option value="en_proceso">En Proceso</option>
          <option value="pendiente">Pendiente</option>
        </select>
      </div>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length" icon="⚙" title="No hay registros de mantenimiento" subtitle="Registra la primera visita técnica" />
        <div v-else class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr>
                <th class="th">Fecha</th>
                <th class="th">Escenario</th>
                <th class="th">Técnico</th>
                <th class="th">Tipo</th>
                <th class="th">Actividades</th>
                <th class="th text-center">Hrs</th>
                <th class="th">Estado</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in items" :key="m.id">
                <td class="td font-medium text-blue-600 whitespace-nowrap">{{ fmtDate(m.fecha) }}</td>
                <td class="td text-slate-700 max-w-[160px] truncate">{{ m.escenario?.nombre || '—' }}</td>
                <td class="td">
                  <div v-if="m.tecnico_obj" class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                         :style="`background:${avatarColor(m.tecnico_obj.nombre_completo)}`">
                      {{ m.tecnico_obj.nombre_completo[0] }}
                    </div>
                    <span class="text-sm text-slate-700">{{ m.tecnico_obj.nombre_completo }}</span>
                  </div>
                  <span v-else class="text-slate-400 text-sm">{{ m.tecnico || '—' }}</span>
                </td>
                <td class="td">
                  <span :class="MANTENIMIENTO_TIPO_BADGE[m.tipo] || 'badge badge-gray'">
                    {{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}
                  </span>
                </td>
                <td class="td max-w-xs text-slate-500 text-xs leading-relaxed">
                  <span class="line-clamp-2">{{ m.actividades || '—' }}</span>
                </td>
                <td class="td text-center font-medium text-slate-700">{{ m.horas || 0 }}h</td>
                <td class="td">
                  <span :class="MANTENIMIENTO_ESTADO_BADGE[m.estado] || 'badge badge-gray'">
                    {{ MANTENIMIENTO_ESTADOS[m.estado] || m.estado }}
                  </span>
                </td>
                <td class="td text-right">
                  <div class="flex gap-1 justify-end items-center">
                    <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-emerald-600 relative"
                            title="Fotos" @click="openFotos(m)">
                      <CameraIcon class="w-4 h-4" />
                      <span v-if="m.fotos?.length"
                            class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                        {{ m.fotos.length }}
                      </span>
                    </button>
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-blue-600" title="Editar" @click="openForm(m)">✏</button>
                    <button v-if="auth.isAdmin" class="btn btn-ghost btn-sm btn-icon text-slate-500 hover:text-red-600" title="Eliminar" @click="confirmDelete(m)">✕</button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>
    </div>

    <!-- Form Modal -->
    <AppModal v-model="showModal" :title="editing ? 'Editar Mantenimiento' : 'Nueva Visita de Mantenimiento'" maxWidth="640px">
      <div class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Técnico Responsable</label>
            <select v-model="form.tecnico_id" class="input" @change="onTecnicoChange">
              <option :value="null">— Sin asignar —</option>
              <option v-for="t in tecnicosActivos" :key="t.id" :value="t.id">
                {{ t.nombre_completo }} · {{ TECNICO_ESPECIALIDADES[t.especialidad] || t.especialidad }}
              </option>
            </select>
            <p v-if="!tecnicosActivos.length" class="text-xs text-amber-600 mt-1.5">
              ⚠ No hay técnicos activos.
              <RouterLink to="/tecnicos" class="underline font-medium">Agregar →</RouterLink>
            </p>
          </div>
          <div>
            <label class="label">Tipo de Mantenimiento *</label>
            <select v-model="form.tipo" class="input">
              <option value="preventivo">Preventivo</option>
              <option value="correctivo">Correctivo</option>
              <option value="operativo">Operativo</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Actividades Realizadas</label>
          <textarea v-model="form.actividades" class="input" rows="4" placeholder="Describe las actividades realizadas durante la visita…" />
        </div>
        <div>
          <label class="label">Observaciones / Incidencias</label>
          <textarea v-model="form.observaciones" class="input" rows="2" placeholder="Problemas encontrados, pendientes, etc." />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <div>
            <label class="label">Estado *</label>
            <select v-model="form.estado" class="input">
              <option value="completado">Completado</option>
              <option value="en_proceso">En Proceso</option>
              <option value="pendiente">Pendiente</option>
            </select>
          </div>
          <div>
            <label class="label">Horas Trabajadas</label>
            <input type="number" v-model="form.horas" class="input" min="0" step="0.5" />
          </div>
        </div>
        <div>
          <label class="label">Personal Adicional en Sitio</label>
          <input v-model="form.personal" class="input" placeholder="Otros nombres separados por coma" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showConfirm" message="¿Eliminar este registro de mantenimiento?" @confirm="doDelete" />

    <!-- ── Photo Manager Modal ─────────────────────── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showFotos" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)"
             @click.self="showFotos = false">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col" style="max-height:90vh">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
              <div>
                <div class="font-semibold text-slate-900 text-sm">Registro Fotográfico</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ fotosTarget?.escenario?.nombre }} · {{ fmtDate(fotosTarget?.fecha || '') }}</div>
              </div>
              <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-slate-700" @click="showFotos = false">✕</button>
            </div>

            <!-- Body -->
            <div class="flex-1 overflow-y-auto p-6 space-y-6">
              <!-- ── ANTES ──────────────────────────────── -->
              <section>
                <div class="flex items-center justify-between mb-2">
                  <h4 class="text-sm font-semibold text-amber-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span> Antes del trabajo
                  </h4>
                  <span class="text-xs text-slate-400">{{ fotosAntes.length }} foto(s)</span>
                </div>
                <label class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-amber-200 rounded-xl p-4 cursor-pointer hover:border-amber-400 hover:bg-amber-50/40 transition-all mb-3"
                       :class="{ 'opacity-50 pointer-events-none': uploadingFoto }">
                  <CameraIcon class="w-6 h-6 text-amber-300" />
                  <span class="text-xs text-slate-500 font-medium">
                    {{ uploadingFoto && uploadingTipo === 'antes'
                       ? `Subiendo ${uploadDone}/${uploadTotal}…`
                       : 'Agregar fotos ANTES (puedes seleccionar varias)' }}
                  </span>
                  <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" multiple class="hidden"
                         :disabled="uploadingFoto" @change="handleFotoUpload($event, 'antes')" />
                </label>
                <div v-if="fotosAntes.length" class="grid grid-cols-3 gap-3">
                  <div v-for="(foto, i) in fotosAntes" :key="'a'+i" class="relative group rounded-xl overflow-hidden bg-slate-100 aspect-[4/3]">
                    <img :src="foto.url" class="w-full h-full object-cover" />
                    <div class="absolute top-1 left-1 bg-amber-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">ANTES</div>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center">
                      <button v-if="auth.isAdmin" class="opacity-0 group-hover:opacity-100 transition-all btn btn-danger btn-sm"
                              @click="deleteFoto(foto.path)">Eliminar</button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-slate-400 text-xs">Sin fotos del estado inicial</div>
              </section>

              <!-- ── DESPUÉS ────────────────────────────── -->
              <section>
                <div class="flex items-center justify-between mb-2">
                  <h4 class="text-sm font-semibold text-emerald-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Después del trabajo
                  </h4>
                  <span class="text-xs text-slate-400">{{ fotosDespues.length }} foto(s)</span>
                </div>
                <label class="flex flex-col items-center justify-center gap-1.5 border-2 border-dashed border-emerald-200 rounded-xl p-4 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/40 transition-all mb-3"
                       :class="{ 'opacity-50 pointer-events-none': uploadingFoto }">
                  <CameraIcon class="w-6 h-6 text-emerald-300" />
                  <span class="text-xs text-slate-500 font-medium">
                    {{ uploadingFoto && uploadingTipo === 'despues'
                       ? `Subiendo ${uploadDone}/${uploadTotal}…`
                       : 'Agregar fotos DESPUÉS (puedes seleccionar varias)' }}
                  </span>
                  <input type="file" accept="image/jpeg,image/jpg,image/png,image/webp" multiple class="hidden"
                         :disabled="uploadingFoto" @change="handleFotoUpload($event, 'despues')" />
                </label>
                <div v-if="fotosDespues.length" class="grid grid-cols-3 gap-3">
                  <div v-for="(foto, i) in fotosDespues" :key="'d'+i" class="relative group rounded-xl overflow-hidden bg-slate-100 aspect-[4/3]">
                    <img :src="foto.url" class="w-full h-full object-cover" />
                    <div class="absolute top-1 left-1 bg-emerald-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded">DESPUÉS</div>
                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center">
                      <button v-if="auth.isAdmin" class="opacity-0 group-hover:opacity-100 transition-all btn btn-danger btn-sm"
                              @click="deleteFoto(foto.path)">Eliminar</button>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-3 text-slate-400 text-xs">Sin fotos del trabajo terminado</div>
              </section>
            </div>

            <!-- Footer -->
            <div class="px-6 py-4 border-t border-slate-100 text-xs text-slate-400 text-right">
              {{ currentFotos.length }} foto{{ currentFotos.length !== 1 ? 's' : '' }} en total · estas fotos aparecerán en el informe mensual
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { CameraIcon } from '@heroicons/vue/24/outline'
import { mantenimientoApi, escenarioApi, tecnicoApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import {
  MANTENIMIENTO_TIPOS, MANTENIMIENTO_TIPO_BADGE,
  MANTENIMIENTO_ESTADOS, MANTENIMIENTO_ESTADO_BADGE,
  TECNICO_ESPECIALIDADES, avatarColor, fmtDate, today,
} from '@/constants'
import type { Mantenimiento, Escenario, Tecnico, MantenimientoFoto } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const auth  = useAuthStore()
const { handleError } = useApiError()

const items      = ref<Mantenimiento[]>([])
const escenarios = ref<Escenario[]>([])
const tecnicos   = ref<Tecnico[]>([])
const loading    = ref(true)
const saving     = ref(false)
const showModal  = ref(false)
const showConfirm = ref(false)
const editing    = ref<Mantenimiento | null>(null)
const toDelete   = ref<Mantenimiento | null>(null)
const filters    = reactive({ search: '', escenario_id: '', tecnico_id: '', tipo: '', estado: '' })

// ── Photo manager ─────────────────────────────────────────
const showFotos     = ref(false)
const fotosTarget   = ref<Mantenimiento | null>(null)
const uploadingFoto = ref(false)
const uploadingTipo = ref<'antes' | 'despues' | null>(null)
const uploadDone    = ref(0)
const uploadTotal   = ref(0)
const currentFotos  = ref<MantenimientoFoto[]>([])

const fotosAntes   = computed(() => currentFotos.value.filter(f => f.tipo === 'antes'))
const fotosDespues = computed(() => currentFotos.value.filter(f => f.tipo === 'despues'))

function openFotos(m: Mantenimiento) {
  fotosTarget.value = m
  // Soporte para datos legacy (string[]) y nuevo formato (MantenimientoFoto[])
  currentFotos.value = (m.fotos || []).map((f: any): MantenimientoFoto => {
    if (typeof f === 'string') {
      return { url: f, path: f.replace(/.*\/storage\//, ''), tipo: 'despues' }
    }
    return {
      url: f.url,
      path: f.path,
      tipo: f.tipo === 'antes' ? 'antes' : 'despues',
    }
  })
  showFotos.value = true
}

async function handleFotoUpload(e: Event, tipo: 'antes' | 'despues') {
  const input = e.target as HTMLInputElement
  const files = Array.from(input.files || [])
  if (!files.length || !fotosTarget.value) return
  uploadingFoto.value = true
  uploadingTipo.value = tipo
  uploadDone.value    = 0
  uploadTotal.value   = files.length
  let okCount = 0, errCount = 0
  let lastFotos: MantenimientoFoto[] | null = null
  for (const file of files) {
    try {
      const { data } = await mantenimientoApi.uploadFoto(fotosTarget.value.id, file, tipo)
      lastFotos = data.fotos
      currentFotos.value = data.fotos
      okCount++
    } catch (err) {
      errCount++
      handleError(err, `Error al subir "${file.name}"`)
    } finally {
      uploadDone.value++
    }
  }
  if (lastFotos) {
    const idx = items.value.findIndex(x => x.id === fotosTarget.value!.id)
    if (idx !== -1) items.value[idx].fotos = lastFotos
  }
  if (okCount) {
    toast.add(`${okCount} foto${okCount !== 1 ? 's' : ''} "${tipo === 'antes' ? 'antes' : 'después'}" agregada${okCount !== 1 ? 's' : ''}${errCount ? ` (${errCount} fallaron)` : ''}`)
  }
  uploadingFoto.value = false
  uploadingTipo.value = null
  uploadDone.value    = 0
  uploadTotal.value   = 0
  input.value = ''
}

async function deleteFoto(path: string) {
  if (!fotosTarget.value) return
  try {
    const { data } = await mantenimientoApi.removeFoto(fotosTarget.value.id, path)
    currentFotos.value = data.fotos
    const idx = items.value.findIndex(x => x.id === fotosTarget.value!.id)
    if (idx !== -1) items.value[idx].fotos = data.fotos
    toast.add('Foto eliminada')
  } catch (err) {
    handleError(err, 'Error al eliminar la foto')
  }
}

const emptyForm = (): Partial<Mantenimiento> => ({
  escenario_id: undefined, tecnico_id: null, fecha: today(), hora: '08:00',
  tecnico: '', tipo: 'preventivo', actividades: '', observaciones: '',
  estado: 'completado', horas: 0, personal: '',
})
const form = ref(emptyForm())

const tecnicosActivos = computed(() => tecnicos.value.filter(t => t.activo))

function onTecnicoChange() {
  const t = tecnicos.value.find(x => x.id === form.value.tecnico_id)
  form.value.tecnico = t ? t.nombre_completo : ''
}

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (filters.search)       params.search = filters.search
    if (filters.escenario_id) params.escenario_id = filters.escenario_id
    if (filters.tecnico_id)   params.tecnico_id = filters.tecnico_id  // server-side now
    if (filters.tipo)         params.tipo = filters.tipo
    if (filters.estado)       params.estado = filters.estado
    const { data } = await mantenimientoApi.list(params)
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar registros')
  } finally {
    loading.value = false
  }
}

function openForm(m?: Mantenimiento) {
  editing.value = m || null
  form.value = m ? { ...m } : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.escenario_id) { toast.add('Selecciona un escenario', 'error'); return }
  if (!form.value.fecha)        { toast.add('La fecha es requerida', 'error'); return }
  saving.value = true
  try {
    if (editing.value) await mantenimientoApi.update(editing.value.id, form.value as Mantenimiento)
    else               await mantenimientoApi.create(form.value as Mantenimiento)
    toast.add('Mantenimiento guardado correctamente')
    showModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar el mantenimiento')
  } finally {
    saving.value = false
  }
}

function confirmDelete(m: Mantenimiento) { toDelete.value = m; showConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await mantenimientoApi.remove(toDelete.value.id)
    toast.add('Registro eliminado')
    showConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar el registro')
  }
}

onMounted(async () => {
  const [esc, tec] = await Promise.all([escenarioApi.list(), tecnicoApi.list()])
  escenarios.value = esc.data.data
  tecnicos.value   = tec.data.data
  await load()
})
</script>
