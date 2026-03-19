<template>
  <div class="auth-shell">
    <div class="auth-card">
      <div class="auth-header">
        <p class="auth-kicker">Welcome back!</p>
        <h1 class="h1signin">Sign In</h1>
        <p class="auth-subtitle">Sign in to start browsing movies.</p>
      </div>
      <div class="auth-body">
        <form
          @submit.prevent="handleSubmit"
          :class="{ 'was-validated': validated }"
          novalidate
        >
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input
              type="email"
              class="form-control"
              id="email"
              v-model="user.email"
              required
            />
            <div class="invalid-feedback">The email is empty or incorrect.</div>
          </div>
          <!-- Password -->
          <PasswordField
            class="mt-3"
            v-model="user.password"
            :label="'Password'"
          />
          <div class="auth-actions">
            <button type="submit" class="btn btn-success">Log in</button>
            <RouterLink to="/registration" class="btn btn-outline-light"
              >Register</RouterLink
            >
          </div>
        </form>
       
      </div>
    </div>
    <!-- hibaüzenet -->
  </div>
</template>

<script>
import PasswordField from "./PasswordField.vue";
import ToastContainer from "@/components/Message/ToastContanier.vue";
class User {
  constructor(email = "", password = "") {
    this.email = email;
    this.password = password;
  }
}
export default {
  name: "UserLogin",
  components: {
    PasswordField,
    ToastContainer,
  },
  data() {
    return {
      password: "",
      email: "",
      validated: false,
      user: new User(),
    };
  },
  methods: {
    handleSubmit(event) {
      const form = event.target;
      this.validated = true;

      if (form.checkValidity() === false) {
        console.log("Hiba:");
      } else {
        console.log("Sikeres validáció!");
        this.$emit("logIn", this.user);
      }
    },
  },
};
</script>

<style scoped src="@/assets/views/UserLogin.css"></style>
