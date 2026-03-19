import { fileURLToPath, URL } from "node:url";
import { defineConfig, loadEnv } from "vite";
import vue from "@vitejs/plugin-vue";

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), "VITE_");
  const buildDir = (env.VITE_BUILD_DIR || "").trim() || "dist";
  const webDir = (env.VITE_WEB_DIR || "").trim() || "/";

  return {
    plugins: [vue()],
    resolve: {
      alias: {
        "@": fileURLToPath(new URL("./src", import.meta.url)),
      },
    },
    build: {
      outDir: buildDir,
      emptyOutDir: false,
    },
    base: mode === "development" ? "/" : webDir,
  };
});
