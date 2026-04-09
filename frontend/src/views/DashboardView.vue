<template>
  <div>
    <LoadingSpinner v-if="loading" />
    <template v-else>

      <!-- ── Stat cards ─────────────────────────────────── -->
      <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        <div v-for="s in stats" :key="s.label"
             class="card card-hover relative overflow-hidden">
          <!-- Background accent -->
          <div class="absolute -right-3 -top-3 w-20 h-20 rounded-full opacity-[0.07]"
               :class="s.accentBg"></div>
          <div class="flex items-start justify-between relative">
            <div>
              <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ s.label }}</div>
              <div class="text-3xl font-bold text-slate-900 mt-2 mb-0.5 tracking-tight">{{ s.value }}</div>
              <div v-if="s.sub" class="text-xs text-slate-400">{{ s.sub }}</div>
            </div>
            <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 ring-1"
                 :class="s.iconClass">
              <component :is="s.icon" class="w-5 h-5" />
            </div>
          </div>
        </div>
      </div>

      <!-- ── Charts row ──────────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-5 gap-5 mb-5">

        <!-- Bar chart — wider -->
        <div class="card lg:col-span-3">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="font-semibold text-slate-900 text-sm">Mantenimientos por Escenario</h3>
              <p class="text-xs text-slate-400 mt-0.5">Acumulado total del contrato</p>
            </div>
            <span class="badge badge-blue">{{ totalMantenimientos }} registros</span>
          </div>
          <Bar v-if="barData" :data="barData" :options="barOpts" style="max-height:210px" />
          <EmptyState v-else icon="⚙" title="Sin datos de mantenimiento" />
        </div>

        <!-- Doughnut chart — narrower -->
        <div class="card lg:col-span-2">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h3 class="font-semibold text-slate-900 text-sm">Equipos por Tipo</h3>
              <p class="text-xs text-slate-400 mt-0.5">{{ data?.stats?.equipos_total ?? 0 }} unidades</p>
            </div>
          </div>
          <Doughnut v-if="doughnutData" :data="doughnutData" :options="doughnutOpts" style="max-height:210px" />
          <EmptyState v-else icon="▣" title="Sin equipos registrados" />
        </div>
      </div>

      <!-- ── Upcoming tables ─────────────────────────────── -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- Mantenimientos -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-900 text-sm">Próximos Mantenimientos</h3>
            <RouterLink to="/mantenimiento" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
              Ver todos →
            </RouterLink>
          </div>
          <div v-if="data?.proximos_mantenimientos?.length" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr>
                  <th class="th">Fecha</th>
                  <th class="th">Escenario</th>
                  <th class="th">Tipo</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in data.proximos_mantenimientos" :key="m.id">
                  <td class="td whitespace-nowrap">
                    <span class="font-medium text-blue-600">{{ fmtDate(m.fecha) }}</span>
                  </td>
                  <td class="td text-slate-600 max-w-[160px] truncate">{{ m.escenario?.nombre }}</td>
                  <td class="td">
                    <span :class="MANTENIMIENTO_TIPO_BADGE[m.tipo] || 'badge badge-gray'">
                      {{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <EmptyState v-else icon="⚙" title="Sin mantenimientos próximos" />
        </div>

        <!-- Eventos -->
        <div class="card">
          <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-slate-900 text-sm">Próximos Eventos</h3>
            <RouterLink to="/eventos" class="text-xs text-blue-600 hover:text-blue-700 font-medium">
              Ver todos →
            </RouterLink>
          </div>
          <div v-if="data?.proximos_eventos?.length" class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr>
                  <th class="th">Fecha</th>
                  <th class="th">Evento</th>
                  <th class="th">Estado</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="e in data.proximos_eventos" :key="e.id">
                  <td class="td whitespace-nowrap">
                    <span class="font-medium text-emerald-600">{{ fmtDate(e.fecha) }}</span>
                  </td>
                  <td class="td font-medium text-slate-800 max-w-[160px] truncate">{{ e.nombre }}</td>
                  <td class="td">
                    <span :class="EVENTO_ESTADO_BADGE[e.estado] || 'badge badge-gray'">
                      {{ EVENTO_ESTADOS[e.estado] || e.estado }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <EmptyState v-else icon="◎" title="Sin eventos próximos" />
        </div>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import {
  Chart as ChartJS, BarElement, CategoryScale,
  LinearScale, ArcElement, Tooltip, Legend,
} from 'chart.js'
import {
  WrenchScrewdriverIcon, StarIcon,
  TvIcon, BuildingOffice2Icon,
} from '@heroicons/vue/24/outline'
import { dashboardApi } from '@/api'
import { useApiError } from '@/composables/useApiError'
import {
  fmtDate,
  MANTENIMIENTO_TIPOS, MANTENIMIENTO_TIPO_BADGE,
  EVENTO_ESTADOS, EVENTO_ESTADO_BADGE,
} from '@/constants'
import type { DashboardData } from '@/types'
import LoadingSpinner from '@/components/LoadingSpinner.vue'
import EmptyState from '@/components/EmptyState.vue'

ChartJS.register(BarElement, CategoryScale, LinearScale, ArcElement, Tooltip, Legend)

const { handleError } = useApiError()
const loading = ref(true)
const data    = ref<DashboardData | null>(null)

const totalMantenimientos = computed(() =>
  (data.value?.mantenimientos_por_escenario || []).reduce((a, b) => a + b.total, 0)
)

const stats = computed(() => {
  const s = data.value?.stats || {}
  return [
    {
      icon:      WrenchScrewdriverIcon,
      label:     'Mantenimientos',
      value:     s.mantenimientos_mes ?? 0,
      sub:       'Este mes',
      iconClass: 'bg-blue-50 text-blue-600 ring-blue-100',
      accentBg:  'bg-blue-500',
    },
    {
      icon:      StarIcon,
      label:     'Eventos',
      value:     s.eventos_mes ?? 0,
      sub:       'Este mes',
      iconClass: 'bg-emerald-50 text-emerald-600 ring-emerald-100',
      accentBg:  'bg-emerald-500',
    },
    {
      icon:      TvIcon,
      label:     'Equipos',
      value:     s.equipos_total ?? 0,
      sub:       'Inventario total',
      iconClass: 'bg-amber-50 text-amber-600 ring-amber-100',
      accentBg:  'bg-amber-500',
    },
    {
      icon:      BuildingOffice2Icon,
      label:     'Escenarios',
      value:     s.escenarios_activos ?? 0,
      sub:       'Activos',
      iconClass: 'bg-purple-50 text-purple-600 ring-purple-100',
      accentBg:  'bg-purple-500',
    },
  ]
})

const barData = computed(() => {
  const raw = data.value?.mantenimientos_por_escenario || []
  if (!raw.length) return null
  return {
    labels: raw.map(r => r.nombre.length > 22 ? r.nombre.substring(0, 22) + '…' : r.nombre),
    datasets: [{
      label: 'Mantenimientos',
      data: raw.map(r => r.total),
      backgroundColor: '#3b82f6',
      hoverBackgroundColor: '#2563eb',
      borderRadius: 6,
      borderSkipped: false,
    }],
  }
})

const barOpts = {
  responsive: true,
  plugins: {
    legend: { display: false },
    tooltip: {
      callbacks: {
        label: (ctx: { parsed: { y: number } }) => ` ${ctx.parsed.y} mantenimientos`,
      },
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { maxRotation: 40, font: { size: 9 }, color: '#94a3b8' },
    },
    y: {
      grid: { color: '#f1f5f9' },
      ticks: { font: { size: 9 }, color: '#94a3b8', stepSize: 1 },
      beginAtZero: true,
    },
  },
}

const doughnutData = computed(() => {
  const raw = data.value?.equipos_por_tipo || {}
  const keys = Object.keys(raw).filter(k => raw[k] > 0)
  if (!keys.length) return null
  const labels: Record<string, string> = {
    pantalla: 'Pantallas', bocina: 'Bocinas',
    consola: 'Consolas', servidor: 'Servidores', otro: 'Otros',
  }
  const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#64748b']
  return {
    labels: keys.map(k => labels[k] || k),
    datasets: [{
      data: keys.map(k => raw[k]),
      backgroundColor: colors,
      borderWidth: 3,
      borderColor: '#ffffff',
      hoverOffset: 4,
    }],
  }
})

const doughnutOpts = {
  responsive: true,
  plugins: {
    legend: {
      position: 'right' as const,
      labels: { font: { size: 11 }, padding: 14, usePointStyle: true, pointStyleWidth: 8 },
    },
  },
  cutout: '70%',
}

onMounted(async () => {
  try {
    const { data: d } = await dashboardApi.stats()
    data.value = d
  } catch (e) {
    handleError(e, 'Error al cargar el dashboard')
  } finally {
    loading.value = false
  }
})
</script>
