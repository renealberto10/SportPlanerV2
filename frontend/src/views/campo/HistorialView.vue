<template>
  <div class="form-mobile max-w-lg mx-auto pb-32">

    <!-- Header -->
    <div class="flex items-center gap-3 mb-5">
      <button class="btn btn-ghost btn-icon" @click="$router.back()">
        <ChevronLeftIcon class="w-5 h-5" />
      </button>
      <div class="flex-1 min-w-0">
        <h2 class="font-bold text-slate-900 text-lg leading-tight">Historial de Registros</h2>
        <p class="text-xs text-slate-400">Solicitudes y mantenimientos guardados</p>
      </div>
      <button class="btn btn-ghost btn-icon" @click="reload" :disabled="loading">
        <ArrowPathIcon class="w-5 h-5" :class="loading ? 'animate-spin text-blue-500' : 'text-slate-400'" />
      </button>
    </div>

    <!-- Tabs -->
    <div class="flex bg-slate-100 rounded-2xl p-1 mb-5 gap-1">
      <button
        v-for="tab in tabs" :key="tab.key"
        :class="['flex-1 flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl text-sm font-semibold transition-all',
          activeTab === tab.key
            ? 'bg-white shadow-sm text-slate-900'
            : 'text-slate-500 hover:text-slate-700']"
        @click="activeTab = tab.key">
        <component :is="tab.icon" class="w-4 h-4" />
        {{ tab.label }}
        <span v-if="tab.count > 0"
          class="ml-0.5 bg-slate-200 text-slate-600 text-[10px] font-bold rounded-full px-1.5 py-0.5 leading-none">
          {{ tab.count }}
        </span>
      </button>
    </div>

    <!-- Loading skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 4" :key="i" class="bg-white rounded-2xl p-4 border border-slate-100 animate-pulse">
        <div class="flex gap-3">
          <div class="w-10 h-10 rounded-xl bg-slate-200 flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="h-3.5 bg-slate-200 rounded w-3/4" />
            <div class="h-3 bg-slate-100 rounded w-1/2" />
          </div>
          <div class="h-5 bg-slate-200 rounded-full w-16" />
        </div>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 border border-red-100 rounded-2xl p-4 text-center">
      <ExclamationTriangleIcon class="w-8 h-8 text-red-400 mx-auto mb-2" />
      <p class="text-sm font-medium text-red-700">No se pudo cargar el historial</p>
      <p class="text-xs text-red-500 mt-0.5">{{ error }}</p>
      <button class="mt-3 btn btn-sm bg-red-100 text-red-700 hover:bg-red-200 rounded-xl px-4" @click="reload">
        Reintentar
      </button>
    </div>

    <!-- Solicitudes tab -->
    <template v-else-if="activeTab === 'solicitudes'">
      <div v-if="solicitudes.length === 0" class="text-center py-16 text-slate-400">
        <ClipboardDocumentListIcon class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="text-sm font-medium">Sin solicitudes registradas</p>
        <RouterLink to="/campo/solicitud" class="mt-3 inline-block text-xs text-amber-600 font-semibold underline">
          Crear solicitud
        </RouterLink>
      </div>

      <div v-else class="space-y-3">
        <div v-for="s in solicitudes" :key="s.id"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <!-- Card header -->
          <div class="flex items-start gap-3 p-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
              <ClipboardDocumentListIcon class="w-5 h-5 text-amber-600" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 leading-snug line-clamp-2">{{ s.actividad }}</p>
              <p class="text-xs text-slate-500 mt-0.5">Solicita: <span class="font-medium text-slate-700">{{ s.solicita }}</span></p>
            </div>
            <span :class="SOLICITUD_ESTADO_BADGE[s.estado]" class="flex-shrink-0 text-[10px]">
              {{ SOLICITUD_ESTADOS[s.estado] }}
            </span>
          </div>
          <!-- Card footer -->
          <div class="flex items-center gap-3 px-4 pb-3 flex-wrap">
            <span class="flex items-center gap-1 text-[11px] text-slate-400">
              <CalendarDaysIcon class="w-3.5 h-3.5" />
              {{ fmtDate(s.fecha_solicitud) }}
            </span>
            <span v-if="s.escenario" class="flex items-center gap-1 text-[11px] text-slate-400">
              <MapPinIcon class="w-3.5 h-3.5" />
              {{ s.escenario.nombre }}
            </span>
            <span v-else-if="s.escenario_texto" class="flex items-center gap-1 text-[11px] text-slate-400">
              <MapPinIcon class="w-3.5 h-3.5" />
              {{ s.escenario_texto }}
            </span>
            <span :class="SOLICITUD_PRIORIDAD_BADGE[s.prioridad]" class="text-[10px] ml-auto">
              {{ s.prioridad }}
            </span>
          </div>
          <div v-if="s.tecnico" class="px-4 pb-3">
            <span class="flex items-center gap-1 text-[11px] text-slate-400">
              <UserIcon class="w-3.5 h-3.5" />
              {{ s.tecnico.nombre_completo }}
            </span>
          </div>
        </div>
      </div>
    </template>

    <!-- Mantenimientos tab -->
    <template v-else>
      <div v-if="mantenimientos.length === 0" class="text-center py-16 text-slate-400">
        <WrenchScrewdriverIcon class="w-12 h-12 mx-auto mb-3 opacity-30" />
        <p class="text-sm font-medium">Sin mantenimientos registrados</p>
        <RouterLink to="/campo/mantenimiento" class="mt-3 inline-block text-xs text-blue-600 font-semibold underline">
          Registrar mantenimiento
        </RouterLink>
      </div>

      <div v-else class="space-y-3">
        <div v-for="m in mantenimientos" :key="m.id"
          class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
          <!-- Card header -->
          <div class="flex items-start gap-3 p-4">
            <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0">
              <WrenchScrewdriverIcon class="w-5 h-5 text-blue-600" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-semibold text-slate-900 leading-snug">
                {{ m.escenario?.nombre ?? '—' }}
              </p>
              <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                {{ m.actividades ?? 'Sin descripción de actividades' }}
              </p>
            </div>
            <span :class="MANTENIMIENTO_ESTADO_BADGE[m.estado]" class="flex-shrink-0 text-[10px]">
              {{ MANTENIMIENTO_ESTADOS[m.estado] }}
            </span>
          </div>
          <!-- Card footer -->
          <div class="flex items-center gap-3 px-4 pb-3 flex-wrap">
            <span class="flex items-center gap-1 text-[11px] text-slate-400">
              <CalendarDaysIcon class="w-3.5 h-3.5" />
              {{ fmtDate(m.fecha) }}
            </span>
            <span v-if="m.tecnico_obj || m.tecnico" class="flex items-center gap-1 text-[11px] text-slate-400">
              <UserIcon class="w-3.5 h-3.5" />
              {{ m.tecnico_obj?.nombre_completo ?? m.tecnico }}
            </span>
            <span :class="MANTENIMIENTO_TIPO_BADGE[m.tipo]" class="text-[10px] ml-auto">
              {{ MANTENIMIENTO_TIPOS[m.tipo] }}
            </span>
          </div>
          <div v-if="m.fotos && m.fotos.length > 0" class="px-4 pb-3">
            <span class="flex items-center gap-1 text-[11px] text-slate-400">
              <PhotoIcon class="w-3.5 h-3.5" />
              {{ m.fotos.length }} foto{{ m.fotos.length !== 1 ? 's' : '' }}
            </span>
          </div>
        </div>
      </div>
    </template>

  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import {
  ChevronLeftIcon, ArrowPathIcon, CalendarDaysIcon, MapPinIcon,
  UserIcon, WrenchScrewdriverIcon, ClipboardDocumentListIcon,
  ExclamationTriangleIcon, PhotoIcon,
} from '@heroicons/vue/24/outline'
import { solicitudApi, mantenimientoApi } from '@/api'
import type { Solicitud, Mantenimiento } from '@/types'
import {
  SOLICITUD_ESTADO_BADGE, SOLICITUD_ESTADOS, SOLICITUD_PRIORIDAD_BADGE,
  MANTENIMIENTO_ESTADO_BADGE, MANTENIMIENTO_ESTADOS,
  MANTENIMIENTO_TIPO_BADGE, MANTENIMIENTO_TIPOS,
  fmtDate,
} from '@/constants'

const activeTab = ref<'solicitudes' | 'mantenimientos'>('solicitudes')
const solicitudes   = ref<Solicitud[]>([])
const mantenimientos = ref<Mantenimiento[]>([])
const loading = ref(false)
const error   = ref('')

const tabs = computed(() => [
  { key: 'solicitudes'   as const, label: 'Solicitudes',   icon: ClipboardDocumentListIcon, count: solicitudes.value.length },
  { key: 'mantenimientos' as const, label: 'Mantenimientos', icon: WrenchScrewdriverIcon,    count: mantenimientos.value.length },
])

async function reload() {
  loading.value = true
  error.value = ''
  try {
    const [resS, resM] = await Promise.all([
      solicitudApi.list(),
      mantenimientoApi.list(),
    ])
    solicitudes.value    = resS.data.data
    mantenimientos.value = resM.data.data
  } catch (e: unknown) {
    error.value = (e as { message?: string }).message ?? 'Error desconocido'
  } finally {
    loading.value = false
  }
}

onMounted(reload)
</script>
