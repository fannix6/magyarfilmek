# README – Technikai követelmények és telepítési útmutató

## Szükséges szoftverek
- Git
- PHP 8.2
- Composer
- Node.js (legalább 20.19.0 ajánlott)
- MySQL

## Forráskód letöltése
1. Klónozd a repository-t.
2. Lépj be a projekt gyökérkönyvtárába (ahol a `client` és `server` mappák vannak).

## Backend telepítés és futtatás
1. Lépj be a `server` mappába.
2. Telepítsd a függőségeket.
3. Hozd létre a `.env` fájlt az `.env.example` alapján, és állítsd be az adatbázist.
4. Generálj alkalmazás kulcsot.
5. Futtasd a migrációkat és seedeket.
6. Indítsd el a szervert.

Parancsok:
cd server
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan serve

## Frontend telepítés és futtatás
1. Lépj be a `client` mappába.
2. Telepítsd a csomagokat.
3. Indítsd el a fejlesztői szervert.

Parancsok:
cd client
npm install
npm run dev

## Tesztek futtatása

Backend:
cd server
php artisan test

Frontend (unit):
cd client
npm run test:unit

Frontend (e2e):
cd client
npm run test:e2e
