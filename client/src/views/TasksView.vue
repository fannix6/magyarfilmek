<template>
  <section class="tasks-page">
    <header class="page-header">
      <h1 class="tasks-title">Tasks</h1>
      <p>{{ filteredTasks.length }} cast links in the catalog</p>
    </header>

    <div v-if="!isAdmin" class="state error">Admin access required.</div>

    <template v-else>
      <div class="tools">
        <input v-model.trim="query" type="search" placeholder="Search movie, actor or role..." />
        <button class="btn-add" type="button" @click="openCreate">+ New Task</button>
      </div>

      <form v-if="editorOpen" class="editor" @submit.prevent="saveTask">
        <h2>{{ editId ? "Edit Task" : "Create Task" }}</h2>
        <div v-if="formErrors.length" class="form-errors">
          <p v-for="(msg, idx) in formErrors" :key="idx">{{ msg }}</p>
        </div>
        <select v-model.number="form.movieid" required>
          <option :value="null" disabled>Select movie</option>
          <option v-for="movie in movies" :key="movie.id" :value="movie.id">
            {{ movie.title }}
          </option>
        </select>
        <select v-model.number="form.personid" required>
          <option :value="null" disabled>Select actor</option>
          <option v-for="person in people" :key="person.id" :value="person.id">
            {{ person.name }}
          </option>
        </select>
        <select v-model.number="form.roleid" required>
          <option :value="null" disabled>Select role</option>
          <option v-for="roleItem in roles" :key="roleItem.id" :value="roleItem.id">
            {{ roleItem.role }}
          </option>
        </select>
        <div class="editor-actions">
          <button type="submit">Save</button>
          <button type="button" @click="closeEditor">Cancel</button>
        </div>
      </form>

      <div v-if="loading" class="state">Loading tasks...</div>
      <div v-else-if="error" class="state error">{{ error }}</div>
      <div v-else-if="filteredTasks.length === 0" class="state">No tasks found.</div>
      <div v-else class="tasks-table-wrap">
        <table class="tasks-table">
          <thead>
            <tr>
              <th>Movie</th>
              <th>Actor</th>
              <th>Role</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="task in filteredTasks" :key="task.id">
              <td>{{ movieName(task.movieid) }}</td>
              <td>{{ personName(task.personid) }}</td>
              <td>{{ roleName(task.roleid) }}</td>
              <td class="actions-cell">
                <button type="button" @click="openEdit(task)">Edit</button>
                <button type="button" class="danger-btn" @click="removeTask(task.id)">Delete</button>
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
import taskService from "@/api/taskService";
import movieService from "@/api/movieService";
import personService from "@/api/personService";
import roleService from "@/api/roleService";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({
  movieid: null,
  personid: null,
  roleid: null,
});

export default {
  data() {
    return {
      loading: true,
      error: "",
      query: "",
      tasks: [],
      movies: [],
      people: [],
      roles: [],
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
    filteredTasks() {
      const needle = this.query.toLowerCase();
      const rows = [...this.tasks].sort((a, b) => {
        const movieCompare = this.movieName(a.movieid).localeCompare(this.movieName(b.movieid));
        if (movieCompare !== 0) return movieCompare;
        const personCompare = this.personName(a.personid).localeCompare(this.personName(b.personid));
        if (personCompare !== 0) return personCompare;
        return this.roleName(a.roleid).localeCompare(this.roleName(b.roleid));
      });
      if (!needle) return rows;
      return rows.filter((task) => {
        const haystack = [
          this.movieName(task.movieid),
          this.personName(task.personid),
          this.roleName(task.roleid),
        ].join(" ").toLowerCase();
        return haystack.includes(needle);
      });
    },
  },
  methods: {
    async loadData() {
      if (!this.isAdmin) {
        this.loading = false;
        return;
      }
      this.loading = true;
      this.error = "";
      try {
        const [tasksRes, moviesRes, peopleRes, rolesRes] = await Promise.all([
          taskService.getAll(),
          movieService.getAll(),
          personService.getAll(),
          roleService.getAll(),
        ]);
        this.tasks = Array.isArray(tasksRes.data) ? tasksRes.data : [];
        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
        this.people = Array.isArray(peopleRes.data) ? peopleRes.data : [];
        this.roles = Array.isArray(rolesRes.data) ? rolesRes.data : [];
      } catch (error) {
        this.error = "Failed to load tasks.";
      } finally {
        this.loading = false;
      }
    },
    movieName(movieId) {
      return this.movies.find((movie) => movie.id === movieId)?.title || `#${movieId}`;
    },
    personName(personId) {
      return this.people.find((person) => person.id === personId)?.name || `#${personId}`;
    },
    roleName(roleId) {
      return this.roles.find((roleItem) => roleItem.id === roleId)?.role || `#${roleId}`;
    },
    openCreate() {
      this.editId = null;
      this.form = {
        movieid: this.movies[0]?.id || null,
        personid: this.people[0]?.id || null,
        roleid: this.roles[0]?.id || null,
      };
      this.formErrors = [];
      this.editorOpen = true;
    },
    openEdit(task) {
      this.editId = task.id;
      this.form = {
        movieid: task.movieid ?? null,
        personid: task.personid ?? null,
        roleid: task.roleid ?? null,
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
    async saveTask() {
      try {
        this.formErrors = [];
        const payload = {
          movieid: Number(this.form.movieid),
          personid: Number(this.form.personid),
          roleid: Number(this.form.roleid),
        };
        if (this.editId) {
          await taskService.update(this.editId, payload);
        } else {
          await taskService.create(payload);
        }
        this.closeEditor();
        await this.loadData();
      } catch (error) {
        const errors = this.extractErrors(error);
        if (errors.length) {
          this.formErrors = errors;
        } else {
          this.error = "Save failed.";
        }
      }
    },
    async removeTask(id) {
      const confirmStore = useConfirmStore();
      const ok = await confirmStore.open({
        title: "Delete task?",
        message: "This cast link will be removed.",
        confirmText: "Delete task",
        cancelText: "Cancel",
      });
      if (!ok) return;
      try {
        await taskService.delete(id);
        await this.loadData();
      } catch (error) {
        this.error = "Delete failed.";
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped src="@/assets/views/TasksView.css"></style>
