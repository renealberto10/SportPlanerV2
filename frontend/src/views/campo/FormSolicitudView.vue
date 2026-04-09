<template>
  <div class="form-mobile max-w-lg mx-auto pb-32">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <button class="btn btn-ghost btn-icon" @click="$router.back()">
        <ChevronLeftIcon class="w-5 h-5" />
      </button>
      <div>
        <h2 class="font-bold text-slate-900 text-lg leading-tight">Solicitud de Servicio</h2>
        <p class="text-xs text-slate-400">SportPlanner · Solicitud de servicio</p>
      </div>
    </div>

    <!-- Sección 1: Solicitante -->
    <div class="form-section">
      <div class="form-section-title">¿Quién solicita?</div>
      <div class="space-y-3">
        <div>
          <label class="label">Nombre del solicitante *</label>
          <input v-model="form.solicita" class="input"
            placeholder="Ej: Luis Villeda, Marcela Rivas, Ing. Carlos Rivera" />
        </div>
        <div>
          <label class="label">Fecha de la solicitud *</label>
          <input type="date" v-model="form.fecha_solicitud" class="input" />
        </div>
      </div>
    </div>

    <!-- Sección 2: Tipo de solicitud -->
    <div class="form-section">
      <div class="form-section-title">¿Qué se necesita?</div>
      <div class="grid grid-cols-2 gap-2 mb-4">
        <button v-for="t in tiposSolicitud" :key="t.value"
          :class="['tipo-btn', tipoSolicitud === t.value ? 'active' : 'inactive']"
          @click="tipoSolicitud = t.value">
          <span class="text-2xl">{{ t.emoji }}</span>
          <span class="text-xs font-bold">{{ t.label }}</span>
          <span class="text-[10px] text-center opacity-70 leading-tight">{{ t.desc }}</span>
        </button>
      </div>
    </div>

    <!-- Sección 3: Descripción de la actividad -->
    <div class="form-section">
      <div class="form-section-title">Descripción de la actividad requerida</div>
      <textarea v-model="form.actividad" class="input w-full" rows="5"
        :placeholder="tipoSolicitud === 'mantenimiento'
          ? 'Describe el problema o el mantenimiento requerido:\nEj: Revisión del sistema de audio — se reporta caída del audio durante eventos...'
          : 'Describe el tipo de soporte técnico necesario:\nEj: Operación de pantallas COLOSSEO para partido de fútbol, se necesitan 2 técnicos...'" />
    </div>

    <!-- Sección 4: Escenario(s) -->
    <div class="form-section">
      <div class="form-section-title">Escenario(s) deportivo(s)</div>
      <div class="space-y-3">
        <select v-model="form.escenario_id" class="input">
          <option :value="null">— Seleccionar escenario principal —</option>
          <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
        </select>
        <div>
          <label class="label">¿Otros escenarios involucrados?</label>
          <input v-model="form.escenario_texto" class="input"
            placeholder="Ej: Las Delicias, Gimnasio Nacional, El Polvorín" />
        </div>
      </div>
    </div>

    <!-- Sección 5: Prioridad y fecha -->
    <div class="form-section">
      <div class="form-section-title">Prioridad y calendarización</div>
      <div class="space-y-3">
        <div>
          <div class="form-section-title">Prioridad *</div>
          <div class="grid grid-cols-3 gap-2">
            <button v-for="p in prioridades" :key="p.value"
              :class="['tipo-btn', form.prioridad === p.value ? 'active' : 'inactive']"
              @click="form.prioridad = p.value">
              <span class="text-xl">{{ p.emoji }}</span>
              <span class="text-xs font-bold">{{ p.label }}</span>
            </button>
          </div>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Fecha sugerida</label>
            <input type="date" v-model="form.fecha_calendarizada" class="input" />
          </div>
          <div>
            <label class="label">Hora preferida</label>
            <input type="time" v-model="form.hora" class="input" />
          </div>
        </div>
      </div>
    </div>

    <!-- Sección 6: Evidencia fotográfica del problema -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="form-section-title mb-0">Evidencia fotográfica</div>
          <div class="text-xs text-slate-400 mt-0.5">Opcional — foto del problema o área afectada</div>
        </div>
        <span class="text-xs text-slate-400">{{ fotos.length }} foto{{ fotos.length !== 1 ? 's' : '' }}</span>
      </div>

      <div class="grid grid-cols-3 gap-2 mb-3">
        <div v-for="(f, i) in fotos" :key="i" class="foto-thumb">
          <img :src="f.preview" />
          <button class="foto-del" @click="removePhoto(i)">✕</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <label class="foto-add-btn">
          <CameraIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Tomar foto</span>
          <input type="file" accept="image/*" capture="environment" class="hidden" @change="addFoto" />
        </label>
        <label class="foto-add-btn">
          <PhotoIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Galería</span>
          <input type="file" accept="image/*" multiple class="hidden" @change="addFotos" />
        </label>
      </div>
    </div>

    <!-- Sección 7: Notas adicionales -->
    <div class="form-section">
      <div class="form-section-title">Información adicional</div>
      <textarea v-model="form.notas" class="input w-full" rows="3"
        placeholder="Contexto adicional, contactos, restricciones de acceso, horarios…" />
    </div>

    <!-- Save bar -->
    <div class="save-bar">
      <button class="btn w-full py-3.5 text-base font-bold rounded-xl text-white"
              style="background:#f59e0b"
              :disabled="saving" @click="save">
        <CheckIcon v-if="!saving" class="w-5 h-5" />
        <span>{{ saving ? 'Enviando…' : 'Enviar Solicitud' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronLeftIcon, CameraIcon, PhotoIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { solicitudApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { today } from '@/constants'
import type { Escenario } from '@/types'

const router = useRouter()
const toast  = useToastStore()
const { handleError } = useApiError()

const saving     = ref(false)
const escenarios = ref<Escenario[]>([])
const tipoSolicitud = ref<'mantenimiento' | 'evento'>('mantenimiento')

const tiposSolicitud = [
  { value: 'mantenimiento', label: 'Mantenimiento', emoji: '🔧', desc: 'Preventivo, correctivo u operativo' },
  { value: 'evento',        label: 'Evento',        emoji: '⚽', desc: 'Soporte técnico para evento' },
]

const prioridades = [
  { value: 'alto',  label: 'Alta',   emoji: '🔴' },
  { value: 'medio', label: 'Media',  emoji: '🟡' },
  { value: 'bajo',  label: 'Baja',   emoji: '🟢' },
]

const form = ref({
  solicita:            '',
  fecha_solicitud:     today(),
  actividad:           '',
  escenario_id:        null as number | null,
  escenario_texto:     '',
  prioridad:           'medio' as 'alto' | 'medio' | 'bajo',
  fecha_calendarizada: null as string | null,
  hora:                null as string | null,
  estado:              'pendiente' as const,
  seguimiento:         '',
  notas:               '',
})

// Photos (stored locally, not uploaded — solicitudes don't have storage endpoint yet)
interface LocalPhoto { file: File; preview: string }
const fotos = ref<LocalPhoto[]>([])
function addFoto(e: Event) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) fotos.value.push({ file, preview: URL.createObjectURL(file) })
  ;(e.target as HTMLInputElement).value = ''
}
function addFotos(e: Event) {
  Array.from((e.target as HTMLInputElement).files ?? []).forEach(file =>
    fotos.value.push({ file, preview: URL.createObjectURL(file) })
  )
  ;(e.target as HTMLInputElement).value = ''
}
function removePhoto(i: number) {
  URL.revokeObjectURL(fotos.value[i].preview)
  fotos.value.splice(i, 1)
}

async function save() {
  if (!form.value.solicita.trim())  { toast.add('Ingresa el nombre del solicitante', 'error'); return }
  if (!form.value.actividad.trim()) { toast.add('Describe la actividad requerida', 'error'); return }
  saving.value = true
  try {
    const payload = {
      ...form.value,
      // prepend tipo to actividad so it's visible in the list
      actividad: `[${tipoSolicitud.value.toUpperCase()}] ${form.value.actividad}`,
    }
    await solicitudApi.create(payload as never)
    toast.add('Solicitud enviada correctamente')
    router.push('/campo')
  } catch (e) {
    handleError(e, 'Error al enviar la solicitud')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const { data } = await escenarioApi.list()
  escenarios.value = data.data
})
</script>
