<template>
  <div class="landing">

    <!-- ── Nav ────────────────────────────────────────────── -->
    <nav class="landing-nav">
      <div class="flex items-center gap-3">
        <div class="nav-logo">
          <span>SP</span>
        </div>
        <span class="nav-brand">SportPlanner</span>
      </div>
      <div class="flex items-center gap-3">
        <a href="#features" class="hidden sm:block text-sm text-slate-400 hover:text-white transition-colors">Funciones</a>
        <a href="#pricing" class="hidden sm:block text-sm text-slate-400 hover:text-white transition-colors">Soluciones</a>
        <RouterLink to="/login" class="nav-cta">Ingresar →</RouterLink>
      </div>
    </nav>

    <!-- ── Hero ───────────────────────────────────────────── -->
    <section class="hero-section">
      <!-- Three.js canvas -->
      <HeroCanvas aria-hidden="true" />
      <!-- Background grid -->
      <div class="hero-grid" aria-hidden="true"></div>
      <!-- Glows -->
      <div class="glow glow-blue" aria-hidden="true"></div>
      <div class="glow glow-purple" aria-hidden="true"></div>

      <div class="hero-inner">
        <!-- Pill badge -->
        <div class="hero-pill">
          <span class="pill-dot"></span>
          Plataforma integral · Gestión deportiva
        </div>

        <h1 class="hero-h1">
          Control total de tus<br />
          <span class="hero-gradient">instalaciones deportivas</span>
        </h1>

        <p class="hero-desc">
          Centraliza mantenimiento, eventos, solicitudes e inventario.<br class="hidden md:block" />
          Tu equipo técnico conectado, desde oficina o campo.
        </p>

        <div class="hero-actions">
          <RouterLink to="/login" class="btn-hero-primary">
            Comenzar ahora
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
          </RouterLink>
          <a href="#features" class="btn-hero-secondary">Ver funcionalidades</a>
        </div>

        <!-- Trusted by strip -->
        <div class="hero-trust">
          <span class="trust-label">Diseñado para</span>
          <div class="trust-items">
            <span v-for="t in trusted" :key="t" class="trust-item">{{ t }}</span>
          </div>
        </div>
      </div>

      <!-- Dashboard preview -->
      <div class="hero-preview">
        <div class="preview-bar">
          <span class="preview-dot red"></span>
          <span class="preview-dot yellow"></span>
          <span class="preview-dot green"></span>
          <span class="preview-url">app.sportplanner.io / dashboard</span>
        </div>
        <div class="preview-body">
          <!-- Fake dashboard -->
          <div class="fake-sidebar">
            <div v-for="i in 6" :key="i" class="fake-nav-item" :class="i === 1 ? 'active' : ''"></div>
          </div>
          <div class="fake-main">
            <div class="fake-stats-row">
              <div v-for="(s, i) in previewStats" :key="i" class="fake-stat">
                <div class="fake-stat-num">{{ s.val }}</div>
                <div class="fake-stat-label">{{ s.label }}</div>
              </div>
            </div>
            <div class="fake-cards">
              <div class="fake-card tall">
                <div class="fake-card-title"></div>
                <div class="fake-bars">
                  <div v-for="(b, i) in bars" :key="i" class="fake-bar-wrap">
                    <div class="fake-bar" :style="{ height: b+'px' }"></div>
                  </div>
                </div>
              </div>
              <div class="fake-card">
                <div class="fake-card-title"></div>
                <div class="fake-list">
                  <div v-for="i in 4" :key="i" class="fake-list-item">
                    <div class="fake-avatar"></div>
                    <div class="fake-lines">
                      <div class="fake-line w-24"></div>
                      <div class="fake-line w-16 short"></div>
                    </div>
                    <div class="fake-badge"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Stats banner ────────────────────────────────────── -->
    <div class="stats-banner">
      <div class="stats-inner">
        <div v-for="s in statsBanner" :key="s.label" class="stat-item">
          <div class="stat-val">{{ s.val }}</div>
          <div class="stat-lbl">{{ s.label }}</div>
        </div>
      </div>
    </div>

    <!-- ── Features ───────────────────────────────────────── -->
    <section id="features" class="features-section">
      <div class="section-inner">
        <div class="section-eyebrow">Funcionalidades</div>
        <h2 class="section-h2">Todo lo que necesitas, en un solo sistema</h2>
        <p class="section-sub">Desde el registro de mantenimientos hasta la generación de reportes con evidencia fotográfica.</p>

        <div class="features-grid">
          <div v-for="f in features" :key="f.title" class="feature-card">
            <div class="feature-icon-wrap">
              <component :is="f.icon" class="w-5 h-5 text-white" />
            </div>
            <h3 class="feature-title">{{ f.title }}</h3>
            <p class="feature-desc">{{ f.desc }}</p>
            <ul class="feature-list">
              <li v-for="item in f.items" :key="item">{{ item }}</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Mobile highlight ────────────────────────────────── -->
    <section class="mobile-section">
      <div class="section-inner mobile-inner">
        <div class="mobile-text">
          <div class="section-eyebrow">Campo + Oficina</div>
          <h2 class="section-h2 text-left">Tu equipo trabaja en el campo, el sistema trabaja contigo</h2>
          <p class="section-sub text-left">Los técnicos capturan fotos, completan formularios y registran piezas directamente desde su teléfono. Sin papel, sin demoras.</p>
          <ul class="mobile-bullets">
            <li v-for="b in mobileBullets" :key="b.text">
              <span class="bullet-icon">✓</span>
              {{ b.text }}
            </li>
          </ul>
        </div>
        <div class="mobile-phone-wrap">
          <div class="mobile-phone">
            <div class="phone-notch"></div>
            <div class="phone-screen">
              <div class="phone-header">
                <div class="phone-title">Mantenimiento</div>
                <div class="phone-sub">Formulario de campo</div>
              </div>
              <div class="phone-field">
                <div class="pf-label">Escenario</div>
                <div class="pf-value">Estadio Municipal</div>
              </div>
              <div class="phone-field">
                <div class="pf-label">Tipo</div>
                <div class="pf-chips">
                  <span class="pf-chip active">Preventivo</span>
                  <span class="pf-chip">Correctivo</span>
                  <span class="pf-chip">Operativo</span>
                </div>
              </div>
              <div class="phone-field">
                <div class="pf-label">Actividades</div>
                <div class="pf-textarea">Revisión de pantallas LED, limpieza de módulos frontales...</div>
              </div>
              <div class="phone-field">
                <div class="pf-label">Evidencia fotográfica</div>
                <div class="pf-photos">
                  <div v-for="i in 3" :key="i" class="pf-photo" :style="{ opacity: i < 3 ? 1 : 0.5 }"></div>
                  <div class="pf-photo-add">+</div>
                </div>
              </div>
              <div class="phone-save-btn">Guardar registro</div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── Workflow ────────────────────────────────────────── -->
    <section id="pricing" class="workflow-section">
      <div class="section-inner">
        <div class="section-eyebrow">Flujo de trabajo</div>
        <h2 class="section-h2">De la solicitud al reporte, sin fricción</h2>
        <div class="workflow-steps">
          <div v-for="(step, i) in workflow" :key="step.title" class="workflow-step">
            <div class="step-num">{{ i + 1 }}</div>
            <div class="step-title">{{ step.title }}</div>
            <div class="step-desc">{{ step.desc }}</div>
            <div v-if="i < workflow.length - 1" class="step-arrow">→</div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── CTA ────────────────────────────────────────────── -->
    <section class="cta-section">
      <div class="cta-glow"></div>
      <div class="cta-inner">
        <h2 class="cta-h2">Lleva la gestión de tus<br />instalaciones al siguiente nivel</h2>
        <p class="cta-sub">Una plataforma pensada para equipos que operan instalaciones deportivas de alto rendimiento.</p>
        <RouterLink to="/login" class="btn-hero-primary btn-cta-lg">
          Acceder al sistema
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </RouterLink>
      </div>
    </section>

    <!-- ── Footer ─────────────────────────────────────────── -->
    <footer class="landing-footer">
      <div class="footer-inner">
        <div class="flex items-center gap-2.5">
          <div class="nav-logo nav-logo-sm"><span>SP</span></div>
          <span class="text-sm font-semibold text-white">SportPlanner</span>
        </div>
        <span class="text-xs text-slate-600">© 2026 SportPlanner · Todos los derechos reservados</span>
      </div>
    </footer>

  </div>
</template>

<script setup lang="ts">
import HeroCanvas from '@/components/HeroCanvas.vue'
import {
  WrenchScrewdriverIcon,
  StarIcon,
  ClipboardDocumentListIcon,
  ArchiveBoxIcon,
  DevicePhoneMobileIcon,
  DocumentChartBarIcon,
} from '@heroicons/vue/24/outline'

const trusted = ['Federaciones deportivas', 'Municipios', 'Estadios', 'Complejos polideportivos']

const previewStats = [
  { val: '24', label: 'Escenarios' },
  { val: '8',  label: 'Técnicos' },
  { val: '97%', label: 'Uptime' },
  { val: '12', label: 'Eventos/mes' },
]
const bars = [40, 65, 48, 80, 55, 90, 70, 60, 85, 72]

const statsBanner = [
  { val: '500+', label: 'Registros de mantenimiento' },
  { val: '100%', label: 'Trazabilidad de piezas' },
  { val: '12+',  label: 'Módulos integrados' },
  { val: '24/7', label: 'Acceso desde cualquier dispositivo' },
]

const features = [
  {
    icon: WrenchScrewdriverIcon,
    title: 'Bitácora de Mantenimiento',
    desc: 'Registra cada visita técnica con toda la información necesaria, incluyendo evidencia fotográfica.',
    items: ['Fotos desde cámara o galería', 'Horas y estado de trabajo', 'Historial por escenario'],
  },
  {
    icon: StarIcon,
    title: 'Gestión de Eventos',
    desc: 'Controla todos los eventos deportivos y culturales con el personal y equipos asignados.',
    items: ['Programación y estado', 'Personal técnico asignado', 'Equipos utilizados'],
  },
  {
    icon: ClipboardDocumentListIcon,
    title: 'Solicitudes de Servicio',
    desc: 'Administra las solicitudes de tu organización con prioridad, seguimiento y asignación de técnico.',
    items: ['Prioridad Alta / Media / Baja', 'Asignación de técnico', 'Estado en tiempo real'],
  },
  {
    icon: ArchiveBoxIcon,
    title: 'Control de Bodega',
    desc: 'Gestiona cambio de piezas con números de serie, desde la solicitud hasta la confirmación de recepción.',
    items: ['Serie instalada y retirada', 'Confirmación de bodega', 'Historial completo'],
  },
  {
    icon: DevicePhoneMobileIcon,
    title: 'Formularios de Campo',
    desc: 'Captura de datos optimizada para móvil con cámara integrada. Sin conexión a escritorio necesaria.',
    items: ['Cámara integrada', 'Diseño táctil', 'Funciona en Android e iOS'],
  },
  {
    icon: DocumentChartBarIcon,
    title: 'Reportes Profesionales',
    desc: 'Genera informes mensuales completos con fotos y datos, listos para exportar en Word o PDF.',
    items: ['Exportación Word y PDF', 'Fotos por escenario', 'Firma y datos de contrato'],
  },
]

const mobileBullets = [
  { text: 'Formularios optimizados para pantallas pequeñas' },
  { text: 'Acceso directo a cámara del dispositivo' },
  { text: 'Registro de piezas con números de serie' },
  { text: 'Sincronización automática al servidor' },
]

const workflow = [
  { title: 'Solicitud',  desc: 'El cliente o equipo registra una solicitud de servicio con prioridad y detalle.' },
  { title: 'Asignación', desc: 'El administrador asigna el técnico más adecuado y fija la fecha.' },
  { title: 'Ejecución',  desc: 'El técnico completa el formulario en campo con fotos y actividades realizadas.' },
  { title: 'Bodega',     desc: 'Las piezas cambiadas se registran y el encargado confirma su recepción.' },
  { title: 'Reporte',    desc: 'Se genera el informe mensual listo para entregar al cliente con toda la evidencia.' },
]
</script>

<style scoped>
/* ── Design tokens ─────────────────────────────────────── */
/* Single accent: a sophisticated indigo-blue */
/* --a: #5b78f6  --a-dim: rgba(91,120,246,0.15)  --a-glow: rgba(91,120,246,0.3) */

/* ── Base ──────────────────────────────────────────────── */
.landing {
  min-height: 100vh;
  background: #050810;
  color: white;
  overflow-x: hidden;
}

/* ── Nav ───────────────────────────────────────────────── */
.landing-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 2rem;
  position: sticky; top: 0; z-index: 50;
  background: rgba(5,8,16,0.85);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.nav-logo {
  width: 2rem; height: 2rem;
  border-radius: 0.5rem;
  background: linear-gradient(135deg,#5b78f6,#7c8ff8);
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 0.7rem; color: white;
  box-shadow: 0 0 14px rgba(91,120,246,0.4);
  flex-shrink: 0;
}
.nav-logo-sm { width: 1.5rem; height: 1.5rem; border-radius: 0.375rem; font-size: 0.55rem; }
.nav-brand { font-size: 0.9375rem; font-weight: 800; color: white; letter-spacing: -0.01em; }
.nav-cta {
  display: inline-flex; align-items: center;
  padding: 0.5rem 1.125rem;
  border-radius: 9999px;
  font-size: 0.8125rem; font-weight: 600; color: white;
  background: rgba(91,120,246,0.15);
  border: 1px solid rgba(91,120,246,0.35);
  text-decoration: none;
  transition: all 0.15s;
}
.nav-cta:hover { background: rgba(91,120,246,0.25); border-color: rgba(91,120,246,0.6); transform: translateY(-1px); }

/* ── Hero ──────────────────────────────────────────────── */
.hero-section {
  position: relative;
  padding: 7rem 1.5rem 5rem;
  text-align: center;
  overflow: hidden;
  min-height: 90vh;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}
.hero-grid {
  position: absolute; inset: 0; z-index: 0;
  background-image:
    linear-gradient(rgba(255,255,255,0.018) 1px, transparent 1px),
    linear-gradient(90deg, rgba(255,255,255,0.018) 1px, transparent 1px);
  background-size: 60px 60px;
  mask-image: radial-gradient(ellipse 80% 60% at 50% 0%, black 40%, transparent 100%);
}
.glow {
  position: absolute; border-radius: 50%;
  filter: blur(90px); pointer-events: none; z-index: 0;
}
.glow-blue {
  width: 700px; height: 500px;
  top: -120px; left: 50%; transform: translateX(-70%);
  background: rgba(91,120,246,0.1);
}
.glow-purple {
  width: 600px; height: 500px;
  top: -80px; right: -120px;
  background: rgba(91,120,246,0.07);
}

.hero-inner { position: relative; z-index: 2; max-width: 780px; margin: 0 auto; }

.hero-pill {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.375rem 1rem;
  border-radius: 9999px;
  font-size: 0.75rem; font-weight: 500; color: #a5b4fc;
  background: rgba(91,120,246,0.08);
  border: 1px solid rgba(91,120,246,0.22);
  margin-bottom: 1.75rem;
}
.pill-dot {
  width: 6px; height: 6px; border-radius: 50%;
  background: #5b78f6;
  box-shadow: 0 0 6px #5b78f6;
  animation: pulse 2s infinite;
}
@keyframes pulse { 0%,100% { opacity:1 } 50% { opacity:.4 } }

.hero-h1 {
  font-size: clamp(2.25rem, 6vw, 4rem);
  font-weight: 900;
  line-height: 1.08;
  letter-spacing: -0.035em;
  color: white;
  margin-bottom: 1.25rem;
}
.hero-gradient {
  background: linear-gradient(135deg, #93a8fb 0%, #c4b8fc 100%);
  -webkit-background-clip: text; -webkit-text-fill-color: transparent;
  background-clip: text;
}
.hero-desc {
  font-size: 1.0625rem; color: #64748b; line-height: 1.75;
  max-width: 520px; margin: 0 auto 2.25rem;
}

.hero-actions { display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap; margin-bottom: 2.5rem; }
.btn-hero-primary {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.875rem 1.875rem;
  border-radius: 0.875rem;
  background: #5b78f6;
  color: white; font-size: 0.9375rem; font-weight: 700;
  text-decoration: none;
  box-shadow: 0 4px 24px rgba(91,120,246,0.35), 0 1px 0 rgba(255,255,255,0.12) inset;
  transition: all 0.2s;
}
.btn-hero-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(91,120,246,0.5); }
.btn-hero-secondary {
  display: inline-flex; align-items: center; gap: 0.5rem;
  padding: 0.875rem 1.875rem;
  border-radius: 0.875rem;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.1);
  color: #94a3b8; font-size: 0.9375rem; font-weight: 600;
  text-decoration: none;
  transition: all 0.2s;
}
.btn-hero-secondary:hover { background: rgba(255,255,255,0.08); color: white; }

.hero-trust { display: flex; align-items: center; gap: 1rem; justify-content: center; flex-wrap: wrap; }
.trust-label { font-size: 0.75rem; color: #475569; white-space: nowrap; }
.trust-items { display: flex; gap: 0.5rem; flex-wrap: wrap; justify-content: center; }
.trust-item {
  font-size: 0.75rem; font-weight: 500; color: #64748b;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.07);
}

/* ── Dashboard preview ─────────────────────────────────── */
.hero-preview {
  position: relative; z-index: 2;
  margin-top: 4rem;
  border-radius: 1rem;
  border: 1px solid rgba(255,255,255,0.08);
  overflow: hidden;
  box-shadow: 0 40px 100px -20px rgba(0,0,0,0.7), 0 0 0 1px rgba(255,255,255,0.05), 0 0 60px rgba(99,102,241,0.08);
  background: #0d1117;
  max-width: 900px;
  margin-left: auto; margin-right: auto;
}
.preview-bar {
  display: flex; align-items: center; gap: 0.375rem;
  padding: 0.625rem 1rem;
  background: #161b22;
  border-bottom: 1px solid rgba(255,255,255,0.06);
}
.preview-dot { width: 10px; height: 10px; border-radius: 50%; }
.preview-dot.red    { background: #ff5f57; }
.preview-dot.yellow { background: #febc2e; }
.preview-dot.green  { background: #28c840; }
.preview-url { margin-left: 0.75rem; font-size: 0.6875rem; color: #475569; font-family: monospace; }
.preview-body { display: flex; min-height: 220px; }

.fake-sidebar {
  width: 48px; background: #0d1117;
  border-right: 1px solid rgba(255,255,255,0.05);
  padding: 0.75rem 0.5rem;
  display: flex; flex-direction: column; gap: 0.5rem;
}
.fake-nav-item {
  height: 24px; border-radius: 0.375rem;
  background: rgba(255,255,255,0.05);
}
.fake-nav-item.active { background: rgba(91,120,246,0.25); }

.fake-main { flex: 1; padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
.fake-stats-row { display: flex; gap: 0.5rem; }
.fake-stat {
  flex: 1; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 0.625rem; padding: 0.625rem 0.75rem;
}
.fake-stat-num { font-size: 1.125rem; font-weight: 700; line-height: 1; color: white; }
.fake-stat-label { font-size: 0.5rem; color: #475569; margin-top: 0.25rem; }

.fake-cards { display: flex; gap: 0.5rem; flex: 1; }
.fake-card {
  flex: 1; background: rgba(255,255,255,0.03);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 0.625rem; padding: 0.75rem;
}
.fake-card.tall { flex: 1.2; }
.fake-card-title { height: 6px; width: 80px; background: rgba(255,255,255,0.08); border-radius: 9999px; margin-bottom: 0.75rem; }

.fake-bars { display: flex; align-items: flex-end; gap: 4px; height: 70px; }
.fake-bar-wrap { flex: 1; display: flex; align-items: flex-end; }
.fake-bar { width: 100%; border-radius: 3px 3px 0 0; background: linear-gradient(to top, #3d5af1, #6f85f7); }

.fake-list { display: flex; flex-direction: column; gap: 0.5rem; }
.fake-list-item { display: flex; align-items: center; gap: 0.5rem; }
.fake-avatar { width: 20px; height: 20px; border-radius: 50%; background: rgba(91,120,246,0.25); flex-shrink: 0; }
.fake-lines { flex: 1; display: flex; flex-direction: column; gap: 3px; }
.fake-line { height: 6px; background: rgba(255,255,255,0.07); border-radius: 9999px; }
.fake-line.w-24 { width: 96px; }
.fake-line.w-16 { width: 64px; }
.fake-line.short { background: rgba(255,255,255,0.04); }
.fake-badge { width: 36px; height: 14px; border-radius: 9999px; background: rgba(91,120,246,0.18); }

/* ── Stats banner ──────────────────────────────────────── */
.stats-banner {
  border-top: 1px solid rgba(255,255,255,0.05);
  border-bottom: 1px solid rgba(255,255,255,0.05);
  background: rgba(255,255,255,0.015);
}
.stats-inner {
  max-width: 900px; margin: 0 auto;
  display: flex; flex-wrap: wrap;
}
.stat-item {
  flex: 1; min-width: 180px;
  padding: 2rem 1.5rem;
  text-align: center;
  border-right: 1px solid rgba(255,255,255,0.05);
}
.stat-item:last-child { border-right: none; }
.stat-val { font-size: 2rem; font-weight: 800; color: white; letter-spacing: -0.03em; background: linear-gradient(135deg,#fff,#a5b4fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
.stat-lbl { font-size: 0.8rem; color: #475569; margin-top: 0.25rem; }

/* ── Features ──────────────────────────────────────────── */
.features-section { padding: 6rem 1.5rem; }
.section-inner { max-width: 960px; margin: 0 auto; }
.section-eyebrow {
  font-size: 0.75rem; font-weight: 700; letter-spacing: 0.12em;
  text-transform: uppercase; color: #7c8ff8;
  text-align: center; margin-bottom: 0.875rem;
}
.section-h2 {
  font-size: clamp(1.75rem, 4vw, 2.5rem);
  font-weight: 800; letter-spacing: -0.03em; color: white;
  text-align: center; margin-bottom: 0.875rem;
}
.section-sub {
  font-size: 1rem; color: #64748b; line-height: 1.7;
  text-align: center; max-width: 560px;
  margin: 0 auto 3.5rem;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(285px, 1fr));
  gap: 1px;
  background: rgba(255,255,255,0.05);
  border: 1px solid rgba(255,255,255,0.05);
  border-radius: 1.25rem;
  overflow: hidden;
}
.feature-card {
  background: #060912;
  padding: 2rem;
  transition: background 0.2s;
}
.feature-card:hover { background: rgba(255,255,255,0.02); }
.feature-icon-wrap {
  width: 2.5rem; height: 2.5rem; border-radius: 0.75rem;
  background: rgba(91,120,246,0.12);
  border: 1px solid rgba(91,120,246,0.25);
  display: flex; align-items: center; justify-content: center;
  margin-bottom: 1.125rem;
  color: #7c8ff8;
}
.feature-icon-wrap :deep(svg) { color: #7c8ff8; }
.feature-title { font-size: 0.9375rem; font-weight: 700; color: white; margin-bottom: 0.5rem; }
.feature-desc { font-size: 0.8125rem; color: #64748b; line-height: 1.6; margin-bottom: 1rem; }
.feature-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.375rem; }
.feature-list li {
  font-size: 0.75rem; color: #475569;
  padding-left: 1.125rem; position: relative;
}
.feature-list li::before {
  content: '✓';
  position: absolute; left: 0;
  color: #5b78f6;
  font-weight: 700;
}

/* ── Mobile section ────────────────────────────────────── */
.mobile-section {
  padding: 6rem 1.5rem;
  background: rgba(255,255,255,0.015);
  border-top: 1px solid rgba(255,255,255,0.05);
  border-bottom: 1px solid rgba(255,255,255,0.05);
}
.mobile-inner {
  display: flex; align-items: center; gap: 4rem; flex-wrap: wrap;
}
.mobile-text { flex: 1; min-width: 280px; }
.mobile-text .section-h2 { text-align: left; }
.mobile-text .section-sub { text-align: left; margin-left: 0; }
.mobile-bullets { list-style: none; padding: 0; margin: 1.5rem 0 0; display: flex; flex-direction: column; gap: 0.875rem; }
.mobile-bullets li { display: flex; align-items: center; gap: 0.75rem; font-size: 0.9rem; color: #94a3b8; }
.bullet-icon {
  width: 1.375rem; height: 1.375rem; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.6rem; color: #7c8ff8; font-weight: 700; flex-shrink: 0;
  background: rgba(91,120,246,0.12);
  border: 1px solid rgba(91,120,246,0.25);
}

/* phone mock */
.mobile-phone-wrap { flex: 0 0 auto; display: flex; justify-content: center; }
.mobile-phone {
  width: 240px;
  background: #0a0d18;
  border-radius: 2rem;
  border: 1px solid rgba(255,255,255,0.08);
  padding: 1rem 0.75rem;
  box-shadow: 0 40px 80px -20px rgba(0,0,0,0.8), 0 0 40px rgba(91,120,246,0.08);
}
.phone-notch { width: 60px; height: 6px; background: #1e293b; border-radius: 9999px; margin: 0 auto 0.875rem; }
.phone-screen { display: flex; flex-direction: column; gap: 0.625rem; }
.phone-header { margin-bottom: 0.25rem; }
.phone-title { font-size: 0.8125rem; font-weight: 700; color: white; }
.phone-sub { font-size: 0.625rem; color: #475569; }
.phone-field { background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); border-radius: 0.625rem; padding: 0.625rem 0.75rem; }
.pf-label { font-size: 0.5625rem; color: #475569; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.375rem; }
.pf-value { font-size: 0.75rem; color: #e2e8f0; font-weight: 500; }
.pf-chips { display: flex; gap: 0.25rem; flex-wrap: wrap; }
.pf-chip { font-size: 0.5625rem; padding: 0.2rem 0.5rem; border-radius: 9999px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.08); color: #64748b; }
.pf-chip.active { background: rgba(91,120,246,0.18); border-color: rgba(91,120,246,0.4); color: #a5b4fc; }
.pf-textarea { font-size: 0.625rem; color: #64748b; line-height: 1.5; }
.pf-photos { display: flex; gap: 0.375rem; }
.pf-photo { flex: 1; aspect-ratio: 1; border-radius: 0.375rem; background: rgba(91,120,246,0.12); border: 1px solid rgba(91,120,246,0.2); }
.pf-photo-add { flex: 1; aspect-ratio: 1; border-radius: 0.375rem; border: 1px dashed rgba(255,255,255,0.12); display: flex; align-items: center; justify-content: center; color: #475569; font-size: 0.875rem; }
.phone-save-btn { text-align: center; background: #5b78f6; border-radius: 0.625rem; padding: 0.625rem; font-size: 0.6875rem; font-weight: 700; color: white; margin-top: 0.25rem; }

/* ── Workflow ───────────────────────────────────────────── */
.workflow-section { padding: 6rem 1.5rem; }
.workflow-steps {
  display: flex; flex-wrap: wrap; gap: 0;
  margin-top: 3rem;
  background: rgba(255,255,255,0.02);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 1.25rem;
  overflow: hidden;
}
.workflow-step {
  flex: 1; min-width: 160px;
  padding: 2rem 1.5rem;
  position: relative;
  border-right: 1px solid rgba(255,255,255,0.05);
  text-align: center;
}
.workflow-step:last-child { border-right: none; }
.step-num {
  width: 2rem; height: 2rem; border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.75rem; font-weight: 800; color: #7c8ff8;
  margin: 0 auto 0.875rem;
  background: rgba(91,120,246,0.1);
  border: 1px solid rgba(91,120,246,0.3);
}
.step-title { font-size: 0.875rem; font-weight: 700; color: white; margin-bottom: 0.5rem; }
.step-desc { font-size: 0.75rem; color: #64748b; line-height: 1.5; }
.step-arrow {
  position: absolute; right: -0.6rem; top: 50%;
  transform: translateY(-50%);
  color: #1e293b; font-size: 1rem; z-index: 1;
}

/* ── CTA ───────────────────────────────────────────────── */
.cta-section {
  padding: 7rem 1.5rem;
  text-align: center;
  position: relative;
  overflow: hidden;
}
.cta-glow {
  position: absolute; top: 50%; left: 50%; transform: translate(-50%,-50%);
  width: 700px; height: 400px;
  background: radial-gradient(ellipse, rgba(91,120,246,0.12) 0%, transparent 70%);
  pointer-events: none;
}
.cta-inner { position: relative; z-index: 1; max-width: 580px; margin: 0 auto; }
.cta-h2 {
  font-size: clamp(1.75rem, 4vw, 2.75rem);
  font-weight: 900; letter-spacing: -0.035em; color: white;
  line-height: 1.1; margin-bottom: 1rem;
}
.cta-sub { font-size: 1rem; color: #64748b; line-height: 1.7; margin-bottom: 2.5rem; }
.btn-cta-lg { padding: 1rem 2.5rem; font-size: 1rem; }

/* ── Footer ────────────────────────────────────────────── */
.landing-footer {
  border-top: 1px solid rgba(255,255,255,0.05);
  padding: 1.5rem 2rem;
}
.footer-inner {
  max-width: 960px; margin: 0 auto;
  display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;
}
</style>
