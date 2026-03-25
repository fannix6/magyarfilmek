<template>
  <div class="mb-3 password-field">
    <label v-if="label" class="form-label" :for="labelId">{{ label }} </label>
    <div class="input-group">
      <input
        :type="showPassword ? 'text' : 'password'"
        class="form-control"
        :class="{ 'is-invalid': !!errorMessage }"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        :required="required"
        :ref="inputRef"
        :id="labelId"
        :placeholder="placeholder"
        :autocomplete="autocomplete"
      />
      <button
        class="btn btn-outline-secondary ms-1"
        type="button"
        @click="showPassword = !showPassword"
      >
        <i :class="showPassword ? 'bi bi-eye-slash' : 'bi bi-eye'"></i>
      </button>
    </div>
    <div v-if="errorMessage" class="invalid-feedback d-block">
      {{ errorMessage }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    modelValue: { type: String },
    label: { type: String, default: "Password" },
    inputRef: { type: String, default: "" },
    labelId: { type: String, default: "" },
    errorMessage: { type: String, default: "" },
    placeholder: { type: String, default: "" },
    autocomplete: { type: String, default: "current-password" },
    required: { type: Boolean, default: true },
  },
  data() {
    return {
      showPassword: false,
    };
  },
};
</script>

<style scoped src="@/assets/components/PasswordField.css"></style>
