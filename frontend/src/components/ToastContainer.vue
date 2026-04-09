<template>
  <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-2 pointer-events-none">
    <TransitionGroup name="toast">
      <div
        v-for="t in toastStore.toasts"
        :key="t.id"
        class="pointer-events-auto flex items-start gap-3 px-4 py-3 rounded-xl text-sm min-w-72 max-w-sm"
        :class="{
          'bg-slate-900 text-white':          t.type === 'info',
          'bg-emerald-600 text-white':        t.type === 'success',
          'bg-red-600 text-white':            t.type === 'error',
        }"
        style="box-shadow:0 10px 25px -5px rgba(0,0,0,0.3),0 4px 10px -5px rgba(0,0,0,0.2)"
      >
        <span class="mt-0.5 text-base leading-none flex-shrink-0">
          {{ t.type === 'success' ? '✓' : t.type === 'error' ? '✕' : 'ℹ' }}
        </span>
        <span class="leading-snug">{{ t.msg }}</span>
      </div>
    </TransitionGroup>
  </div>
</template>

<script setup lang="ts">
import { useToastStore } from '@/stores/toast'
const toastStore = useToastStore()
</script>

<style scoped>
.toast-enter-active { transition: all 0.28s cubic-bezier(0.16,1,0.3,1); }
.toast-leave-active { transition: all 0.2s ease; }
.toast-enter-from   { opacity: 0; transform: translateX(100%) scale(0.95); }
.toast-leave-to     { opacity: 0; transform: translateX(100%); }
</style>
