import { defineStore } from "pinia";

let pendingResolve = null;

export const useConfirmStore = defineStore("confirm", {
  state: () => ({
    isOpen: false,
    title: "Confirm",
    message: "",
    confirmText: "Delete",
    cancelText: "Cancel",
  }),
  actions: {
    open(options = {}) {
      const {
        title = "Confirm",
        message = "",
        confirmText = "Delete",
        cancelText = "Cancel",
      } = options;

      this.title = title;
      this.message = message;
      this.confirmText = confirmText;
      this.cancelText = cancelText;
      this.isOpen = true;

      return new Promise((resolve) => {
        pendingResolve = resolve;
      });
    },
    confirm() {
      this.isOpen = false;
      if (pendingResolve) {
        pendingResolve(true);
        pendingResolve = null;
      }
    },
    cancel() {
      this.isOpen = false;
      if (pendingResolve) {
        pendingResolve(false);
        pendingResolve = null;
      }
    },
  },
});
