<template>
  <section class="details-page">
    <div v-if="loading" class="state">Loading movie...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else-if="!movie" class="state">Movie not found.</div>
    <template v-else>
      <header class="hero" :style="heroStyle">
        <div class="shade"></div>
        <div class="hero-content">
          <h1>{{ movie.title }}</h1>
          <p class="meta">
            <span>{{ movie.produced || "Unknown year" }}</span>
            <span>{{ movie.length || "N/A" }}</span>
            <span>{{ movie.premiere || "No premiere data" }}</span>
          </p>
          <div class="hero-actions">
            <a
              v-if="movie.watchlink"
              class="btn btn-light"
              :href="movie.watchlink"
              target="_blank"
              rel="noopener noreferrer"
            >
              <i class="bi bi-play-fill"></i>
              Watch Trailer
            </a>
            <a
              v-if="movie.imdblink"
              class="btn btn-dark"
              :href="movie.imdblink"
              target="_blank"
              rel="noopener noreferrer"
            >
              IMDb
            </a>
          </div>
        </div>
      </header>

      <section class="reviews-section">
        <h2>Reviews</h2>

        <form v-if="isLoggedIn" class="review-form" @submit.prevent="saveReview">
          <input v-model.number="form.score" type="number" min="1" max="10" required placeholder="Score 1-10" />
          <textarea
            v-model.trim="form.opinion"
            rows="3"
            required
            placeholder="Write your review about this movie"
          ></textarea>
          <div class="form-actions">
            <button type="submit">{{ editId ? "Update Review" : "Post Review" }}</button>
            <button v-if="editId" type="button" @click="cancelEdit">Cancel</button>
          </div>
        </form>

        <p v-else class="hint">Guest mode: sign in to write a review.</p>

        <div v-if="movieReviews.length === 0" class="state">No reviews yet.</div>
        <article v-for="entry in movieReviews" :key="entry.id" class="review-card">
          <div class="score">{{ entry.score }}/10</div>
          <p class="opinion">{{ entry.opinion }}</p>
          <p class="author">By {{ entry.userid === currentUserId ? "You" : `User #${entry.userid}` }}</p>
          <div v-if="canEdit(entry)" class="actions">
            <button type="button" @click="startEdit(entry)">Edit</button>
            <button type="button" @click="removeReview(entry.id)">Delete</button>
          </div>
        </article>
      </section>
    </template>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import movieService from "@/api/movieService";
import reviewService from "@/api/reviewService";
import { getTrailerThumbnailUrl } from "@/utils/media";

const emptyForm = () => ({ score: 8, opinion: "" });

export default {
  data() {
    return {
      loading: true,
      error: "",
      movie: null,
      reviews: [],
      form: emptyForm(),
      editId: null,
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "role", "item"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
    },
    currentUserId() {
      return this.item?.id || null;
    },
    movieId() {
      return Number(this.$route.params.id);
    },
    heroStyle() {
      const thumb = getTrailerThumbnailUrl(this.movie?.watchlink);
      return {
        backgroundImage: `linear-gradient(180deg, rgba(0,0,0,0.25), rgba(0,0,0,0.82)), url("${thumb}")`,
        backgroundSize: "cover",
        backgroundPosition: "center",
      };
    },
    movieReviews() {
      return this.reviews.filter((r) => r.movieid === this.movieId);
    },
  },
  methods: {
    canEdit(entry) {
      return this.isAdmin || (this.currentUserId && entry.userid === this.currentUserId);
    },
    async loadData() {
      this.loading = true;
      this.error = "";
      try {
        const [movieRes, reviewsRes] = await Promise.all([
          movieService.getById(this.movieId),
          reviewService.getAll(),
        ]);
        this.movie = movieRes.data || null;
        this.reviews = Array.isArray(reviewsRes.data) ? reviewsRes.data : [];
      } catch (error) {
        this.error = "Could not load movie details.";
      } finally {
        this.loading = false;
      }
    },
    async saveReview() {
      try {
        if (this.editId) {
          await reviewService.update(this.editId, { score: this.form.score, opinion: this.form.opinion });
        } else {
          await reviewService.create({
            score: this.form.score,
            opinion: this.form.opinion,
            movieid: this.movieId,
          });
        }
        this.cancelEdit();
        await this.loadData();
      } catch (error) {
        this.error = "Failed to save review.";
      }
    },
    startEdit(entry) {
      this.editId = entry.id;
      this.form = { score: entry.score, opinion: entry.opinion };
    },
    cancelEdit() {
      this.editId = null;
      this.form = emptyForm();
    },
    async removeReview(id) {
      if (!confirm("Delete this review?")) return;
      try {
        await reviewService.delete(id);
        await this.loadData();
      } catch (error) {
        this.error = "Failed to delete review.";
      }
    },
  },
  watch: {
    "$route.params.id"() {
      this.cancelEdit();
      this.loadData();
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped>
.details-page { max-width: 1280px; margin: 0 auto; padding: 1.2rem; }
.hero { min-height: 56vh; border-radius: 0.9rem; overflow: hidden; position: relative; display: flex; align-items: end; }
.shade { position: absolute; inset: 0; background: linear-gradient(180deg, rgba(0,0,0,0.12), rgba(0,0,0,0.86)); }
.hero-content { position: relative; z-index: 2; padding: 1.5rem; width: min(880px, 100%); }
.hero-content h1 { margin: 0; font-size: clamp(2rem, 5vw, 3.6rem); line-height: 1.05; }
.meta { margin-top: 0.7rem; display: flex; gap: 0.7rem; flex-wrap: wrap; color: #d3d3d3; }
.hero-actions { margin-top: 1rem; display: flex; gap: 0.55rem; flex-wrap: wrap; }
.btn { border-radius: 0.4rem; padding: 0.55rem 0.9rem; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 0.2rem; }
.btn-light { background: #fff; color: #111; }
.btn-dark { background: rgba(109,109,110,0.75); color: #fff; }
.reviews-section { margin-top: 1.4rem; }
.reviews-section h2 { margin: 0 0 0.75rem; }
.hint { color: #b9b9b9; }
.review-form { margin-bottom: 1rem; display: grid; gap: 0.55rem; max-width: 640px; background: #151515; border: 1px solid #2a2a2a; border-radius: 0.65rem; padding: 0.9rem; }
.review-form input, .review-form textarea { background: #202020; border: 1px solid #333; color: #fff; border-radius: 0.4rem; padding: 0.6rem 0.7rem; }
.form-actions { display: flex; gap: 0.45rem; }
.form-actions button { border: 0; border-radius: 0.4rem; padding: 0.52rem 0.8rem; background: #2f2f2f; color: #fff; }
.form-actions button:first-child { background: #e50914; }
.review-card { background: #171717; border: 1px solid #2a2a2a; border-radius: 0.65rem; padding: 0.9rem; margin-bottom: 0.65rem; }
.score { display: inline-block; background: #e50914; border-radius: 999px; padding: 0.2rem 0.55rem; font-size: 0.8rem; font-weight: 700; }
.opinion { margin: 0.6rem 0 0; color: #d6d6d6; }
.author { margin: 0.55rem 0 0; color: #b9b9b9; font-size: 0.9rem; }
.actions { margin-top: 0.55rem; display: flex; gap: 0.45rem; }
.actions button { border: 0; background: rgba(255,255,255,0.14); color: #fff; border-radius: 0.35rem; padding: 0.3rem 0.55rem; }
.state { color: #bdbdbd; }
.error { color: #ff7f7f; }
</style>

