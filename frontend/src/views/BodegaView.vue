<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">Bodega — Control de Piezas</h2>
        <p class="text-xs text-slate-500 mt-0.5">Piezas retiradas en campo pendientes de entrega a bodega</p>
      </div>
      <div class="flex gap-2">
        <button :class="['btn', filterBodega === 'pendiente' ? 'btn-primary' : 'btn-outline']"
                @click="filterBodega = 'pendiente'; load()">
          Pendientes <span v-if="pendienteCount" class="ml-1 bg-red-500 text-white text-xs rounded-full px-1.5">{{ pendienteCount }}</span>
        </button>
        <button :class="['btn', filterBodega === 'recibido' ? 'btn-primary' : 'btn-outline']"
                @click="filterBodega = 'recibido'; load()">
          Recibidos
        </button>
        <button :class="['btn', filterBodega === '' ? 'btn-primary' : 'btn-outline']"
                @click="filterBodega = ''; load()">
          Todos
        </button>
      </div>
    </div>

    <!-- Summary card for pending -->
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
        />
        <div v-else class="space-y-3">
          <div v-for="p in items" :key="p.id"
               class="border rounded-xl p-4 transition-all"
               :class="p.estado_bodega === 'pendiente' ? 'border-amber-200 bg-amber-50/30' : 'border-slate-200 bg-white'">
            <div class="flex items-start justify-between gap-4">
              <div class="flex-1 min-w-0">
                <!-- Header row -->
                <div class="flex items-center gap-2 flex-wrap mb-2">
                  <span :class="TIPO_PIEZA_BADGE[p.tipo_pieza]">{{ TIPO_PIEZA_LABELS[p.tipo_pieza] }}</span>
                  <span v-if="p.estado_bodega === 'pendiente'" class="badge badge-yellow">Pendiente recepción</span>
                  <span v-else class="badge badge-green">Recibido en bodega</span>
                </div>
                <!-- Description -->
                <p class="font-semibold text-slate-900 text-sm mb-1">{{ p.descripcion_pieza }}</p>
                <!-- Meta -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-6 gap-y-1 text-xs text-slate-500 mt-2">
                  <div><span class="font-medium text-slate-600">Escenario:</span> {{ p.escenario?.nombre || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Equipo:</span> {{ p.equipo?.nombre || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Técnico:</span> {{ p.tecnico?.nombre_completo || '—' }}</div>
                  <div><span class="font-medium text-slate-600">Fecha:</span> {{ fmtDate(p.fecha) }}</div>
                </div>
                <!-- Serials -->
                <div class="flex gap-6 mt-2">
                  <div v-if="p.serie_instalada" class="text-xs">
                    <span class="font-semibold text-emerald-700">Instalada (nueva):</span>
                    <code class="ml-1 bg-emerald-50 text-emerald-800 px-1.5 py-0.5 rounded font-mono">{{ p.serie_instalada }}</code>
                  </div>
                  <div v-if="p.serie_retirada" class="text-xs">
                    <span class="font-semibold text-red-700">Retirada (vieja):</span>
                    <code class="ml-1 bg-red-50 text-red-800 px-1.5 py-0.5 rounded font-mono">{{ p.serie_retirada }}</code>
                  </div>
                </div>
                <!-- Reception info -->
                <div v-if="p.estado_bodega === 'recibido'" class="mt-2 text-xs text-slate-500">
                  Recibido por <strong>{{ p.recibido_por }}</strong> el {{ fmtDate(p.fecha_recepcion || '') }}
                </div>
                <div v-if="p.notas" class="mt-1 text-xs text-slate-400 italic">{{ p.notas }}</div>
              </div>

              <!-- Action -->
              <div class="flex-shrink-0">
                <button v-if="p.estado_bodega === 'pendiente'"
                        class="btn btn-success btn-sm"
                        @click="openConfirm(p)">
                  <CheckIcon class="w-4 h-4" /> Confirmar recepción
                </button>
                <div v-else class="text-xs text-emerald-600 font-medium flex items-center gap-1">
                  <CheckCircleIcon class="w-4 h-4" /> Recibido
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>

    <!-- Confirm reception modal -->
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
          <textarea v-model="confirmForm.notas" class="input" rows="2" placeholder="Estado de la pieza recibida, observaciones…" />
        </div>
      </div>
      <template #footer>
        <button class="btn btn-outline" @click="showConfirmModal = false">Cancelar</button>
        <button class="btn btn-success" :disabled="saving" @click="doConfirmar">
          {{ saving ? 'Guardando…' : '✓ Confirmar recepción' }}
        </button>
      </template>
    </AppModal>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { ExclamationTriangleIcon, CheckIcon, CheckCircleIcon } from '@heroicons/vue/24/outline'
import { cambioPiezaApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { TIPO_PIEZA_LABELS, TIPO_PIEZA_BADGE, fmtDate, today } from '@/constants'
import type { CambioPieza } from '@/types'
import AppModal from '@/components/AppModal.vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

const toast = useToastStore()
const { handleError } = useApiError()

const items          = ref<CambioPieza[]>([])
const loading        = ref(true)
const saving         = ref(false)
const filterBodega   = ref<string>('pendiente')
const showConfirmModal = ref(false)
const confirmTarget  = ref<CambioPieza | null>(null)
const confirmForm    = ref({ recibido_por: '', fecha_recepcion: today(), notas: '' })

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

onMounted(load)
</script>
