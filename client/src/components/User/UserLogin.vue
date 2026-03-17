<template>
  <div class="auth-shell">
    <div class="auth-card">
      <div class="auth-header">
        <p class="auth-kicker">Üdv újra</p>
        <h1>Bejelentkezés</h1>
        <p class="auth-subtitle">Lépj be, és írhatsz értékelést a filmekhez.</p>
      </div>
      <div class="auth-body">
        <form
          @submit.prevent="handleSubmit"
          :class="{ 'was-validated': validated }"
          novalidate
        >
          <!-- Email -->
          <div class="mb-3">
            <label for="email" class="form-label">Email címed:</label>
            <input
              type="email"
              class="form-control"
              id="email"
              v-model="user.email"
              required
            />
            <div class="invalid-feedback">Az email üres, vagy helytelen</div>
          </div>
          <!-- Password -->
          <PasswordField
            class="mt-3"
            v-model="user.password"
            :label="'Jelszavad'"
          />
          <div class="auth-actions">
            <button type="submit" class="btn btn-success">Belépés</button>
            <RouterLink to="/registration" class="btn btn-outline-light"
              >Regisztráció</RouterLink
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
      //Bekapcsolja a bootstrap hiba színező logikáját
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

<style scoped src="@/assets/views/AuthView.css"></style>
