<template>
  <section class="users-page">
    <header class="page-header">
      <h1 class="users-title">Users</h1>
      <p>{{ filteredUsers.length }} registered users</p>
    </header>

    <div v-if="!isAdmin" class="state error">Admin access required.</div>

    <template v-else>
      <div class="tools">
        <input v-model.trim="query" type="search" placeholder="Search user by name or email..." />
      </div>

      <form v-if="editorOpen" class="editor" @submit.prevent="saveUser">
        <h2>Edit User</h2>
        <div v-if="formErrors.length" class="form-errors">
          <p v-for="(msg, idx) in formErrors" :key="idx">{{ msg }}</p>
        </div>
        <input v-model.trim="form.name" placeholder="Name" />
        <input v-model.trim="form.email" type="email" placeholder="Email" />
        <select v-model.number="form.role">
          <option :value="1">Admin</option>
          <option :value="2">User</option>
        </select>
        <input v-model.trim="form.password" type="password" placeholder="New password (optional)" />
        <div class="editor-actions">
          <button type="submit">Save</button>
          <button type="button" @click="closeEditor">Cancel</button>
        </div>
      </form>

      <div v-if="loading" class="state">Loading users...</div>
      <div v-else-if="error" class="state error">{{ error }}</div>
      <div v-else-if="filteredUsers.length === 0" class="state">No users found.</div>
      <div v-else class="users-table-wrap">
        <table class="users-table">
          <thead>
            <tr>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id">
              <td>{{ user.name }}</td>
              <td>{{ user.email }}</td>
              <td>{{ roleLabel(user.role) }}</td>
              <td class="actions-cell">
                <button type="button" @click="openEdit(user)">Edit</button>
                <button type="button" class="danger-btn" @click="removeUser(user.id)">Delete</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </template>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import userService from "@/api/userService";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({
  name: "",
  email: "",
  role: 2,
  password: "",
});

export default {
  data() {
    return {
      loading: true,
      error: "",
      users: [],
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
    filteredUsers() {
      const needle = this.query.toLowerCase();
      const rows = [...this.users].sort((a, b) => (a.name || "").localeCompare(b.name || ""));
      if (!needle) return rows;
      return rows.filter((user) =>
        `${user.name || ""} ${user.email || ""}`.toLowerCase().includes(needle),
      );
    },
  },
  methods: {
    async loadUsers() {
      if (!this.isAdmin) {
        this.loading = false;
        return;
      }
      this.loading = true;
      this.error = "";
      try {
        const response = await userService.getAll();
        this.users = Array.isArray(response.data) ? response.data : [];
      } catch (error) {
        this.error = "Failed to load users.";
      } finally {
        this.loading = false;
      }
    },
    roleLabel(role) {
      return Number(role) === 1 ? "Admin" : "User";
    },
    openEdit(user) {
      this.editId = user.id;
      this.form = {
        name: user.name || "",
        email: user.email || "",
        role: Number(user.role) || 2,
        password: "",
      };
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
    async saveUser() {
      try {
        this.formErrors = [];
        const payload = {
          name: this.form.name.trim(),
          email: this.form.email.trim(),
          role: Number(this.form.role),
        };
        if (this.form.password.trim()) {
          payload.password = this.form.password.trim();
        }
        await userService.update(this.editId, payload);
        this.closeEditor();
        await this.loadUsers();
      } catch (error) {
        const errors = this.extractErrors(error);
        if (errors.length) {
          this.formErrors = errors;
        } else {
          this.error = "Save failed.";
        }
      }
    },
    async removeUser(id) {
      const confirmStore = useConfirmStore();
      const ok = await confirmStore.open({
        title: "Delete user?",
        message: "This user account will be removed.",
        confirmText: "Delete user",
        cancelText: "Cancel",
      });
      if (!ok) return;
      try {
        await userService.delete(id);
        await this.loadUsers();
      } catch (error) {
        const message = error?.response?.data?.message;
        this.error = message || "Delete failed.";
      }
    },
  },
  mounted() {
    this.loadUsers();
  },
};
</script>

<style scoped src="@/assets/views/UsersView.css"></style>
