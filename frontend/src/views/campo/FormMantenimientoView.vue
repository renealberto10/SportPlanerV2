<template>
  <div class="form-mobile max-w-lg mx-auto pb-32">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <button class="btn btn-ghost btn-icon" @click="$router.back()">
        <ChevronLeftIcon class="w-5 h-5" />
      </button>
      <div>
        <h2 class="font-bold text-slate-900 text-lg leading-tight">Mantenimiento</h2>
        <p class="text-xs text-slate-400">Formulario de campo</p>
      </div>
    </div>

    <!-- Sección 1: Datos básicos -->
    <div class="form-section">
      <div class="form-section-title">Datos de la visita</div>

      <div class="space-y-3">
        <div>
          <label class="label">Escenario *</label>
          <select v-model="form.escenario_id" class="input">
            <option :value="null">— Seleccionar escenario —</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
        </div>
        <div>
          <label class="label">Técnico responsable</label>
          <select v-model="form.tecnico_id" class="input" @change="onTecnicoChange">
            <option :value="null">— Sin asignar —</option>
            <option v-for="t in tecnicos" :key="t.id" :value="t.id">{{ t.nombre_completo }}</option>
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

    <!-- Sección 2: Tipo de mantenimiento -->
    <div class="form-section">
      <div class="form-section-title">Tipo de mantenimiento</div>
      <div class="flex gap-2">
        <button v-for="t in tipos" :key="t.value"
          :class="['tipo-btn', form.tipo === t.value ? 'active' : 'inactive']"
          @click="form.tipo = t.value">
          <component :is="t.icon" class="w-5 h-5" />
          <span class="text-xs">{{ t.label }}</span>
        </button>
      </div>
    </div>

    <!-- Sección 3: Actividades -->
    <div class="form-section">
      <div class="form-section-title">Actividades realizadas</div>
      <textarea v-model="form.actividades" class="input w-full" rows="5"
        placeholder="Describe detalladamente lo que se hizo:&#10;• Limpieza de gabinetes y ventiladores&#10;• Calibración de pantallas&#10;• Revisión de cableado…" />
    </div>

    <!-- Sección 4: Fotos ANTES -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div class="form-section-title mb-0">📷 Fotos ANTES del mantenimiento</div>
        <span class="text-xs text-slate-400">{{ fotosAntes.length }} foto{{ fotosAntes.length !== 1 ? 's' : '' }}</span>
      </div>
      <p class="text-xs text-slate-400 mb-3">Estado inicial del equipo o escenario.</p>

      <div v-if="fotosAntes.length" class="grid grid-cols-3 gap-2 mb-3">
        <div v-for="(f, i) in fotosAntes" :key="'a'+i" class="foto-thumb">
          <img :src="f.preview" />
          <button class="foto-del" @click="removePhoto('antes', i)">✕</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <label class="foto-add-btn">
          <CameraIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Tomar foto</span>
          <input type="file" accept="image/*" capture="environment" class="hidden" @change="addFoto($event, 'antes')" />
        </label>
        <label class="foto-add-btn">
          <PhotoIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Galería (varias)</span>
          <input type="file" accept="image/*" multiple class="hidden" @change="addFotos($event, 'antes')" />
        </label>
      </div>
    </div>

    <!-- Sección 4b: Fotos DESPUÉS -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div class="form-section-title mb-0">✅ Fotos DESPUÉS del mantenimiento</div>
        <span class="text-xs text-slate-400">{{ fotosDespues.length }} foto{{ fotosDespues.length !== 1 ? 's' : '' }}</span>
      </div>
      <p class="text-xs text-slate-400 mb-3">Resultado final tras la intervención.</p>

      <div v-if="fotosDespues.length" class="grid grid-cols-3 gap-2 mb-3">
        <div v-for="(f, i) in fotosDespues" :key="'d'+i" class="foto-thumb">
          <img :src="f.preview" />
          <button class="foto-del" @click="removePhoto('despues', i)">✕</button>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-2">
        <label class="foto-add-btn">
          <CameraIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Tomar foto</span>
          <input type="file" accept="image/*" capture="environment" class="hidden" @change="addFoto($event, 'despues')" />
        </label>
        <label class="foto-add-btn">
          <PhotoIcon class="w-6 h-6" />
          <span class="text-xs font-semibold">Galería (varias)</span>
          <input type="file" accept="image/*" multiple class="hidden" @change="addFotos($event, 'despues')" />
        </label>
      </div>
    </div>

    <!-- Sección 5: Piezas cambiadas -->
    <div class="form-section">
      <div class="flex items-center justify-between mb-3">
        <div class="form-section-title mb-0">Piezas cambiadas</div>
        <button class="btn btn-outline btn-sm" @click="addPieza">+ Agregar</button>
      </div>

      <div v-if="piezas.length === 0" class="text-center py-4 text-slate-300 text-sm">
        Sin piezas reemplazadas en esta visita
      </div>

      <div v-for="(pz, i) in piezas" :key="i" class="border border-slate-200 rounded-xl p-3 mb-3 relative">
        <button class="absolute top-2 right-2 w-6 h-6 rounded-full bg-slate-100 text-slate-400 text-xs font-bold hover:bg-red-100 hover:text-red-500 flex items-center justify-center"
                @click="piezas.splice(i, 1)">✕</button>
        <div class="grid grid-cols-2 gap-2 mb-2">
          <div>
            <label class="label">Tipo</label>
            <select v-model="pz.tipo_pieza" class="input">
              <option v-for="(lbl, key) in TIPO_PIEZA_LABELS" :key="key" :value="key">{{ lbl }}</option>
            </select>
          </div>
          <div>
            <label class="label">Equipo</label>
            <select v-model="pz.equipo_id" class="input">
              <option :value="null">— General —</option>
              <option v-for="eq in equiposFiltrados" :key="eq.id" :value="eq.id">{{ eq.nombre }}</option>
            </select>
          </div>
        </div>
        <div class="mb-2">
          <label class="label">Descripción *</label>
          <input v-model="pz.descripcion_pieza" class="input" placeholder="Ej: Módulo LED fila 3 panel A" />
        </div>
        <div class="grid grid-cols-2 gap-2">
          <div>
            <label class="label">Serie nueva ✅</label>
            <input v-model="pz.serie_instalada" class="input font-mono" placeholder="SN-NUEVO" />
          </div>
          <div>
            <label class="label">Serie retirada ❌</label>
            <input v-model="pz.serie_retirada" class="input font-mono" placeholder="SN-VIEJO" />
          </div>
        </div>
      </div>
    </div>

    <!-- Sección 6: Cierre -->
    <div class="form-section">
      <div class="form-section-title">Cierre de visita</div>
      <div class="space-y-3">
        <div>
          <label class="label">Observaciones / Incidencias</label>
          <textarea v-model="form.observaciones" class="input w-full" rows="3"
            placeholder="Problemas encontrados, pendientes, recomendaciones…" />
        </div>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="label">Estado *</label>
            <select v-model="form.estado" class="input">
              <option value="completado">✅ Completado</option>
              <option value="en_proceso">🔄 En Proceso</option>
              <option value="pendiente">⏳ Pendiente</option>
            </select>
          </div>
          <div>
            <label class="label">Horas trabajadas</label>
            <input type="number" v-model="form.horas" class="input" min="0" step="0.5" placeholder="0" />
          </div>
        </div>
        <div>
          <label class="label">Número de visitas</label>
          <input type="number" v-model.number="form.visitas" class="input" min="1" step="1" placeholder="1" />
          <p class="text-xs text-slate-500 mt-1">Si este registro agrupa varias visitas en un solo reporte, indica el total.</p>
        </div>
        <div>
          <label class="label">Personal adicional en sitio</label>
          <input v-model="form.personal" class="input" placeholder="Nombres separados por coma" />
        </div>
      </div>
    </div>

    <!-- Save bar (sticky) -->
    <div class="save-bar">
      <button class="btn btn-primary w-full py-3.5 text-base font-bold rounded-xl"
              :disabled="saving" @click="save">
        <CheckIcon v-if="!saving" class="w-5 h-5" />
        <span>{{ saving ? 'Guardando…' : 'Guardar Registro' }}</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import {
  ChevronLeftIcon, CameraIcon, PhotoIcon, CheckIcon,
  WrenchScrewdriverIcon, BoltIcon, Cog8ToothIcon,
} from '@heroicons/vue/24/outline'
import { mantenimientoApi, escenarioApi, tecnicoApi, equipoApi, cambioPiezaApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { TIPO_PIEZA_LABELS, today } from '@/constants'
import type { Escenario, Tecnico, Equipo } from '@/types'

const router = useRouter()
const toast  = useToastStore()
const { handleError } = useApiError()

const saving     = ref(false)
const escenarios = ref<Escenario[]>([])
const tecnicos   = ref<Tecnico[]>([])
const equipos    = ref<Equipo[]>([])

const tipos = [
  { value: 'preventivo', label: 'Preventivo', icon: WrenchScrewdriverIcon },
  { value: 'correctivo', label: 'Correctivo', icon: BoltIcon },
  { value: 'operativo',  label: 'Operativo',  icon: Cog8ToothIcon },
]

const form = ref({
  escenario_id: null as number | null,
  tecnico_id:   null as number | null,
  tecnico:      '',
  fecha:        today(),
  hora:         new Date().toTimeString().slice(0, 5),
  tipo:         'preventivo',
  actividades:  '',
  observaciones:'',
  estado:       'completado',
  horas:        0,
  visitas:      1,
  personal:     '',
})

const equiposFiltrados = computed(() =>
  form.value.escenario_id
    ? equipos.value.filter(e => e.escenario_id === form.value.escenario_id)
    : equipos.value
)

function onTecnicoChange() {
  const t = tecnicos.value.find(x => x.id === form.value.tecnico_id)
  form.value.tecnico = t?.nombre_completo ?? ''
}

// ── Photos ────────────────────────────────────────────────
interface LocalPhoto { file: File; preview: string }
type FotoTipo = 'antes' | 'despues'
const fotosAntes   = ref<LocalPhoto[]>([])
const fotosDespues = ref<LocalPhoto[]>([])
const totalFotos = computed(() => fotosAntes.value.length + fotosDespues.value.length)

function bucket(tipo: FotoTipo) {
  return tipo === 'antes' ? fotosAntes : fotosDespues
}
function addFoto(e: Event, tipo: FotoTipo) {
  const file = (e.target as HTMLInputElement).files?.[0]
  if (file) bucket(tipo).value.push({ file, preview: URL.createObjectURL(file) })
  ;(e.target as HTMLInputElement).value = ''
}
function addFotos(e: Event, tipo: FotoTipo) {
  Array.from((e.target as HTMLInputElement).files ?? []).forEach(file =>
    bucket(tipo).value.push({ file, preview: URL.createObjectURL(file) })
  )
  ;(e.target as HTMLInputElement).value = ''
}
function removePhoto(tipo: FotoTipo, i: number) {
  const arr = bucket(tipo).value
  URL.revokeObjectURL(arr[i].preview)
  arr.splice(i, 1)
}

// ── Piezas ────────────────────────────────────────────────
interface PiezaLocal { tipo_pieza: string; descripcion_pieza: string; equipo_id: number | null; serie_instalada: string; serie_retirada: string }
const piezas = ref<PiezaLocal[]>([])
function addPieza() { piezas.value.push({ tipo_pieza: 'otro', descripcion_pieza: '', equipo_id: null, serie_instalada: '', serie_retirada: '' }) }

// ── Save ──────────────────────────────────────────────────
async function save() {
  if (!form.value.escenario_id) { toast.add('Selecciona un escenario', 'error'); return }
  if (!form.value.actividades?.trim()) { toast.add('Describe las actividades realizadas', 'error'); return }
  saving.value = true
  try {
    const { data: mantResp } = await mantenimientoApi.create(form.value as never)
    const mantId: number = (mantResp as any).data?.id ?? (mantResp as any).id

    // Upload photos (antes y después)
    for (const f of fotosAntes.value) {
      await mantenimientoApi.uploadFoto(mantId, f.file, 'antes')
    }
    for (const f of fotosDespues.value) {
      await mantenimientoApi.uploadFoto(mantId, f.file, 'despues')
    }

    // Save piezas
    for (const pz of piezas.value) {
      if (!pz.descripcion_pieza.trim()) continue
      await cambioPiezaApi.create({
        escenario_id:     form.value.escenario_id!,
        mantenimiento_id: mantId,
        evento_id:        null,
        equipo_id:        pz.equipo_id,
        descripcion_pieza: pz.descripcion_pieza,
        tipo_pieza:        pz.tipo_pieza as never,
        serie_instalada:   pz.serie_instalada || null,
        serie_retirada:    pz.serie_retirada || null,
        tecnico_id:        form.value.tecnico_id,
        fecha:             form.value.fecha,
        estado_bodega:     'pendiente',
        recibido_por:      null,
        fecha_recepcion:   null,
        notas:             null,
      } as never)
    }

    toast.add(`Mantenimiento guardado — ${totalFotos.value} foto(s) adjunta(s) (${fotosAntes.value.length} antes / ${fotosDespues.value.length} después)`)
    router.push('/campo')
  } catch (e) {
    handleError(e, 'Error al guardar el registro')
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  const [esc, tec, eq] = await Promise.all([
    escenarioApi.list(), tecnicoApi.list(), equipoApi.list(),
  ])
  escenarios.value = esc.data.data
  tecnicos.value   = tec.data.data.filter(t => t.activo)
  equipos.value    = eq.data.data
})
</script>
