<script setup lang="ts">
import { cn } from '@/lib/utils';

interface TextareaProps {
  modelValue?: string;
  placeholder?: string;
  disabled?: boolean;
  readonly?: boolean;
  rows?: number;
  class?: string;
  id?: string;
}

const props = withDefaults(defineProps<TextareaProps>(), {
  modelValue: '',
  rows: 3
});

const emit = defineEmits<{
  'update:modelValue': [value: string];
}>();

const updateValue = (event: Event) => {
  const target = event.target as HTMLTextAreaElement;
  emit('update:modelValue', target.value);
};
</script>

<template>
  <textarea
    :id="props.id"
    :value="modelValue"
    @input="updateValue"
    :placeholder="props.placeholder"
    :disabled="props.disabled"
    :readonly="props.readonly"
    :rows="props.rows"
    :class="cn(
      'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      props.class
    )"
  />
</template>