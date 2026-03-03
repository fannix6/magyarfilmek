<template>
  <div class="container-fluid my-container mt-2">
    <Menu />
    <Breadcrumb />

    <RouterView class="my-content-height" />

    <Footer :content="footerContent" />
    <ToastContanier />
  </div>
</template>

<script>
import Menu from "./components/Layout/Menu.vue";
import Footer from "./components/Layout/Footer.vue";
import Breadcrumb from "./components/Layout/Breadcrumb.vue";
import ToastContanier from "./components/Message/ToastContanier.vue";

export default {
  components: {
    Menu,
    Footer,
    Breadcrumb,
    ToastContanier,
  },
  data() {
    return {
      footerContent: "Ez egy masik lablec szoveg",
    };
  },
};
</script>

<style>
</style>
