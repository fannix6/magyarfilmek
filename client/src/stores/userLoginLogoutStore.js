import { defineStore } from "pinia";
// import { useToastStore } from "@/stores/toastStore";
import service from "@/api/userLoginLogoutService";
import { useToastStore } from "./toastStore";

export const useUserLoginLogoutStore = defineStore("userLoginLogout", {
  //Ezek a változók
  state: () => ({
    item: JSON.parse(localStorage.getItem("user_data")) || null,
    loading: false,
    error: null,
    rolNames: ["Admin", ""],
  }),
  //valamilyen formában visszaadja
  getters: {
    token() {
      if (!this.item) {
        return null;
      }
      return this.item.token;
    },
    role() {
      if (!this.item) {
        return null;
      }
      return this.item.role;
    },
    userName() {
      if (!this.item) {
        return null;
      }
      return this.item.name;
    },
    userNameWithRole() {
      if (!this.item) {
        return null;
      }
      const userInfo = `${this.item.name}`;
      return userInfo;
    },
    isLoggedIn() {
      return this.item != null ? true : false;
    },
  },

  actions: {
    clearSession() {
      this.item = null;
      localStorage.removeItem("user_data");
    },
    canAccess(requiredRoles) {
      if (!requiredRoles || requiredRoles.length === 0) return true;
      if (!this.isLoggedIn) return false;
      return requiredRoles.includes(this.role);
    },
    async login(data) {
      try {
        this.loading = true;
        this.error = null;
        const response = await service.login(data);
        this.item = response.data;
        localStorage.setItem("user_data", JSON.stringify(response.data));
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
    async logout() {
      try {
        this.error = null;
        this.loading = true;
        const response = await service.logout();
        this.clearSession();
        const toastStore = useToastStore();
        toastStore.messages.push("Logout successful");
        toastStore.show("Success");

        return true;
      } catch (err) {
        this.error = err;
        this.clearSession();
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
    async getMeRefresh() {
      try {
        this.error = null;
        this.loading = true;
        const response = await service.getMeRefresh();
        this.item.name = response.data.name;
        this.item.email = response.data.email;
        return true;
      } catch (err) {
        this.error = err;
        throw err;
        return false;
      } finally {
        this.loading = false;
      }
    },
  },
});
