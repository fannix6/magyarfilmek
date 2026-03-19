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

<style scoped>
.app-shell {
  min-height: 100vh;
  background: #0b0b0b;
}

.app-main {
  min-height: calc(100vh - 76px);
}
</style>
