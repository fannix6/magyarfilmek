<template>
  <div
    class="alert alert-primary alert-dismissible fade show custom-center-alert"
    :class="{
      'alert-danger': type == 'Error',
      'alert-success': type == 'Success',
    }"
    role="alert"
    v-if="messages.length"
  >
    <div class="mb-2">
      <h5>{{ type }}</h5>
      <p v-for="message in messages" :key="message" class="m-0">
        {{ message }}
      </p>
    </div>
    <button type="button" class="btn-close" @click="close()" aria-label="Close"></button>
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

