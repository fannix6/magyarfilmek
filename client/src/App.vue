<template>
  <div class="app-shell">
    <Menu />
    <main class="app-main">
      <RouterView />
    </main>
    <ToastContanier />
    <ConfirmDialog />
  </div>
</template>

<script>
import Menu from "./components/Layout/Menu.vue";
import ToastContanier from "./components/Message/ToastContanier.vue";
import ConfirmDialog from "./components/ConfirmDialog.vue";

export default {
  components: {
    Menu,
    ToastContanier,
    ConfirmDialog,
  },
};
</script>

<style scoped src="@/assets/App.css"></style>
