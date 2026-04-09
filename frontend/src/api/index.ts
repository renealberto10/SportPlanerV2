import axios from 'axios'
import type { AxiosError } from 'axios'
import type {
  Escenario, Equipo, Tecnico, Mantenimiento, Evento,
  Solicitud, CambioPieza, DashboardData, ApiListResponse, User,
} from '@/types'

const api = axios.create({
  baseURL: '/api/v1',
  headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
})

// Attach token from localStorage on every request
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  response => response,
  (error: AxiosError) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token')
      window.location.href = '/login'
    }
    if (error.response?.status === 500 && import.meta.env.DEV) {
      console.error('[API 500]', error.config?.url, error.response.data)
    }
    return Promise.reject(error)
  },
)

// ── Users ─────────────────────────────────────────────────
export const userApi = {
  list:   ()                                                  => api.get<User[]>('/users'),
  create: (data: Omit<User, 'id'> & { password: string })    => api.post<User>('/users', data),
  update: (id: number, data: Partial<Omit<User, 'id'>> & { password?: string }) =>
    api.put<User>(`/users/${id}`, data),
  remove: (id: number)                                        => api.delete(`/users/${id}`),
}

export default api

// ── Type helpers ──────────────────────────────────────────
type EscenarioForm     = Omit<Escenario, 'id' | 'equipos_count' | 'mantenimientos_count' | 'eventos_count' | 'created_at'>
type EquipoForm        = Omit<Equipo, 'id' | 'escenario' | 'created_at'>
type TecnicoForm       = Omit<Tecnico, 'id' | 'nombre_completo' | 'mantenimientos_count' | 'created_at'>
type MantenimientoForm = Omit<Mantenimiento, 'id' | 'escenario' | 'tecnico_obj' | 'created_at'>
type EventoForm        = Omit<Evento, 'id' | 'escenario' | 'created_at'>
type SolicitudForm     = Omit<Solicitud, 'id' | 'escenario' | 'tecnico' | 'created_at'>
type CambioPiezaForm   = Omit<CambioPieza, 'id' | 'escenario' | 'equipo' | 'tecnico' | 'created_at'>
type Params            = Record<string, unknown>

// ── API modules ───────────────────────────────────────────
export const escenarioApi = {
  list:   ()                                          => api.get<ApiListResponse<Escenario>>('/escenarios'),
  get:    (id: number)                                => api.get<Escenario>(`/escenarios/${id}`),
  create: (data: EscenarioForm)                       => api.post<Escenario>('/escenarios', data),
  update: (id: number, data: Partial<EscenarioForm>) => api.put<Escenario>(`/escenarios/${id}`, data),
  remove: (id: number)                                => api.delete(`/escenarios/${id}`),
}

export const equipoApi = {
  list:   (params?: Params)                          => api.get<ApiListResponse<Equipo>>('/equipos', { params }),
  get:    (id: number)                               => api.get<Equipo>(`/equipos/${id}`),
  create: (data: EquipoForm)                         => api.post<Equipo>('/equipos', data),
  update: (id: number, data: Partial<EquipoForm>)   => api.put<Equipo>(`/equipos/${id}`, data),
  remove: (id: number)                               => api.delete(`/equipos/${id}`),
}

export const mantenimientoApi = {
  list:   (params?: Params)                                  => api.get<ApiListResponse<Mantenimiento>>('/mantenimientos', { params }),
  get:    (id: number)                                       => api.get<Mantenimiento>(`/mantenimientos/${id}`),
  create: (data: MantenimientoForm)                          => api.post<Mantenimiento>('/mantenimientos', data),
  update: (id: number, data: Partial<MantenimientoForm>)    => api.put<Mantenimiento>(`/mantenimientos/${id}`, data),
  remove: (id: number)                                       => api.delete(`/mantenimientos/${id}`),
  uploadFoto: (id: number, file: File) => {
    const form = new FormData()
    form.append('foto', file)
    return api.post<{ url: string; path: string; fotos: Array<{ path: string; url: string }> }>(
      `/mantenimientos/${id}/fotos`, form,
      { headers: { 'Content-Type': 'multipart/form-data' } },
    )
  },
  removeFoto: (id: number, path: string) =>
    api.delete(`/mantenimientos/${id}/fotos`, { data: { path } }),
}

export const eventoApi = {
  list:   (params?: Params)                         => api.get<ApiListResponse<Evento>>('/eventos', { params }),
  get:    (id: number)                              => api.get<Evento>(`/eventos/${id}`),
  create: (data: EventoForm)                        => api.post<Evento>('/eventos', data),
  update: (id: number, data: Partial<EventoForm>)  => api.put<Evento>(`/eventos/${id}`, data),
  remove: (id: number)                              => api.delete(`/eventos/${id}`),
}

export const dashboardApi = {
  stats:   (params?: Params) => api.get<DashboardData>('/dashboard', { params }),
  reporte: (params: Params)  => api.get('/reporte', { params }),
}

export const tecnicoApi = {
  list:   (params?: Params)                          => api.get<ApiListResponse<Tecnico>>('/tecnicos', { params }),
  get:    (id: number)                               => api.get<Tecnico>(`/tecnicos/${id}`),
  create: (data: TecnicoForm)                        => api.post<Tecnico>('/tecnicos', data),
  update: (id: number, data: Partial<TecnicoForm>)  => api.put<Tecnico>(`/tecnicos/${id}`, data),
  remove: (id: number)                               => api.delete(`/tecnicos/${id}`),
}

export const solicitudApi = {
  list:   (params?: Params)                              => api.get<ApiListResponse<Solicitud>>('/solicitudes', { params }),
  get:    (id: number)                                   => api.get<Solicitud>(`/solicitudes/${id}`),
  create: (data: SolicitudForm)                          => api.post<Solicitud>('/solicitudes', data),
  update: (id: number, data: Partial<SolicitudForm>)    => api.put<Solicitud>(`/solicitudes/${id}`, data),
  remove: (id: number)                                   => api.delete(`/solicitudes/${id}`),
}

export const cambioPiezaApi = {
  list:             (params?: Params)                              => api.get<ApiListResponse<CambioPieza>>('/cambios-piezas', { params }),
  create:           (data: CambioPiezaForm)                        => api.post<CambioPieza>('/cambios-piezas', data),
  update:           (id: number, data: Partial<CambioPiezaForm>)  => api.put<CambioPieza>(`/cambios-piezas/${id}`, data),
  remove:           (id: number)                                   => api.delete(`/cambios-piezas/${id}`),
  confirmarRecepcion: (id: number, data: { recibido_por: string; fecha_recepcion: string; notas?: string }) =>
    api.post<CambioPieza>(`/cambios-piezas/${id}/recepcion`, data),
}
