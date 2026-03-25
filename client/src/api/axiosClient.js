import axios from "axios";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";

const resolveBaseUrl = () => {
  const envUrl = (import.meta.env.VITE_API_URL || "").trim();
  if (envUrl) return envUrl;

  // Safe fallback for prod builds without env: same-origin /api
  if (import.meta.env.PROD && typeof window !== "undefined") {
    return `${window.location.origin}/api`;
  }

  // Dev fallback (local Laravel default)
  return "http://localhost:8000/api";
};

const apiClient = axios.create({
  baseURL: resolveBaseUrl(),
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

apiClient.interceptors.request.use(
  (config) => {
    const token = useUserLoginLogoutStore().token;
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

apiClient.interceptors.response.use(
  (response) => response.data,
  (error) => {
        
    const toastStore = useToastStore();
    if (error.response) {
      const status = error.response.status;
      let message = error.response.data.message || "Error";

      if (status === 422) {
        return Promise.reject(error);
      }

      if (status === 401) {
        const authStore = useUserLoginLogoutStore();
        const hadToken = Boolean(authStore.token);

        if (hadToken) {
          authStore.clearSession();
          message = "Your login has expired. Please log in again.";

          if (typeof window !== "undefined" && window.location.pathname !== "/login") {
            window.setTimeout(() => {
              const nextPath = `${window.location.pathname}${window.location.search}`;
              window.location.href = `/login?redirect=${encodeURIComponent(nextPath)}`;
            }, 100);
          }
        }

        toastStore.messages.push(message);
        toastStore.show("Error");
        return Promise.reject(error);
      }

      if (status === 402) {
        message = "Payment required.";
      }

      if (status === 403) {
        message = "Access denied.";
      }

      if (status === 404) {
        message = "Resource not found.";
      }

      if (status === 409) {
        message = "Conflict: this record already exists or is still in use.";
      }

      if (status === 500) {
        if (message.includes("1451")) {
          message =
          "The row cannot be deleted because it already exists in another table!";
        } else {
          message = "A server-side error occurred during the operation.";
        }
      } 
      toastStore.messages.push(message);
      toastStore.show("Error");
    } else {
      toastStore.messages.push("The server is unavaible");
      toastStore.show("Error");
    }

    return Promise.reject(error);
  },
);

export default apiClient;
