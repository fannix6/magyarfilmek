<template>
  <section class="roles-page">
    <header class="page-header">
      <h1 class="roles-title">Roles</h1>
      <p>{{ roles.length }} roles in the catalog</p>
    </header>

    <div v-if="!isAdmin" class="state error">
      Admin access required.
    </div>

    <template v-else>
      <div class="tools">
        <input v-model.trim="query" type="search" placeholder="Search role..." />
        <button class="btn-add" type="button" @click="openCreate">+ New Role</button>
      </div>

      <form v-if="editorOpen" class="editor" @submit.prevent="saveRole">
        <h2>{{ editId ? "Edit Role" : "Create Role" }}</h2>
        <div v-if="formErrors.length" class="form-errors">
          <p v-for="(msg, idx) in formErrors" :key="idx">{{ msg }}</p>
        </div>
        <input v-model.trim="form.role" placeholder="Role name" required />
        <div class="editor-actions">
          <button type="submit">Save</button>
          <button type="button" @click="closeEditor">Cancel</button>
        </div>
      </form>

      <div v-if="loading" class="state">Loading roles...</div>
      <div v-else-if="error" class="state error">{{ error }}</div>
      <div v-else-if="filteredRoles.length === 0" class="state">No roles found.</div>
      <div v-else class="roles-grid">
        <article v-for="roleItem in filteredRoles" :key="roleItem.id" class="role-card">
          <div>
            <p class="role-id">#{{ roleItem.id }}</p>
            <h2>{{ roleItem.role }}</h2>
          </div>
          <div class="role-actions">
            <button type="button" @click="openEdit(roleItem)">Edit</button>
            <button type="button" class="danger-btn" @click="removeRole(roleItem.id)">Delete</button>
          </div>
        </article>
      </div>
    </template>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import roleService from "@/api/roleService";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({ role: "" });

export default {
  data() {
    return {
      loading: true,
      error: "",
      roles: [],
      query: "",
      editorOpen: false,
      editId: null,
      form: emptyForm(),
      formErrors: [],
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "role"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
    },
    filteredRoles() {
      const needle = this.query.toLowerCase();
      return this.roles
        .filter((item) => (item.role || "").toLowerCase().includes(needle))
        .sort((a, b) => (a.role || "").localeCompare(b.role || ""));
    },
  },
  methods: {
    async loadRoles() {
      if (!this.isAdmin) {
        this.loading = false;
        return;
      }
      this.loading = true;
      this.error = "";
      try {
        const response = await roleService.getAll();
        this.roles = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        this.error = "Failed to load roles.";
      } finally {
        this.loading = false;
      }
    },
    openCreate() {
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
      this.editorOpen = true;
    },
    openEdit(roleItem) {
      this.editId = roleItem.id;
      this.form = { role: roleItem.role || "" };
      this.formErrors = [];
      this.editorOpen = true;
    },
    closeEditor() {
      this.editorOpen = false;
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
    },
    extractErrors(error) {
      const errors = error?.response?.data?.errors;
      if (!errors) return [];
      return Object.values(errors).flat().filter(Boolean);
    },
    async saveRole() {
      try {
        this.formErrors = [];
        const payload = { role: this.form.role.trim() };
        if (this.editId) {
          await roleService.update(this.editId, payload);
        } else {
          await roleService.create(payload);
        }
        this.closeEditor();
        await this.loadRoles();
      } catch (error) {
        const errors = this.extractErrors(error);
        if (errors.length) {
          this.formErrors = errors;
        } else {
          this.error = "Save failed.";
        }
      }
    },
    async removeRole(id) {
      const confirmStore = useConfirmStore();
      const ok = await confirmStore.open({
        title: "Delete role?",
        message: "If this role is used by cast records, deletion may fail.",
        confirmText: "Delete role",
        cancelText: "Cancel",
      });
      if (!ok) return;
      try {
        await roleService.delete(id);
        await this.loadRoles();
      } catch (error) {
        const message = error?.response?.data?.message;
        this.error = message || "Delete failed. This role may still be in use.";
      }
    },
  },
  mounted() {
    this.loadRoles();
  },
};
</script>

<style scoped src="@/assets/views/RolesView.css"></style>
