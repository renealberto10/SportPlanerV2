import { useToastStore } from '@/stores/toast'
import type { AxiosError } from 'axios'
import type { ApiError } from '@/types'

export function useApiError() {
  const toast = useToastStore()

  function handleError(error: unknown, fallback = 'Ha ocurrido un error inesperado') {
    const err = error as AxiosError<ApiError>

    if (err?.response?.data?.errors) {
      const firstField = Object.values(err.response.data.errors)[0]
      const msg = Array.isArray(firstField) ? firstField[0] : fallback
      toast.add(msg, 'error')
      return
    }

    if (err?.response?.data?.message) {
      toast.add(err.response.data.message, 'error')
      return
    }

    if (err?.message && import.meta.env.DEV) {
      console.error('[API Error]', err.message, error)
    }

    toast.add(fallback, 'error')
  }

  return { handleError }
}
