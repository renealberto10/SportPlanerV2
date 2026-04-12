<template>
  <div class="max-w-md mx-auto">
    <div class="mb-6">
      <h2 class="page-title">Formularios de Campo</h2>
      <p class="text-xs text-slate-500 mt-0.5">Registro in situ — optimizado para móvil</p>
    </div>

    <div class="space-y-3">
      <!-- Mantenimiento -->
      <RouterLink to="/campo/mantenimiento"
        class="flex items-center gap-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm
               hover:border-blue-300 hover:shadow-md transition-all active:scale-[0.98]">
        <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-blue-500/30">
          <WrenchScrewdriverIcon class="w-7 h-7 text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-slate-900">Mantenimiento</div>
          <div class="text-sm text-slate-500 mt-0.5">Registro de visita técnica preventiva, correctiva u operativa</div>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-slate-300 flex-shrink-0" />
      </RouterLink>

      <!-- Evento -->
      <RouterLink to="/campo/evento"
        class="flex items-center gap-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm
               hover:border-emerald-300 hover:shadow-md transition-all active:scale-[0.98]">
        <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-emerald-500/30">
          <StarIcon class="w-7 h-7 text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-slate-900">Cobertura de Evento</div>
          <div class="text-sm text-slate-500 mt-0.5">Registro de evento deportivo, cultural o producción técnica</div>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-slate-300 flex-shrink-0" />
      </RouterLink>

      <!-- Solicitud INDES -->
      <RouterLink to="/campo/solicitud"
        class="flex items-center gap-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm
               hover:border-amber-300 hover:shadow-md transition-all active:scale-[0.98]">
        <div class="w-14 h-14 rounded-2xl bg-amber-500 flex items-center justify-center flex-shrink-0 shadow-lg shadow-amber-500/30">
          <ClipboardDocumentListIcon class="w-7 h-7 text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-slate-900">Solicitud de Servicio</div>
          <div class="text-sm text-slate-500 mt-0.5">Solicitud de mantenimiento o evento por parte del equipo INDES</div>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-slate-300 flex-shrink-0" />
      </RouterLink>

      <!-- Historial -->
      <RouterLink to="/campo/historial"
        class="flex items-center gap-4 bg-white rounded-2xl p-5 border border-slate-200 shadow-sm
               hover:border-violet-300 hover:shadow-md transition-all active:scale-[0.98]">
        <div class="w-14 h-14 rounded-2xl bg-violet-600 flex items-center justify-center flex-shrink-0 shadow-lg shadow-violet-500/30">
          <ClockIcon class="w-7 h-7 text-white" />
        </div>
        <div class="flex-1 min-w-0">
          <div class="font-bold text-slate-900">Historial de Registros</div>
          <div class="text-sm text-slate-500 mt-0.5">Ver solicitudes y mantenimientos ya guardados</div>
        </div>
        <ChevronRightIcon class="w-5 h-5 text-slate-300 flex-shrink-0" />
      </RouterLink>
    </div>

    <!-- Quick tips -->
    <div class="mt-6 bg-blue-50 border border-blue-100 rounded-2xl p-4">
      <div class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-2">Consejos</div>
      <ul class="text-xs text-blue-600 space-y-1">
        <li>· Toma fotos <strong>antes y después</strong> del trabajo</li>
        <li>· Registra <strong>series exactas</strong> de piezas reemplazadas</li>
        <li>· El formulario funciona <strong>sin conexión a internet</strong> (guardado local)</li>
      </ul>
    </div>
  </div>
</template>

<script setup lang="ts">
import { WrenchScrewdriverIcon, StarIcon, ClipboardDocumentListIcon, ChevronRightIcon, ClockIcon } from '@heroicons/vue/24/outline'
</script>
