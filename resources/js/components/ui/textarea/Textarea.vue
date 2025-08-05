<script setup lang="ts">
import { computed } from 'vue'
import { cn } from '@/lib/utils'

interface Props {
  class?: string
  id?: string
  placeholder?: string
  rows?: number
  disabled?: boolean
  readonly?: boolean
  required?: boolean
  modelValue?: string
}

const props = withDefaults(defineProps<Props>(), {
  rows: 3
})

const emit = defineEmits<{
  'update:modelValue': [value: string]
}>()

const value = computed({
  get: () => props.modelValue || '',
  set: (value: string) => emit('update:modelValue', value)
})
</script>

<template>
  <textarea
    :id="id"
    v-model="value"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
    :placeholder="placeholder"
    :rows="rows"
    :disabled="disabled"
    :readonly="readonly"
    :required="required"
  />
</template>