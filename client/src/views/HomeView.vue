<template>
  <section class="home-page">
    <div class="hero" :style="heroStyle">
      <div class="hero-shade"></div>
      <div class="hero-content">
        <p class="hero-kicker">Unlimited archive of Hungarian classics</p>
        <h1>{{ featuredMovie.title || "Hungarian Movie Archive" }}</h1>
        <p class="hero-meta">
          <span>{{ featuredMovie.produced || "Unknown" }}</span>
          <span>{{ featuredMovie.length || "N/A" }}</span>
          <span>{{ featuredCast }}</span>
        </p>
        <p class="hero-desc">Explore imported movies, actors and credits with a modern streaming UI.</p>

        <form class="hero-search" @submit.prevent="goToMovies">
          <input v-model.trim="searchInput" type="search" placeholder="Search in imported movies..." />
          <button type="submit">Search</button>
        </form>

        <div class="hero-actions">
          <button class="btn-play" type="button" @click="openFeaturedLink">
            <i class="bi bi-play-fill"></i>
            Watch
          </button>
          <RouterLink class="btn-info" to="/movies">Browse Movies</RouterLink>
        </div>
      </div>
    </div>

    <div class="rows" v-if="!loading">
      <section class="row-block">
        <h2>Trending Now</h2>
        <div class="row-grid">
          <article
            v-for="movie in trendingMovies"
            :key="movie.id"
            class="movie-card"
            :style="cardStyle(movie)"
            @click="openMovie(movie)"
          >
            <div class="card-overlay">
              <h3>{{ movie.title }}</h3>
              <p>{{ movie.produced || "-" }} - {{ movie.length || "N/A" }}</p>
            </div>
          </article>
        </div>
      </section>

      <section class="row-block">
        <h2>Top Cast</h2>
        <div class="actor-row">
          <article v-for="actor in topActors" :key="actor.id" class="actor-card">
            <img
              v-if="actor.photo"
              class="actor-photo"
              :src="getActorPhoto(actor.photo)"
              :alt="actor.name"
              @error="onPhotoError"
            />
            <h3>{{ actor.name }}</h3>
            <p>{{ actor.creditCount }} credits</p>
          </article>
        </div>
      </section>
    </div>

    <div v-else class="state">Loading catalog...</div>
    <div v-if="error" class="state error">{{ error }}</div>
  </section>
</template>

<script>
import { mapActions } from "pinia";
import { useSearchStore } from "@/stores/searchStore";
import personService from "@/api/personService";
import movieService from "@/api/movieService";
import taskService from "@/api/taskService";
import { getActorPhotoUrl, getMovieTargetUrl, getTrailerThumbnailUrl } from "@/utils/media";

export default {
  data() {
    return {
      loading: true,
      error: "",
      movies: [],
      people: [],
      tasks: [],
      searchInput: "",
    };
  },
  computed: {
    featuredMovie() {
      return this.movies[0] || {};
    },
    heroStyle() {
      return this.cardStyle(this.featuredMovie, true);
    },
    trendingMovies() {
      return this.movies.slice(0, 18);
    },
    topActors() {
      const countByPerson = this.tasks.reduce((acc, task) => {
        acc[task.personid] = (acc[task.personid] || 0) + 1;
        return acc;
      }, {});

      return this.people
        .map((person) => ({
          ...person,
          creditCount: countByPerson[person.id] || 0,
        }))
        .sort((a, b) => b.creditCount - a.creditCount)
        .slice(0, 12);
    },
    featuredCast() {
      if (!this.featuredMovie.id) return "No cast loaded";
      const castIds = this.tasks
        .filter((t) => t.movieid === this.featuredMovie.id)
        .slice(0, 3)
        .map((t) => t.personid);
      const names = this.people.filter((p) => castIds.includes(p.id)).map((p) => p.name);
      return names.length ? names.join(", ") : "Cast unavailable";
    },
  },
  methods: {
    ...mapActions(useSearchStore, ["setSearchWord"]),
    async loadData() {
      this.loading = true;
      this.error = "";
      try {
        const [moviesRes, peopleRes, tasksRes] = await Promise.all([
          movieService.getAll(),
          personService.getAll(),
          taskService.getAll(),
        ]);

        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
        this.people = Array.isArray(peopleRes.data) ? peopleRes.data : [];
        this.tasks = Array.isArray(tasksRes.data) ? tasksRes.data : [];
      } catch (err) {
        this.error = "Could not load imported data from API.";
      } finally {
        this.loading = false;
      }
    },
    cardStyle(item, hero = false) {
      const thumb = getTrailerThumbnailUrl(item?.watchlink);
      if (thumb) {
        return {
          backgroundImage: `linear-gradient(180deg, rgba(0,0,0,${hero ? 0.25 : 0.15}), rgba(0,0,0,0.78)), url("${thumb}")`,
          backgroundSize: "cover",
          backgroundPosition: "center",
        };
      }
      const text = item?.title || "movie";
      let hash = 0;
      for (let i = 0; i < text.length; i += 1) hash = text.charCodeAt(i) + ((hash << 5) - hash);
      const hue = Math.abs(hash % 360);
      const base = hero ? 28 : 38;
      return {
        background: `linear-gradient(135deg, hsl(${hue}, 70%, ${base}%), #101010 75%)`,
      };
    },
    goToMovies() {
      this.setSearchWord(this.searchInput);
      this.$router.push("/movies");
    },
    openFeaturedLink() {
      const target = getMovieTargetUrl(this.featuredMovie);
      if (target) {
        window.open(target, "_blank", "noopener,noreferrer");
      }
    },
    openMovie(movie) {
      this.$router.push(`/movies/${movie.id}`);
    },
    getActorPhoto(photoFileName) {
      return getActorPhotoUrl(photoFileName);
    },
    onPhotoError(event) {
      event.target.style.display = "none";
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped>
.home-page {
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 1.2rem 2rem;
}

.hero {
  margin-top: 1rem;
  min-height: 62vh;
  border-radius: 1rem;
  position: relative;
  overflow: hidden;
  display: flex;
  align-items: end;
}

.hero-shade {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, rgba(0, 0, 0, 0.25), rgba(0, 0, 0, 0.9));
}

.hero-content {
  position: relative;
  z-index: 2;
  width: min(760px, 100%);
  padding: 2rem;
}

.hero-kicker {
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #d3d3d3;
  font-size: 0.82rem;
}

.hero h1 {
  margin: 0.4rem 0 0.5rem;
  font-size: clamp(2rem, 5vw, 4rem);
  line-height: 1.05;
}

.hero-meta {
  display: flex;
  gap: 0.8rem;
  color: #d8d8d8;
  font-size: 0.95rem;
  flex-wrap: wrap;
}

.hero-desc {
  color: #d0d0d0;
  margin-top: 0.8rem;
}

.hero-search {
  margin-top: 1rem;
  max-width: 620px;
  display: flex;
  gap: 0.5rem;
}

.hero-search input {
  flex: 1;
  border: 1px solid rgba(255, 255, 255, 0.22);
  background: rgba(0, 0, 0, 0.35);
  color: #fff;
  border-radius: 0.4rem;
  padding: 0.7rem 0.85rem;
}

.hero-search button,
.btn-play,
.btn-info {
  border: 0;
  border-radius: 0.4rem;
  padding: 0.7rem 1rem;
  font-weight: 600;
  text-decoration: none;
}

.hero-search button,
.btn-play {
  background: #fff;
  color: #111;
}

.btn-info {
  background: rgba(109, 109, 110, 0.7);
  color: #fff;
}

.hero-actions {
  display: flex;
  gap: 0.65rem;
  margin-top: 0.9rem;
}

.rows {
  margin-top: 1.8rem;
}

.row-block {
  margin-bottom: 1.8rem;
}

.row-block h2 {
  margin: 0 0 0.7rem;
  font-size: 1.35rem;
}

.row-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
  gap: 0.7rem;
}

.movie-card {
  height: 230px;
  border-radius: 0.6rem;
  overflow: hidden;
  position: relative;
  transition: transform 0.2s ease;
  cursor: pointer;
}

.movie-card:hover {
  transform: translateY(-5px) scale(1.02);
}

.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(180deg, transparent 40%, rgba(0, 0, 0, 0.86));
  display: flex;
  justify-content: end;
  flex-direction: column;
  padding: 0.75rem;
}

.card-overlay h3 {
  margin: 0;
  font-size: 1rem;
}

.card-overlay p {
  margin: 0.25rem 0 0;
  color: #cfcfcf;
  font-size: 0.85rem;
}

.actor-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
  gap: 0.7rem;
}

.actor-card {
  background: #1a1a1a;
  border: 1px solid #2a2a2a;
  border-radius: 0.6rem;
  padding: 0.8rem;
}
.actor-photo {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 0.5rem;
  border: 1px solid #2d2d2d;
  margin-bottom: 0.55rem;
}

.actor-card h3 {
  margin: 0;
  font-size: 1rem;
}

.actor-card p {
  margin: 0.35rem 0 0;
  color: #bdbdbd;
  font-size: 0.9rem;
}

.state {
  margin-top: 1.5rem;
  color: #bdbdbd;
}

.error {
  color: #ff7777;
}
</style>
