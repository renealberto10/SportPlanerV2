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
        <button class="btn btn-success" :disabled="generatingPdf" @click="printDoc">{{ generatingPdf ? "Generando PDF..." : "Descargar PDF" }}</button>
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
      <div class="report-page" v-for="esc in reportData.escenarios" :key="esc.id">

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

          <!-- ── RESUMEN EJECUTIVO (KPI cards) ─────────────────── -->
          <div class="r-section">
            <div class="r-section-title">Resumen Ejecutivo</div>
            <div class="r-kpi-grid">
              <div class="r-kpi r-kpi-blue">
                <div class="r-kpi-num">{{ mantsForEsc(esc.id).length }}</div>
                <div class="r-kpi-lbl">Visitas realizadas</div>
              </div>
              <div class="r-kpi r-kpi-indigo">
                <div class="r-kpi-num">{{ horasForEsc(esc.id) }}<span class="r-kpi-unit">h</span></div>
                <div class="r-kpi-lbl">Horas-hombre</div>
              </div>
              <div class="r-kpi r-kpi-emerald">
                <div class="r-kpi-num">{{ tecnicosForEsc(esc.id).length }}</div>
                <div class="r-kpi-lbl">Técnicos asignados</div>
              </div>
              <div class="r-kpi r-kpi-amber">
                <div class="r-kpi-num">{{ piezasForEsc(esc.id).length }}</div>
                <div class="r-kpi-lbl">Piezas reemplazadas</div>
              </div>
              <div class="r-kpi r-kpi-cyan">
                <div class="r-kpi-num">{{ mantsForEsc(esc.id).filter(m => m.estado === 'completado').length }}</div>
                <div class="r-kpi-lbl">Visitas completadas</div>
              </div>
              <div class="r-kpi r-kpi-rose">
                <div class="r-kpi-num">{{ totalFotosForEsc(esc.id) }}</div>
                <div class="r-kpi-lbl">Evidencias fotográficas</div>
              </div>
            </div>

            <!-- Distribución por tipo -->
            <div class="r-dist-grid">
              <div class="r-dist-bar">
                <div class="r-dist-label">
                  <span><span class="r-dot" style="background:#3b82f6"></span> Preventivo</span>
                  <span>{{ countTipo(esc.id, 'preventivo') }}</span>
                </div>
                <div class="r-bar-track">
                  <div class="r-bar-fill" :style="{width: pctTipo(esc.id, 'preventivo'), background:'#3b82f6'}"></div>
                </div>
              </div>
              <div class="r-dist-bar">
                <div class="r-dist-label">
                  <span><span class="r-dot" style="background:#ef4444"></span> Correctivo</span>
                  <span>{{ countTipo(esc.id, 'correctivo') }}</span>
                </div>
                <div class="r-bar-track">
                  <div class="r-bar-fill" :style="{width: pctTipo(esc.id, 'correctivo'), background:'#ef4444'}"></div>
                </div>
              </div>
              <div class="r-dist-bar">
                <div class="r-dist-label">
                  <span><span class="r-dot" style="background:#10b981"></span> Operativo</span>
                  <span>{{ countTipo(esc.id, 'operativo') }}</span>
                </div>
                <div class="r-bar-track">
                  <div class="r-bar-fill" :style="{width: pctTipo(esc.id, 'operativo'), background:'#10b981'}"></div>
                </div>
              </div>
            </div>
          </div>

          <!-- 1. INTRODUCCIÓN -->
          <div class="r-section">
            <div class="r-section-title">1. Introducción</div>
            <p class="r-paragraph">
              Durante el mes de <strong>{{ mesNombre(reportData.mes) }} de {{ reportData.anio }}</strong> se realizaron
              <strong>{{ mantsForEsc(esc.id).length }} visita(s) de mantenimiento</strong>
              {{ tiposRealizados(esc.id) }} en las instalaciones de <strong>{{ esc.nombre }}</strong>,
              a cargo de {{ config.contratista || 'ISATECH, S.A.S. de C.V.' }}.
              Se acumularon <strong>{{ horasForEsc(esc.id) }} horas-hombre</strong> de trabajo técnico
              <span v-if="piezasForEsc(esc.id).length">y se reemplazaron <strong>{{ piezasForEsc(esc.id).length }} pieza(s)</strong> con su respectivo registro de número de serie</span>.
            </p>
            <p class="r-paragraph">
              Las actividades desarrolladas estuvieron orientadas a garantizar la seguridad operativa,
              la continuidad del servicio y el correcto funcionamiento de los equipos de audio,
              pantallas digitales y cableado del escenario. Cada intervención cuenta con evidencia
              fotográfica del estado <em>antes</em> y <em>después</em> del trabajo realizado, sumando un
              total de <strong>{{ totalFotosForEsc(esc.id) }} evidencias gráficas</strong>
              ({{ totalFotosTipo(esc.id, 'antes') }} antes / {{ totalFotosTipo(esc.id, 'despues') }} después)
              que respaldan la trazabilidad y calidad de las labores ejecutadas.
            </p>
            <p class="r-paragraph" v-if="esc.descripcion">
              <strong>Descripción del escenario:</strong> {{ esc.descripcion }}
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
              <li v-if="hasType(esc.id,'operativo')">
                Soporte operativo: movimientos, montaje/desmontaje y configuración de equipos para eventos.
              </li>
              <li v-if="piezasForEsc(esc.id).length">
                Reemplazo de {{ piezasForEsc(esc.id).length }} pieza(s) con registro de número de serie y entrega a bodega.
              </li>
              <li>
                Documentación fotográfica de cada actividad y elaboración de bitácora técnica.
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
              <li>Validación de funcionamiento al cierre de cada intervención.</li>
            </ul>
          </div>

          <!-- 4. BITÁCORA RESUMIDA -->
          <div class="r-section">
            <div class="r-section-title">4. Bitácora del Período</div>
            <table class="r-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Hora</th>
                  <th>Tipo</th>
                  <th>Técnico</th>
                  <th class="text-center">Hrs</th>
                  <th>Estado</th>
                  <th class="text-center">Evid.</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="m in mantsForEsc(esc.id)" :key="'b'+m.id">
                  <td class="font-semibold text-slate-700 whitespace-nowrap">{{ fmtDate(m.fecha) }}</td>
                  <td class="text-xs text-slate-500">{{ m.hora || '—' }}</td>
                  <td><span :class="tipoBadge(m.tipo)">{{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}</span></td>
                  <td class="text-xs">{{ m.tecnico_obj?.nombre_completo || m.tecnico || '—' }}</td>
                  <td class="text-center font-semibold">{{ m.horas || 0 }}</td>
                  <td><span :class="estadoBadge(m.estado)">{{ m.estado }}</span></td>
                  <td class="text-center text-xs">{{ getFotos(m, 'antes').length + getFotos(m, 'despues').length }}</td>
                </tr>
                <tr v-if="!mantsForEsc(esc.id).length">
                  <td colspan="7" class="text-center text-slate-400 italic py-4">Sin visitas registradas en el período.</td>
                </tr>
              </tbody>
              <tfoot v-if="mantsForEsc(esc.id).length">
                <tr class="r-tfoot">
                  <td colspan="4" class="text-right font-bold">TOTALES</td>
                  <td class="text-center font-bold">{{ horasForEsc(esc.id) }}</td>
                  <td></td>
                  <td class="text-center font-bold">{{ totalFotosForEsc(esc.id) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          <!-- 5. DETALLE DE ACTIVIDADES + FOTOS ANTES/DESPUÉS -->
          <div class="r-section">
            <div class="r-section-title">5. Detalle de Actividades con Evidencia Fotográfica</div>

            <div v-if="!mantsForEsc(esc.id).length" class="r-paragraph text-slate-500 italic">
              Sin visitas registradas en el período.
            </div>

            <div v-for="(m, idx) in mantsForEsc(esc.id)" :key="m.id" class="r-mant-card">
              <div class="r-mant-header">
                <div class="r-mant-num">#{{ idx + 1 }}</div>
                <div class="r-mant-fecha">
                  {{ fmtDate(m.fecha) }}
                  <span v-if="m.hora" class="text-xs text-slate-500 font-normal ml-1">{{ m.hora }}</span>
                </div>
                <span :class="tipoBadge(m.tipo)">{{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}</span>
                <span :class="estadoBadge(m.estado)">{{ m.estado }}</span>
                <div class="r-mant-tec">Téc: {{ m.tecnico_obj?.nombre_completo || m.tecnico || '—' }}</div>
                <div class="r-mant-hrs" v-if="m.horas">{{ m.horas }}h</div>
              </div>

              <!-- Datos de la visita -->
              <div class="r-meta-row">
                <div class="r-meta-cell"><span class="r-meta-k">Fecha</span><span class="r-meta-v">{{ fmtDate(m.fecha) }}</span></div>
                <div class="r-meta-cell" v-if="m.hora"><span class="r-meta-k">Hora</span><span class="r-meta-v">{{ m.hora }}</span></div>
                <div class="r-meta-cell"><span class="r-meta-k">Tipo</span><span class="r-meta-v capitalize">{{ MANTENIMIENTO_TIPOS[m.tipo] || m.tipo }}</span></div>
                <div class="r-meta-cell" v-if="m.horas"><span class="r-meta-k">Duración</span><span class="r-meta-v">{{ m.horas }} hrs</span></div>
                <div class="r-meta-cell" v-if="m.personal"><span class="r-meta-k">Personal</span><span class="r-meta-v">{{ m.personal }}</span></div>
              </div>

              <div class="r-mant-body">
                <div class="r-mant-col">
                  <div class="r-mant-label">▸ Actividades realizadas</div>
                  <div class="r-mant-text">{{ m.actividades || '—' }}</div>
                </div>
                <div class="r-mant-col" v-if="m.observaciones">
                  <div class="r-mant-label">▸ Observaciones / Resultado</div>
                  <div class="r-mant-text">{{ m.observaciones }}</div>
                </div>
              </div>

              <!-- Piezas reemplazadas en esta visita -->
              <div v-if="piezasForMant(m.id).length" class="r-mant-piezas">
                <div class="r-mant-label" style="padding:0.625rem 0.875rem 0.25rem">▸ Piezas reemplazadas en esta visita</div>
                <table class="r-table r-table-mini">
                  <thead><tr><th>Pieza</th><th>Tipo</th><th>Serie nueva</th><th>Serie retirada</th></tr></thead>
                  <tbody>
                    <tr v-for="p in piezasForMant(m.id)" :key="p.id">
                      <td>{{ p.descripcion_pieza }}</td>
                      <td class="capitalize">{{ p.tipo_pieza?.replace('_',' ') }}</td>
                      <td class="font-mono text-xs">{{ p.serie_instalada || '—' }}</td>
                      <td class="font-mono text-xs">{{ p.serie_retirada || '—' }}</td>
                    </tr>
                  </tbody>
                </table>
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
          </div>

          <!-- 6. PIEZAS REEMPLAZADAS (consolidado) -->
          <div class="r-section" v-if="piezasForEsc(esc.id).length">
            <div class="r-section-title">6. Consolidado de Piezas Reemplazadas</div>
            <table class="r-table">
              <thead>
                <tr>
                  <th>Fecha</th>
                  <th>Pieza</th>
                  <th>Tipo</th>
                  <th>Equipo</th>
                  <th>Serie instalada</th>
                  <th>Serie retirada</th>
                  <th>Bodega</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in piezasForEsc(esc.id)" :key="p.id">
                  <td class="text-xs whitespace-nowrap">{{ fmtDate(p.fecha) }}</td>
                  <td>{{ p.descripcion_pieza }}</td>
                  <td class="capitalize">{{ p.tipo_pieza?.replace('_',' ') }}</td>
                  <td class="text-xs">{{ p.equipo?.nombre || '—' }}</td>
                  <td class="font-mono text-xs">{{ p.serie_instalada || '—' }}</td>
                  <td class="font-mono text-xs">{{ p.serie_retirada || '—' }}</td>
                  <td>
                    <span :class="p.estado_bodega === 'recibido' ? 'badge badge-green' : 'badge badge-yellow'">
                      {{ p.estado_bodega }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 8. RESULTADOS GLOBALES -->
          <div class="r-section">
            <div class="r-section-title">{{ resultsSectionNum(esc.id) }}. Resultados Globales</div>
            <table class="r-table r-table-compact">
              <tbody>
                <tr>
                  <td class="r-td-label">Visitas de mantenimiento realizadas</td>
                  <td class="font-bold text-center">{{ mantsForEsc(esc.id).length }}</td>
                  <td class="r-td-status">Completado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Mantenimientos preventivos</td>
                  <td class="font-bold text-center">{{ countTipo(esc.id, 'preventivo') }}</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Mantenimientos correctivos</td>
                  <td class="font-bold text-center">{{ countTipo(esc.id, 'correctivo') }}</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Soportes operativos</td>
                  <td class="font-bold text-center">{{ countTipo(esc.id, 'operativo') }}</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Total horas-hombre trabajadas</td>
                  <td class="font-bold text-center">{{ horasForEsc(esc.id) }} hrs</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Técnicos involucrados</td>
                  <td class="font-bold text-center">{{ tecnicosForEsc(esc.id).length }}</td>
                  <td class="r-td-status">Identificado</td>
                </tr>
                <tr v-if="piezasForEsc(esc.id).length">
                  <td class="r-td-label">Piezas reemplazadas</td>
                  <td class="font-bold text-center">{{ piezasForEsc(esc.id).length }}</td>
                  <td class="r-td-status">Registrado</td>
                </tr>
                <tr>
                  <td class="r-td-label">Fotos de evidencia (antes / después)</td>
                  <td class="font-bold text-center">{{ totalFotosTipo(esc.id, 'antes') }} / {{ totalFotosTipo(esc.id, 'despues') }} = {{ totalFotosForEsc(esc.id) }}</td>
                  <td class="r-td-status">Adjuntas</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- 9. ESTADO FINAL -->
          <div class="r-section">
            <div class="r-section-title">{{ resultsSectionNum(esc.id) + 1 }}. Estado Final de los Sistemas</div>
            <p class="r-paragraph">
              Concluidas las labores del mes de {{ mesNombre(reportData.mes) }} de {{ reportData.anio }},
              las instalaciones de <strong>{{ esc.nombre }}</strong> quedaron en condiciones operativas y seguras,
              cumpliendo con los estándares técnicos definidos en el contrato vigente.
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

          <!-- 10. CONCLUSIONES Y RECOMENDACIONES -->
          <div class="r-section">
            <div class="r-section-title">{{ resultsSectionNum(esc.id) + 2 }}. Conclusiones y Recomendaciones</div>
            <ul class="r-list">
              <li>
                Se cumplió la programación de mantenimiento del período sin incidentes que afectaran
                la disponibilidad de los sistemas críticos del escenario.
              </li>
              <li v-if="hasType(esc.id, 'correctivo')">
                Se atendieron de forma oportuna las intervenciones correctivas requeridas, restableciendo
                la operación normal de los equipos afectados.
              </li>
              <li v-if="piezasForEsc(esc.id).length">
                Se documentaron y registraron en bodega las {{ piezasForEsc(esc.id).length }} pieza(s)
                retirada(s), conservando trazabilidad por número de serie.
              </li>
              <li>
                Se recomienda mantener el calendario preventivo mensual para preservar la vida útil
                de los equipos y evitar fallas mayores.
              </li>
              <li>
                Se sugiere reforzar la limpieza de filtros y disipadores en pantallas digitales antes
                del próximo período de alta demanda de eventos.
              </li>
            </ul>
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

      <div class="text-center no-print mt-6 pb-6">
        <button class="btn btn-outline" @click="reportData = null">← Volver</button>
        <button class="btn btn-success ml-2" :disabled="generatingPdf" @click="printDoc">{{ generatingPdf ? "Generando PDF..." : "Descargar PDF" }}</button>
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
                <td class="text-center">{{ eventosForEsc(esc.id).filter((e) => e.estado === 'realizado').length }}</td>
                <td class="text-center">{{ eventosForEsc(esc.id).filter((e) => e.estado === 'programado').length }}</td>
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
      <div v-for="(esc, escIdx) in escenariosConEventos" :key="'e-'+esc.id"
           :class="['r-esc-block', escIdx > 0 ? 'page-break-before' : '']">
        <div class="report-page">
          <div class="r-header r-header-compact">
            <div class="r-header-top">
              <div class="r-logo-box">
                <div class="r-logo-mark">SP</div>
                <div>
                  <div class="r-logo-name">{{ esc.nombre }}</div>
                  <div class="r-logo-sub">Reporte de Eventos · {{ mesNombre(reportData.mes) }} {{ reportData.anio }}</div>
                </div>
              </div>
              <div class="r-header-meta">
                <div><span class="r-meta-label">Eventos:</span> {{ eventosForEsc(esc.id).length }}</div>
                <div><span class="r-meta-label">Realizados:</span> {{ eventosForEsc(esc.id).filter(e => e.estado === 'realizado').length }}</div>
                <div><span class="r-meta-label">Programados:</span> {{ eventosForEsc(esc.id).filter(e => e.estado === 'programado').length }}</div>
              </div>
            </div>
          </div>

          <!-- Tabla resumen del escenario -->
          <div class="r-section">
            <div class="r-section-title">Bitácora de eventos</div>
            <table class="r-table r-table-compact">
              <thead>
                <tr>
                  <th style="width:2.4rem">#</th>
                  <th style="width:5.2rem">Fecha</th>
                  <th style="width:3.2rem">Hora</th>
                  <th>Evento</th>
                  <th style="width:5rem">Tipo</th>
                  <th style="width:5.2rem">Estado</th>
                  <th class="text-center" style="width:3rem">Fotos</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(ev, i) in eventosForEsc(esc.id)" :key="'r-'+ev.id">
                  <td class="text-center">{{ i + 1 }}</td>
                  <td>{{ fmtDate(ev.fecha) }}</td>
                  <td>{{ ev.hora || '—' }}</td>
                  <td class="font-semibold">{{ ev.nombre }}</td>
                  <td class="capitalize">{{ ev.tipo }}</td>
                  <td><span :class="eventoEstadoBadge(ev.estado)">{{ ev.estado }}</span></td>
                  <td class="text-center">{{ getEventoFotos(ev).length }}</td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Detalle por evento -->
          <div class="r-section">
            <div class="r-section-title">Detalle de eventos</div>

            <div v-for="(ev, i) in eventosForEsc(esc.id)" :key="ev.id" class="r-ev-card">
              <div class="r-ev-head">
                <div class="r-ev-num">#{{ i + 1 }}</div>
                <div class="r-ev-title">{{ ev.nombre }}</div>
                <span :class="eventoEstadoBadge(ev.estado)">{{ ev.estado }}</span>
              </div>

              <div class="r-ev-meta">
                <div class="r-meta-cell"><div class="r-meta-k">Fecha</div><div class="r-meta-v">{{ fmtDate(ev.fecha) }}</div></div>
                <div class="r-meta-cell"><div class="r-meta-k">Hora</div><div class="r-meta-v">{{ ev.hora || '—' }}</div></div>
                <div class="r-meta-cell"><div class="r-meta-k">Tipo</div><div class="r-meta-v capitalize">{{ ev.tipo }}</div></div>
                <div class="r-meta-cell" v-if="ev.personal"><div class="r-meta-k">Personal técnico</div><div class="r-meta-v">{{ ev.personal }}</div></div>
              </div>

              <div v-if="ev.descripcion || ev.equipos_notas" class="r-ev-body">
                <div v-if="ev.descripcion">
                  <div class="r-mant-label">Descripción</div>
                  <div class="r-mant-text">{{ ev.descripcion }}</div>
                </div>
                <div v-if="ev.equipos_notas" class="mt-2">
                  <div class="r-mant-label">Equipos / Notas técnicas</div>
                  <div class="r-mant-text">{{ ev.equipos_notas }}</div>
                </div>
              </div>

              <div v-if="getEventoFotos(ev).length" class="r-ev-fotos">
                <div class="r-ev-fotos-title">Evidencia fotográfica ({{ getEventoFotos(ev).length }})</div>
                <div class="r-fotos-grid">
                  <div v-for="(f, fi) in getEventoFotos(ev)" :key="fi" class="r-foto-item">
                    <img :src="f.url" class="r-foto-img" />
                  </div>
                </div>
              </div>
            </div>

            <div v-if="!eventosForEsc(esc.id).length" class="r-paragraph text-slate-500 italic">
              Sin eventos registrados en este escenario para el período.
            </div>
          </div>

          <div class="r-footer">
            SportPlanner · {{ esc.nombre }} · {{ mesNombre(reportData.mes) }} {{ reportData.anio }}
          </div>
        </div>
      </div>

      <div class="text-center no-print mt-6 pb-6">
        <button class="btn btn-outline" @click="reportData = null">← Volver</button>
        <button class="btn btn-success ml-2" :disabled="generatingPdf" @click="printDoc">{{ generatingPdf ? "Generando PDF..." : "Descargar PDF" }}</button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed, onMounted } from 'vue'
// @ts-ignore — html2pdf.js no provee tipos oficiales
import html2pdf from 'html2pdf.js'
import { dashboardApi, escenarioApi } from '@/api'
import { useToastStore } from '@/stores/toast'
import { useApiError } from '@/composables/useApiError'
import { MESES, MANTENIMIENTO_TIPOS, fmtDate } from '@/constants'
import type { Escenario, Mantenimiento, Evento, CambioPieza, Equipo, MantenimientoFoto, EventoFoto } from '@/types'

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

// ── PDF generation (html2pdf.js) ──────────────────────────
const generatingPdf = ref(false)
const printDoc = async () => {
  const el = document.getElementById('reporte-doc')
  if (!el) { toast.error('No se encontró el reporte'); return }
  generatingPdf.value = true
  try {
    const periodo = `${mesNombre(reportData.value?.mes || 0)}_${reportData.value?.anio || ''}`
    const tipo    = reportData.value?.tipoReporte === 'eventos' ? 'Eventos' : 'Mantenimiento'
    const filename = `Reporte_${tipo}_${periodo}.pdf`.replace(/\s+/g, '_')

    await html2pdf()
      .set({
        margin: [10, 10, 12, 10], // mm: top, right, bottom, left
        filename,
        image:       { type: 'jpeg', quality: 0.95 },
        html2canvas: {
          scale: 2,
          useCORS: true,
          allowTaint: false,
          logging: false,
          backgroundColor: '#ffffff',
          windowWidth: 1180,
          // Saltar cualquier control / botón / bloque no-imprimible
          ignoreElements: (node: Element) =>
            node.classList?.contains('no-print') ||
            node.tagName === 'BUTTON',
          // Aplicar clase .pdf-mode al clon para reforzar bordes y contraste
          onclone: (doc: Document) => {
            const root = doc.getElementById('reporte-doc')
            if (root) root.classList.add('pdf-mode')
          },
        },
        jsPDF:       { unit: 'mm', format: 'a4', orientation: 'portrait' },
        pagebreak:   { mode: ['css', 'legacy'], before: '.page-break-before', avoid: ['.r-mant-card', '.r-foto-block', '.r-section', 'tr'] },
      } as any)
      .from(el)
      .save()
  } catch (e) {
    console.error(e)
    toast.error('Error generando el PDF')
  } finally {
    generatingPdf.value = false
  }
}

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
const piezasForMant     = (mantId: number): CambioPieza[] =>
  (reportData.value?.cambios_piezas || []).filter((p: CambioPieza) => p.mantenimiento_id === mantId)
const equiposForEsc     = (id: number): Equipo[] =>
  (reportData.value?.equipos || []).filter((e: Equipo) => e.escenario_id === id)
const hasType           = (id: number, tipo: string) =>
  mantsForEsc(id).some((m: Mantenimiento) => m.tipo === tipo)
const horasForEsc       = (id: number) =>
  mantsForEsc(id).reduce((s: number, m: Mantenimiento) => s + (m.horas || 0), 0)
const countTipo         = (id: number, tipo: string) =>
  mantsForEsc(id).filter((m: Mantenimiento) => m.tipo === tipo).length
const pctTipo           = (id: number, tipo: string) => {
  const total = mantsForEsc(id).length
  if (!total) return '0%'
  return Math.round((countTipo(id, tipo) / total) * 100) + '%'
}
const tecnicosForEsc    = (id: number) => {
  const set = new Set<string>()
  mantsForEsc(id).forEach((m: Mantenimiento) => {
    const name = m.tecnico_obj?.nombre_completo || m.tecnico
    if (name) set.add(name)
  })
  return [...set]
}
const tiposRealizados = (id: number) => {
  const tipos = [...new Set(mantsForEsc(id).map((m: Mantenimiento) => MANTENIMIENTO_TIPOS[m.tipo] || m.tipo))]
  return tipos.length ? `(${tipos.join(', ')})` : ''
}
const resultsSectionNum = (id: number) => {
  // Sections 1-5 fixed; 6 piezas (if any), then resultados.
  let n = 6
  if (piezasForEsc(id).length) n++
  return n
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

const totalFotosTipo = (id: number, tipo: 'antes' | 'despues') =>
  mantsForEsc(id).reduce((s, m) => s + getFotos(m, tipo).length, 0)

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

const equipoEstadoBadge = (estado: string) => ({
  operativo:      'badge badge-green',
  mantenimiento:  'badge badge-yellow',
  falla:          'badge badge-red',
  baja:           'badge badge-gray',
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
/* ══ Print rules ══════════════════════════════════════════ */
@page {
  size: A4 portrait;
  margin: 1.2cm 1.4cm;
}

@media print {
  /* Force colors/backgrounds */
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }

  html, body { background: white !important; margin: 0 !important; padding: 0 !important; }

  .no-print    { display: none !important; }
  .no-screen   { display: block !important; }
  .page-break  { page-break-after: always; break-after: page; }

  #reporte-doc { padding: 0 !important; margin: 0 !important; max-width: none !important; }

  .report-page {
    box-shadow: none !important;
    border: none !important;
    border-radius: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    max-width: none !important;
    width: 100% !important;
    page-break-after: always;
    break-after: page;
  }
  .report-page:last-child { page-break-after: auto; break-after: auto; }

  /* Avoid breaks inside critical blocks */
  .r-section,
  .r-mant-card,
  .r-foto-block,
  .r-foto-item,
  .r-kpi,
  .r-dist-bar,
  table, tr, .r-table { page-break-inside: avoid; break-inside: avoid; }

  thead { display: table-header-group; }
  tfoot { display: table-footer-group; }

  .r-section-title { page-break-after: avoid; break-after: avoid; }

  /* Tighter print typography */
  body, .report-page { font-size: 10.5pt; line-height: 1.35; }
  .r-section { margin-bottom: 0.8rem; }
  .r-paragraph { margin-bottom: 0.4rem; }

  /* Photos: keep crisp + fit */
  img { max-width: 100% !important; break-inside: avoid; }
  .r-foto-img, .r-foto img { max-height: 7cm; object-fit: cover; }

  /* Buttons / interactive */
  button, .btn { display: none !important; }
}

/* Hide print-only blocks on screen */
.no-screen { display: none; }

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
/* Page-break entre escenarios para html2pdf y print */
.report-page + .report-page {
  page-break-before: always;
  break-before: page;
}
/* Bloques de escenario también respetan el salto */
.r-esc-block + .r-esc-block,
.report-page + .r-esc-block {
  page-break-before: always;
  break-before: page;
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

/* ── KPI grid (resumen ejecutivo) ────────────────────────── */
.r-kpi-grid {
  display: grid; grid-template-columns: repeat(3, 1fr); gap: 0.625rem;
  margin-bottom: 1rem;
}
.r-kpi {
  border-radius: 0.625rem; padding: 0.75rem 0.875rem;
  border: 1px solid; display: flex; flex-direction: column; justify-content: center;
}
.r-kpi-num { font-size: 1.5rem; font-weight: 800; line-height: 1; letter-spacing: -0.02em; }
.r-kpi-unit { font-size: 0.875rem; font-weight: 600; margin-left: 0.125rem; opacity: 0.8; }
.r-kpi-lbl {
  font-size: 0.6875rem; font-weight: 600; text-transform: uppercase;
  letter-spacing: 0.04em; margin-top: 0.25rem; opacity: 0.85;
}
.r-kpi-blue    { background:#eff6ff; border-color:#bfdbfe; color:#1e40af; }
.r-kpi-indigo  { background:#eef2ff; border-color:#c7d2fe; color:#3730a3; }
.r-kpi-emerald { background:#ecfdf5; border-color:#a7f3d0; color:#065f46; }
.r-kpi-amber   { background:#fffbeb; border-color:#fde68a; color:#92400e; }
.r-kpi-cyan    { background:#ecfeff; border-color:#a5f3fc; color:#155e75; }
.r-kpi-rose    { background:#fff1f2; border-color:#fecdd3; color:#9f1239; }

/* Distribución por tipo */
.r-dist-grid {
  background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 0.625rem;
  padding: 0.75rem 0.875rem;
  display: grid; gap: 0.5rem;
}
.r-dist-bar { font-size: 0.75rem; }
.r-dist-label {
  display: flex; justify-content: space-between; align-items: center;
  font-weight: 600; color: #374151; margin-bottom: 0.2rem;
}
.r-dist-label .r-dot { margin-right: 0.4rem; }
.r-bar-track { height: 0.4rem; background:#e2e8f0; border-radius:9999px; overflow:hidden; }
.r-bar-fill  { height:100%; border-radius:9999px; transition: width 0.3s; }

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
.r-mant-num   {
  font-size: 0.6875rem; font-weight: 800; color: white;
  background: #1e3a5f; padding: 0.125rem 0.5rem; border-radius: 0.4rem;
  letter-spacing: 0.05em;
}
.r-mant-tec   { font-size: 0.75rem; color: #64748b; margin-left: auto; }
.r-mant-hrs   { font-size: 0.75rem; font-weight: 700; color: #1e3a5f; background: #dbeafe; padding: 0.125rem 0.5rem; border-radius: 9999px; }
.r-mant-body  { padding: 0.875rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.r-mant-label { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; margin-bottom: 0.25rem; }
.r-mant-text  { font-size: 0.8rem; color: #374151; line-height: 1.6; white-space: pre-line; }
.r-mant-col:only-child { grid-column: 1 / -1; }

/* Meta-row (datos clave de la visita) */
.r-meta-row {
  display: flex; flex-wrap: wrap; gap: 0; background: #fafbfc;
  border-bottom: 1px solid #f1f5f9;
}
.r-meta-cell {
  flex: 1 1 auto; padding: 0.5rem 0.75rem; min-width: 9rem;
  border-right: 1px solid #f1f5f9; display: flex; flex-direction: column;
}
.r-meta-cell:last-child { border-right: none; }
.r-meta-k { font-size: 0.625rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: #94a3b8; }
.r-meta-v { font-size: 0.75rem; color: #1e3a5f; font-weight: 600; margin-top: 0.125rem; }

/* Mini-table embedded in card (piezas por visita) */
.r-mant-piezas { padding: 0 0.875rem 0.75rem; background: #fafbfc; border-bottom: 1px solid #f1f5f9; }
.r-table-mini {
  margin: 0; font-size: 0.7rem;
}
.r-table-mini th { background: #475569; padding: 0.3rem 0.5rem; font-size: 0.625rem; }
.r-table-mini td { padding: 0.3rem 0.5rem; }

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
.r-tfoot td { background: #f1f5f9 !important; color: #1e3a5f; padding-top: 0.5rem; padding-bottom: 0.5rem; }
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

/* ── Eventos: header compacto ────────────────────────────── */
.r-header-compact {
  padding-bottom: 0.875rem;
  margin-bottom: 1.25rem;
}
.r-header-compact .r-header-top { margin-bottom: 0; align-items: center; }
.r-header-compact .r-header-meta {
  display: flex; gap: 1.25rem; font-size: 0.75rem;
}

/* ── Eventos: card detalle ───────────────────────────────── */
.r-ev-card {
  border: 1px solid #cbd5e1; border-radius: 0.5rem;
  margin-bottom: 0.75rem; overflow: hidden;
  page-break-inside: avoid; break-inside: avoid;
}
.r-ev-head {
  display: flex; align-items: center; gap: 0.625rem;
  background: #1e3a5f; color: #ffffff;
  padding: 0.5rem 0.75rem;
}
.r-ev-num {
  font-size: 0.7rem; font-weight: 800;
  background: rgba(255,255,255,0.18); color: #ffffff;
  padding: 0.125rem 0.5rem; border-radius: 0.3rem;
  letter-spacing: 0.05em;
}
.r-ev-title {
  flex: 1; font-size: 0.875rem; font-weight: 700; color: #ffffff;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.r-ev-head .badge {
  background: #ffffff; color: #1e3a5f; border: 1px solid #ffffff;
}

.r-ev-meta {
  display: flex; flex-wrap: wrap;
  background: #f8fafc; border-bottom: 1px solid #e2e8f0;
}
.r-ev-meta .r-meta-cell {
  flex: 1 1 25%; min-width: 25%;
  padding: 0.4rem 0.75rem;
  border-right: 1px solid #e2e8f0;
}
.r-ev-meta .r-meta-cell:last-child { border-right: none; }

.r-ev-body {
  padding: 0.625rem 0.75rem;
  border-bottom: 1px solid #f1f5f9;
}

.r-ev-fotos { padding: 0.625rem 0.75rem; }
.r-ev-fotos-title {
  font-size: 0.65rem; font-weight: 700; text-transform: uppercase;
  letter-spacing: 0.06em; color: #64748b; margin-bottom: 0.4rem;
}
.r-ev-fotos .r-fotos-grid {
  padding: 0; border-top: none;
  grid-template-columns: repeat(3, 1fr); gap: 0.4rem;
}
.r-ev-fotos .r-foto-img {
  aspect-ratio: 4/3; max-height: 4.2cm;
}

/* Page-break helper para html2pdf (config: pagebreak.before) */
.page-break-before { page-break-before: always; break-before: page; }

/* ════════════════════════════════════════════════════════════
   PDF MODE — aplicado por html2pdf.js en el clon (onclone).
   Refuerza bordes, oscurece separadores y oculta cualquier
   control para que el PDF se vea como un documento impreso.
   ════════════════════════════════════════════════════════════ */
.pdf-mode {
  background: #ffffff !important;
}
.pdf-mode .no-print,
.pdf-mode button,
.pdf-mode .btn { display: none !important; }

/* Quitar sombras / radios suaves que html2canvas renderiza feo */
.pdf-mode .report-page {
  box-shadow: none !important;
  border: none !important;
  border-radius: 0 !important;
  padding: 0 !important;
  margin: 0 0 8mm 0 !important;
  max-width: none !important;
  width: 100% !important;
}

/* Eventos en PDF: bordes definidos */
.pdf-mode .r-ev-card {
  border: 1px solid #475569 !important;
  border-radius: 4px !important;
  margin-bottom: 8px !important;
}
.pdf-mode .r-ev-head {
  background: #1e3a5f !important;
  color: #ffffff !important;
}
.pdf-mode .r-ev-meta {
  background: #f1f5f9 !important;
  border-bottom: 1px solid #94a3b8 !important;
}
.pdf-mode .r-ev-meta .r-meta-cell {
  border-right: 1px solid #cbd5e1 !important;
}
.pdf-mode .r-ev-body {
  border-bottom: 1px solid #cbd5e1 !important;
}

/* Bordes más oscuros y consistentes */
.pdf-mode .r-mant-card {
  border: 1px solid #94a3b8 !important;
  border-radius: 4px !important;
  margin-bottom: 10px !important;
}
.pdf-mode .r-mant-header {
  background: #eef2f7 !important;
  border-bottom: 1px solid #94a3b8 !important;
}
.pdf-mode .r-mant-piezas,
.pdf-mode .r-meta-row,
.pdf-mode .r-fotos-pair,
.pdf-mode .r-fotos-grid,
.pdf-mode .r-fotos-none {
  border-top: 1px solid #cbd5e1 !important;
}
.pdf-mode .r-fotos-side + .r-fotos-side {
  border-left: 1px solid #cbd5e1 !important;
}
.pdf-mode .r-meta-cell {
  border-right: 1px solid #cbd5e1 !important;
}

/* Tablas con bordes visibles en TODAS las celdas */
.pdf-mode .r-table {
  border: 1px solid #1e3a5f !important;
}
.pdf-mode .r-table th {
  background: #1e3a5f !important;
  color: #ffffff !important;
  border: 1px solid #1e3a5f !important;
}
.pdf-mode .r-table td {
  border: 1px solid #cbd5e1 !important;
  background: #ffffff !important;
}
.pdf-mode .r-table tr:nth-child(even) td {
  background: #f1f5f9 !important;
}
.pdf-mode .r-tfoot td {
  background: #e2e8f0 !important;
  font-weight: 700 !important;
}

/* KPI con bordes más definidos */
.pdf-mode .r-kpi {
  border-width: 1.5px !important;
}

/* Header con doble línea para que se vea sólido */
.pdf-mode .r-header {
  border-bottom: 3px solid #1e3a5f !important;
}
.pdf-mode .r-header-fields {
  background: #f1f5f9 !important;
  border: 1px solid #cbd5e1 !important;
}

/* Distribución por tipo */
.pdf-mode .r-dist-grid {
  background: #f8fafc !important;
  border: 1px solid #cbd5e1 !important;
}
.pdf-mode .r-bar-track {
  background: #cbd5e1 !important;
}

/* Section title más legible */
.pdf-mode .r-section-title {
  border-left: 4px solid #1e3a5f !important;
  background: #f8fafc;
  padding: 4px 0 4px 10px;
}

/* Subsection y separadores */
.pdf-mode .r-subsection-title {
  border-bottom: 1px solid #94a3b8 !important;
}

/* Fotos con borde sutil para que no “floten” */
.pdf-mode .r-foto-item,
.pdf-mode .r-foto-img {
  border: 1px solid #cbd5e1 !important;
  border-radius: 3px !important;
}

/* Firmas con líneas oscuras */
.pdf-mode .r-sig-line {
  border-top: 2px solid #475569 !important;
}

/* Footer separador visible */
.pdf-mode .r-footer {
  border-top: 1px solid #94a3b8 !important;
  color: #475569 !important;
}
</style>
