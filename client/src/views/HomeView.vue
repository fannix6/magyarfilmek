<template>
  <section class="home-page">
    <div class="hero" :style="heroStyle">
      <div class="hero-shade"></div>
      <div class="hero-content">
        <p class="hero-kicker">Unlimited archive of Hungarian classics</p>
        <h1>{{ featuredMovie.title || "Hungarian Movie Archive" }}</h1>
        <p class="hero-meta">
          <span>{{ featuredMovie.produced || "Unknown" }}</span>
          <span>{{ featuredMovie.length ? featuredMovie.length + " minutes" : "N/A" }}</span>
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
              <p>{{ movie.produced || "-" }} - {{ movie.length ? movie.length + " minutes" : "N/A" }}</p>
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
import reviewService from "@/api/reviewService";
import { getActorPhotoUrl, getMovieCoverUrl, getMovieTargetUrl, getTrailerThumbnailUrl } from "@/utils/media";

export default {
  data() {
    return {
      loading: true,
      error: "",
      movies: [],
      people: [],
      tasks: [],
      reviews: [],
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
      const stats = this.reviews.reduce((acc, review) => {
        const movieId = review.movieid;
        if (!movieId) return acc;
        const entry = acc[movieId] || { sum: 0, count: 0 };
        entry.sum += Number(review.score) || 0;
        entry.count += 1;
        acc[movieId] = entry;
        return acc;
      }, {});

      const avgByMovie = Object.entries(stats).map(([movieId, stat]) => ({
        movieId: Number(movieId),
        avg: stat.count ? stat.sum / stat.count : 0,
        count: stat.count,
      }));

      const rankedIds = avgByMovie
        .sort((a, b) => {
          if (b.avg !== a.avg) return b.avg - a.avg;
          if (b.count !== a.count) return b.count - a.count;
          return a.movieId - b.movieId;
        })
        .slice(0, 18)
        .map((item) => item.movieId);

      const movieById = Object.fromEntries(this.movies.map((m) => [m.id, m]));
      return rankedIds.map((id) => movieById[id]).filter(Boolean);
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
        const [moviesRes, peopleRes, tasksRes, reviewsRes] = await Promise.all([
          movieService.getAll(),
          personService.getAll(),
          taskService.getAll(),
          reviewService.getAll(),
        ]);

        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
        this.people = Array.isArray(peopleRes.data) ? peopleRes.data : [];
        this.tasks = Array.isArray(tasksRes.data) ? tasksRes.data : [];
        this.reviews = Array.isArray(reviewsRes.data) ? reviewsRes.data : [];
      } catch (err) {
        this.error = "Could not load imported data from API.";
      } finally {
        this.loading = false;
      }
    },
    cardStyle(item, hero = false) {
      const cover = getMovieCoverUrl(item?.cover);
      const thumb = cover || getTrailerThumbnailUrl(item?.watchlink);
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

<style scoped src="@/assets/views/HomeView.css"></style>
