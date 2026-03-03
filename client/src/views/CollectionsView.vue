<template>
  <section class="collections-page">
    <header class="page-header">
      <h1>Collections</h1>
      <p>Auto-grouped from imported movie titles</p>
    </header>

    <div v-if="loading" class="state">Loading collections...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else class="groups">
      <article v-for="group in grouped" :key="group.letter" class="group-card">
        <h2>{{ group.letter }}</h2>
        <p>{{ group.count }} movies</p>
      </article>
    </div>
  </section>
</template>

<script>
import movieService from "@/api/movieService";

export default {
  data() {
    return {
      loading: true,
      error: "",
      movies: [],
    };
  },
  computed: {
    grouped() {
      const counts = this.movies.reduce((acc, movie) => {
        const letter = (movie.title || "?")[0]?.toUpperCase() || "?";
        acc[letter] = (acc[letter] || 0) + 1;
        return acc;
      }, {});

      return Object.entries(counts)
        .map(([letter, count]) => ({ letter, count }))
        .sort((a, b) => a.letter.localeCompare(b.letter));
    },
  },
  methods: {
    async loadData() {
      this.loading = true;
      this.error = "";
      try {
        const response = await movieService.getAll();
        this.movies = Array.isArray(response.data) ? response.data : [];
      } catch (err) {
        this.error = "Failed to load collections.";
      } finally {
        this.loading = false;
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped src="@/assets/views/CollectionsView.css"></style>
