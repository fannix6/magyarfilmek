# Tesztelési dokumentáció

## REST API tesztek

A manuális API hívások a `server/request.rest` fájlban találhatók.

### Lefedett funkciók:
- login
- token lekérés
- CRUD műveletek:
  - users
  - movies
  - people
  - roles
  - tasks
  - reviews

### Használat:
1. Backend elindítása
2. `request.rest` megnyitása
3. Kérések futtatása sorrendben
4. Válaszok ellenőrzése

---

## Backend tesztek

A tesztek helye: `server/tests`

### Típusok:
- Unit tesztek: `Unit/`
- Feature tesztek: `Feature/`

### Futtatás:
```bash
cd server
php artisan test
```

## Eredmény mentése
```bash
php artisan test > backend-test-results.txt
```
- A fájl a repository gyökerébe kerül.

---

### Példa teszt:
```php
$login = $this->login(self::ADMIN_EMAIL, self::PASSWORD);
$login->assertStatus(200);

$token = $this->myGetToken($login);
$response = $this->myGet("/api/movies", $token);
$response->assertStatus(200);
```

## Frontend tesztek
### Unit tesztek (Vitest)
```Bash
cd client
npm run test:unit
```
- Mentés:
```Bash
npm run test:unit > frontend-unit-test-results.txt
```

### E2E tesztek (Cypress)
```Bash
cd client
npm run test:e2e
```
- Mentés:
```Bash
npm run test:e2e > frontend-e2e-test-results.txt
```

---

## Példa unit teszt
```JavaStript
import { describe, it, expect } from "vitest";
import { mount } from "@vue/test-utils";
import App from "../App.vue";

describe("App", () => {
  it("betöltődik", () => {
    const wrapper = mount(App);
    expect(wrapper.exists()).toBe(true);
  });
});
```

---

## Dokumentáció
- A tesztfuttatások kimenetei a repo gyökerében legyenek.
- A képernyőképek a `DokumnetacioKepek/` mappába kerülnek.
