<template>
    <!-- Login / Landing — no shell -->
    <RouterView v-if="isLoginPage" />

    <!-- Main shell -->
    <div v-else class="flex h-screen overflow-hidden bg-slate-50">

      <!-- ── Mobile overlay ─────────────────────────────────── -->
      <Transition name="overlay">
        <div v-if="sidebarOpen"
             class="fixed inset-0 z-30 bg-black/50 lg:hidden"
             @click="sidebarOpen = false" />
      </Transition>

      <!-- ── Sidebar ──────────────────────────────────────────── -->
      <aside id="sidebar"
             :class="['sidebar w-64 flex-shrink-0 flex flex-col',
                      'fixed inset-y-0 left-0 z-40 lg:relative lg:z-auto',
                      'transition-transform duration-300 ease-in-out',
                      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0']">

        <!-- Logo mark + close btn -->
        <div class="flex items-center justify-between px-4 py-5 md:px-5"
             style="border-bottom:1px solid rgba(255,255,255,0.07)">
          <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 font-black text-white text-sm"
                 style="background:linear-gradient(135deg,#3b82f6,#1d4ed8);box-shadow:0 4px 14px rgba(59,130,246,0.45)">
              SP
            </div>
            <div>
              <div class="text-sm font-bold text-white tracking-wide leading-tight">SportPlanner</div>
              <div class="text-xs leading-tight" style="color:rgba(148,163,184,0.6)">Gestión Deportiva</div>
            </div>
          </div>
          <!-- Close button (mobile only) -->
          <button @click="sidebarOpen = false"
                  class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-white/10 transition-colors">
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-3 py-2 overflow-y-auto space-y-px" @click="sidebarOpen = false">

          <div class="nav-section">Principal</div>
          <RouterLink to="/dashboard" class="nav-link" activeClass="active">
            <component :is="icons.dashboard" class="w-4 h-4 flex-shrink-0" />
            <span>Dashboard</span>
          </RouterLink>

          <div class="nav-section">Gestión</div>
          <RouterLink to="/escenarios" class="nav-link" activeClass="active">
            <component :is="icons.escenarios" class="w-4 h-4 flex-shrink-0" />
            <span>Escenarios</span>
          </RouterLink>
          <RouterLink to="/equipos" class="nav-link" activeClass="active">
            <component :is="icons.equipos" class="w-4 h-4 flex-shrink-0" />
            <span>Equipos</span>
          </RouterLink>
          <RouterLink to="/tecnicos" class="nav-link" activeClass="active">
            <component :is="icons.tecnicos" class="w-4 h-4 flex-shrink-0" />
            <span>Técnicos</span>
          </RouterLink>
          <RouterLink to="/calendario" class="nav-link" activeClass="active">
            <component :is="icons.calendario" class="w-4 h-4 flex-shrink-0" />
            <span>Calendario</span>
          </RouterLink>
          <RouterLink to="/mantenimiento" class="nav-link" activeClass="active">
            <component :is="icons.mantenimiento" class="w-4 h-4 flex-shrink-0" />
            <span>Mantenimiento</span>
          </RouterLink>
          <RouterLink to="/eventos" class="nav-link" activeClass="active">
            <component :is="icons.eventos" class="w-4 h-4 flex-shrink-0" />
            <span>Eventos</span>
          </RouterLink>

          <div class="nav-section">Operaciones</div>
          <RouterLink to="/solicitudes" class="nav-link" activeClass="active">
            <component :is="icons.solicitudes" class="w-4 h-4 flex-shrink-0" />
            <span>Solicitudes</span>
          </RouterLink>
          <RouterLink to="/campo" class="nav-link" activeClass="active">
            <component :is="icons.campo" class="w-4 h-4 flex-shrink-0" />
            <span>Form. de Campo</span>
          </RouterLink>
          <RouterLink to="/bodega" class="nav-link" activeClass="active">
            <component :is="icons.bodega" class="w-4 h-4 flex-shrink-0" />
            <span>Bodega</span>
          </RouterLink>

          <div class="nav-section">Informes</div>
          <RouterLink to="/reportes" class="nav-link" activeClass="active">
            <component :is="icons.reportes" class="w-4 h-4 flex-shrink-0" />
            <span>Reportes</span>
          </RouterLink>

          <!-- Admin only -->
          <template v-if="auth.isAdmin">
            <div class="nav-section">Administración</div>
            <RouterLink to="/usuarios" class="nav-link" activeClass="active">
              <component :is="icons.users" class="w-4 h-4 flex-shrink-0" />
              <span>Usuarios</span>
            </RouterLink>
          </template>

        </nav>

        <!-- Logout -->
        <div class="mx-3 mb-4">
          <button @click="logout"
                  class="w-full flex items-center gap-2.5 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all duration-150"
                  style="color:rgba(148,163,184,0.75);background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06)"
                  onmouseover="this.style.background='rgba(239,68,68,0.12)';this.style.color='#fca5a5';this.style.borderColor='rgba(239,68,68,0.2)'"
                  onmouseout="this.style.background='rgba(255,255,255,0.03)';this.style.color='rgba(148,163,184,0.75)';this.style.borderColor='rgba(255,255,255,0.06)'">
            <component :is="icons.logout" class="w-4 h-4 flex-shrink-0" />
            <span>Cerrar sesión</span>
          </button>
        </div>
      </aside>

      <!-- ── Main ──────────────────────────────────────────── -->
      <div id="main" class="flex-1 flex flex-col overflow-hidden min-w-0">

        <!-- Header -->
        <header class="app-header">
          <div class="flex items-center gap-2.5">
            <!-- Hamburger (mobile only) -->
            <button @click="sidebarOpen = true"
                    class="lg:hidden p-2 -ml-1 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
              <Bars3Icon class="w-5 h-5" />
            </button>
            <span class="hidden lg:block w-[3px] h-5 rounded-full bg-blue-500 flex-shrink-0"></span>
            <h1 class="text-sm font-semibold text-slate-900 truncate">{{ pageTitle }}</h1>
          </div>
          <div class="flex items-center gap-3">
            <span class="hidden sm:block text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full">
              {{ currentDate }}
            </span>
            <!-- User badge -->
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                   :title="auth.user?.name ?? ''">
                {{ auth.initials }}
              </div>
              <div class="hidden md:block text-right">
                <div class="text-xs font-semibold text-slate-700 leading-tight">{{ auth.user?.name }}</div>
                <div class="text-[10px] text-slate-400 leading-tight capitalize">{{ auth.user?.rol }}</div>
              </div>
            </div>
          </div>
        </header>

        <!-- Content -->
        <main class="flex-1 overflow-y-auto p-4 md:p-6">
          <RouterView />
        </main>
      </div>

      <ToastContainer />
    </div>
</template>

<script setup lang="ts">
import { ref, computed, watch, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import {
  Squares2X2Icon,
  BuildingOffice2Icon,
  TvIcon,
  UsersIcon,
  CalendarDaysIcon,
  WrenchScrewdriverIcon,
  StarIcon,
  DocumentChartBarIcon,
  ArrowRightStartOnRectangleIcon,
  ClipboardDocumentListIcon,
  DevicePhoneMobileIcon,
  ArchiveBoxIcon,
  UserGroupIcon,
  Bars3Icon,
  XMarkIcon,
} from '@heroicons/vue/24/outline'
import ToastContainer from '@/components/ToastContainer.vue'
import { useAuthStore } from '@/stores/auth'

const auth   = useAuthStore()
const route  = useRoute()
const router = useRouter()

const sidebarOpen = ref(false)

const isLoginPage = computed(() => route.meta?.public === true)
const pageTitle   = computed(() => (route.meta?.title as string) || 'SportPlanner')
const currentDate = new Intl.DateTimeFormat('es-SV', { dateStyle: 'full' }).format(new Date())

// Close sidebar on route change (mobile)
watch(() => route.path, () => { sidebarOpen.value = false })

const icons = {
  dashboard:    Squares2X2Icon,
  escenarios:   BuildingOffice2Icon,
  equipos:      TvIcon,
  tecnicos:     UsersIcon,
  calendario:   CalendarDaysIcon,
  mantenimiento: WrenchScrewdriverIcon,
  eventos:      StarIcon,
  solicitudes:  ClipboardDocumentListIcon,
  campo:        DevicePhoneMobileIcon,
  bodega:       ArchiveBoxIcon,
  reportes:     DocumentChartBarIcon,
  users:        UserGroupIcon,
  logout:       ArrowRightStartOnRectangleIcon,
}

async function logout() {
  await auth.logout()
  router.push('/login')
}

onMounted(() => auth.fetchMe())
</script>

<style scoped>
.overlay-enter-active, .overlay-leave-active { transition: opacity 0.25s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }
</style>
