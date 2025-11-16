<script setup lang="ts">
import InputError from './InputError.vue';
import Input from './ui/input/Input.vue';
import Label from './ui/label/Label.vue';

defineProps<{
    errors?: Record<string, string>;
    label?: string;
    id?: string;
    type?: string;
    name?: string;
    required?: boolean;
    autofocus?: boolean;
    tabindex?: number;
    autocomplete?: string;
    placeholder?: string;
    error?: string;
}>();

</script>
<template>
    <div class="grid gap-2">
        <Label :for="id">{{ label }}</Label>
        <Input :id="id" :type="type" :name="name" :required="required" :autofocus="autofocus" :tabindex="tabindex"
            :autocomplete="autocomplete" :placeholder="placeholder" />
        <InputError :message="error" />
    </div>
</template>
