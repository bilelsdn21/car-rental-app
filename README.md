# RCar — Car Rental Platform

A car rental web app: users browse a catalog of cars listed by rental agencies, book one for a date range, and agencies manage their own listings. Admin role for user/listing management.

## Features

- Three roles: **user** (browse, book), **agency** (list/edit/delete own cars), **admin** (manage users).
- Catalog with search and category filtering.
- Booking flow with confirmation.
- Login hardened against SQL injection (parameterized queries + input checks), with a PHPUnit test covering injection attempts.

## Stack

PHP (procedural + a small MVC split: `model/`, `view/`, `controllers/`), MySQL (`mysqli`), PHPUnit for tests.

## Project structure

```
model/        db.php (connection), query.sql (schema)
view/         pages (catalog, car, bookings, dashboard, admin...)
controllers/  form handlers (login, register, add/edit/delete car, booking...)
includes/     shared partials (navbar, footer, session helper)
src/auth.php  login check used by the test suite
testes/       PHPUnit tests
```

## Run locally

Needs PHP + MySQL (e.g. XAMPP).

```bash
composer install
mysql -u root -p < model/query.sql
php -S localhost:8000 -t view
```
Then open http://localhost:8000/index.php

## Tests

```bash
vendor/bin/phpunit testes/AuthTest.php
```
