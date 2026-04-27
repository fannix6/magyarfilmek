# Dokumentáció: Magyar filmek/Hungarian Movies

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
- MySql technológia
- `hungarianmovies.jpg` -ben megtekinthető a diagram
- Biztonsági mentés `AdatbazisBackup.sql` néven található
- A teljes adatbázis a `AdatbazisBackup.sql` fájlból visszaállítható

## Tábla és mező
### `users` – felhasználók
- Mezők: `id`, `name`, `role`, `password`, `created_at`, `updated_at`

### `roles` – szerepkörök
- Mezők: `id`, `role`, `created_at`, `updated_at`
- Szerepkörök: admin, guest, user
  
### `tasks` – feladatok
- Mezők: `id`, `roleid`, `personid`, `movieid`

### `people` – személyek
- Mezők: `id`, `name`, `gender`, `photo`, `created_at`, `updated_at`

### `reviews` – véleményes
- Mezők: `id`, `score`, `opinion`, `movieid`, `userid`, `created_at`, `updated_at`

### `movies` – filmek
- Mezők: `id`, `title`, `produced`, `length`, `premiere`, `watchlink`, `imdblink`, `cover`, `created_at`, `updated_at`


---

## Backend

### Technológia
A backend Laravel keretrendszerrel készült.

Telepítés és futtatás:
```console
cd server
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve
```

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
```console
Schema::create('movies', function (Blueprint $table) {
        $table->id();
        $table->string('title', 125)->unique();
        $table->integer('produced')->nullable();
        $table->string('length')->nullable();
        $table->string('premiere')->nullable();
        $table->text('watchlink')->nullable();
        $table->text('imdblink')->nullable();

        $table->timestamps();
```
---

### Seeder
A seederek tesztadatokat töltenek fel az adatbázisba.

Seeder mintakód:
`server/database/seeders/MovieSeeder.php`
```php
$fileName = 'csv/movies.csv';
        $delimeter = ';';
        $data = CsvReader::csvToArray($fileName, $delimeter);
        Movie::factory()->createMany($data);
```

---

### Endpointok

## Backend

### Endpointok
Az endpointok a `server/routes/api.php` fájlban találhatók.  
A védett útvonalak `auth:sanctum` middleware-rel és `ability:*` jogosultsági ellenőrzéssel futnak.

#### Auth
- `POST /api/users/login` – bejelentkezés
- `POST /api/users` – regisztráció
- `POST /api/users/logout` – kijelentkezés
- `GET /api/user` – aktuális felhasználó

#### Users (admin)
- `GET /api/users` – felhasználók listája
- `GET /api/users/{id}` – felhasználó lekérdezése
- `PUT /api/users/{id}` – felhasználó módosítása
- `DELETE /api/users/{id}` – felhasználó törlése

#### Movies
- `GET /api/movies` – filmek listája
- `GET /api/movies/{id}` – film lekérdezése
- `POST /api/movies` – film létrehozása (admin)
- `PUT /api/movies/{id}` – film módosítása (admin)
- `DELETE /api/movies/{id}` – film törlése (admin)

Kapcsolódó:
- `GET /api/movies/{id}/reviews` – film értékelései
- `GET /api/movies/{id}/people` – szereplők és készítők
- `GET /api/movies/{id}/tasks` – stáblista

#### Reviews
- `GET /api/reviews` – értékelések listája
- `GET /api/reviews/{id}` – értékelés lekérdezése
- `POST /api/reviews` – értékelés létrehozása
- `PUT /api/reviews/{id}` – értékelés módosítása
- `DELETE /api/reviews/{id}` – értékelés törlése

Kapcsolódó:
- `GET /api/users/{id}/reviews` – felhasználó értékelései

#### People
- `GET /api/people` – személyek listája
- `GET /api/people/{id}` – személy lekérdezése
- `POST /api/people` – személy létrehozása (admin)
- `PUT /api/people/{id}` – személy módosítása (admin)
- `DELETE /api/people/{id}` – személy törlése (admin)

Kapcsolódó:
- `GET /api/people/{id}/movies` – filmek, amelyekben szerepel

#### Tasks
- `GET /api/tasks` – kapcsolatok listája
- `POST /api/tasks` – kapcsolat létrehozása (film–személy–szerep)
- `DELETE /api/tasks/{id}` – kapcsolat törlése

#### Roles
- `GET /api/roles` – szerepkörök listája
- `POST /api/roles` – szerepkör létrehozása (admin)
- `PUT /api/roles/{id}` – szerepkör módosítása (admin)
- `DELETE /api/roles/{id}` – szerepkör törlése (admin)

---

### Minta kontroller

- `server/app/Http/Controllers/MovieController.php`

```php
public function store(StoreMovieRequest $request)
{
    return $this->apiResponse(function () use ($request) {
        $this->authorizeAdmin();
        $data = $request->validated();
        return Movie::create($data);
    });
}
```
---

### Model példa
- `server/app/Models/Movie.php`
  
```php
 protected $fillable = ['title', 'produced', 'length'];

    public function reviews()
    {
        return $this->hasMany(Review::class, 'movieid');
    }

    public function people()
    {
        return $this->belongsToMany(
            Person::class,
            'tasks',
            'movieid',
            'personid'
        );
    }
```
---

### Validáció (422)
- `server/app/Http/Requests/StoreMovieRequest.php`
```php
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'max:255'],
        'produced' => ['required', 'integer', 'min:1900'],
        'length' => ['required', 'string']
    ];
}
```
---

### Autentikáció
- Bejelentkezés: POST /api/users/login
- Kijelentkezés: POST /api/users/logout
- Token alapú azonosítás:
- Authorization: Bearer <token>
- Szerepkörök: admin, user
- Jogosultságok: role alapján kiosztott abilities

---

## Frontend
- Technológia: Vue 3 + Vite + Pinia + Axios + Bootstrap
- Belépési pont: `client/src/main.js`
- Fő komponens: `client/src/App.vue`
#### Oldal szerkezet
- Router: `client/src/router/index.js`
- Oldalak: `client/src/views`
- Komponensek: `client/src/components`
- 
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

### Mappa struktúra
- `client/src/api` – API hívások
- `client/src/stores` – állapotkezelés (Pinia)
- `client/src/views` – oldalak
- `client/src/components` – komponensek
- `client/src/router` – routing
---

### Példa API hívás
- `client/src/api/movieService.js`
``` javaScript
import axiosClient from "./axiosClient";

export default {
  getAll() {
    return axiosClient.get("/movies");
  },
};
```


---

### Program működés

Kártyák:
- A filmek kártya nézetben jelennek meg.

Lapozás:
- Az adatok több oldalra bontva jelennek meg.

Űrlapok:
- Az adatok bevitele validációval történik.

---

### Dizájn
Az alkalmazás reszponzív, így mobilon és asztali nézetben is megfelelően működik.

---

## Forráslista
- Laravel dokumentáció
- Vue.js dokumentáció
- Bootstrap dokumentáció
