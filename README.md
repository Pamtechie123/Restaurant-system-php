# ChopOps – Restaurant Management System

PHP (Slim) + MongoDB backend, Tailwind CSS + vanilla JavaScript frontend.

## Setup

1. Install packages (once): `composer install`
2. Copy `.env.example` to `.env` and fill it in. `ADMIN_CODE` is the secret needed to create an admin account.
3. Add the sample dishes (optional): `php seed.php`
4. Start the backend: `php -S localhost:8000 -t public`
5. Start the frontend in a second terminal: `php -S localhost:5500 -t restaurant-system-frontend`
   (VS Code Live Server also works). Then open http://localhost:5500

XAMPP is not needed: PHP's built-in server and MongoDB Compass are enough.
The PHP `mongodb` extension must be enabled (`extension=mongodb` in php.ini).

If the backend runs on another address, change `SERVER` in `restaurant-system-frontend/js/config.js`.

## Admin

- Create an admin at `/admin/register.html` (needs `ADMIN_CODE` from `.env`), then log in at `/admin/login.html`.
- Admins are saved in their own `admins` collection, so changing a customer's data can never make them an admin.

## Folders

```
public/index.php            starts the Slim app (CORS, JSON errors)
public/images, uploads      dish photos (uploads = images added by the admin)
src/routes.php              every API route in one file
src/helpers.php             database connection, login check, small helpers
src/Controllers/            Auth, Menu, Order, Reservation, Admin
restaurant-system-frontend/ pages, css/style.css, js/app.js (shared), js/admin.js (admin shared)
seed.php                    adds sample dishes
```
