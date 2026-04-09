import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import api from '@/api'
import type { User } from '@/types'

export const useAuthStore = defineStore('auth', () => {
  const user  = ref<User | null>(null)
  const token = ref<string | null>(localStorage.getItem('auth_token'))

  const isLoggedIn = computed(() => !!token.value)
  const isAdmin    = computed(() => user.value?.rol === 'admin')
  const isTecnico  = computed(() => user.value?.rol === 'tecnico')
  const isBodega   = computed(() => user.value?.rol === 'bodega')
  const initials   = computed(() => {
    if (!user.value) return '?'
    return user.value.name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase()
  })

  function setToken(t: string) {
    token.value = t
    localStorage.setItem('auth_token', t)
  }

  async function login(email: string, password: string) {
    const res = await api.post<{ token: string; user: User }>('/auth/login', { email, password })
    setToken(res.data.token)
    user.value = res.data.user
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      const res = await api.get<User>('/auth/me')
      user.value = res.data
    } catch {
      clear()
    }
  }

  async function logout() {
    try { await api.post('/auth/logout') } catch { /* ignore */ }
    clear()
  }

  function clear() {
    token.value = null
    user.value  = null
    localStorage.removeItem('auth_token')
  }

  return { user, token, isLoggedIn, isAdmin, isTecnico, isBodega, initials, login, logout, fetchMe, clear }
})
