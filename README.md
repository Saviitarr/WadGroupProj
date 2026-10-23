# Campus Connect

Student accommodation listing and booking web app for NUST students.
WAD621S Assessment 4 (group project). Stack: HTML, CSS, JavaScript, PHP, MySQL (PDO). No Bootstrap.

## Setup (XAMPP)
1. Copy this folder into `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac).
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` → **Import** → `database/schema.sql`, then import `database/seed.sql`.
   (schema.sql creates the `accommodation` database; seed.sql adds sample data.)
4. Open `http://localhost/<folder-name>/` in the browser.

Database settings are in `config/db.php` (XAMPP defaults: user `root`, no password).

## Sample logins
| Role | Email | Password |
|---|---|---|
| Admin | admin@nust.na | Admin@123 |
| Student | 224056781@nust.na | Student@123 |
| Student | 224056782@nust.na | Student@123 |
| Student | 224056783@nust.na | Student@123 |

## Pages
| Page | File | Owner |
|---|---|---|
| Home | `index.php` | Member 1 |
| About | `about.php` | Member 1 |
| Register / Log in / Log out | `register.php`, `login.php`, `logout.php` | Members 1 + 4 |
| Listings + search/filter | `listings.php`, `js/listings.js`, `js/data.js`, `api/listings.php` | Member 2 |
| Listing details | `listing-details.php`, `js/details.js` | Member 2 |
| Booking form (insert/update) | `book.php`, `includes/booking_rules.php`, `js/booking-form.js` | Member 3 |
| Student dashboard (cancel/delete) | `dashboard.php`, `booking-action.php`, `js/dashboard.js` | Member 3 |
| Contact | `contact.php`, `js/contact-form.js` | Member 3 |
| Admin: overview, listings (add/edit/delete), bookings (approve/decline/cancel), users, messages | `admin/` | Member 5 |
| Database, auth, security | `database/`, `config/db.php`, `include/auth.php` | Member 4 |

## Folder structure
```
admin/        admin area (only role = admin can open it)
api/          listings.php - JSON data for the listings pages
config/       db.php - PDO connection
css/          style.css (global), listings.css, forms.css
database/     schema.sql, seed.sql
include/      auth.php - session, login checks, e() escaping
includes/     header.php, footer.php, helpers.php, booking_rules.php
js/           script.js (menu), listings/details/data, form validation
```

## Security
- Passwords stored with `password_hash()` and checked with `password_verify()`; new session id on login.
- Every query uses PDO prepared statements (no SQL injection).
- All output is escaped with `htmlspecialchars` via `e()` (no XSS).
- POST forms carry a CSRF token; admin pages call `require_admin()`.
