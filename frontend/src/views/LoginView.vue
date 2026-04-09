<template>
  <div class="login-shell">

    <!-- ── Left panel ─────────────────────────────────────── -->
    <div class="login-panel-left">
      <div class="login-panel-glow"></div>
      <div class="login-panel-glow2"></div>

      <!-- Logo + brand -->
      <RouterLink to="/" class="login-brand">
        <div class="login-logo">SP</div>
        <div>
          <div class="text-lg font-black text-white tracking-widest">SportPlanner</div>
          <div class="text-xs text-blue-300/60 tracking-wider uppercase">Gestión Deportiva</div>
        </div>
      </RouterLink>

      <!-- Feature list -->
      <div class="login-features">
        <div class="login-panel-heading">Gestión inteligente de<br /><span class="login-panel-accent">instalaciones deportivas</span></div>
        <div class="space-y-4 mt-8">
          <div v-for="f in features" :key="f.text" class="login-feature-item">
            <div class="login-feature-dot" :style="{ background: f.color }"></div>
            <div>
              <div class="text-sm font-semibold text-white">{{ f.text }}</div>
              <div class="text-xs text-slate-400 mt-0.5">{{ f.sub }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom badge -->
      <div class="login-contract-badge">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
        Plataforma segura · Acceso cifrado
      </div>
    </div>

    <!-- ── Right panel (form) ──────────────────────────────── -->
    <div class="login-panel-right">
      <div class="login-form-wrap">

        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Bienvenido</h1>
          <p class="text-sm text-slate-500 mt-1">Ingresa tus credenciales para continuar</p>
        </div>

        <!-- Form -->
        <form @submit.prevent="submit" class="space-y-5">

          <div>
            <label class="login-label">Correo electrónico</label>
            <div class="login-input-wrap">
              <EnvelopeIcon class="login-input-icon" />
              <input v-model="email" type="email" required autocomplete="email"
                     class="login-input" placeholder="usuario@correo.com" />
            </div>
          </div>

          <div>
            <label class="login-label">Contraseña</label>
            <div class="login-input-wrap">
              <LockClosedIcon class="login-input-icon" />
              <input v-model="password" :type="showPass ? 'text' : 'password'"
                     required autocomplete="current-password"
                     class="login-input pr-11" placeholder="••••••••" />
              <button type="button" @click="showPass = !showPass"
                      class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors">
                <EyeSlashIcon v-if="showPass" class="w-4 h-4" />
                <EyeIcon v-else class="w-4 h-4" />
              </button>
            </div>
          </div>

          <!-- Error -->
          <Transition name="fade">
            <div v-if="errorMsg" class="flex items-start gap-2.5 bg-red-50 border border-red-200 rounded-xl px-4 py-3">
              <ExclamationCircleIcon class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" />
              <span class="text-xs text-red-700 leading-relaxed">{{ errorMsg }}</span>
            </div>
          </Transition>

          <!-- Submit -->
          <button type="submit" :disabled="loading" class="login-submit">
            <span v-if="loading" class="flex items-center gap-2 justify-center">
              <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              Ingresando…
            </span>
            <span v-else class="flex items-center gap-2 justify-center">
              Ingresar al sistema
              <ArrowRightIcon class="w-4 h-4" />
            </span>
          </button>

        </form>

        <!-- Footer note -->
        <div class="mt-8 pt-6 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
          <span>Acceso solo personal autorizado</span>
          <RouterLink to="/" class="hover:text-slate-600 transition-colors flex items-center gap-1">
            <ArrowLeftIcon class="w-3 h-3" /> Volver
          </RouterLink>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import type { AxiosError } from 'axios'
import {
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ArrowRightIcon,
  ArrowLeftIcon,
  ExclamationCircleIcon,
} from '@heroicons/vue/24/outline'

const auth     = useAuthStore()
const router   = useRouter()
const email    = ref('')
const password = ref('')
const showPass = ref(false)
const loading  = ref(false)
const errorMsg = ref('')

const features = [
  { text: 'Mantenimiento y Eventos',  sub: 'Bitácora completa con fotos y técnicos',       color: '#3b82f6' },
  { text: 'Solicitudes de servicio',  sub: 'Gestión de solicitudes con seguimiento',        color: '#8b5cf6' },
  { text: 'Control de Bodega',        sub: 'Piezas y series con trazabilidad completa',     color: '#10b981' },
  { text: 'Formularios móviles',      sub: 'Captura en campo con cámara integrada',         color: '#f59e0b' },
  { text: 'Reportes profesionales',   sub: 'Exportación Word/PDF con evidencia fotográfica', color: '#ef4444' },
]

async function submit() {
  errorMsg.value = ''
  loading.value  = true
  try {
    await auth.login(email.value, password.value)
    router.push('/dashboard')
  } catch (e) {
    const err = e as AxiosError<{ errors?: { email?: string[] }; message?: string }>
    errorMsg.value =
      err.response?.data?.errors?.email?.[0] ||
      err.response?.data?.message ||
      'Error al iniciar sesión. Verifica tus credenciales.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-shell {
  min-height: 100vh;
  display: flex;
}

/* ── Left panel ─────────────────────────────────── */
.login-panel-left {
  display: none;
  position: relative;
  flex-direction: column;
  width: 44%;
  flex-shrink: 0;
  background: #020817;
  padding: 2.5rem;
  overflow: hidden;
}
@media (min-width: 768px) {
  .login-panel-left { display: flex; }
}

.login-panel-glow {
  position: absolute; top: -80px; left: -80px;
  width: 500px; height: 500px;
  background: radial-gradient(circle, rgba(59,130,246,0.2) 0%, transparent 70%);
  pointer-events: none;
}
.login-panel-glow2 {
  position: absolute; bottom: -100px; right: -100px;
  width: 400px; height: 400px;
  background: radial-gradient(circle, rgba(124,58,237,0.15) 0%, transparent 70%);
  pointer-events: none;
}

.login-brand {
  display: flex; align-items: center; gap: 0.75rem;
  text-decoration: none;
  position: relative; z-index: 1;
}
.login-logo {
  width: 2.5rem; height: 2.5rem;
  border-radius: 0.75rem;
  display: flex; align-items: center; justify-content: center;
  font-weight: 900; font-size: 0.85rem; color: white;
  background: linear-gradient(135deg,#3b82f6,#1d4ed8);
  box-shadow: 0 4px 14px rgba(59,130,246,0.5);
}

.login-features {
  flex: 1;
  display: flex;
  flex-direction: column;
  justify-content: center;
  position: relative; z-index: 1;
}
.login-panel-heading {
  font-size: 1.875rem;
  font-weight: 800;
  line-height: 1.2;
  letter-spacing: -0.02em;
  color: white;
}
.login-panel-accent {
  background: linear-gradient(135deg,#60a5fa,#818cf8);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}
.login-feature-item {
  display: flex; align-items: flex-start; gap: 0.875rem;
}
.login-feature-dot {
  width: 0.5rem; height: 0.5rem;
  border-radius: 9999px;
  flex-shrink: 0;
  margin-top: 0.35rem;
  box-shadow: 0 0 8px currentColor;
}
.login-contract-badge {
  display: flex; align-items: center; gap: 0.5rem;
  font-size: 0.6875rem; color: #475569;
  position: relative; z-index: 1;
  border-top: 1px solid rgba(255,255,255,0.05);
  padding-top: 1.25rem;
}

/* ── Right panel ────────────────────────────────── */
.login-panel-right {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f8fafc;
  padding: 2rem 1.5rem;
}
.login-form-wrap {
  width: 100%;
  max-width: 400px;
}

.login-label {
  display: block;
  font-size: 0.8125rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}
.login-input-wrap {
  position: relative;
}
.login-input-icon {
  position: absolute;
  left: 0.875rem;
  top: 50%;
  transform: translateY(-50%);
  width: 1rem; height: 1rem;
  color: #9ca3af;
  pointer-events: none;
}
.login-input {
  width: 100%;
  background: white;
  border: 1.5px solid #e5e7eb;
  border-radius: 0.75rem;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  font-size: 0.9375rem;
  color: #111827;
  transition: border-color 0.15s, box-shadow 0.15s;
  outline: none;
}
.login-input::placeholder { color: #d1d5db; }
.login-input:focus {
  border-color: #3b82f6;
  box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
}

.login-submit {
  width: 100%;
  padding: 0.875rem;
  border-radius: 0.875rem;
  background: linear-gradient(135deg,#2563eb,#1d4ed8);
  color: white;
  font-size: 0.9375rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  box-shadow: 0 4px 20px rgba(37,99,235,0.35), 0 1px 0 rgba(255,255,255,0.1) inset;
  transition: all 0.15s;
  margin-top: 0.5rem;
}
.login-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 6px 28px rgba(37,99,235,0.45);
}
.login-submit:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

/* Fade transition for error */
.fade-enter-active, .fade-leave-active { transition: all 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }
</style>
