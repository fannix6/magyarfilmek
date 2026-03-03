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

<style scoped>
.nf-header {
  position: sticky;
  top: 0;
  z-index: 1000;
  backdrop-filter: blur(10px);
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.45));
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
}

.nf-nav {
  max-width: 1280px;
  margin: 0 auto;
  min-height: 76px;
  padding: 0.75rem 1.2rem;
  display: flex;
  align-items: center;
  gap: 1.2rem;
}

.nf-brand {
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.55rem;
}

.logo-mark {
  width: 30px;
  height: 30px;
  border-radius: 0.35rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  background: #e50914;
  color: #fff;
  font-weight: 800;
}

.logo-text {
  font-size: 1.25rem;
  font-weight: 700;
  color: #fff;
}

.nf-links {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  gap: 0.95rem;
}

.nf-link {
  text-decoration: none;
  color: #d6d6d6;
  font-weight: 500;
  padding: 0.45rem 0.65rem;
  border-radius: 0.4rem;
}

.nf-link.router-link-exact-active,
.nf-link.router-link-active,
.nf-link:hover {
  color: #fff;
  background: rgba(255, 255, 255, 0.1);
}

.nf-actions {
  margin-left: auto;
  display: flex;
  align-items: center;
  gap: 0.8rem;
}

.icon-btn,
.logout-btn {
  border: 0;
  background: transparent;
  color: #fff;
  font-size: 1.15rem;
}

.signin-btn {
  text-decoration: none;
  background: #fff;
  color: #111;
  padding: 0.45rem 0.9rem;
  border-radius: 0.4rem;
  font-weight: 600;
}

.user-chip {
  display: inline-flex;
  align-items: center;
  gap: 0.35rem;
  color: #fff;
  background: rgba(255, 255, 255, 0.08);
  border: 1px solid rgba(255, 255, 255, 0.12);
  border-radius: 999px;
  padding: 0.2rem 0.5rem;
  font-size: 0.9rem;
}

@media (max-width: 880px) {
  .nf-nav {
    flex-wrap: wrap;
    gap: 0.7rem;
  }

  .nf-links {
    order: 3;
    width: 100%;
    overflow-x: auto;
    white-space: nowrap;
    padding-bottom: 0.3rem;
  }
}
</style>
