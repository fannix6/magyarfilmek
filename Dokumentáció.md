# Dokumentáció

## A szoftver célja
A projekt célja egy webalkalmazás létrehozása, amely magyar filmek adatainak kezelésére és megjelenítésére szolgál. A rendszer lehetőséget biztosít a filmek listázására, megtekintésére és kezelésére.

A felhasználók böngészhetik a filmeket, míg megfelelő jogosultság esetén módosíthatják is az adatokat.

---

## A használat rövid bemutatása
Az alkalmazás megnyitása után a felhasználó egy listában látja a filmeket. Lehetősége van:
- filmek megtekintésére
- részletek megnyitására
- keresésre

Bejelentkezés után további funkciók érhetők el, például:
- új film hozzáadása
- meglévő adatok szerkesztése

---

# Komponensek technikai leírása

## Adatbázis

### Technológia
Az alkalmazás MySQL adatbázist használ.

### Tábla és mezők (példa)
filmek:
- id
- cim
- ev
- leiras
- created_at
- updated_at

---

## Backend

### Technológia
A backend Laravel keretrendszerrel készült.

### Telepítés
composer create-project laravel/laravel projektnev

### Használt parancsok
- php artisan serve
- php artisan migrate
- php artisan db:seed
- php artisan make:model
- php artisan make:controller

---

### Migráció
A migrációk az adatbázis struktúráját hozzák létre.

Példa:
Schema::create('filmek', function (Blueprint $table) {
    $table->id();
    $table->string('cim');
    $table->integer('ev');
    $table->text('leiras');
    $table->timestamps();
});

---

### Seeder
A seederek tesztadatokat töltenek fel az adatbázisba.

Példa:
DB::table('filmek')->insert([
    'cim' => 'Minta film',
    'ev' => 2020,
    'leiras' => 'Leírás'
]);

---

### Endpointok
- GET /api/filmek
- POST /api/filmek

---

### Middleware
A middleware-ek biztosítják, hogy csak jogosult felhasználók férjenek hozzá bizonyos adatokhoz.

---

### Kontroller példa
public function index() {
    return Film::all();
}

---

### Model példa
class Film extends Model {
    protected $fillable = ['cim', 'ev', 'leiras'];
}

---

### Validáció (422)
$request->validate([
    'cim' => 'required',
    'ev' => 'required|integer'
]);

---

### Autentikáció
A rendszer kezeli:
- bejelentkezést
- kijelentkezést
- tokeneket
- jogosultsági szinteket

---

## Frontend

### Technológia
A frontend Vue.js használatával készült.

---

### Fő modulok
- film lista
- részletező oldal
- bejelentkezés

---

### Alkalmazás felépítése
Belépési pontok:
- main.js
- App.vue

Fő részek:
- head
- menü
- tartalom

---

### Jogosultság kezelés
- backend oldalon middleware
- frontend oldalon:
  - menü elemek kezelése
  - route védelem

---

### Mappastruktúra
- api
- store (pinia)
- components
- views
- router

---

### Program működés

Kártyák:
A filmek kártya nézetben jelennek meg.

Lapozás:
Az adatok több oldalra bontva jelennek meg.

Űrlapok:
Az adatok bevitele validációval történik.

---

### Dizájn
Az alkalmazás reszponzív, így mobilon és asztali nézetben is megfelelően működik.

---

## Forráslista
- Laravel dokumentáció
- Vue.js dokumentáció
- Stack Overflow
- online tutorialok
