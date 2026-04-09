<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
      <component v-if="heroIcon" :is="heroIcon" class="w-6 h-6 text-slate-400" />
      <span v-else class="text-2xl text-slate-400">{{ icon }}</span>
    </div>
    <p class="font-semibold text-slate-600 text-sm">{{ title }}</p>
    <p v-if="subtitle" class="text-xs text-slate-400 mt-1 max-w-xs">{{ subtitle }}</p>
  </div>
</template>

<script setup lang="ts">
import type { Component } from 'vue'
defineProps<{
  icon?: string
  heroIcon?: Component
  title: string
  subtitle?: string
}>()
</script>
