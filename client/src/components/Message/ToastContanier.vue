<template>
  <div v-if="messages.length" class="toast-wrap" role="status" aria-live="polite">
    <div
      v-for="(message, idx) in messages"
      :key="`${message}-${idx}`"
      class="toast-card"
      :class="{
        'toast-error': type == 'Error',
        'toast-success': type == 'Success',
      }"
      role="alert"
    >
      <div class="toast-icon" aria-hidden="true">
        <i v-if="type == 'Error'" class="bi bi-x-octagon-fill"></i>
        <i v-else class="bi bi-check-circle-fill"></i>
      </div>
      <div class="toast-body">
        <p class="toast-title">{{ type }}</p>
        <p class="toast-message">{{ message }}</p>
      </div>
      <button type="button" class="toast-close" @click="close()" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  </div>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useToastStore } from "@/stores/toastStore";

export default {
  methods: {
    ...mapActions(useToastStore, ["close"]),
  },
  computed: {
    ...mapState(useToastStore, ["messages", "type"]),
  },
};
</script>

<style scoped src="@/assets/components/ToastContanier.css"></style>

