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
          <div class="score">{{ entry.score }}/5</div>
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

<style scoped src="@/assets/views/MovieDetailsView.css"></style>
