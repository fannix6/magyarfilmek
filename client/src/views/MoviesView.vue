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
      <section v-if="!editId" class="actors">
        <div class="actors-header">
          <h3>Actors</h3>
          <button type="button" @click="addActorRow">+ Add actor</button>
        </div>
        <div v-if="actorsLoading" class="actors-state">Loading actors...</div>
        <div v-else-if="actorsError" class="actors-state error">{{ actorsError }}</div>
        <div v-else-if="actors.length === 0" class="actors-state">No actors selected.</div>
        <div v-else class="actors-list">
          <div v-for="(entry, idx) in actors" :key="entry.key" class="actor-row">
            <select v-model.number="entry.personid" required>
              <option :value="null" disabled>Select actor</option>
              <option v-for="person in people" :key="person.id" :value="person.id">
                {{ person.name }}
              </option>
            </select>
            <select v-model.number="entry.roleid">
              <option v-for="roleItem in roles" :key="roleItem.id" :value="roleItem.id">
                {{ roleItem.role }}
              </option>
            </select>
            <button type="button" @click="removeActorRow(idx)">Remove</button>
          </div>
        </div>
      </section>
      <div class="editor-actions">
        <button type="submit">Save</button>
        <button type="button" @click="closeEditor">Cancel</button>
      </div>
    </form>

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
    };
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord", "searchMode"]),
    ...mapState(useUserLoginLogoutStore, ["role", "isLoggedIn"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
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
        const response = await movieService.getAll();
        this.movies = Array.isArray(response.data) ? response.data : [];
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
    openCreate() {
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
      this.actors = [];
      this.ensurePeopleRoles();
      this.addActorRow();
      this.editorOpen = true;
    },
    openEdit(movie) {
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
      this.editorOpen = true;
    },
    closeEditor() {
      this.editorOpen = false;
      this.editId = null;
      this.form = emptyForm();
      this.formErrors = [];
      this.actors = [];
      this.actorsError = "";
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
        } else {
          const created = await movieService.create(payload);
          const movie = created?.data;
          const actorRows = this.actors.filter((a) => Number(a.personid));
          if (movie?.id && actorRows.length) {
            await Promise.all(
              actorRows.map((entry) =>
                taskService.create({
                  movieid: movie.id,
                  personid: Number(entry.personid),
                  roleid: Number(entry.roleid) || 1,
                }),
              ),
            );
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
