<template>
  <section class="catalog-page">
    <header class="page-header">
      <h1>Movies</h1>
      <p>{{ filteredMovies.length }} titles from imported dataset</p>
    </header>

    <div class="tools">
      <input v-model.trim="localSearch" type="search" placeholder="Search title..." />
      <select v-model="sortKey">
        <option value="title">Title</option>
        <option value="produced">Year</option>
      </select>
      <button v-if="isAdmin" class="btn-add" type="button" @click="openCreate">+ New Movie</button>
    </div>

    <form v-if="isAdmin && editorOpen" class="editor" @submit.prevent="saveMovie">
      <h2>{{ editId ? "Edit Movie" : "Create Movie" }}</h2>
      <input v-model.trim="form.title" placeholder="Title" required />
      <input v-model.number="form.produced" type="number" min="1888" placeholder="Year" />
      <input v-model.trim="form.length" placeholder="Length (e.g. 96 min)" />
      <input v-model.trim="form.premiere" placeholder="Premiere" />
      <input v-model.trim="form.watchlink" placeholder="Trailer / watch link" />
      <input v-model.trim="form.imdblink" placeholder="IMDb link" />
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
        <p><strong>Length:</strong> {{ movie.length || "-" }}</p>
        <p><strong>Presentation:</strong> {{ movie.premiere || "-" }}</p>
        <p><strong>Evaluation:</strong> {{ movieRating(movie) }}</p>

        <div class="actions">
          <button
            v-if="movie.watchlink"
            type="button"
            class="is-disabled"
            disabled
          >Trailer</button>
          <button type="button" class="is-disabled" disabled>Info</button>
          <button
            v-if="movie.imdblink"
            type="button"
            class="is-disabled"
            disabled
          >IMDb</button>
          <button v-if="isAdmin" type="button" @click="openEdit(movie)">Edit</button>
          <button v-if="isAdmin" type="button" @click="removeMovie(movie.id)">Delete</button>
        </div>
      </article>
    </div>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import movieService from "@/api/movieService";

const emptyForm = () => ({
  title: "",
  produced: null,
  length: "",
  premiere: "",
  watchlink: "",
  imdblink: "",
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
    };
  },
  computed: {
    ...mapState(useSearchStore, ["searchWord"]),
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
      return sorted.filter((m) => (m.title || "").toLowerCase().includes(needle));
    },
  },
  watch: {
    searchWord(value) {
      if (value) this.localSearch = value;
    },
  },
  methods: {
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
    movieRating(movie) {
      if (!movie.imdblink) return "-";
      const seed = (movie.title || "").length + (movie.produced || 0);
      return (Math.max(1, Math.min(9.9, (seed % 90) / 10 + 1))).toFixed(1);
    },
    openCreate() {
      this.editId = null;
      this.form = emptyForm();
      this.editorOpen = true;
    },
    openEdit(movie) {
      this.editId = movie.id;
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
    },
    async saveMovie() {
      try {
        const payload = { ...this.form };
        if (!payload.produced) payload.produced = null;
        if (this.editId) {
          await movieService.update(this.editId, payload);
        } else {
          await movieService.create(payload);
        }
        this.closeEditor();
        await this.loadMovies();
      } catch (error) {
        this.error = "Save failed.";
      }
    },
    async removeMovie(id) {
      if (!confirm("Delete this movie?")) return;
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
