<template>
  <section class="profile-page">
    <header class="page-header">
      <h1 class="h1profile">Profile</h1>
      <p v-if="isLoggedIn">Your account details and your reviews.</p>
      <p v-else>Sign in to view your profile.</p>
    </header>

    <div v-if="!isLoggedIn" class="state">
      <RouterLink to="/login" class="link-btn">Go to login</RouterLink>
    </div>

    <div v-else class="profile-grid">
      <section class="card">
        <h2>Account</h2>
        <div class="row"><span>Name</span><strong>{{ item?.name || '-' }}</strong></div>
        <div class="row"><span>Email</span><strong>{{ item?.email || '-' }}</strong></div>
        <div class="row"><span>Role</span><strong>{{ roleLabel }}</strong></div>
      </section>

      <section class="card">
        <h2>Your reviews</h2>
        <div v-if="loading" class="state">Loading your reviews...</div>
        <div v-else-if="error" class="state error">{{ error }}</div>
        <div v-else-if="!userReviews.length" class="state">You have not written any reviews yet.</div>
        <div v-else class="review-list">
          <article v-for="entry in userReviews" :key="entry.id" class="review-card">
            <div class="review-head">
              <h3>{{ entry.movieTitle }}</h3>
              <StarRating :model-value="scoreToStars(entry.score)" readonly />
              <span class="score">{{ scoreToStars(entry.score).toFixed(1) }}/5</span>
            </div>
            <p class="opinion">{{ entry.opinion }}</p>
          </article>
        </div>
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

export default {
  components: { StarRating },
  data() {
    return {
      loading: false,
      error: "",
      reviews: [],
      movies: [],
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "item", "role", "rolNames"]),
    roleLabel() {
      if (!this.role) return "-";
      return this.role === 1 ? "Admin" : "User";
    },
    movieById() {
      return Object.fromEntries(this.movies.map((m) => [m.id, m]));
    },
    userReviews() {
      if (!this.item?.id) return [];
      return this.reviews
        .filter((r) => r.userid === this.item.id)
        .map((r) => ({
          ...r,
          movieTitle: this.movieById[r.movieid]?.title || `Movie #${r.movieid}`,
        }))
        .sort((a, b) => a.movieTitle.localeCompare(b.movieTitle));
    },
  },
  methods: {
    scoreToStars(score) {
      return (Number(score) || 0) / 2;
    },
    async loadData() {
      if (!this.isLoggedIn) return;
      this.loading = true;
      this.error = "";
      try {
        const [reviewsRes, moviesRes] = await Promise.all([
          reviewService.getAll(),
          movieService.getAll(),
        ]);
        this.reviews = Array.isArray(reviewsRes.data) ? reviewsRes.data : [];
        this.movies = Array.isArray(moviesRes.data) ? moviesRes.data : [];
      } catch (err) {
        this.error = "Failed to load profile reviews.";
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

<style scoped src="@/assets/views/ProfileView.css"></style>
