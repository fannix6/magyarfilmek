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

<style scoped>
.collections-page { max-width: 1280px; margin: 0 auto; padding: 1.3rem; }
.page-header h1 { margin: 0; font-size: 2rem; }
.page-header p { margin-top: 0.35rem; color: #b3b3b3; }
.groups { margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.65rem; }
.group-card { background: #171717; border: 1px solid #2a2a2a; border-radius: 0.6rem; padding: 0.8rem; }
.group-card h2 { margin: 0; }
.group-card p { margin: 0.35rem 0 0; color: #c7c7c7; font-size: 0.9rem; }
.state { color: #bdbdbd; }
.error { color: #ff7f7f; }
</style>
