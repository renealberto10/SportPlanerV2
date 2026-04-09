export interface Escenario {
  id: number
  nombre: string
  descripcion: string | null
  direccion: string | null
  activo: boolean
  tiene_colosseo: boolean
  equipos_count?: number
  mantenimientos_count?: number
  eventos_count?: number
  created_at: string
}

export interface Equipo {
  id: number
  escenario_id: number | null
  escenario?: Pick<Escenario, 'id' | 'nombre'>
  nombre: string
  tipo: 'pantalla' | 'bocina' | 'consola' | 'servidor' | 'otro'
  modelo: string | null
  serie: string | null
  estado: 'operativo' | 'mantenimiento' | 'falla' | 'baja'
  fecha_instalacion: string | null
  notas: string | null
  created_at: string
}

export interface Tecnico {
  id: number
  nombre: string
  apellido: string
  nombre_completo: string
  telefono: string | null
  email: string | null
  especialidad: 'audio' | 'video' | 'electronica' | 'redes' | 'general'
  activo: boolean
  notas: string | null
  mantenimientos_count?: number
  created_at: string
}

export interface Mantenimiento {
  id: number
  escenario_id: number
  escenario?: Pick<Escenario, 'id' | 'nombre'>
  tecnico_id: number | null
  tecnico_obj?: Pick<Tecnico, 'id' | 'nombre_completo'>
  fecha: string
  hora: string | null
  tecnico: string | null
  tipo: 'preventivo' | 'correctivo' | 'operativo'
  actividades: string | null
  observaciones: string | null
  estado: 'pendiente' | 'en_proceso' | 'completado'
  horas: number | null
  personal: string | null
  fotos: string[]
  created_at: string
}

export interface Evento {
  id: number
  escenario_id: number
  escenario?: Pick<Escenario, 'id' | 'nombre'>
  nombre: string
  fecha: string
  hora: string | null
  tipo: 'deportivo' | 'cultural' | 'produccion' | 'otro'
  estado: 'programado' | 'en_curso' | 'realizado' | 'cancelado'
  descripcion: string | null
  personal: string | null
  equipos_notas: string | null
  created_at: string
}

export interface DashboardStats {
  mantenimientos_mes: number
  eventos_mes: number
  equipos_total: number
  escenarios_activos: number
}

export interface DashboardData {
  stats: DashboardStats
  proximos_mantenimientos: Mantenimiento[]
  proximos_eventos: Evento[]
  mantenimientos_por_escenario: Array<{ nombre: string; total: number }>
  equipos_por_tipo: Record<string, number>
}

export interface Solicitud {
  id: number
  fecha_solicitud: string
  actividad: string
  escenario_id: number | null
  escenario?: Pick<Escenario, 'id' | 'nombre'>
  escenario_texto: string | null
  solicita: string
  fecha_calendarizada: string | null
  hora: string | null
  tecnico_id: number | null
  tecnico?: Pick<Tecnico, 'id' | 'nombre_completo'>
  seguimiento: string | null
  prioridad: 'alto' | 'medio' | 'bajo'
  estado: 'pendiente' | 'en_proceso' | 'completado' | 'cancelado'
  notas: string | null
  created_at: string
}

export interface CambioPieza {
  id: number
  mantenimiento_id: number | null
  evento_id: number | null
  escenario_id: number
  escenario?: Pick<Escenario, 'id' | 'nombre'>
  equipo_id: number | null
  equipo?: Pick<Equipo, 'id' | 'nombre'>
  descripcion_pieza: string
  tipo_pieza: 'led' | 'tarjeta_señal' | 'ventilador' | 'cable' | 'fuente_poder' | 'conector' | 'bocina' | 'modulo' | 'otro'
  serie_instalada: string | null
  serie_retirada: string | null
  tecnico_id: number | null
  tecnico?: Pick<Tecnico, 'id' | 'nombre_completo'>
  fecha: string
  estado_bodega: 'pendiente' | 'recibido'
  recibido_por: string | null
  fecha_recepcion: string | null
  notas: string | null
  created_at: string
}

export interface User {
  id: number
  name: string
  email: string
  rol: 'admin' | 'tecnico' | 'bodega' | 'viewer'
  activo: boolean
}

export interface ApiListResponse<T> {
  data: T[]
}

export interface ApiError {
  message?: string
  errors?: Record<string, string[]>
}
