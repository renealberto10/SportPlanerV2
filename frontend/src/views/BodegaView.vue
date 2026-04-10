<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Bodega — Control de Piezas</h2>
        <p class="text-xs text-slate-500 mt-0.5">Piezas retiradas en campo pendientes de entrega a bodega</p>
      </div>
      <div class="flex flex-wrap gap-2">
        <div class="flex gap-1">
          <button :class="['btn btn-sm', filterBodega === 'pendiente' ? 'btn-primary' : 'btn-outline']"
                  @click="filterBodega = 'pendiente'; load()">
            Pendientes
            <span v-if="pendienteCount" class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5 leading-none py-0.5">{{ pendienteCount }}</span>
          </button>
          <button :class="['btn btn-sm', filterBodega === 'recibido' ? 'btn-primary' : 'btn-outline']"
                  @click="filterBodega = 'recibido'; load()">Recibidos</button>
          <button :class="['btn btn-sm', filterBodega === '' ? 'btn-primary' : 'btn-outline']"
                  @click="filterBodega = ''; load()">Todos</button>
        </div>
        <button class="btn btn-primary btn-sm" @click="openForm()">
          <span class="text-base leading-none">+</span> Agregar Pieza
        </button>
      </div>
    </div>

    <!-- Pending alert -->
    <div v-if="filterBodega === 'pendiente' && items.length" class="card mb-4 border-amber-200 bg-amber-50/50">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
          <ExclamationTriangleIcon class="w-5 h-5 text-amber-600" />
        </div>
        <div>
          <div class="font-semibold text-amber-800 text-sm">{{ items.length }} pieza{{ items.length !== 1 ? 's' : '' }} pendiente{{ items.length !== 1 ? 's' : '' }} de recibir</div>
          <div class="text-xs text-amber-600 mt-0.5">Confirma la recepción de cada pieza retirada por los técnicos</div>
        </div>
      </div>
    </div>

    <div class="card">
      <LoadingSpinner v-if="loading" />
      <template v-else>
        <EmptyState v-if="!items.length"
          :icon="filterBodega === 'pendiente' ? '✓' : '▣'"
          :title="filterBodega === 'pendiente' ? 'Sin piezas pendientes — bodega al día' : 'Sin registros'"
          :subtitle="filterBodega === '' ? 'Agrega piezas retiradas en campo usando el botón + Agregar Pieza' : undefined"
        />
        <div v-else class="space-y-3">
          <div v-for="p in items" :key="p.id"
               class="border rounded-xl p-4 transition-all"
               :class="p.estado_bodega === 'pendiente' ? 'border-amber-200 bg-amber-50/30' : 'border-slate-200 bg-white'">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <!-- Badges -->
                <div class="flex items-center gap-2 flex-wrap mb-2">
                  <span :class="TIPO_PIEZA_BADGE[p.tipo_pieza]">{{ TIPO_PIEZA_LABELS[p.tipo_pieza] }}</span>
                  <span v-if="p.estado_bodega === 'pendiente'" class="badge badge-yellow">Pendiente recepción</span>
                  <span v-else class="badge badge-green">Recibido en bodega</span>
                </div>
                <!-- Description -->
                <p class="font-semibold text-slate-900 text-sm mb-1">{{ p.descripcion_pieza }}</p>
                <!-- Meta grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-1 text-xs text-slate-500 mt-2">
                  <div><span class="font-medium text-slate-600">Escenario:</span> {{ p.escenario?.nombre || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Equipo:</span> {{ p.equipo?.nombre || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Técnico:</span> {{ p.tecnico?.nombre_completo || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Fecha:</span> {{ fmtDate(p.fecha) }}</div>
                </div>
                <!-- Serials -->
                <div v-if="p.serie_instalada || p.serie_retirada" class="flex gap-6 mt-2">
                  <div v-if="p.serie_instalada" class="text-xs">
                    <span class="font-semibold text-emerald-700">Instalada:</span>
                    <code class="ml-1 bg-emerald-50 text-emerald-800 px-1.5 py-0.5 rounded font-mono">{{ p.serie_instalada }}</code>
                  </div>
                  <div v-if="p.serie_retirada" class="text-xs">
                    <span class="font-semibold text-red-700">Retirada:</span>
                    <code class="ml-1 bg-red-50 text-red-800 px-1.5 py-0.5 rounded font-mono">{{ p.serie_retirada }}</code>
                  </div>
                </div>
                <!-- Reception info -->
                <div v-if="p.estado_bodega === 'recibido'" class="mt-2 text-xs text-slate-500">
                  Recibido por <strong>{{ p.recibido_por }}</strong> el {{ fmtDate(p.fecha_recepcion || '') }}
                </div>
                <div v-if="p.notas" class="mt-1 text-xs text-slate-400 italic">{{ p.notas }}</div>
              </div>

              <!-- Actions -->
              <div class="flex-shrink-0 flex flex-col sm:flex-row items-end sm:items-center gap-2">
                <button v-if="p.estado_bodega === 'pendiente'"
                        class="btn btn-success btn-sm"
                        @click="openConfirm(p)">
                  <CheckIcon class="w-4 h-4" /> Confirmar
                </button>
                <div v-else class="text-xs text-emerald-600 font-medium flex items-center gap-1 whitespace-nowrap">
                  <CheckCircleIcon class="w-4 h-4" /> Recibido
                </div>
                <div class="flex gap-1">
                  <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-blue-600" title="Editar" @click="openForm(p)">✏</button>
                  <button class="btn btn-ghost btn-sm btn-icon text-slate-400 hover:text-red-600" title="Eliminar" @click="confirmDelete(p)">✕</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- ── Create / Edit modal ─────────────────────────── -->
    <AppModal v-model="showFormModal" :title="editing ? 'Editar Pieza' : 'Agregar Pieza a Bodega'" maxWidth="600px">
      <div v-if="form" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Escenario *</label>
            <select v-model="form.escenario_id" class="input">
              <option :value="null">— Seleccionar —</option>
              <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
            </select>
          </div>
          <div>
            <label class="label">Fecha *</label>
            <input type="date" v-model="form.fecha" class="input" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Tipo de Pieza *</label>
            <select v-model="form.tipo_pieza" class="input">
              <option v-for="(label, key) in TIPO_PIEZA_LABELS" :key="key" :value="key">{{ label }}</option>
            </select>
          </div>
          <div>
            <label class="label">Equipo (opcional)</label>
            <select v-model="form.equipo_id" class="input">
              <option :value="null">— Sin equipo —</option>
              <option v-for="eq in equipos" :key="eq.id" :value="eq.id">{{ eq.nombre }}</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Descripción de la Pieza *</label>
          <input v-model="form.descripcion_pieza" class="input" placeholder="Ej: Módulo LED fila 3, panel A" />
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Serie Retirada (vieja)</label>
            <input v-model="form.serie_retirada" class="input font-mono" placeholder="Número de serie" />
          </div>
          <div>
            <label class="label">Serie Instalada (nueva)</label>
            <input v-model="form.serie_instalada" class="input font-mono" placeholder="Número de serie" />
          </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="label">Técnico</label>
            <select v-model="form.tecnico_id" class="input">
              <option :value="null">— Sin asignar —</option>
              <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
            </select>
          </div>
          <div>
            <label class="label">Estado Bodega</label>
            <select v-model="form.estado_bodega" class="input">
              <option value="pendiente">Pendiente recepción</option>
              <option value="recibido">Recibido</option>
            </select>
          </div>
        </div>
        <div>
          <label class="label">Notas</label>
          <textarea v-model="form.notas" class="input" rows="2" placeholder="Observaciones sobre la pieza…" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showFormModal = false">Cancelar</button>
        <button class="btn btn-primary" :disabled="saving" @click="save">
          {{ saving ? 'Guardando…' : 'Guardar' }}
        </button>
      </template>
    </AppModal>

    <!-- ── Confirm reception modal ────────────────────── -->
    <AppModal v-model="showConfirmModal" title="Confirmar Recepción de Pieza" maxWidth="480px">
      <div v-if="confirmTarget" class="space-y-4">
        <div class="bg-slate-50 rounded-xl p-4 text-sm">
          <div class="font-semibold text-slate-900">{{ confirmTarget.descripcion_pieza }}</div>
          <div v-if="confirmTarget.serie_retirada" class="text-xs text-slate-500 mt-1">
            Serie retirada: <code class="font-mono">{{ confirmTarget.serie_retirada }}</code>
          </div>
        </div>
        <div>
          <label class="label">Recibido por *</label>
          <input v-model="confirmForm.recibido_por" class="input" placeholder="Tu nombre completo" />
        </div>
        <div>
          <label class="label">Fecha de recepción *</label>
          <input type="date" v-model="confirmForm.fecha_recepcion" class="input" />
        </div>
        <div>
          <label class="label">Notas (opcional)</label>
          <textarea v-model="confirmForm.notas" class="input" rows="2" placeholder="Estado de la pieza recibida…" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showConfirmModal = false">Cancelar</button>
        <button class="btn btn-success" :disabled="saving" @click="doConfirmar">
          {{ saving ? 'Guardando…' : '✓ Confirmar recepción' }}
        </button>
      </template>
    </AppModal>

    <ConfirmDialog v-model="showDeleteConfirm" message="¿Eliminar este registro de pieza?" @confirm="doDelete" />
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ExclamationTriangleIcon, CheckIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { cambioPiezaApi, escenarioApi, equipoApi, tecnicoApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { TIPO_PIEZA_LABELS, TIPO_PIEZA_BADGE, fmtDate, today } from '@/constants'
import type { CambioPieza, Escenario, Equipo, Tecnico } from '@/types'
import AppModal from '@/components/AppModal.vue'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const { handleError } = useApiError()

const items      = ref<CambioPieza[]>([])
const escenarios = ref<Escenario[]>([])
const equipos    = ref<Equipo[]>([])
const tecnicos   = ref<Tecnico[]>([])
const loading    = ref(true)
const saving     = ref(false)
const filterBodega = ref<string>('pendiente')

// Form modal
const showFormModal = ref(false)
const editing       = ref<CambioPieza | null>(null)
type PiezaForm = {
  escenario_id: number | null; equipo_id: number | null; tecnico_id: number | null
  descripcion_pieza: string; tipo_pieza: string; serie_instalada: string; serie_retirada: string
  fecha: string; estado_bodega: string; notas: string
}
const emptyForm = (): PiezaForm => ({
  escenario_id: null, equipo_id: null, tecnico_id: null,
  descripcion_pieza: '', tipo_pieza: 'otro',
  serie_instalada: '', serie_retirada: '',
  fecha: today(), estado_bodega: 'pendiente', notas: '',
})
const form = ref<PiezaForm>(emptyForm())

// Confirm reception modal
const showConfirmModal = ref(false)
const confirmTarget    = ref<CambioPieza | null>(null)
const confirmForm      = ref({ recibido_por: '', fecha_recepcion: today(), notas: '' })

// Delete
const showDeleteConfirm = ref(false)
const toDelete          = ref<CambioPieza | null>(null)

const pendienteCount = computed(() => items.value.filter(p => p.estado_bodega === 'pendiente').length)

async function load() {
  loading.value = true
  try {
    const params: Record<string, unknown> = {}
    if (filterBodega.value) params.estado_bodega = filterBodega.value
    const { data } = await cambioPiezaApi.list(params)
    items.value = data.data
  } catch (e) {
    handleError(e, 'Error al cargar registros de bodega')
  } finally {
    loading.value = false
  }
}

function openForm(p?: CambioPieza) {
  editing.value = p || null
  form.value = p ? {
    escenario_id: p.escenario_id, equipo_id: p.equipo_id ?? null,
    tecnico_id: p.tecnico_id ?? null, descripcion_pieza: p.descripcion_pieza,
    tipo_pieza: p.tipo_pieza, serie_instalada: p.serie_instalada ?? '',
    serie_retirada: p.serie_retirada ?? '', fecha: p.fecha,
    estado_bodega: p.estado_bodega, notas: p.notas ?? '',
  } : emptyForm()
  showFormModal.value = true
}

async function save() {
  if (!form.value.escenario_id)              { toast.add('Selecciona un escenario', 'error'); return }
  if (!form.value.descripcion_pieza.trim())  { toast.add('Ingresa la descripción', 'error'); return }
  saving.value = true
  try {
    const payload = { ...form.value, escenario_id: form.value.escenario_id! } as never
    if (editing.value) await cambioPiezaApi.update(editing.value.id, payload)
    else               await cambioPiezaApi.create(payload)
    toast.add(editing.value ? 'Pieza actualizada' : 'Pieza registrada en bodega')
    showFormModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al guardar')
  } finally {
    saving.value = false
  }
}

function openConfirm(p: CambioPieza) {
  confirmTarget.value = p
  confirmForm.value = { recibido_por: '', fecha_recepcion: today(), notas: '' }
  showConfirmModal.value = true
}

async function doConfirmar() {
  if (!confirmTarget.value) return
  if (!confirmForm.value.recibido_por) { toast.add('Ingresa tu nombre', 'error'); return }
  saving.value = true
  try {
    await cambioPiezaApi.confirmarRecepcion(confirmTarget.value.id, confirmForm.value)
    toast.add('Recepción confirmada correctamente')
    showConfirmModal.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al confirmar recepción')
  } finally {
    saving.value = false
  }
}

function confirmDelete(p: CambioPieza) { toDelete.value = p; showDeleteConfirm.value = true }

async function doDelete() {
  if (!toDelete.value) return
  try {
    await cambioPiezaApi.remove(toDelete.value.id)
    toast.add('Pieza eliminada')
    showDeleteConfirm.value = false
    await load()
  } catch (e) {
    handleError(e, 'Error al eliminar')
  }
}

onMounted(async () => {
  await load()
  const [esc, eq, tec] = await Promise.all([
    escenarioApi.list(),
    equipoApi.list(),
    tecnicoApi.list(),
  ])
  escenarios.value = esc.data.data
  equipos.value    = eq.data.data
  tecnicos.value   = tec.data.data.filter((t: Tecnico) => t.activo)
})
</script>
