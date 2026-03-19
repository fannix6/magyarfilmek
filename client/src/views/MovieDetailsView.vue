<template>
  <section class="details-page">
    <div v-if="loading" class="state">Loading movie...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else-if="!movie" class="state">Movie not found.</div>
    <template v-else>
      <header class="hero" :style="heroStyle">
        <div class="shade"></div>
        <div class="hero-content">
          <h1 class="h2title">{{ movie.title }}</h1>
          <p class="meta">
            <span>{{ movie.produced || "Unknown year" }}</span>
            <span>{{ movie.length ? movie.length + " minutes" : "N/A" }}</span>
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
              Watch Movie
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
            <div class="avg-score" v-if="reviewCount">
              <StarRating :model-value="averageScore" readonly size="lg" />
              <span class="avg-score__meta">{{ averageScore.toFixed(1) }}/5 · {{ reviewCount }} review{{ reviewCount === 1 ? "" : "s" }}</span>
            </div>
            <div class="avg-score avg-score--empty" v-else>
              <StarRating :model-value="0" readonly size="lg" />
              <span class="avg-score__meta">No reviews yet</span>
            </div>
          </div>
        </div>
      </header>

      <section class="reviews-section">
        <h2 class="h2review">Reviews</h2>

        <form v-if="isLoggedIn" class="review-form" @submit.prevent="saveReview">
          <div class="star-input">
            <StarRating v-model="form.score" />
            <span class="star-input__value">{{ form.score.toFixed(1) }}/5</span>
          </div>
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
          <StarRating :model-value="scoreToStars(entry.score)" readonly />
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
import StarRating from "@/components/StarRating.vue";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({ score: 4, opinion: "" });

export default {
  components: { StarRating },
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
    reviewCount() {
      return this.movieReviews.length;
    },
    averageScore() {
      if (!this.reviewCount) return 0;
      const total = this.movieReviews.reduce((sum, r) => sum + (Number(r.score) || 0), 0);
      return total / this.reviewCount / 2;
    },
  },
  methods: {
    scoreToStars(score) {
      return (Number(score) || 0) / 2;
    },
    starsToScore(stars) {
      return Math.round((Number(stars) || 0) * 2);
    },
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
          await reviewService.update(this.editId, {
            score: this.starsToScore(this.form.score),
            opinion: this.form.opinion,
          });
        } else {
          await reviewService.create({
            score: this.starsToScore(this.form.score),
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
      this.form = { score: this.scoreToStars(entry.score), opinion: entry.opinion };
    },
    cancelEdit() {
      this.editId = null;
      this.form = emptyForm();
    },
    async removeReview(id) {
      const confirmStore = useConfirmStore();
      const ok = await confirmStore.open({
        title: "Delete review?",
        message: "This action cannot be undone.",
        confirmText: "Delete review",
        cancelText: "Cancel",
      });
      if (!ok) return;
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
