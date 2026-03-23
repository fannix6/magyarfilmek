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

    <div class="group-tools">
      <input
        v-model.trim="groupQuery"
        type="search"
        placeholder="Search movie title..."
      />
      <select v-model.number="minStars">
        <option :value="0">All ratings</option>
        <option :value="1">1+ stars</option>
        <option :value="2">2+ stars</option>
        <option :value="3">3+ stars</option>
        <option :value="4">4+ stars</option>
        <option :value="5">5 stars</option>
      </select>
    </div>

    <div v-if="loading" class="state">Loading reviews...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else class="groups">
      <section v-for="group in visibleGroups" :key="group.movieId" class="group-card">
        <header class="group-header">
          <div>
            <h2>{{ group.title }}</h2>
            <p class="meta">{{ group.count }} review{{ group.count === 1 ? "" : "s" }}</p>
          </div>
          <div class="avg">
            <StarRating :model-value="scoreToStars(group.avgScore)" readonly />
            <span class="avg-value">{{ scoreToStars(group.avgScore).toFixed(1) }}/5</span>
          </div>
        </header>

        <details class="group-details">
          <summary>Show reviews</summary>
          <div class="group-list">
            <article v-for="entry in group.entries" :key="entry.id" class="review-card">
              <StarRating :model-value="scoreToStars(entry.score)" readonly />
              <p class="line">{{ entry.opinion }}</p>
              <p class="author">By {{ entry.author }}</p>
              <div v-if="canEdit(entry)" class="actions">
                <button type="button" @click="startEdit(entry)">Edit</button>
                <button type="button" @click="removeReview(entry.id)">Delete</button>
              </div>
            </article>
          </div>
        </details>
      </section>
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
      groupQuery: "",
      minStars: 0,
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
    groupedReviews() {
      const map = new Map();
      for (const entry of this.reviewFeed) {
        if (!map.has(entry.movieid)) {
          map.set(entry.movieid, {
            movieId: entry.movieid,
            title: entry.movieTitle,
            entries: [],
            avgScore: 0,
            count: 0,
          });
        }
        const group = map.get(entry.movieid);
        group.entries.push(entry);
        group.avgScore += Number(entry.score) || 0;
        group.count += 1;
      }

      const groups = [];
      for (const group of map.values()) {
        group.avgScore = group.count ? group.avgScore / group.count : 0;
        groups.push(group);
      }

      const titleById = this.movieById;
      const withEmpty = this.movies
        .filter((m) => !map.has(m.id))
        .map((m) => ({
          movieId: m.id,
          title: m.title || `Movie #${m.id}`,
          entries: [],
          avgScore: 0,
          count: 0,
        }));

      return [...groups, ...withEmpty].sort((a, b) => {
        return (titleById[a.movieId]?.title || a.title).localeCompare(
          titleById[b.movieId]?.title || b.title
        );
      });
    },
    visibleGroups() {
      const needle = (this.groupQuery || "").toLowerCase();
      return this.groupedReviews.filter((group) => {
        const title = (group.title || "").toLowerCase();
        const avgStars = this.scoreToStars(group.avgScore);
        if (needle && !title.includes(needle)) return false;
        if (this.minStars && avgStars < this.minStars) return false;
        return true;
      });
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
