<template>
  <div v-if="isOpen" class="confirm-backdrop" @click.self="cancel">
    <div class="confirm-card" role="dialog" aria-modal="true" :aria-label="title">
      <div class="confirm-header">
        <div class="confirm-icon">
          <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <div class="confirm-title">
          <h2>{{ title }}</h2>
          <p v-if="message">{{ message }}</p>
        </div>
      </div>
      <div class="confirm-actions">
        <button type="button" class="btn-cancel" @click="cancel">{{ cancelText }}</button>
        <button type="button" class="btn-confirm" @click="confirm">{{ confirmText }}</button>
      </div>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useConfirmStore } from "@/stores/confirmStore";

export default {
  computed: {
    ...mapState(useConfirmStore, ["isOpen", "title", "message", "confirmText", "cancelText"]),
  },
  methods: {
    ...mapActions(useConfirmStore, ["confirm", "cancel"]),
  },
};
</script>

<style scoped src="@/assets/components/ConfirmDialog.css"></style>
