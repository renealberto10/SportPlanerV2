<template>
  <div class="form-mobile max-w-lg mx-auto pb-32">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <button class="btn btn-ghost btn-icon" @click="$router.back()">
        <ChevronLeftIcon class="w-5 h-5" />
      </button>
      <div>
        <h2 class="font-bold text-slate-900 text-lg leading-tight">Cobertura de Evento</h2>
        <p class="text-xs text-slate-400">Formulario de campo</p>
      </div>
    </div>

    <!-- Sección 1: Datos del evento -->
    <div class="form-section">
      <div class="form-section-title">Datos del evento</div>
      <div class="space-y-3">
        <div>
          <label class="label">Nombre del evento *</label>
          <input v-model="form.nombre" class="input" placeholder="Ej: Copa El Salvador — Semifinales" />
        </div>
        <div>
          <label class="label">Escenario *</label>
          <select v-model="form.escenario_id" class="input">
            <option :value="null">— Seleccionar escenario —</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Fecha *</label>
            <input type="date" v-model="form.fecha" class="input" />
          </div>
          <div>
            <label class="label">Hora</label>
            <input type="time" v-model="form.hora" class="input" />
          </div>
        </div>
      </div>
    </div>

    <!-- Sección 2: Tipo y estado -->
    <div class="form-section">
      <div class="form-section-title">Tipo de evento</div>
      <div class="grid grid-cols-2 gap-2 mb-4">
        <button v-for="t in tiposEvento" :key="t.value"
          :class="['tipo-btn', form.tipo === t.value ? 'active' : 'inactive']"
          @click="form.tipo = t.value">
          <span class="text-xl">{{ t.emoji }}</span>
          <span class="text-xs">{{ t.label }}</span>
        </button>
      </div>
      <div class="form-section-title">Estado</div>
      <div class="grid grid-cols-3 gap-2">
        <button v-for="s in estadosEvento" :key="s.value"
          :class="['tipo-btn', form.estado === s.value ? 'active' : 'inactive']"
          @click="form.estado = s.value">
          <span>{{ s.emoji }}</span>
          <span class="text-xs">{{ s.label }}</span>
        </button>
      </div>
    </div>

    <!-- Sección 3: Personal técnico -->
    <div class="form-section">
      <div class="form-section-title">Personal técnico en sitio</div>
      <div class="space-y-2">
        <div v-for="(tec, i) in personalSeleccionado" :key="i"
             class="flex items-center gap-2 bg-blue-50 rounded-xl px-3 py-2">
          <div class="w-7 h-7 rounded-lg flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
               :style="`background:${avatarColor(tec)}`">{{ tec[0] }}</div>
          <span class="text-sm font-medium text-slate-800 flex-1">{{ tec }}</span>
          <button class="text-slate-400 hover:text-red-500 text-sm font-bold" @click="personalSeleccionado.splice(i, 1)">✕</button>
        </div>
        <select class="input" @change="agregarPersonal">
          <option value="">+ Agregar técnico…</option>
          <option v-for="t in tecnicos" :key="t.id" :value="t.nombre_completo">{{ t.nombre_completo }}</option>
        </select>
        <input v-model="personalExtra" class="input" placeholder="Nombre adicional (si no está en lista)" />
        <button v-if="personalExtra.trim()" class="btn btn-outline btn-sm w-full" @click="addPersonalExtra">
          + Agregar "{{ personalExtra }}"
        </button>
      </div>
    </div>

    <!-- Sección 4: Equipos y descripción -->
    <div class="form-section">
      <div class="form-section-title">Equipos y producción técnica</div>
      <div class="space-y-3">
        <div>
          <label class="label">Equipos utilizados</label>
          <textarea v-model="form.equipos_notas" class="input w-full" rows="3"
            placeholder="Ej: 2x Pantallas COLOSSEO, Sistema de audio completo, Consola de mezclas Yamaha CL5…" />
        </div>
        <div>
          <label class="label">Descripción / Notas operativas</label>
          <textarea v-model="form.descripcion" class="input w-full" rows="3"
            placeholder="Operación del evento, incidencias, condiciones, público estimado…" />
        </div>
      </div>
    </div>

    <!-- Sección 5: Fotos -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div class="form-section-title mb-0">Registro fotográfico</div>
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

    <!-- Sección 6: Piezas / incidencias de equipo -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div>
          <div class="form-section-title mb-0">Piezas / Repuestos usados</div>
          <div class="text-xs text-slate-400 mt-0.5">Opcional — solo si se reemplazó algún componente</div>
        </div>
        <button class="btn btn-outline btn-sm" @click="addPieza">+ Agregar</button>
      </div>

      <div v-if="piezas.length === 0" class="text-center py-3 text-slate-300 text-sm">
        Sin repuestos utilizados
      </div>

      <div v-for="(pz, i) in piezas" :key="i" class="border border-slate-200 rounded-xl p-3 mb-3 relative">
        <button class="absolute top-2 right-2 w-6 h-6 rounded-full bg-slate-100 text-slate-400 text-xs hover:bg-red-100 hover:text-red-500 flex items-center justify-center"
                @click="piezas.splice(i, 1)">✕</button>
        <div class="mb-2">
          <label class="label">Tipo</label>
          <select v-model="pz.tipo_pieza" class="input">
            <option v-for="(lbl, key) in TIPO_PIEZA_LABELS" :key="key" :value="key">{{ lbl }}</option>
          </select>
        </div>
        <div class="mb-2">
          <label class="label">Descripción *</label>
          <input v-model="pz.descripcion_pieza" class="input" placeholder="Descripción de la pieza" />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="label">Serie nueva ✅</label>
            <input v-model="pz.serie_instalada" class="input font-mono text-xs" placeholder="SN-NUEVO" />
          </div>
          <div>
            <label class="label">Serie retirada ❌</label>
            <input v-model="pz.serie_retirada" class="input font-mono text-xs" placeholder="SN-VIEJO" />
          </div>
        </div>
      </div>
    </div>

    <!-- Save bar -->
    <div class="save-bar">
      <button class="btn btn-success w-full py-3.5 text-base font-bold rounded-xl"
              :disabled="saving" @click="save">
        <CheckIcon v-if="!saving" class="w-5 h-5" />
        <span>{{ saving ? 'Guardando…' : 'Guardar Evento' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { ChevronLeftIcon, CameraIcon, PhotoIcon, CheckIcon } from '@heroicons/vue/24/outline'
import { eventoApi, escenarioApi, tecnicoApi, cambioPiezaApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { TIPO_PIEZA_LABELS, avatarColor, today } from '@/constants'
import type { Escenario, Tecnico } from '@/types'

const router = useRouter()
const toast  = useToastStore()
const { handleError } = useApiError()

const saving     = ref(false)
const escenarios = ref<Escenario[]>([])
const tecnicos   = ref<Tecnico[]>([])

const tiposEvento = [
  { value: 'deportivo',  label: 'Deportivo',   emoji: '⚽' },
  { value: 'cultural',   label: 'Cultural',    emoji: '🎭' },
  { value: 'produccion', label: 'Producción',  emoji: '🎬' },
  { value: 'otro',       label: 'Otro',        emoji: '📋' },
]
const estadosEvento = [
  { value: 'programado', label: 'Programado', emoji: '📅' },
  { value: 'en_curso',   label: 'En Curso',   emoji: '🔴' },
  { value: 'realizado',  label: 'Realizado',  emoji: '✅' },
]

const form = ref({
  nombre:       '',
  escenario_id: null as number | null,
  fecha:        today(),
  hora:         new Date().toTimeString().slice(0, 5),
  tipo:         'deportivo',
  estado:       'en_curso',
  descripcion:  '',
  equipos_notas:'',
})

const personalSeleccionado = ref<string[]>([])
const personalExtra = ref('')

function agregarPersonal(e: Event) {
  const val = (e.target as HTMLSelectElement).value
  if (val && !personalSeleccionado.value.includes(val)) personalSeleccionado.value.push(val)
  ;(e.target as HTMLSelectElement).value = ''
}
function addPersonalExtra() {
  const name = personalExtra.value.trim()
  if (name && !personalSeleccionado.value.includes(name)) personalSeleccionado.value.push(name)
  personalExtra.value = ''
}

// Photos
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

// Piezas
interface PiezaLocal { tipo_pieza: string; descripcion_pieza: string; serie_instalada: string; serie_retirada: string }
const piezas = ref<PiezaLocal[]>([])
function addPieza() { piezas.value.push({ tipo_pieza: 'otro', descripcion_pieza: '', serie_instalada: '', serie_retirada: '' }) }

async function save() {
  if (!form.value.nombre.trim()) { toast.add('Ingresa el nombre del evento', 'error'); return }
  if (!form.value.escenario_id)  { toast.add('Selecciona un escenario', 'error'); return }
  saving.value = true
  try {
    const { data: ev } = await eventoApi.create({
      ...form.value,
      escenario_id: form.value.escenario_id!,
      personal:     personalSeleccionado.value.join(', '),
    } as never)

    // Note: eventos don't have foto upload in the current API, so we skip photos
    // (could be added later like mantenimientos)

    // Piezas
    for (const pz of piezas.value) {
      if (!pz.descripcion_pieza.trim()) continue
      await cambioPiezaApi.create({
        escenario_id:     form.value.escenario_id!,
        evento_id:        ev.id,
        mantenimiento_id: null,
        equipo_id:        null,
        descripcion_pieza: pz.descripcion_pieza,
        tipo_pieza:        pz.tipo_pieza as never,
        serie_instalada:   pz.serie_instalada || null,
        serie_retirada:    pz.serie_retirada || null,
        tecnico_id:        null,
        fecha:             form.value.fecha,
        estado_bodega:     'pendiente',
        recibido_por:      null,
        fecha_recepcion:   null,
        notas:             null,
      } as never)
    }

    toast.add('Evento guardado correctamente')
    router.push('/campo')
  } catch (e) {
    handleError(e, 'Error al guardar el evento')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const [esc, tec] = await Promise.all([escenarioApi.list(), tecnicoApi.list()])
  escenarios.value = esc.data.data
  tecnicos.value   = tec.data.data.filter(t => t.activo)
})
</script>
