<template>
  <section class="reviews-page">
    <header class="page-header">
      <h1>Reviews</h1>
      <p v-if="isLoggedIn">You can write a review as logged user.</p>
      <p v-else>Guest mode: read only.</p>
    </header>

    <form v-if="isLoggedIn" class="review-form" @submit.prevent="saveReview">
      <select v-model.number="form.movieid" required>
        <option :value="null" disabled>Select movie</option>
        <option v-for="movie in movies" :key="movie.id" :value="movie.id">{{ movie.title }}</option>
      </select>
      <input v-model.number="form.score" type="number" min="1" max="10" required placeholder="Score 1-10" />
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
        <div class="badge">{{ entry.score }}/10</div>
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

const emptyForm = () => ({ movieid: null, score: 8, opinion: "" });

export default {
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
        author: r.userid === this.currentUserId ? "You" : `User #${r.userid}`,
      }));
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
        const payload = { ...this.form };
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
        score: entry.score,
        opinion: entry.opinion,
      };
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
