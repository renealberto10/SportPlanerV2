import { defineStore } from 'pinia'
import { ref } from 'vue'

export interface Toast { id: number; msg: string; type: 'success'|'error'|'info' }

export const useToastStore = defineStore('toast', () => {
  const toasts = ref<Toast[]>([])
  let seq = 0

  function add(msg: string, type: Toast['type'] = 'success') {
    const id = ++seq
    toasts.value.push({ id, msg, type })
    setTimeout(() => { toasts.value = toasts.value.filter(t => t.id !== id) }, 3500)
  }

  return { toasts, add }
})
