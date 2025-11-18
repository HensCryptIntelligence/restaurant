# Restaurant Project Skeleton (PHP Native, MPA, Simple MVC)
This is a minimal starter skeleton for your restaurant project using PHP native + MySQL (InnoDB) and simple MVC pattern.
Designed for team of 5, with one feature per folder. Includes an **Auth** example (login/register) and front controller.

## How to use
1. Copy the contents of `public` into your XAMPP `htdocs/<project>` folder or set up a Virtual Host pointing to `public`.
2. Import the SQL in `migrations/001_init_schema.sql` into your local MySQL (phpMyAdmin / CLI).
3. Update database credentials in `app/config/config.php`.
4. Start XAMPP (Apache + MySQL) and open `http://localhost/<project>/`

## What included
- `public/index.php` — front controller routing with `?page=...`
- `features/` — folders per feature (auth, orders, reservations, transactions, admin)
- `shared/` — helpers (db, auth, csrf), partials (header/footer), assets
- `migrations/001_init_schema.sql` — initial schema
- `example` — simple SQL seed and notes

This skeleton is intentionally simple and easy to read for beginners.
