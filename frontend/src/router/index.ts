import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/',      component: () => import('@/views/LandingView.vue'), meta: { public: true, title: 'SportPlanner · Gestión Deportiva' } },
    { path: '/login', component: () => import('@/views/LoginView.vue'),  meta: { public: true, title: 'Iniciar sesión' } },
    { path: '/dashboard',      component: () => import('@/views/DashboardView.vue'),     meta: { title: 'Dashboard' } },
    { path: '/escenarios',     component: () => import('@/views/EscenariosView.vue'),    meta: { title: 'Escenarios' } },
    { path: '/equipos',        component: () => import('@/views/EquiposView.vue'),       meta: { title: 'Equipos' } },
    { path: '/tecnicos',       component: () => import('@/views/TecnicosView.vue'),      meta: { title: 'Técnicos' } },
    { path: '/calendario',     component: () => import('@/views/CalendarioView.vue'),    meta: { title: 'Calendario' } },
    { path: '/mantenimiento',  component: () => import('@/views/MantenimientoView.vue'), meta: { title: 'Mantenimiento' } },
    { path: '/eventos',        component: () => import('@/views/EventosView.vue'),       meta: { title: 'Eventos' } },
    { path: '/solicitudes',    component: () => import('@/views/SolicitudesView.vue'),   meta: { title: 'Solicitudes' } },
    { path: '/campo',          component: () => import('@/views/CampoView.vue'),         meta: { title: 'Formularios de Campo' } },
    { path: '/campo/mantenimiento', component: () => import('@/views/campo/FormMantenimientoView.vue'), meta: { title: 'Form. Mantenimiento' } },
    { path: '/campo/evento',        component: () => import('@/views/campo/FormEventoView.vue'),        meta: { title: 'Form. Evento' } },
    { path: '/campo/solicitud',     component: () => import('@/views/campo/FormSolicitudView.vue'),     meta: { title: 'Form. Solicitud' } },

    { path: '/bodega',         component: () => import('@/views/BodegaView.vue'),        meta: { title: 'Bodega — Piezas' } },
    { path: '/reportes',       component: () => import('@/views/ReportesView.vue'),      meta: { title: 'Reportes' } },
    { path: '/usuarios',       component: () => import('@/views/UsersView.vue'),         meta: { title: 'Usuarios', requiresAdmin: true } },
  ],
})

router.beforeEach(to => {
  const token = localStorage.getItem('auth_token')
  if (to.meta.public) return true
  if (!token) return '/login'
  if (to.meta.requiresAdmin) {
    // Defer to UsersView — store will handle the 403 from API
    return true
  }
  return true
})

export default router
