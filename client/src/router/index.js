import { createRouter, createWebHistory } from "vue-router";
import HomeView from "@/views/HomeView.vue";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "home",
      component: HomeView,
      meta: {
        title: () => "Home",
      },
    },
    {
      path: "/movies",
      name: "movies",
      component: () => import("@/views/MoviesView.vue"),
      meta: {
        title: () => "Movies",
      },
    },
    {
      path: "/movies/:id",
      name: "movie-details",
      component: () => import("@/views/MovieDetailsView.vue"),
      meta: {
        title: () => "Movie Details",
      },
    },
    {
      path: "/actors",
      name: "actors",
      component: () => import("@/views/ActorsView.vue"),
      meta: {
        title: () => "Actors",
      },
    },
    {
      path: "/roles",
      name: "roles",
      component: () => import("@/views/RolesView.vue"),
      meta: {
        title: () => "Roles",
        requiresAdmin: true,
      },
    },
    {
      path: "/reviews",
      name: "reviews",
      component: () => import("@/views/ReviewsView.vue"),
      meta: {
        title: () => "Reviews",
      },
    },
    {
      path: "/profile",
      name: "profile",
      component: () => import("@/views/ProfileView.vue"),
      meta: {
        title: () => "Profile",
      },
    },
    {
      path: "/collections",
      name: "collections",
      component: () => import("@/views/CollectionsView.vue"),
      meta: {
        title: () => "Collections",
      },
    },
    {
      path: "/about",
      name: "about",
      component: () => import("@/views/AboutView.vue"),
      meta: {
        title: () => "About",
      },
    },
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/LoginView.vue"),
      meta: {
        title: () => "Login",
      },
    },
    {
      path: "/registration",
      name: "registration",
      component: () => import("@/views/RegistrationView.vue"),
      meta: {
        title: () => "Registration",
      },
    },
    {
      path: "/adatok/student",
      redirect: "/movies",
    },
    {
      path: "/adatok/sport",
      redirect: "/actors",
    },
    {
      path: "/adatok/users",
      redirect: "/reviews",
    },
    {
      path: "/adatok/schoolclass",
      redirect: "/collections",
    },
    {
      path: "/adatok/plaingsport",
      redirect: "/collections",
    },
    {
      path: "/:pathMatch(.*)*",
      name: "NotFound",
      component: () => import("@/views/404.vue"),
      meta: {
        title: () => "404",
      },
    },
  ],
});

router.beforeEach((to) => {
  const authStore = useUserLoginLogoutStore();

  if (to.meta.requiresAdmin) {
    if (!authStore.isLoggedIn) {
      return {
        path: "/login",
        query: { redirect: to.fullPath },
      };
    }

    if (authStore.role !== 1) {
      return { path: "/" };
    }
  }

  document.title = `FaBeTV - ${to.meta.title ? to.meta.title(to) : "Page"}`;
});

export default router;
