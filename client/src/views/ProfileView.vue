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
        <h2>Change password</h2>
        <form class="password-form" @submit.prevent="changePassword">
          <PasswordField
            v-model="passwordForm.oldpassword"
            label="Current password"
            label-id="current-password"
            autocomplete="current-password"
            :error-message="passwordErrors.oldpassword"
          />
          <PasswordField
            v-model="passwordForm.newpassword"
            label="New password"
            label-id="new-password"
            autocomplete="new-password"
            :error-message="passwordErrors.newpassword"
          />
          <PasswordField
            v-model="passwordForm.newpassword_confirmation"
            label="Confirm new password"
            label-id="confirm-new-password"
            autocomplete="new-password"
            :error-message="passwordErrors.newpassword_confirmation"
          />
          <div class="password-actions">
            <button type="submit" class="link-btn" :disabled="passwordSaving">
              {{ passwordSaving ? "Saving..." : "Update password" }}
            </button>
          </div>
        </form>
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
import userLoginLogoutService from "@/api/userLoginLogoutService";
import PasswordField from "@/components/User/PasswordField.vue";
import StarRating from "@/components/StarRating.vue";
import { useToastStore } from "@/stores/toastStore";

export default {
  components: { StarRating, PasswordField },
  data() {
    return {
      loading: false,
      error: "",
      reviews: [],
      movies: [],
      passwordSaving: false,
      passwordForm: {
        oldpassword: "",
        newpassword: "",
        newpassword_confirmation: "",
      },
      passwordErrors: {
        oldpassword: "",
        newpassword: "",
        newpassword_confirmation: "",
      },
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
    resetPasswordForm() {
      this.passwordForm = {
        oldpassword: "",
        newpassword: "",
        newpassword_confirmation: "",
      };
      this.passwordErrors = {
        oldpassword: "",
        newpassword: "",
        newpassword_confirmation: "",
      };
    },
    async changePassword() {
      this.passwordErrors = {
        oldpassword: "",
        newpassword: "",
        newpassword_confirmation: "",
      };

      if (!this.passwordForm.oldpassword) {
        this.passwordErrors.oldpassword = "Current password is required.";
      }
      if (!this.passwordForm.newpassword) {
        this.passwordErrors.newpassword = "New password is required.";
      }
      if (this.passwordForm.newpassword !== this.passwordForm.newpassword_confirmation) {
        this.passwordErrors.newpassword_confirmation = "The new passwords do not match.";
      }

      if (Object.values(this.passwordErrors).some(Boolean)) return;

      this.passwordSaving = true;
      try {
        await userLoginLogoutService.updatePassword(this.passwordForm);
        this.resetPasswordForm();
        const toastStore = useToastStore();
        toastStore.messages.push("Password updated successfully.");
        toastStore.show("Success");
      } catch (err) {
        const errors = err?.response?.data?.errors || {};
        this.passwordErrors.oldpassword = errors.oldpassword?.[0] || "";
        this.passwordErrors.newpassword = errors.newpassword?.[0] || "";
        this.passwordErrors.newpassword_confirmation =
          errors.newpassword_confirmation?.[0] || "";
      } finally {
        this.passwordSaving = false;
      }
    },
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
