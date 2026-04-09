export const EQUIPO_TIPOS: Record<string, string> = {
  pantalla: 'Pantalla COLOSSEO',
  bocina: 'Bocina',
  consola: 'Consola de Audio',
  servidor: 'Servidor',
  otro: 'Otro',
}

export const EQUIPO_ESTADOS: Record<string, string> = {
  operativo: 'Operativo',
  mantenimiento: 'En Mantenimiento',
  falla: 'Con Falla',
  baja: 'Baja',
}

export const EQUIPO_ESTADO_BADGE: Record<string, string> = {
  operativo: 'badge badge-green',
  mantenimiento: 'badge badge-yellow',
  falla: 'badge badge-red',
  baja: 'badge badge-gray',
}

export const MANTENIMIENTO_TIPOS: Record<string, string> = {
  preventivo: 'Preventivo',
  correctivo: 'Correctivo',
  operativo: 'Operativo',
}

export const MANTENIMIENTO_TIPO_BADGE: Record<string, string> = {
  preventivo: 'badge badge-blue',
  correctivo: 'badge badge-orange',
  operativo: 'badge badge-purple',
}

export const MANTENIMIENTO_ESTADOS: Record<string, string> = {
  completado: 'Completado',
  en_proceso: 'En Proceso',
  pendiente: 'Pendiente',
}

export const MANTENIMIENTO_ESTADO_BADGE: Record<string, string> = {
  completado: 'badge badge-green',
  en_proceso: 'badge badge-yellow',
  pendiente: 'badge badge-gray',
}

export const EVENTO_TIPOS: Record<string, string> = {
  deportivo: 'Deportivo',
  cultural: 'Cultural',
  produccion: 'Producción Técnica',
  otro: 'Otro',
}

export const EVENTO_ESTADOS: Record<string, string> = {
  programado: 'Programado',
  en_curso: 'En Curso',
  realizado: 'Realizado',
  cancelado: 'Cancelado',
}

export const EVENTO_ESTADO_BADGE: Record<string, string> = {
  programado: 'badge badge-yellow',
  en_curso: 'badge badge-blue',
  realizado: 'badge badge-green',
  cancelado: 'badge badge-red',
}

export const TECNICO_ESPECIALIDADES: Record<string, string> = {
  audio: 'Audio',
  video: 'Video / Pantallas',
  electronica: 'Electrónica',
  redes: 'Redes / IT',
  general: 'General',
}

export const TECNICO_ESPECIALIDAD_BADGE: Record<string, string> = {
  audio: 'badge badge-yellow',
  video: 'badge badge-purple',
  electronica: 'badge badge-blue',
  redes: 'badge badge-green',
  general: 'badge badge-gray',
}

export const MESES = [
  'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
]

export const AVATAR_COLORS = [
  '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6',
  '#ef4444', '#06b6d4', '#84cc16', '#ec4899',
]

export const SOLICITUD_PRIORIDAD_BADGE: Record<string, string> = {
  alto:  'badge badge-red',
  medio: 'badge badge-yellow',
  bajo:  'badge badge-green',
}

export const SOLICITUD_ESTADO_BADGE: Record<string, string> = {
  pendiente:  'badge badge-gray',
  en_proceso: 'badge badge-yellow',
  completado: 'badge badge-green',
  cancelado:  'badge badge-red',
}

export const SOLICITUD_ESTADOS: Record<string, string> = {
  pendiente:  'Pendiente',
  en_proceso: 'En Proceso',
  completado: 'Completado',
  cancelado:  'Cancelado',
}

export const TIPO_PIEZA_LABELS: Record<string, string> = {
  led:          'Módulo LED',
  tarjeta_señal:'Tarjeta de Señal',
  ventilador:   'Ventilador',
  cable:        'Cable',
  fuente_poder: 'Fuente de Poder',
  conector:     'Conector',
  bocina:       'Bocina / Driver',
  modulo:       'Módulo/Tarjeta',
  otro:         'Otro',
}

export const TIPO_PIEZA_BADGE: Record<string, string> = {
  led:          'badge badge-blue',
  tarjeta_señal:'badge badge-purple',
  ventilador:   'badge badge-gray',
  cable:        'badge badge-gray',
  fuente_poder: 'badge badge-orange',
  conector:     'badge badge-gray',
  bocina:       'badge badge-yellow',
  modulo:       'badge badge-blue',
  otro:         'badge badge-gray',
}

export const fmtDate = (d: string) =>
  d ? new Date(d + 'T12:00:00').toLocaleDateString('es-SV') : '—'

export const avatarColor = (name: string) =>
  AVATAR_COLORS[(name?.charCodeAt(0) ?? 0) % AVATAR_COLORS.length]

export const today = () => new Date().toISOString().split('T')[0]
