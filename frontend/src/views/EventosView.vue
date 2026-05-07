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

    <!-- Controls -->
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
      <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
             fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input v-model="search" class="input pl-9" placeholder="Buscar nombre, personal…" />
      </div>
      <select v-model="filterEscenario" class="input sm:w-52">
        <option value="">Todos los escenarios</option>
        <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
      </select>
      <select v-model="filterEstado" class="input sm:w-44">
        <option value="">Todos los estados</option>
        <option value="programado">Programado</option>
        <option value="en_curso">En Curso</option>
        <option value="realizado">Realizado</option>
        <option value="cancelado">Cancelado</option>
      </select>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!filteredItems.length" icon="◎" title="No hay eventos registrados" subtitle="Registra el primer evento técnico" />
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
                <th class="th">Fotos</th>
                <th class="th text-right">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="e in filteredItems" :key="e.id">
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
                <td class="td">
                  <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-emerald-600 relative"
                          title="Fotos" @click="openFotos(e)">
                    <CameraIcon class="w-4 h-4" />
                    <span v-if="e.fotos?.length"
                          class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-emerald-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center leading-none">
                      {{ e.fotos.length }}
                    </span>
                  </button>
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

    <!-- Photo Manager Modal -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showFotos" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background:rgba(0,0,0,0.6);backdrop-filter:blur(4px)"
             @click.self="showFotos = false">
          <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col" style="max-height:90vh">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
              <div>
                <div class="font-semibold text-slate-900 text-sm">Registro Fotográfico</div>
                <div class="text-xs text-slate-400 mt-0.5">{{ fotosEvento?.nombre }} · {{ fmtDate(fotosEvento?.fecha || '') }}</div>
              </div>
              <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-slate-700" @click="showFotos = false">✕</button>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
              <label class="flex flex-col items-center justify-center gap-2 border-2 border-dashed border-slate-200 rounded-xl p-6 cursor-pointer hover:border-emerald-400 hover:bg-emerald-50/40 transition-all mb-5"
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
                  <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-all flex items-center justify-center gap-2">
                    <a :href="foto.url" target="_blank"
                       class="opacity-0 group-hover:opacity-100 transition-all btn btn-sm bg-white/90 text-slate-700">Ver</a>
                    <button v-if="auth.isAdmin"
                            class="opacity-0 group-hover:opacity-100 transition-all btn btn-danger btn-sm"
                            @click="deleteFoto(foto.path)">Eliminar</button>
                  </div>
                </div>
              </div>
              <div v-else class="text-center py-8 text-slate-400 text-sm">Sin fotos adjuntas — agrega fotos del evento</div>
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
import { useAuthStore } from '@/stores/auth'
import { eventoApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { EVENTO_TIPOS, EVENTO_ESTADOS, EVENTO_ESTADO_BADGE, fmtDate, today } from '@/constants'
import type { Evento, Escenario, EventoFoto } from '@/types'
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
const showFotos     = ref(false)
const fotosEvento   = ref<Evento | null>(null)
const uploadingFoto = ref(false)

// ── Client-side filters (no server-filter by estado — prevents disappearing items) ──
const search         = ref('')
const filterEscenario = ref<number | ''>('')
const filterEstado   = ref('')

const filteredItems = computed(() => {
  let list = items.value
  if (search.value.trim()) {
    const q = search.value.toLowerCase()
    list = list.filter(e =>
      e.nombre?.toLowerCase().includes(q) ||
      e.personal?.toLowerCase().includes(q) ||
      e.escenario?.nombre?.toLowerCase().includes(q)
    )
  }
  if (filterEscenario.value) list = list.filter(e => e.escenario_id === Number(filterEscenario.value))
  if (filterEstado.value)    list = list.filter(e => e.estado === filterEstado.value)
  return list
})

const currentFotos = computed<EventoFoto[]>(() => {
  const raw = (fotosEvento.value?.fotos ?? []) as any[]
  return raw.map(f =>
    typeof f === 'string'
      ? { path: f, url: `/storage/${f}` }
      : { path: f.path, url: f.url },
  )
})

function openFotos(e: Evento) { fotosEvento.value = e; showFotos.value = true }

async function handleFotoUpload(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (!file || !fotosEvento.value) return
  uploadingFoto.value = true
  try {
    const res = await eventoApi.uploadFoto(fotosEvento.value.id, file)
    fotosEvento.value.fotos = res.data.fotos as EventoFoto[]
    const idx = items.value.findIndex(ev => ev.id === fotosEvento.value!.id)
    if (idx !== -1) items.value[idx].fotos = fotosEvento.value.fotos
  } catch (err) { handleError(err, 'Error al subir la foto') }
  finally { uploadingFoto.value = false; (e.target as HTMLInputElement).value = '' }
}

async function deleteFoto(path: string) {
  if (!fotosEvento.value) return
  try {
    await eventoApi.removeFoto(fotosEvento.value.id, path)
    fotosEvento.value.fotos = (fotosEvento.value.fotos as any[]).filter((f: any) =>
      (typeof f === 'string' ? f : f.path) !== path,
    )
    const idx = items.value.findIndex(ev => ev.id === fotosEvento.value!.id)
    if (idx !== -1) items.value[idx].fotos = fotosEvento.value.fotos
  } catch (err) { handleError(err, 'Error al eliminar la foto') }
}

const emptyForm = (): Partial<Evento> => ({
  nombre: '', fecha: today(), hora: '10:00', escenario_id: undefined,
  tipo: 'deportivo', estado: 'programado', descripcion: '', personal: '', equipos_notas: '',
})
const form = ref(emptyForm())

// Load ALL events — filtering is done client-side so edits never disappear
async function load() {
  loading.value = true
  try {
    const { data } = await eventoApi.list()
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar eventos')
  } finally {
    loading.value = false
  }
}

function openForm(e?: Evento) {
  editing.value = e || null
  form.value = e
    ? {
        nombre: e.nombre,
        fecha: e.fecha,
        hora: e.hora,
        escenario_id: e.escenario_id,
        tipo: e.tipo,
        estado: e.estado,
        descripcion: e.descripcion,
        personal: e.personal,
        equipos_notas: e.equipos_notas,
      }
    : emptyForm()
  showModal.value = true
}

async function save() {
  if (!form.value.nombre?.trim()) { toast.add('El nombre es requerido', 'error'); return }
  if (!form.value.escenario_id)   { toast.add('Selecciona un escenario', 'error'); return }
  saving.value = true
  try {
    const payload = {
      nombre:        form.value.nombre,
      fecha:         form.value.fecha,
      hora:          form.value.hora || null,
      escenario_id:  form.value.escenario_id,
      tipo:          form.value.tipo,
      estado:        form.value.estado,
      descripcion:   form.value.descripcion || null,
      personal:      form.value.personal || null,
      equipos_notas: form.value.equipos_notas || null,
    }
    if (editing.value) {
      await eventoApi.update(editing.value.id, payload as Evento)
    } else {
      await eventoApi.create(payload as Evento)
    }
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
