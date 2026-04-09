<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        style="background:rgba(15,23,42,0.6);backdrop-filter:blur(4px)"
        @click.self="$emit('update:modelValue', false)"
      >
        <div class="bg-white rounded-2xl w-full max-w-sm"
             style="box-shadow:0 25px 50px -12px rgba(0,0,0,0.35)">
          <!-- Icon header -->
          <div class="px-6 pt-6 pb-4 text-center">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center mx-auto mb-4 text-2xl ring-1 ring-red-100">
              🗑️
            </div>
            <h3 class="font-semibold text-slate-900 text-base">Confirmar eliminación</h3>
            <p class="text-sm text-slate-500 mt-2 leading-relaxed">{{ message }}</p>
          </div>
          <!-- Actions -->
          <div class="px-6 py-4 border-t border-slate-100 flex gap-2 bg-slate-50/60 rounded-b-2xl">
            <button class="btn btn-outline flex-1" @click="$emit('update:modelValue', false)">
              Cancelar
            </button>
            <button class="btn btn-danger flex-1" @click="$emit('confirm')">
              Eliminar
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
defineProps<{ modelValue: boolean; message?: string }>()
defineEmits(['update:modelValue', 'confirm'])
</script>

<style scoped>
.modal-enter-active, .modal-leave-active { transition: all 0.2s cubic-bezier(0.16,1,0.3,1); }
.modal-enter-from, .modal-leave-to       { opacity: 0; transform: scale(0.94) translateY(4px); }
</style>
