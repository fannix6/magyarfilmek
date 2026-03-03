<template>
  <header class="nf-header">
    <nav class="nf-nav">
      <RouterLink class="nf-brand" to="/">
        <span class="logo-mark">N</span>
        <span class="logo-text">FaBeTV</span>
      </RouterLink>

      <ul class="nf-links">
        <li><RouterLink to="/" class="nf-link">Home</RouterLink></li>
        <li><RouterLink to="/movies" class="nf-link">Movies</RouterLink></li>
        <li><RouterLink to="/reviews" class="nf-link">Reviews</RouterLink></li>
        <li><RouterLink to="/actors" class="nf-link">Actors</RouterLink></li>
      </ul>

      <div class="nf-actions">
        <button class="icon-btn" type="button" @click="goSearch">
          <i class="bi bi-search"></i>
        </button>

        <RouterLink v-if="!isLoggedIn" class="signin-btn" to="/login">
          Sign In
        </RouterLink>
        <RouterLink v-if="!isLoggedIn" class="signup-btn" to="/registration">
          Sign Up
        </RouterLink>

        <div v-else class="user-chip">
          <span>{{ userNameWithRole }}</span>
          <button type="button" class="logout-btn" @click="onLogout">
            <i class="bi bi-box-arrow-right"></i>
          </button>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "userNameWithRole"]),
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["logout"]),
    goSearch() {
      this.$router.push("/movies");
    },
    async onLogout() {
      try {
        await this.logout();
        this.$router.push("/");
      } catch (error) {
        console.log("Logout failed");
      }
    },
  },
};
</script>

<style scoped src="@/assets/components/Menu.css"></style>
