import axios from "axios";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import { useToastStore } from "@/stores/toastStore";

// apiClient objektum:
// tartalmazza az összes crud függvényt
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
  baseURL: resolveBaseUrl(), //..:8000/api
  headers: {
    Accept: "application/json",
    "Content-Type": "application/json",
  },
});

// REQUEST INTERCEPTOR (elfogó):
// Lefut minden egyes kérés előtt
apiClient.interceptors.request.use(
  (config) => {
    const token = useUserLoginLogoutStore().token; // Vagy a Pinia store-ból
    // const token = "";
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// RESPONSE INTERCEPTOR:
// Lefut minden válasz érkezésekor
apiClient.interceptors.response.use(
  // Ha minden ok, akkor vissza: adatok
  (response) => response.data,
  //Ha hiba: akkor kezeli a hibát és visszaküld egy Promies.reject(error)-t
  (error) => {
        
    const toastStore = useToastStore();
    // Ha a szerver válaszolt
    if (error.response) {
      const status = error.response.status;
      let message = error.response.data.message || "Hiba történt";

      // 1. Speciális eset: 422 Unprocessable Entity (Validációs hiba)
      if (status === 422) {
        // Itt NE dobjunk toast-ot, mert a Bootstrap mezők alá akarjuk tenni a hibát.
        // Csak adjuk tovább a hibát a komponensnek.
        return Promise.reject(error);
      }

      // 2. Speciális eset: 401 Unauthorized
      if (status === 401) {
        const authStore = useUserLoginLogoutStore();
        const hadToken = Boolean(authStore.token);

        if (hadToken) {
          authStore.clearSession();
          message = "Lejart a bejelentkezesed. Jelentkezz be ujra.";

          if (typeof window !== "undefined" && window.location.pathname !== "/login") {
            window.setTimeout(() => {
              const nextPath = `${window.location.pathname}${window.location.search}`;
              window.location.href = `/login?redirect=${encodeURIComponent(nextPath)}`;
            }, 100);
          }
        }

        // Ha login-nál kapunk 401-et, azt kiírhatjuk toast-ban (pl. Rossz jelszó)
        toastStore.messages.push(message);
        toastStore.show("Error");
        return Promise.reject(error);
      }

      
      if (status === 500) {
        // Megnézzük, hogy a szerver üzenete tartalmazza-e a MySQL 1451-es kódját
        if (message.includes("1451")) {
          message =
          "A sor nem törölhető, mert már szerepel egy másik táblában!";
        } else {
          message = "Szerver oldali hiba történt a művelet során.";
        }
      }
      
      // 3. Minden egyéb hiba (500, 404, 403, stb.)
      toastStore.messages.push(message);
      toastStore.show("Error");
    } else {
      // Hálózati hiba (nincs válasz)
      toastStore.messages.push("A szerver nem elérhető.");
      toastStore.show("Error");
    }

    return Promise.reject(error);
  },
);

export default apiClient;
