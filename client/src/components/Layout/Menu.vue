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
        <li><RouterLink to="/about" class="nf-link">About us</RouterLink></li>
      </ul>

      <div class="nf-actions">
        <button class="icon-btn" type="button" @click="goSearch">
          <i class="bi bi-search"></i>
        </button>

        <div v-if="isLoggedIn" class="profile-menu" @keydown.escape="profileOpen = false">
          <button
            class="icon-btn"
            type="button"
            title="Profile"
            aria-label="Profile"
            @click="profileOpen = !profileOpen"
          >
            <i class="bi bi-person-lines-fill"></i>
          </button>
          <div v-if="profileOpen" class="profile-popover">
            <RouterLink class="profile-item" to="/profile" @click="profileOpen = false">
              Profile info
            </RouterLink>
            <div v-if="isAdmin" class="profile-section">
              <p class="profile-section-title">Admin Tasks</p>
              <RouterLink class="profile-item" to="/users" @click="profileOpen = false">
                Users
              </RouterLink>
              <RouterLink class="profile-item" to="/roles" @click="profileOpen = false">
                Roles
              </RouterLink>
              <RouterLink class="profile-item" to="/tasks" @click="profileOpen = false">
                Tasks
              </RouterLink>
            </div>
            <button class="profile-item danger" type="button" @click="onLogout">
              Log out
            </button>
          </div>
        </div>

        <RouterLink v-if="!isLoggedIn" class="signin-btn" to="/login">
          Sign In
        </RouterLink>
        <RouterLink v-if="!isLoggedIn" class="signup-btn" to="/registration">
          Sign Up
        </RouterLink>
      </div>
    </nav>
  </header>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "role"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
    },
  },
  data() {
    return {
      profileOpen: false,
    };
  },
  methods: {
    ...mapActions(useUserLoginLogoutStore, ["logout"]),
    goSearch() {
      this.$router.push("/movies");
    },
    async onLogout() {
      try {
        await this.logout();
        this.profileOpen = false;
        this.$router.push("/");
      } catch (error) {
        console.log("Logout failed");
      }
    },
  },
};
</script>

<style scoped src="@/assets/components/Menu.css"></style>
