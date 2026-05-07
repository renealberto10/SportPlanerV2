<template>
  <div>
    <!-- ── Control bar ──────────────────────────────────── -->
    <div class="page-header no-print">
      <div>
        <h2 class="page-title">Generador de Reportes</h2>
        <p class="text-xs text-slate-500 mt-0.5">Informes técnicos con evidencia fotográfica</p>
      </div>
      <div v-if="reportData" class="flex gap-2">
        <button class="btn btn-outline" @click="reportData = null">✕ Cerrar</button>
        <button class="btn btn-success" @click="window.print()">Imprimir / PDF</button>
      </div>
    </div>

    <!-- ── Filter panel ─────────────────────────────────── -->
    <div v-if="!reportData" class="card mb-6 no-print">
      <h3 class="font-semibold text-slate-800 text-sm mb-4">Configurar Reporte</h3>

      <!-- Tipo de reporte -->
      <div class="mb-5">
        <label class="label">Tipo de Reporte *</label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <button type="button"
                  class="text-left rounded-xl border-2 p-4 transition-all"
                  :class="filters.tipoReporte === 'mantenimiento'
                    ? 'border-blue-500 bg-blue-50/50 ring-2 ring-blue-100'
                    : 'border-slate-200 hover:border-slate-300'"
                  @click="filters.tipoReporte = 'mantenimiento'">
            <div class="flex items-center gap-2 mb-1">
              <span class="w-2 h-2 rounded-full bg-blue-500"></span>
              <div class="font-semibold text-sm text-slate-800">Reporte de Mantenimiento</div>
            </div>
            <div class="text-xs text-slate-500 leading-relaxed">
              Informe técnico detallado de un solo escenario, con fotos antes y después del trabajo.
            </div>
          </button>
          <button type="button"
                  class="text-left rounded-xl border-2 p-4 transition-all"
                  :class="filters.tipoReporte === 'eventos'
                    ? 'border-emerald-500 bg-emerald-50/50 ring-2 ring-emerald-100'
                    : 'border-slate-200 hover:border-slate-300'"
                  @click="filters.tipoReporte = 'eventos'">
            <div class="flex items-center gap-2 mb-1">
              <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
              <div class="font-semibold text-sm text-slate-800">Reporte de Eventos</div>
            </div>
            <div class="text-xs text-slate-500 leading-relaxed">
              Resumen de eventos del periodo, todos los escenarios en un solo documento, separados por escenario.
            </div>
          </button>
        </div>
      </div>

      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
        <div>
          <label class="label">Mes</label>
          <select v-model="filters.mes" class="input">
            <option v-for="(m, i) in MESES" :key="i" :value="i + 1">{{ m }}</option>
          </select>
        </div>
        <div>
          <label class="label">Año</label>
          <select v-model="filters.anio" class="input">
            <option :value="2026">2026</option>
            <option :value="2025">2025</option>
          </select>
        </div>
        <div class="md:col-span-2">
          <label class="label">
            Escenario
            <span v-if="filters.tipoReporte === 'mantenimiento'" class="text-red-500">*</span>
          </label>
          <select v-model="filters.escenario_id" class="input">
            <option v-if="filters.tipoReporte === 'eventos'" value="">Todos los escenarios</option>
            <option v-else value="" disabled>Seleccionar escenario…</option>
            <option v-for="e in escenarios" :key="e.id" :value="e.id">{{ e.nombre }}</option>
          </select>
          <p v-if="filters.tipoReporte === 'mantenimiento'" class="text-xs text-amber-600 mt-1.5">
            ⚠ El reporte de mantenimiento sólo se genera para un escenario a la vez.
          </p>
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4 mb-5">
        <div>
          <label class="label">Contratista</label>
          <input v-model="config.contratista" class="input" placeholder="ISATECH, S.A.S. de C.V." />
        </div>
        <div>
          <label class="label">Administrado por</label>
          <input v-model="config.administrador" class="input" placeholder="INDES" />
        </div>
      </div>
      <button class="btn btn-primary" :disabled="loading || !canGenerate" @click="generar">
        {{ loading ? 'Generando…' : '↗ Generar Reporte' }}
      </button>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════
         REPORTE DE MANTENIMIENTO (un solo escenario)
         ════════════════════════════════════════════════════════════════════ -->
    <div v-if="reportData && reportData.tipoReporte === 'mantenimiento'" id="reporte-doc">
      <template v-for="esc in reportData.escenarios" :key="esc.id">
        <div class="report-page">

          <!-- HEADER -->
          <div class="r-header">
            <div class="r-header-top">
              <div class="r-logo-box">
                <div class="r-logo-mark">SP</div>
                <div>
                  <div class="r-logo-name">SportPlanner</div>
                  <div class="r-logo-sub">{{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}</div>
                </div>
              </div>
              <div class="r-header-meta">
                <div><span class="r-meta-label">Mes:</span> {{ mesNombre(reportData.mes) }} {{ reportData.anio }}</div>
                <div v-if="config.administrador"><span class="r-meta-label">Cliente:</span> {{ config.administrador }}</div>
              </div>
            </div>
            <div class="r-title-block">
              <div class="r-title">REPORTE TÉCNICO DE MANTENIMIENTO</div>
              <div class="r-subtitle">{{ esc.nombre }}</div>
              <div class="r-period">{{ mesNombre(reportData.mes) }} de {{ reportData.anio }}</div>
            </div>
            <div class="r-header-fields">
              <div class="r-field"><span class="r-field-label">Escenario:</span> {{ esc.nombre }}</div>
              <div class="r-field"><span class="r-field-label">Áreas cubiertas:</span> Audio y Bocinas, Pantallas Digitales, Producción Técnica</div>
              <div class="r-field"><span class="r-field-label">Contratista:</span> {{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}</div>
              <div class="r-field" v-if="config.administrador"><span class="r-field-label">Administrado por:</span> {{ config.administrador }}</div>
            </div>
          </div>

          <!-- 1. INTRODUCCIÓN -->
          <div class="r-section">
            <div class="r-section-title">1. Introducción</div>
            <p class="r-paragraph">
              Durante el mes de {{ mesNombre(reportData.mes) }} de {{ reportData.anio }} se realizaron
              {{ mantsForEsc(esc.id).length }} visita(s) de mantenimiento
              {{ tiposRealizados(esc.id) }} en las instalaciones de <strong>{{ esc.nombre }}</strong>,
              a cargo de {{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}.
            </p>
            <p class="r-paragraph">
              Las actividades desarrolladas estuvieron orientadas a garantizar la seguridad operativa,
              la continuidad del servicio y el correcto funcionamiento de los equipos de audio,
              pantallas digitales y cableado del escenario. Cada intervención cuenta con evidencia
              fotográfica del estado <em>antes</em> y <em>después</em> del trabajo realizado.
            </p>
          </div>

          <!-- 2. ALCANCE -->
          <div class="r-section">
            <div class="r-section-title">2. Alcance del Trabajo</div>
            <ul class="r-list">
              <li v-if="hasType(esc.id,'preventivo') || hasType(esc.id,'correctivo')">
                Limpieza y revisión de bocinas, rejillas protectoras y conectores de audio.
              </li>
              <li v-if="hasType(esc.id,'preventivo') || hasType(esc.id,'correctivo')">
                Mantenimiento de pantallas digitales: limpieza de gabinetes, ventiladores, rejillas, filtros y conectores.
              </li>
              <li v-if="hasType(esc.id,'preventivo') || hasType(esc.id,'correctivo')">
                Revisión y mantenimiento de cableado eléctrico y de señal.
              </li>
              <li v-if="hasType(esc.id,'correctivo')">
                Soldaduras y cambios en componentes defectuosos (LEDs, tarjetas de señal, fuentes de poder).
              </li>
              <li v-if="piezasForEsc(esc.id).length">
                Reemplazo de {{ piezasForEsc(esc.id).length }} pieza(s) con registro de número de serie.
              </li>
            </ul>
          </div>

          <!-- 3. METODOLOGÍA -->
          <div class="r-section">
            <div class="r-section-title">3. Metodología de Trabajo</div>
            <ul class="r-list">
              <li>Desenergización de circuitos antes de cada intervención.</li>
              <li>Uso de equipo de protección personal (EPP) por parte del personal técnico.</li>
              <li>Limpieza técnica mediante aire comprimido, brocha antiestática y limpiador dieléctrico.</li>
              <li>Pruebas de continuidad y verificación de conexiones.</li>
              <li>Revisión mecánica de tornillería, fijaciones y tensores de estructura.</li>
              <li>Registro fotográfico antes / después y elaboración de bitácora.</li>
            </ul>
          </div>

          <!-- 4. ACTIVIDADES + FOTOS ANTES/DESPUÉS -->
          <div class="r-section">
            <div class="r-section-title">4. Actividades de Mantenimiento</div>

            <div v-if="!mantsForEsc(esc.id).length" class="r-paragraph text-slate-500 italic">
              Sin visitas registradas en el período.
            </div>

            <div v-for="m in mantsForEsc(esc.id)" :key="m.id" class="r-mant-card">
              <div class="r-mant-header">
                <div class="r-mant-fecha">{{ fmtDate(m.fecha) }}</div>
                <span :class="tipoBadge(m.tipo)">{{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}</span>
                <span :class="estadoBadge(m.estado)">{{ m.estado }}</span>
                <div class="r-mant-tec">Téc: {{ m.tecnico_obj?.nombre_completo || m.tecnico || '—' }}</div>
                <div class="r-mant-hrs" v-if="m.horas">{{ m.horas }}h</div>
              </div>

              <div class="r-mant-body">
                <div class="r-mant-col">
                  <div class="r-mant-label">Actividades realizadas</div>
                  <div class="r-mant-text">{{ m.actividades || '—' }}</div>
                </div>
                <div class="r-mant-col" v-if="m.observaciones">
                  <div class="r-mant-label">Observaciones / Resultado</div>
                  <div class="r-mant-text">{{ m.observaciones }}</div>
                </div>
              </div>

              <!-- Fotos ANTES / DESPUÉS -->
              <div v-if="getFotos(m, 'antes').length || getFotos(m, 'despues').length" class="r-fotos-pair">

                <div class="r-fotos-side r-fotos-antes">
                  <div class="r-fotos-side-title">
                    <span class="r-dot r-dot-antes"></span> ANTES
                    <span class="r-fotos-count">{{ getFotos(m, 'antes').length }}</span>
                  </div>
                  <div v-if="getFotos(m, 'antes').length" class="r-fotos-grid-2">
                    <div v-for="(f, i) in getFotos(m, 'antes')" :key="'a'+i" class="r-foto-item">
                      <img :src="f.url" class="r-foto-img" />
                    </div>
                  </div>
                  <div v-else class="r-fotos-empty">Sin foto del estado inicial</div>
                </div>

                <div class="r-fotos-side r-fotos-despues">
                  <div class="r-fotos-side-title">
                    <span class="r-dot r-dot-despues"></span> DESPUÉS
                    <span class="r-fotos-count">{{ getFotos(m, 'despues').length }}</span>
                  </div>
                  <div v-if="getFotos(m, 'despues').length" class="r-fotos-grid-2">
                    <div v-for="(f, i) in getFotos(m, 'despues')" :key="'d'+i" class="r-foto-item">
                      <img :src="f.url" class="r-foto-img" />
                    </div>
                  </div>
                  <div v-else class="r-fotos-empty">Sin foto del trabajo terminado</div>
                </div>

              </div>
              <div v-else class="r-fotos-none">— Sin evidencia fotográfica —</div>
            </div>

            <!-- Piezas reemplazadas -->
            <template v-if="piezasForEsc(esc.id).length">
              <div class="r-subsection-title">4.1 Piezas Reemplazadas</div>
              <table class="r-table">
                <thead>
                  <tr>
                    <th>Pieza</th>
                    <th>Tipo</th>
                    <th>Serie instalada</th>
                    <th>Serie retirada</th>
                    <th>Técnico</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in piezasForEsc(esc.id)" :key="p.id">
                    <td>{{ p.descripcion_pieza }}</td>
                    <td class="capitalize">{{ p.tipo_pieza?.replace('_',' ') }}</td>
                    <td class="font-mono text-xs">{{ p.serie_instalada || '—' }}</td>
                    <td class="font-mono text-xs">{{ p.serie_retirada || '—' }}</td>
                    <td>{{ p.tecnico?.nombre_completo || '—' }}</td>
                  </tr>
                </tbody>
              </table>
            </template>
          </div>

          <!-- 5. RESULTADOS -->
          <div class="r-section">
            <div class="r-section-title">5. Resultados Globales</div>
            <table class="r-table r-table-compact">
              <tbody>
                <tr>
                  <td class="r-td-label">Visitas de mantenimiento realizadas</td>
                  <td class="font-bold text-center">{{ mantsForEsc(esc.id).length }}</td>
                  <td class="r-td-status">Completado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Horas trabajadas</td>
                  <td class="font-bold text-center">{{ horasForEsc(esc.id) }} hrs</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr v-if="piezasForEsc(esc.id).length">
                  <td class="r-td-label">Piezas reemplazadas</td>
                  <td class="font-bold text-center">{{ piezasForEsc(esc.id).length }}</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Fotos de evidencia (antes / después)</td>
                  <td class="font-bold text-center">{{ totalFotosForEsc(esc.id) }}</td>
                  <td class="r-td-status">Adjuntas</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 6. ESTADO FINAL -->
          <div class="r-section">
            <div class="r-section-title">6. Estado Final de los Sistemas</div>
            <p class="r-paragraph">
              Concluidas las labores del mes de {{ mesNombre(reportData.mes) }} de {{ reportData.anio }},
              las instalaciones de <strong>{{ esc.nombre }}</strong> quedaron en condiciones operativas y seguras.
            </p>
            <table class="r-table r-table-compact">
              <thead><tr><th>Sistema</th><th>Estado</th></tr></thead>
              <tbody>
                <tr v-for="s in sistemasEstado" :key="s.nombre">
                  <td>{{ s.nombre }}</td>
                  <td class="font-semibold text-emerald-700">{{ s.estado }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- FIRMAS -->
          <div class="r-section r-signatures">
            <div class="r-sig-col">
              <div class="r-sig-line"></div>
              <div class="r-sig-name">{{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}</div>
              <div class="r-sig-role">Técnico Responsable</div>
            </div>
            <div class="r-sig-col">
              <div class="r-sig-line"></div>
              <div class="r-sig-name">{{ config.administrador || 'Cliente' }}</div>
              <div class="r-sig-role">Administrador del Contrato</div>
            </div>
          </div>

          <div class="r-footer">
            Generado el {{ new Date().toLocaleDateString('es-SV') }} · SportPlanner · Mantenimiento {{ mesNombre(reportData.mes) }} {{ reportData.anio }}
          </div>
        </div>
      </template>

      <div class="text-center no-print mt-6 pb-6">
        <button class="btn btn-outline" @click="reportData = null">← Volver</button>
        <button class="btn btn-success ml-2" @click="window.print()">Imprimir / Exportar PDF</button>
      </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════════════
         REPORTE DE EVENTOS (todos los escenarios, separados)
         ════════════════════════════════════════════════════════════════════ -->
    <div v-if="reportData && reportData.tipoReporte === 'eventos'" id="reporte-doc">

      <!-- Portada general -->
      <div class="report-page">
        <div class="r-header">
          <div class="r-header-top">
            <div class="r-logo-box">
              <div class="r-logo-mark">SP</div>
              <div>
                <div class="r-logo-name">SportPlanner</div>
                <div class="r-logo-sub">{{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}</div>
              </div>
            </div>
            <div class="r-header-meta">
              <div><span class="r-meta-label">Mes:</span> {{ mesNombre(reportData.mes) }} {{ reportData.anio }}</div>
              <div v-if="config.administrador"><span class="r-meta-label">Cliente:</span> {{ config.administrador }}</div>
            </div>
          </div>
          <div class="r-title-block">
            <div class="r-title">REPORTE DE EVENTOS Y PRODUCCIÓN TÉCNICA</div>
            <div class="r-subtitle">Resumen del Periodo</div>
            <div class="r-period">{{ mesNombre(reportData.mes) }} de {{ reportData.anio }}</div>
          </div>
          <div class="r-header-fields">
            <div class="r-field"><span class="r-field-label">Escenarios incluidos:</span> {{ escenariosConEventos.length }}</div>
            <div class="r-field"><span class="r-field-label">Total de eventos:</span> {{ reportData.eventos.length }}</div>
            <div class="r-field"><span class="r-field-label">Contratista:</span> {{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}</div>
            <div class="r-field" v-if="config.administrador"><span class="r-field-label">Administrado por:</span> {{ config.administrador }}</div>
          </div>
        </div>

        <div class="r-section">
          <div class="r-section-title">Resumen General del Periodo</div>
          <table class="r-table">
            <thead>
              <tr>
                <th>Escenario</th>
                <th class="text-center">Eventos</th>
                <th class="text-center">Realizados</th>
                <th class="text-center">Programados</th>
                <th class="text-center">Fotos</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="esc in escenariosConEventos" :key="esc.id">
                <td class="font-semibold">{{ esc.nombre }}</td>
                <td class="text-center font-bold">{{ eventosForEsc(esc.id).length }}</td>
                <td class="text-center">{{ eventosForEsc(esc.id).filter((e: Evento) => e.estado === 'realizado').length }}</td>
                <td class="text-center">{{ eventosForEsc(esc.id).filter((e: Evento) => e.estado === 'programado').length }}</td>
                <td class="text-center">{{ totalFotosEventosForEsc(esc.id) }}</td>
              </tr>
            </tbody>
          </table>
          <div v-if="!escenariosConEventos.length" class="r-paragraph text-slate-500 italic mt-3">
            No hay eventos registrados en el período seleccionado.
          </div>
        </div>
      </div>

      <!-- Una página por escenario -->
      <template v-for="esc in escenariosConEventos" :key="'e-'+esc.id">
        <div class="page-break no-screen"></div>
        <div class="report-page">
          <div class="r-header">
            <div class="r-title-block">
              <div class="r-title">EVENTOS — {{ esc.nombre }}</div>
              <div class="r-period">{{ mesNombre(reportData.mes) }} de {{ reportData.anio }}</div>
            </div>
          </div>

          <div v-for="ev in eventosForEsc(esc.id)" :key="ev.id" class="r-mant-card">
            <div class="r-mant-header">
              <div class="r-mant-fecha">{{ fmtDate(ev.fecha) }}<span v-if="ev.hora" class="text-slate-400 font-normal ml-1">{{ ev.hora }}</span></div>
              <span class="badge badge-blue capitalize">{{ ev.tipo }}</span>
              <span :class="eventoEstadoBadge(ev.estado)">{{ ev.estado }}</span>
              <div class="r-mant-tec">{{ ev.nombre }}</div>
            </div>

            <div class="r-mant-body">
              <div class="r-mant-col" v-if="ev.descripcion">
                <div class="r-mant-label">Descripción</div>
                <div class="r-mant-text">{{ ev.descripcion }}</div>
              </div>
              <div class="r-mant-col" v-if="ev.personal">
                <div class="r-mant-label">Personal técnico</div>
                <div class="r-mant-text">{{ ev.personal }}</div>
              </div>
              <div class="r-mant-col" v-if="ev.equipos_notas" style="grid-column: 1 / -1">
                <div class="r-mant-label">Equipos / Notas técnicas</div>
                <div class="r-mant-text">{{ ev.equipos_notas }}</div>
              </div>
            </div>

            <div v-if="getEventoFotos(ev).length" class="r-fotos-grid">
              <div v-for="(f, i) in getEventoFotos(ev)" :key="i" class="r-foto-item">
                <img :src="f.url" class="r-foto-img" />
              </div>
            </div>
          </div>

          <div class="r-footer">
            SportPlanner · Eventos {{ esc.nombre }} · {{ mesNombre(reportData.mes) }} {{ reportData.anio }}
          </div>
        </div>
      </template>

      <div class="text-center no-print mt-6 pb-6">
        <button class="btn btn-outline" @click="reportData = null">← Volver</button>
        <button class="btn btn-success ml-2" @click="window.print()">Imprimir / Exportar PDF</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
import { dashboardApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { MESES, MANTENIMIENTO_TIPOS, fmtDate } from '@/constants'
import type { Escenario, Mantenimiento, Evento, CambioPieza, MantenimientoFoto, EventoFoto } from '@/types'

const toast = useToastStore()
const { handleError } = useApiError()

const loading    = ref(false)
const escenarios = ref<Escenario[]>([])
const reportData = ref<any>(null)
const now        = new Date()

const filters = reactive({
  tipoReporte: 'mantenimiento' as 'mantenimiento' | 'eventos',
  mes: now.getMonth() + 1,
  anio: now.getFullYear(),
  escenario_id: '' as number | '',
})
const config = reactive({ contratista: 'ISATECH, S.A.S. de C.V.', administrador: 'INDES' })

const mesNombre = (m: number) => MESES[m - 1] || ''
const window    = globalThis

const canGenerate = computed(() =>
  filters.tipoReporte === 'eventos' || !!filters.escenario_id,
)

// ── Per-escenario filters ─────────────────────────────────
const mantsForEsc       = (id: number): Mantenimiento[] =>
  (reportData.value?.mantenimientos || []).filter((m: Mantenimiento) => m.escenario_id === id)
const eventosForEsc     = (id: number): Evento[] =>
  (reportData.value?.eventos || []).filter((e: Evento) => e.escenario_id === id)
const piezasForEsc      = (id: number): CambioPieza[] =>
  (reportData.value?.cambios_piezas || []).filter((p: CambioPieza) => p.escenario_id === id)
const hasType           = (id: number, tipo: string) =>
  mantsForEsc(id).some((m: Mantenimiento) => m.tipo === tipo)
const horasForEsc       = (id: number) =>
  mantsForEsc(id).reduce((s: number, m: Mantenimiento) => s + (m.horas || 0), 0)
const tiposRealizados = (id: number) => {
  const tipos = [...new Set(mantsForEsc(id).map((m: Mantenimiento) => MANTENIMIENTO_TIPOS[m.tipo] || m.tipo))]
  return tipos.length ? `(${tipos.join(', ')})` : ''
}

// ── Foto helpers ──────────────────────────────────────────
function getFotos(m: Mantenimiento, tipo: 'antes' | 'despues'): MantenimientoFoto[] {
  const fotos = (m.fotos || []) as any[]
  return fotos
    .map((f: any): MantenimientoFoto =>
      typeof f === 'string'
        ? { url: f, path: f, tipo: 'despues' }
        : { url: f.url, path: f.path, tipo: f.tipo === 'antes' ? 'antes' : 'despues' },
    )
    .filter(f => f.tipo === tipo)
}

function getEventoFotos(e: Evento): EventoFoto[] {
  const fotos = (e.fotos || []) as any[]
  return fotos.map((f: any): EventoFoto =>
    typeof f === 'string' ? { url: `/storage/${f}`, path: f } : { url: f.url, path: f.path },
  )
}

const totalFotosForEsc = (id: number) =>
  mantsForEsc(id).reduce((s, m) => s + getFotos(m, 'antes').length + getFotos(m, 'despues').length, 0)

const totalFotosEventosForEsc = (id: number) =>
  eventosForEsc(id).reduce((s, e) => s + getEventoFotos(e).length, 0)

const escenariosConEventos = computed<Escenario[]>(() => {
  if (!reportData.value) return []
  const ids = new Set<number>(reportData.value.eventos.map((e: Evento) => e.escenario_id))
  return reportData.value.escenarios.filter((esc: Escenario) => ids.has(esc.id))
})

// ── Badge helpers ─────────────────────────────────────────
const tipoBadge = (tipo: string) => ({
  preventivo: 'badge badge-blue',
  correctivo:  'badge badge-red',
  operativo:   'badge badge-green',
}[tipo] || 'badge badge-gray')

const estadoBadge = (estado: string) => ({
  completado:  'badge badge-green',
  en_proceso:  'badge badge-yellow',
  pendiente:   'badge badge-gray',
}[estado] || 'badge badge-gray')

const eventoEstadoBadge = (estado: string) => ({
  realizado:  'badge badge-green',
  en_curso:   'badge badge-yellow',
  programado: 'badge badge-blue',
  cancelado:  'badge badge-red',
}[estado] || 'badge badge-gray')

const sistemasEstado = [
  { nombre: 'Sistema de Audio y Bocinas',      estado: 'Operativo' },
  { nombre: 'Pantallas Digitales',             estado: 'Operativo' },
  { nombre: 'Cableado Eléctrico y de Señal',   estado: 'Verificado y Organizado' },
  { nombre: 'Estructuras Metálicas y Tensores',estado: 'Revisados y Ajustados' },
  { nombre: 'Equipo de Cabina de Producción',  estado: 'Operativo' },
]

// ── Generate ──────────────────────────────────────────────
async function generar() {
  if (filters.tipoReporte === 'mantenimiento' && !filters.escenario_id) {
    toast.add('Selecciona un escenario para el reporte de mantenimiento', 'error')
    return
  }
  loading.value = true
  try {
    const params: Record<string, unknown> = { mes: filters.mes, anio: filters.anio }
    if (filters.escenario_id) params.escenario_id = filters.escenario_id
    const { data } = await dashboardApi.reporte(params)
    reportData.value = { ...data, tipoReporte: filters.tipoReporte }

    const empty = filters.tipoReporte === 'mantenimiento'
      ? !data.mantenimientos?.length
      : !data.eventos?.length

    if (empty) {
      toast.add('Sin registros para el período seleccionado', 'info')
    } else {
      toast.add('Reporte generado')
    }
  } catch (e) {
    handleError(e, 'Error al generar el reporte')
  } finally {
    loading.value = false
  }
}

onMounted(async () => {
  const { data } = await escenarioApi.list()
  escenarios.value = data.data
})
</script>

<style scoped>
/* ── Print + screen base ─────────────────────────────────── */
@media print {
  .no-print    { display: none !important; }
  .page-break  { page-break-after: always; }
  #reporte-doc { padding: 0; }
  .report-page { box-shadow: none !important; border: none !important; padding: 1.5cm 2cm; }
}
.no-screen { display: none; }
@media print { .no-screen { display: block; } }

/* ── Report page ─────────────────────────────────────────── */
.report-page {
  background: white;
  border-radius: 0.75rem;
  border: 1px solid #e2e8f0;
  box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  padding: 2.5rem;
  margin-bottom: 2rem;
  max-width: 900px;
  margin-left: auto; margin-right: auto;
}

/* ── Header ─────────────────────────────────────────────── */
.r-header {
  border-bottom: 3px solid #1e3a5f;
  padding-bottom: 1.5rem;
  margin-bottom: 1.75rem;
}
.r-header-top {
  display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem;
}
.r-logo-box { display: flex; align-items: center; gap: 0.75rem; }
.r-logo-mark {
  width: 2.25rem; height: 2.25rem; border-radius: 0.5rem;
  background: linear-gradient(135deg,#3b82f6,#1d4ed8);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 0.75rem; color: white;
}
.r-logo-name { font-size: 1rem; font-weight: 800; color: #1e3a5f; letter-spacing: -0.01em; }
.r-logo-sub  { font-size: 0.6875rem; color: #64748b; }
.r-header-meta { text-align: right; font-size: 0.75rem; color: #64748b; line-height: 1.7; }
.r-meta-label { font-weight: 600; color: #374151; }

.r-title-block { text-align: center; margin-bottom: 1.25rem; }
.r-title  { font-size: 1.125rem; font-weight: 800; color: #1e3a5f; letter-spacing: 0.04em; text-transform: uppercase; }
.r-subtitle { font-size: 1rem; font-weight: 700; color: #374151; margin-top: 0.375rem; }
.r-period { font-size: 0.8125rem; color: #64748b; margin-top: 0.25rem; }

.r-header-fields {
  display: grid; grid-template-columns: 1fr 1fr; gap: 0.25rem 2rem;
  background: #f8fafc; border-radius: 0.5rem; padding: 0.875rem 1rem;
}
.r-field { font-size: 0.8rem; color: #374151; }
.r-field-label { font-weight: 700; color: #1e3a5f; margin-right: 0.35rem; }

/* ── Sections ────────────────────────────────────────────── */
.r-section { margin-bottom: 1.75rem; }
.r-section-title {
  font-size: 0.875rem; font-weight: 800; color: #1e3a5f;
  border-left: 3px solid #3b82f6; padding-left: 0.625rem;
  margin-bottom: 0.875rem; text-transform: uppercase; letter-spacing: 0.04em;
}
.r-subsection-title {
  font-size: 0.8125rem; font-weight: 700; color: #374151;
  margin: 1rem 0 0.625rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.375rem;
}
.r-paragraph { font-size: 0.8125rem; color: #374151; line-height: 1.7; margin-bottom: 0.5rem; }
.r-list { font-size: 0.8125rem; color: #374151; line-height: 1.7; padding-left: 1.25rem; margin: 0.5rem 0; }
.r-list li { list-style: disc; margin-bottom: 0.25rem; }

/* ── Mantenimiento card ──────────────────────────────────── */
.r-mant-card {
  border: 1px solid #e2e8f0; border-radius: 0.625rem;
  margin-bottom: 0.875rem; overflow: hidden;
}
.r-mant-header {
  display: flex; align-items: center; gap: 0.625rem;
  background: #f8fafc; padding: 0.625rem 0.875rem;
  border-bottom: 1px solid #e2e8f0; flex-wrap: wrap;
}
.r-mant-fecha { font-size: 0.8125rem; font-weight: 700; color: #1e3a5f; white-space: nowrap; }
.r-mant-tec   { font-size: 0.75rem; color: #64748b; margin-left: auto; }
.r-mant-hrs   { font-size: 0.75rem; font-weight: 700; color: #1e3a5f; background: #dbeafe; padding: 0.125rem 0.5rem; border-radius: 9999px; }
.r-mant-body  { padding: 0.875rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.r-mant-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 0.25rem; }
.r-mant-text  { font-size: 0.8rem; color: #374151; line-height: 1.6; }
.r-mant-col:only-child { grid-column: 1 / -1; }

/* ── Fotos antes/después ─────────────────────────────────── */
.r-fotos-pair {
  display: grid; grid-template-columns: 1fr 1fr;
  border-top: 1px solid #f1f5f9;
}
.r-fotos-side { padding: 0.875rem; }
.r-fotos-side + .r-fotos-side { border-left: 1px solid #f1f5f9; }
.r-fotos-side-title {
  font-size: 0.6875rem; font-weight: 800; letter-spacing: 0.08em;
  text-transform: uppercase; color: #475569;
  display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.5rem;
}
.r-dot { width: 0.5rem; height: 0.5rem; border-radius: 9999px; display: inline-block; }
.r-dot-antes   { background: #f59e0b; }
.r-dot-despues { background: #10b981; }
.r-fotos-count {
  margin-left: auto; font-size: 0.625rem; font-weight: 700;
  color: #64748b; background: #f1f5f9; padding: 0.075rem 0.4rem; border-radius: 9999px;
}
.r-fotos-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; }
.r-fotos-empty {
  font-size: 0.7rem; color: #94a3b8; font-style: italic;
  text-align: center; padding: 0.5rem 0;
  border: 1px dashed #e2e8f0; border-radius: 0.4rem;
}
.r-fotos-none {
  text-align: center; font-size: 0.7rem; color: #94a3b8;
  font-style: italic; padding: 0.625rem; border-top: 1px solid #f1f5f9;
}

/* ── Fotos eventos (single grid) ─────────────────────────── */
.r-fotos-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.5rem;
  padding: 0.875rem; border-top: 1px solid #f1f5f9;
}
.r-foto-item { border-radius: 0.5rem; overflow: hidden; }
.r-foto-img  { width: 100%; aspect-ratio: 4/3; object-fit: cover; display: block; }

/* ── Tables ──────────────────────────────────────────────── */
.r-table {
  width: 100%; font-size: 0.8rem; border-collapse: collapse; margin-bottom: 0.5rem;
}
.r-table th {
  background: #1e3a5f; color: white; font-weight: 700; font-size: 0.6875rem;
  text-transform: uppercase; letter-spacing: 0.05em; padding: 0.5rem 0.75rem; text-align: left;
}
.r-table td {
  padding: 0.5rem 0.75rem; border-bottom: 1px solid #f1f5f9; color: #374151; vertical-align: top;
}
.r-table tr:last-child td { border-bottom: none; }
.r-table tr:nth-child(even) td { background: #f8fafc; }
.r-table-compact td { padding: 0.375rem 0.75rem; }
.r-td-label  { color: #374151; font-size: 0.8rem; }
.r-td-status { font-weight: 600; color: #1e3a5f; text-align: center; }

/* ── Signatures ──────────────────────────────────────────── */
.r-signatures {
  display: grid; grid-template-columns: 1fr 1fr; gap: 4rem;
  padding-top: 3rem; margin-top: 0.5rem;
}
.r-sig-col   { text-align: center; }
.r-sig-line  { border-top: 2px solid #cbd5e1; margin-bottom: 0.75rem; }
.r-sig-name  { font-weight: 700; font-size: 0.875rem; color: #1e3a5f; }
.r-sig-role  { font-size: 0.75rem; color: #64748b; margin-top: 0.25rem; }

/* ── Footer ──────────────────────────────────────────────── */
.r-footer {
  text-align: center; font-size: 0.6875rem; color: #94a3b8;
  border-top: 1px solid #f1f5f9; padding-top: 1rem; margin-top: 1.5rem;
}
</style>
