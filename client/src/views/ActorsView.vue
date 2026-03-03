<template>
  <section class="actors-page">
    <header class="page-header">
      <h1>Actors</h1>
      <p>{{ filteredActors.length }} people from imported dataset</p>
    </header>

    <div class="tools">
      <input v-model.trim="query" class="search" type="search" placeholder="Search actor..." />
      <button v-if="isAdmin" class="btn-add" type="button" @click="openCreate">+ New Actor</button>
    </div>

    <form v-if="isAdmin && editorOpen" class="editor" @submit.prevent="saveActor">
      <h2>{{ editId ? "Edit Actor" : "Create Actor" }}</h2>
      <input v-model.trim="form.name" placeholder="Name" required />
      <select v-model.number="form.gender">
        <option :value="null">Unknown</option>
        <option :value="1">Male</option>
        <option :value="0">Female</option>
      </select>
      <input v-model.trim="form.photo" placeholder="Photo file name" />
      <div class="editor-actions">
        <button type="submit">Save</button>
        <button type="button" @click="closeEditor">Cancel</button>
      </div>
    </form>

    <div v-if="loading" class="state">Loading actors...</div>
    <div v-else-if="error" class="state error">{{ error }}</div>
    <div v-else class="actors-grid">
      <article v-for="person in filteredActors" :key="person.id" class="actor-card">
        <img
          v-if="person.photo"
          class="avatar-image"
          :src="getPhoto(person.photo)"
          :alt="person.name"
          @error="onPhotoError"
        />
        <div v-else class="avatar">{{ initials(person.name) }}</div>
        <h2>{{ person.name }}</h2>
        <p>{{ creditCount(person.id) }} credits</p>
        <div v-if="isAdmin" class="row-actions">
          <button type="button" @click="openEdit(person)">Edit</button>
          <button type="button" @click="removeActor(person.id)">Delete</button>
        </div>
      </article>
    </div>
  </section>
</template>

<script>
import { mapState } from "pinia";
import { useUserLoginLogoutStore } from "@/stores/userLoginLogoutStore";
import personService from "@/api/personService";
import taskService from "@/api/taskService";
import { getActorPhotoUrl } from "@/utils/media";

const emptyForm = () => ({ name: "", gender: null, photo: "" });

export default {
  data() {
    return {
      loading: true,
      error: "",
      query: "",
      people: [],
      tasks: [],
      editorOpen: false,
      editId: null,
      form: emptyForm(),
    };
  },
  computed: {
    ...mapState(useUserLoginLogoutStore, ["isLoggedIn", "role"]),
    isAdmin() {
      return this.isLoggedIn && this.role === 1;
    },
    filteredActors() {
      const needle = this.query.toLowerCase();
      return this.people.filter((p) => (p.name || "").toLowerCase().includes(needle));
    },
  },
  methods: {
    async loadData() {
      this.loading = true;
      this.error = "";
      try {
        const [peopleRes, tasksRes] = await Promise.all([personService.getAll(), taskService.getAll()]);
        this.people = Array.isArray(peopleRes.data) ? peopleRes.data : [];
        this.tasks = Array.isArray(tasksRes.data) ? tasksRes.data : [];
      } catch (err) {
        this.error = "Failed to load actors.";
      } finally {
        this.loading = false;
      }
    },
    creditCount(personId) {
      return this.tasks.filter((t) => t.personid === personId).length;
    },
    initials(name) {
      return (name || "?").split(" ").slice(0, 2).map((n) => n[0] || "").join("").toUpperCase();
    },
    getPhoto(photoFileName) {
      return getActorPhotoUrl(photoFileName);
    },
    onPhotoError(event) {
      event.target.style.display = "none";
    },
    openCreate() {
      this.editId = null;
      this.form = emptyForm();
      this.editorOpen = true;
    },
    openEdit(person) {
      this.editId = person.id;
      this.form = { name: person.name || "", gender: person.gender ?? null, photo: person.photo || "" };
      this.editorOpen = true;
    },
    closeEditor() {
      this.editorOpen = false;
      this.editId = null;
      this.form = emptyForm();
    },
    async saveActor() {
      try {
        const payload = { ...this.form };
        if (payload.gender !== 0 && payload.gender !== 1) payload.gender = null;
        if (this.editId) {
          await personService.update(this.editId, payload);
        } else {
          await personService.create(payload);
        }
        this.closeEditor();
        await this.loadData();
      } catch (error) {
        this.error = "Save failed.";
      }
    },
    async removeActor(id) {
      if (!confirm("Delete this actor?")) return;
      try {
        await personService.delete(id);
        await this.loadData();
      } catch (error) {
        this.error = "Delete failed.";
      }
    },
  },
  mounted() {
    this.loadData();
  },
};
</script>

<style scoped src="@/assets/views/ActorsView.css"></style>
