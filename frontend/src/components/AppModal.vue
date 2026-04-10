<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-end sm:items-center justify-center sm:p-4"
        style="background:rgba(15,23,42,0.6);backdrop-filter:blur(4px)"
        @click.self="$emit('update:modelValue', false)"
      >
        <div
          class="bg-white w-full flex flex-col rounded-t-2xl sm:rounded-2xl"
          :style="`max-width:${maxWidth || '560px'};max-height:92vh;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35)`"
        >
          <!-- Header -->
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
            <h3 class="font-semibold text-slate-900 text-base">{{ title }}</h3>
            <button
              class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors text-lg leading-none"
              @click="$emit('update:modelValue', false)"
            >×</button>
          </div>

          <!-- Body -->
          <div class="overflow-y-auto px-6 py-5 flex-1">
            <slot />
          </div>

          <!-- Footer -->
          <div
            v-if="$slots.footer"
            class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2 flex-shrink-0 bg-slate-50/50 rounded-b-2xl"
          >
            <slot name="footer" />
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{ modelValue: boolean; title: string; maxWidth?: string }>()
defineEmits(['update:modelValue'])
</script>

<script lang="ts">
export default { inheritAttrs: false }
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s cubic-bezier(0.16,1,0.3,1); }
.modal-enter-from, .modal-leave-to       { opacity: 0; transform: scale(0.96) translateY(4px); }
</style>
