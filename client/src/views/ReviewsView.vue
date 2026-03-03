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

<style scoped>
.reviews-page { max-width: 1280px; margin: 0 auto; padding: 1.3rem; }
.page-header h1 { margin: 0; font-size: 2rem; }
.page-header p { color: #b3b3b3; margin-top: 0.35rem; }
.review-form { margin: 1rem 0; display: grid; gap: 0.55rem; max-width: 640px; background: #151515; border: 1px solid #2a2a2a; border-radius: 0.65rem; padding: 0.9rem; }
.review-form select, .review-form input, .review-form textarea { background: #202020; border: 1px solid #333; color: #fff; border-radius: 0.4rem; padding: 0.6rem 0.7rem; }
.form-actions { display: flex; gap: 0.5rem; }
.form-actions button { border: 0; border-radius: 0.4rem; padding: 0.55rem 0.8rem; background: #2f2f2f; color: #fff; }
.form-actions button:first-child { background: #e50914; }
.feed { margin-top: 1rem; display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 0.75rem; }
.feed-card { background: #171717; border: 1px solid #2a2a2a; border-radius: 0.65rem; padding: 0.9rem; }
.badge { display: inline-block; background: #e50914; color: #fff; border-radius: 999px; padding: 0.2rem 0.55rem; font-size: 0.72rem; font-weight: 600; }
.feed-card h2 { margin: 0.65rem 0 0.35rem; font-size: 1.02rem; }
.line { margin: 0; color: #d2d2d2; }
.author { margin: 0.55rem 0 0; color: #bdbdbd; font-size: 0.9rem; }
.actions { margin-top: 0.65rem; display: flex; gap: 0.45rem; }
.actions button { border: 0; background: rgba(255,255,255,0.14); color: #fff; border-radius: 0.35rem; padding: 0.3rem 0.55rem; }
.state { color: #bdbdbd; }
.error { color: #ff7f7f; }
</style>
