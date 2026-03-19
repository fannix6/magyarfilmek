<template>
  <section class="reviews-page">
    <header class="page-header">
      <h1 class="h1review">Reviews</h1>
      <p v-if="isLoggedIn">You can write a review as logged user.</p>
      <p v-else>Guest mode: read only.</p>
    </header>

    <form v-if="isLoggedIn" class="review-form" @submit.prevent="saveReview">
      <select v-model.number="form.movieid" required>
        <option :value="null" disabled>Select movie</option>
        <option v-for="movie in movies" :key="movie.id" :value="movie.id">{{ movie.title }}</option>
      </select>
      <div class="star-input">
        <StarRating v-model="form.score" />
        <span class="star-input__value">{{ form.score.toFixed(1) }}/5</span>
      </div>
      <textarea v-model.trim="form.opinion" rows="3" required placeholder="Write your opinion"></textarea>
      <div class="form-actions">
        <button type="submit">{{ editId ? "Update" : "Post Review" }}</button>
        <button v-if="editId" type="button" @click="cancelEdit">Cancel</button>
      </div>
    </form>

    <div v-if="loading" class="state">Loading reviews...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else class="feed">
      <article v-for="entry in reviewFeed" :key="entry.id" class="feed-card">
        <StarRating :model-value="scoreToStars(entry.score)" readonly />
        <h2>{{ entry.movieTitle }}</h2>
        <p class="line">{{ entry.opinion }}</p>
        <p class="author">By {{ entry.author }}</p>
        <div v-if="canEdit(entry)" class="actions">
          <button type="button" @click="startEdit(entry)">Edit</button>
          <button type="button" @click="removeReview(entry.id)">Delete</button>
        </div>
      </article>
    </div>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import reviewService from "@/api/reviewService";
import movieService from "@/api/movieService";
import StarRating from "@/components/StarRating.vue";
import { useConfirmStore } from "@/stores/confirmStore";

const emptyForm = () => ({ movieid: null, score: 4, opinion: "" });

export default {
  components: { StarRating },
  data() {
    return {
      loading: true,
      error: "",
      reviews: [],
      movies: [],
      editId: null,
      form: emptyForm(),
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
    movieById() {
      return Object.fromEntries(this.movies.map((m) => [m.id, m]));
    },
    reviewFeed() {
      return this.reviews.map((r) => ({
        ...r,
        movieTitle: this.movieById[r.movieid]?.title || `Movie #${r.movieid}`,
        author:
          r.userid === this.currentUserId
            ? "You"
            : r.user?.name || `User #${r.userid}`,
      }));
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
        const [reviewsRes, moviesRes] = await Promise.all([reviewService.getAll(), movieService.getAll()]);
        this.reviews = Array.isArray(reviewsRes.data) ? reviewsRes.data : [];
        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
      } catch (err) {
        this.error = "Failed to load reviews.";
      } finally {
        this.loading = false;
      }
    },
    async saveReview() {
      try {
        const payload = {
          ...this.form,
          score: this.starsToScore(this.form.score),
        };
        if (this.editId) {
          await reviewService.update(this.editId, { score: payload.score, opinion: payload.opinion });
        } else {
          await reviewService.create(payload);
        }
        this.cancelEdit();
        await this.loadData();
      } catch (err) {
        this.error = "Save failed.";
      }
    },
    startEdit(entry) {
      this.editId = entry.id;
      this.form = {
        movieid: entry.movieid,
        score: this.scoreToStars(entry.score),
        opinion: entry.opinion,
      };
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
      } catch (err) {
        this.error = "Delete failed.";
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped src="@/assets/views/ReviewsView.css"></style>
