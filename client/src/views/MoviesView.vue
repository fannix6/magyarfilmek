<template>
  <section class="catalog-page">
    <header class="page-header">
      <h1 class="h1movies">Movies</h1>
      <p>{{ filteredMovies.length }} titles from imported dataset</p>
    </header>

    <div class="tools">
      <div class="search-tabs" role="tablist" aria-label="Search mode">
        <button
          type="button"
          class="search-tab"
          :class="{ active: searchMode === 'title' }"
          role="tab"
          :aria-selected="searchMode === 'title'"
          @click="setSearchMode('title')"
        >
          Title
        </button>
        <button
          type="button"
          class="search-tab"
          :class="{ active: searchMode === 'year' }"
          role="tab"
          :aria-selected="searchMode === 'year'"
          @click="setSearchMode('year')"
        >
          Year
        </button>
      </div>
      <input
        v-model.trim="localSearch"
        type="search"
        :placeholder="searchMode === 'year' ? 'Search year...' : 'Search title...'"
      />
      <select v-model="sortKey">
        <option value="title">Title</option>
        <option value="produced">Year</option>
      </select>
      <button v-if="isAdmin" class="btn-add" type="button" @click="openCreate">+ New Movie</button>
    </div>

    <form v-if="isAdmin && editorOpen" class="editor" @submit.prevent="saveMovie">
      <h2>{{ editId ? "Edit Movie" : "Create Movie" }}</h2>
      <div v-if="formErrors.length" class="form-errors">
        <p v-for="(msg, idx) in formErrors" :key="idx">{{ msg }}</p>
      </div>
      <input v-model.trim="form.title" placeholder="Title" required />
      <input v-model.number="form.produced" type="number" min="1888" placeholder="Year" />
      <input v-model.trim="form.length" placeholder="Length (e.g. 96 min)" />
      <input v-model.trim="form.premiere" placeholder="Premiere" />
      <input v-model.trim="form.watchlink" placeholder="Trailer / watch link" />
      <input v-model.trim="form.imdblink" placeholder="IMDb link" />
      <section class="actors">
        <div class="actors-header">
          <div>
            <h3>People</h3>
            <p>{{ actorSummary }}</p>
          </div>
          <button type="button" @click="openPeopleModal">Manage people</button>
        </div>
        <div v-if="actorsLoading" class="actors-state">Loading people...</div>
        <div v-else-if="actorsError" class="actors-state error">{{ actorsError }}</div>
        <div v-else class="actors-state">{{ actorSummary }}</div>
      </section>
      <div class="editor-actions">
        <button type="submit">Save</button>
        <button type="button" @click="closeEditor">Cancel</button>
      </div>
    </form>

    <div v-if="editorOpen && peopleModalOpen" class="people-modal" @click.self="closePeopleModal">
      <section class="people-modal-card">
        <div class="people-modal-header">
          <div>
            <h3>People</h3>
            <p>Add or remove cast rows for this movie.</p>
          </div>
          <button type="button" class="people-modal-close" @click="closePeopleModal" aria-label="Close">
            x
          </button>
        </div>
        <div v-if="actorsLoading" class="actors-state">Loading people...</div>
        <div v-else-if="actorsError" class="actors-state error">{{ actorsError }}</div>
        <div v-else class="people-table-wrap">
          <table class="people-table">
            <thead>
              <tr>
                <th>Actor</th>
                <th>Role</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="actors.length === 0">
                <td colspan="3" class="people-empty">No actors selected yet.</td>
              </tr>
              <tr v-for="(entry, idx) in actors" :key="entry.key">
                <td>
                  <select v-model.number="entry.personid">
                    <option :value="null" disabled>Select actor</option>
                    <option v-for="person in people" :key="person.id" :value="person.id">
                      {{ person.name }}
                    </option>
                  </select>
                </td>
                <td>
                  <select v-model.number="entry.roleid">
                    <option v-for="roleItem in roles" :key="roleItem.id" :value="roleItem.id">
                      {{ roleItem.role }}
                    </option>
                  </select>
                </td>
                <td>
                  <button type="button" class="danger-btn" @click="removeActorRow(idx)">- Remove</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="people-modal-actions">
          <button type="button" @click="addActorRow">+ Add actor</button>
          <button type="button" @click="closePeopleModal">Done</button>
        </div>
      </section>
    </div>

    <div v-if="loading" class="state">Loading movies...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else class="movies-grid">
      <article v-for="movie in filteredMovies" :key="movie.id" class="movie-card">
        <h2 class="movie-title">{{ movie.title }}</h2>
        <p><strong>Production Year:</strong> {{ movie.produced || "-" }}</p>
        <p><strong>Length:</strong> {{ movie.length ? movie.length + " minutes" : "-" }}</p>
        <p><strong>Presentation:</strong> {{ movie.premiere || "-" }}</p>
        <p><strong>Evaluation:</strong> {{ movieRating(movie) }}</p>

        <div class="actions">
          <a
            v-if="movie.watchlink"
            :href="movie.watchlink"
            target="_blank"
            rel="noopener noreferrer"
          >Full Movie</a>
          <button type="button" @click="openInfo(movie.id)">Info</button>
          <a
            v-if="movie.imdblink"
            :href="movie.imdblink"
            target="_blank"
            rel="noopener noreferrer"
          >IMDb</a>
          <button v-if="isAdmin" type="button" @click="openEdit(movie)">Edit</button>
          <button v-if="isAdmin" type="button" @click="removeMovie(movie.id)">Delete</button>
        </div>
      </article>
    </div>
  </section>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import movieService from "@/api/movieService";
import personService from "@/api/personService";
import roleService from "@/api/roleService";
import taskService from "@/api/taskService";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({
  title: "",
  produced: null,
  length: "",
  premiere: "",
  watchlink: "",
  imdblink: "",
});
const emptyActorRow = (roleId, key) => ({
  key,
  taskId: null,
  personid: null,
  roleid: roleId,
});

export default {
  data() {
    return {
      loading: true,
      error: "",
      movies: [],
      localSearch: "",
      sortKey: "title",
      editorOpen: false,
      editId: null,
      form: emptyForm(),
      formErrors: [],
      people: [],
      roles: [],
      actors: [],
      actorsLoading: false,
      actorsError: "",
      actorKey: 1,
      allTasks: [],
      peopleModalOpen: false,
    };
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord", "searchMode"]),
    ...mapState(useUserLoginLogoutStore, ["role", "isLoggedIn"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
    },
    actorSummary() {
      if (this.actorsLoading) return "Loading people...";
      if (this.actorsError) return this.actorsError;
      const count = this.actors.filter((actor) => Number(actor.personid)).length;
      if (!count) return "No actors selected.";
      return `${count} actor${count === 1 ? "" : "s"} selected`;
    },
    filteredMovies() {
      const needle = (this.localSearch || this.searchWord || "").toLowerCase();
      const sorted = [...this.movies].sort((a, b) => {
        if (this.sortKey === "produced") return (b.produced || 0) - (a.produced || 0);
        return (a.title || "").localeCompare(b.title || "");
      });
      if (!needle) return sorted;
      return sorted.filter((m) => {
        if (this.searchMode === "year") {
          const yearText = m.produced != null ? String(m.produced) : "";
          return yearText.includes(needle);
        }
        return (m.title || "").toLowerCase().includes(needle);
      });
    },
  },
  watch: {
    searchWord(value) {
      if (value) this.localSearch = value;
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["setSearchMode"]),
    async loadMovies() {
      this.loading = true;
      this.error = "";
      try {
        const [moviesRes, tasksRes] = await Promise.all([
          movieService.getAll(),
          taskService.getAll(),
        ]);
        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
        this.allTasks = Array.isArray(tasksRes.data) ? tasksRes.data : [];
      } catch (error) {
        this.error = "Failed to load movies.";
      } finally {
        this.loading = false;
      }
    },
    openInfo(id) {
      this.$router.push(`/movies/${id}`);
    },
    movieRating(movie) {
      if (!movie.imdblink) return "-";
      const seed = (movie.title || "").length + (movie.produced || 0);
      return (Math.max(1, Math.min(9.9, (seed % 90) / 10 + 1))).toFixed(1);
    },
    async openCreate() {
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
      this.actors = [];
      this.actorsError = "";
      await this.ensurePeopleRoles();
      this.addActorRow();
      this.editorOpen = true;
    },
    async openEdit(movie) {
      this.editId = movie.id;
      this.formErrors = [];
      this.form = {
        title: movie.title || "",
        produced: movie.produced || null,
        length: movie.length || "",
        premiere: movie.premiere || "",
        watchlink: movie.watchlink || "",
        imdblink: movie.imdblink || "",
      };
      this.actors = [];
      this.actorsError = "";
      await this.ensurePeopleRoles();
      this.loadActorsForMovie(movie.id);
      this.editorOpen = true;
    },
    closeEditor() {
      this.editorOpen = false;
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
      this.actors = [];
      this.actorsError = "";
      this.peopleModalOpen = false;
    },
    async openPeopleModal() {
      await this.ensurePeopleRoles();
      if (!this.actors.length && !this.actorsError) {
        this.addActorRow();
      }
      this.peopleModalOpen = true;
    },
    closePeopleModal() {
      this.peopleModalOpen = false;
    },
    normalizeUrl(value) {
      const trimmed = (value || "").trim();
      if (!trimmed) return "";
      if (/^https?:\/\//i.test(trimmed)) return trimmed;
      return `https://${trimmed}`;
    },
    async ensurePeopleRoles() {
      if (this.actorsLoading) return;
      if (this.people.length && this.roles.length) return;
      this.actorsLoading = true;
      this.actorsError = "";
      try {
        const [peopleRes, rolesRes] = await Promise.all([
          personService.getAll(),
          roleService.getAll(),
        ]);
        this.people = Array.isArray(peopleRes.data) ? peopleRes.data : [];
        this.roles = Array.isArray(rolesRes.data) ? rolesRes.data : [];
      } catch (error) {
        this.actorsError = "Failed to load actors list.";
      } finally {
        this.actorsLoading = false;
      }
    },
    addActorRow() {
      const defaultRoleId = this.roles.find((r) => r.id === 1)?.id || 1;
      this.actors.push(emptyActorRow(defaultRoleId, this.actorKey++));
    },
    removeActorRow(index) {
      this.actors.splice(index, 1);
    },
    loadActorsForMovie(movieId) {
      const defaultRoleId = this.roles.find((r) => r.id === 1)?.id || 1;
      const movieTasks = this.allTasks.filter((task) => task.movieid === movieId);
      this.actors = movieTasks.map((task) => ({
        key: this.actorKey++,
        taskId: task.id,
        personid: task.personid,
        roleid: task.roleid || defaultRoleId,
      }));
      if (!this.actors.length) {
        this.addActorRow();
      }
    },
    async syncActors(movieId) {
      const normalizedActors = this.actors
        .filter((entry) => Number(entry.personid))
        .map((entry) => ({
          ...entry,
          personid: Number(entry.personid),
          roleid: Number(entry.roleid) || 1,
        }));

      const existingTaskIds = new Set(
        this.allTasks.filter((task) => task.movieid === movieId).map((task) => task.id),
      );
      const keptTaskIds = new Set(
        normalizedActors.map((entry) => entry.taskId).filter((taskId) => Number(taskId)),
      );
      const deleteIds = [...existingTaskIds].filter((taskId) => !keptTaskIds.has(taskId));

      await Promise.all([
        ...normalizedActors.map((entry) => {
          const payload = {
            movieid: movieId,
            personid: entry.personid,
            roleid: entry.roleid,
          };
          if (entry.taskId) {
            return taskService.update(entry.taskId, payload);
          }
          return taskService.create(payload);
        }),
        ...deleteIds.map((taskId) => taskService.delete(taskId)),
      ]);
    },
    extractErrors(error) {
      const errors = error?.response?.data?.errors;
      if (!errors) return [];
      return Object.values(errors).flat().filter(Boolean);
    },
    async saveMovie() {
      try {
        this.formErrors = [];
        const payload = {
          ...this.form,
          watchlink: this.normalizeUrl(this.form.watchlink),
          imdblink: this.normalizeUrl(this.form.imdblink),
        };
        if (!payload.produced) payload.produced = null;
        if (this.editId) {
          await movieService.update(this.editId, payload);
          await this.syncActors(this.editId);
        } else {
          const created = await movieService.create(payload);
          const movie = created?.data;
          if (movie?.id) {
            await this.syncActors(movie.id);
          }
        }
        this.closeEditor();
        await this.loadMovies();
      } catch (error) {
        const errors = this.extractErrors(error);
        if (errors.length) {
          this.formErrors = errors;
        } else {
          this.error = "Save failed.";
        }
      }
    },
    async removeMovie(id) {
      const confirmStore = useConfirmStore();
      const ok = await confirmStore.open({
        title: "Delete movie?",
        message: "This action cannot be undone.",
        confirmText: "Delete movie",
        cancelText: "Cancel",
      });
      if (!ok) return;
      try {
        await movieService.delete(id);
        await this.loadMovies();
      } catch (error) {
        this.error = "Delete failed.";
      }
    },
  },
  mounted() {
    this.localSearch = this.searchWord || "";
    this.loadMovies();
  },
};
</script>

<style scoped src="@/assets/views/MoviesView.css"></style>
