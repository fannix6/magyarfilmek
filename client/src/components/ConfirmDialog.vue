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

<style scoped>
.confirm-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.65);
  backdrop-filter: blur(6px);
  display: grid;
  place-items: center;
  z-index: 50;
  padding: 1.5rem;
}

.confirm-card {
  width: min(520px, 100%);
  background: linear-gradient(160deg, #1c1c1c, #121212);
  border: 1px solid rgba(227, 158, 248, 0.35);
  border-radius: 1rem;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.45);
  padding: 1.4rem;
  color: #f5f5f5;
}

.confirm-header {
  display: flex;
  gap: 0.9rem;
  align-items: center;
}

.confirm-icon {
  width: 44px;
  height: 44px;
  border-radius: 50%;
  display: grid;
  place-items: center;
  background: rgba(227, 158, 248, 0.18);
  color: #e39ef8;
  font-size: 1.2rem;
}

.confirm-title h2 {
  margin: 0;
  font-size: 1.25rem;
}

.confirm-title p {
  margin: 0.3rem 0 0;
  color: #c9c9c9;
}

.confirm-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.6rem;
  margin-top: 1.2rem;
}

.btn-cancel,
.btn-confirm {
  border: 0;
  border-radius: 0.6rem;
  padding: 0.55rem 0.9rem;
  font-weight: 600;
}

.btn-cancel {
  background: rgba(255, 255, 255, 0.1);
  color: #f0f0f0;
}

.btn-confirm {
  background: #e39ef8;
  color: #111;
}
</style>
