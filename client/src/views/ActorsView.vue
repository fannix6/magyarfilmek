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
        <div class="avatar">{{ initials(person.name) }}</div>
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

<style scoped>
.actors-page { max-width: 1280px; margin: 0 auto; padding: 1.3rem; }
.page-header h1 { margin: 0; font-size: 2rem; }
.page-header p { margin-top: 0.35rem; color: #b3b3b3; }
.tools { display: flex; gap: 0.6rem; align-items: center; margin: 0.95rem 0 1rem; flex-wrap: wrap; }
.search { width: min(520px, 100%); background: #1b1b1b; border: 1px solid #2e2e2e; color: #fff; border-radius: 0.45rem; padding: 0.65rem 0.75rem; }
.btn-add { background: #e50914; border: 1px solid #e50914; color: #fff; border-radius: 0.45rem; padding: 0.62rem 0.75rem; }
.editor { display: grid; gap: 0.5rem; margin-bottom: 1rem; background: #151515; border: 1px solid #2a2a2a; border-radius: 0.6rem; padding: 0.85rem; max-width: 520px; }
.editor h2 { margin: 0; font-size: 1rem; }
.editor input, .editor select { background: #202020; border: 1px solid #333; color: #fff; border-radius: 0.4rem; padding: 0.6rem 0.7rem; }
.editor-actions { display: flex; gap: 0.5rem; }
.editor-actions button { border: 0; border-radius: 0.4rem; padding: 0.55rem 0.8rem; background: #2f2f2f; color: #fff; }
.editor-actions button:first-child { background: #e50914; }
.actors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 0.75rem; }
.actor-card { background: #171717; border: 1px solid #2a2a2a; border-radius: 0.65rem; padding: 0.95rem; }
.avatar { width: 46px; height: 46px; border-radius: 50%; background: linear-gradient(135deg, #e50914, #930009); display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
.actor-card h2 { margin: 0.75rem 0 0; font-size: 1rem; }
.actor-card p { margin: 0.35rem 0 0; color: #c3c3c3; }
.row-actions { margin-top: 0.65rem; display: flex; gap: 0.45rem; }
.row-actions button { border: 0; background: rgba(255,255,255,0.14); color: #fff; border-radius: 0.35rem; padding: 0.3rem 0.55rem; }
.state { color: #bdbdbd; }
.error { color: #ff7f7f; }
</style>
