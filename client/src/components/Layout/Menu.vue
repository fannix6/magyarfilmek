<template>
  <header class="cinema-header">
    <nav class="navbar navbar-expand-lg cinema-nav">
      <div class="container-fluid cinema-inner">
        <RouterLink class="cinema-brand" to="/">
          <span class="brand-icon">
            <i class="bi bi-film"></i>
          </span>
          <span class="brand-text">Cinema<span class="brand-accent">Hub</span></span>
        </RouterLink>

        <button
          class="navbar-toggler cinema-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav mx-auto cinema-menu">
            <li class="nav-item">
              <RouterLink class="nav-link cinema-link" to="/">
                <i class="bi bi-grid-1x2-fill"></i>
                Home
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link cinema-link" to="/adatok/student">
                <i class="bi bi-film"></i>
                Movies
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link cinema-link" to="/adatok/users">
                <i class="bi bi-star"></i>
                Reviews
              </RouterLink>
            </li>
            <li class="nav-item">
              <RouterLink class="nav-link cinema-link" to="/adatok/sport">
                <i class="bi bi-people"></i>
                Actors
              </RouterLink>
            </li>
          </ul>

          <div class="cinema-actions">
            <button
              type="button"
              class="cinema-search"
              aria-label="Kereses"
              @click="onClickSearchButton"
            >
              <i class="bi bi-search"></i>
            </button>

            <RouterLink v-if="!isLoggedIn" class="cinema-signin" to="/login">
              <i class="bi bi-box-arrow-in-right"></i>
              Sign In
            </RouterLink>

            <div v-else class="cinema-user">
              <RouterLink class="cinema-profile" to="/">
                <i class="bi bi-person"></i>
                {{ userNameWithRole }}
              </RouterLink>
              <button class="cinema-logout" type="button" @click="onClickLogut()">
                <i class="bi bi-box-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </header>
</template>

<script>
import { mapActions, mapState } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

export default {
  data() {
    return {
      searchWordInput: "",
    };
  },
  watch: {
    searchWordInput(value) {
      if (!value) {
        this.resetSearchWord();
      }
    },
    searchWord(value) {
      this.searchWordInput = value;
    },
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord"]),
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "userNameWithRole"]),
  },
  methods: {
    ...mapActions(useSearchStore, ["resetSearchWord", "setSearchWord"]),
    ...mapActions(useUserLoginLogoutStore, ["logout"]),
    onClickSearchButton() {
      this.setSearchWord(this.searchWordInput);
    },
    async onClickLogut() {
      try {
        await this.logout();
        this.$router.push("/");
      } catch (error) {
        console.log("Kijelentkezesi hiba!");
      }
    },
  },
};
</script>

<style scoped>
:global(:root) {
  --cinema-bg: #060817;
  --cinema-surface: #0e1228;
  --cinema-muted: #c6ccd8;
  --cinema-yellow: #ffb800;
  --cinema-yellow-soft: rgba(255, 184, 0, 0.2);
}

.cinema-header {
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  background: radial-gradient(circle at 20% -180%, #2a326b 0%, transparent 55%),
    linear-gradient(180deg, #070914 0%, #05060f 100%);
}

.cinema-nav {
  padding: 0.6rem 1rem;
}

.cinema-inner {
  gap: 1rem;
}

.cinema-brand {
  align-items: center;
  color: #fff;
  display: inline-flex;
  font-size: 1.75rem;
  font-weight: 700;
  gap: 0.65rem;
  letter-spacing: 0.2px;
  text-decoration: none;
}

.brand-icon {
  align-items: center;
  background-color: var(--cinema-yellow);
  border-radius: 0.6rem;
  color: #111;
  display: inline-flex;
  font-size: 1.1rem;
  height: 2rem;
  justify-content: center;
  width: 2rem;
}

.brand-accent {
  color: var(--cinema-yellow);
}

.cinema-menu {
  background: rgba(255, 255, 255, 0.04);
  border: 1px solid rgba(255, 255, 255, 0.08);
  border-radius: 0.9rem;
  gap: 0.25rem;
  padding: 0.25rem;
}

.cinema-link {
  align-items: center;
  border-radius: 0.7rem;
  color: var(--cinema-muted);
  display: inline-flex;
  font-weight: 500;
  gap: 0.45rem;
  padding: 0.55rem 0.85rem;
}

.cinema-link:hover {
  background-color: rgba(255, 255, 255, 0.06);
  color: #fff;
}

.cinema-link.router-link-exact-active,
.cinema-link.router-link-active {
  background-color: var(--cinema-yellow-soft);
  color: var(--cinema-yellow);
}

.cinema-actions {
  align-items: center;
  display: flex;
  gap: 0.9rem;
  margin-left: auto;
}

.cinema-search {
  background: transparent;
  border: 0;
  color: #f4f6fb;
  font-size: 1.2rem;
  padding: 0.25rem;
}

.cinema-signin {
  align-items: center;
  background-color: var(--cinema-yellow);
  border-radius: 0.7rem;
  color: #111;
  display: inline-flex;
  font-weight: 600;
  gap: 0.5rem;
  padding: 0.55rem 1rem;
  text-decoration: none;
}

.cinema-signin:hover {
  background-color: #ffc933;
}

.cinema-user {
  align-items: center;
  display: inline-flex;
  gap: 0.5rem;
}

.cinema-profile {
  align-items: center;
  color: #f4f6fb;
  display: inline-flex;
  gap: 0.35rem;
  text-decoration: none;
}

.cinema-logout {
  background: transparent;
  border: 0;
  color: #f4f6fb;
  font-size: 1.25rem;
  padding: 0.15rem;
}

.cinema-toggler {
  border-color: rgba(255, 255, 255, 0.3);
}

@media (max-width: 991px) {
  .cinema-menu {
    margin: 1rem 0;
    width: fit-content;
  }

  .cinema-actions {
    margin: 0 0 0.6rem;
  }
}
</style>
