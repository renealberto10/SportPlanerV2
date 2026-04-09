import { defineStore } from 'pinia'
import { ref } from 'vue'
import { escenarioApi } from '@/api'

export const useEscenariosStore = defineStore('escenarios', () => {
  const items = ref<any[]>([])
  const loading = ref(false)

  async function fetch() {
    loading.value = true
    const { data } = await escenarioApi.list()
    items.value = data.data
    loading.value = false
  }

  async function save(form: any, id?: number) {
    if (id) await escenarioApi.update(id, form)
    else    await escenarioApi.create(form)
    await fetch()
  }

  async function remove(id: number) {
    await escenarioApi.remove(id)
    await fetch()
  }

  return { items, loading, fetch, save, remove }
})
