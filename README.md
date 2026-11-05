# Sahayata Unified Platform

A disaster relief management web application for Bangladesh. Victims request help,
admins approve requests and assign deliveries, donors make (demo) donations,
volunteers deliver relief, and a manager oversees the system.

Built with **HTML5, CSS3, JavaScript (ES modules), PHP 8+ and MySQL (PDO)** —
no frameworks or libraries.

> **Project status:** Phase 1 (foundation) and Phase 2 (authentication) are complete.
> After logging in, each role currently sees a temporary page; the real role
> screens are built in Phases 3–7.

---

## Contents

1. [Requirements](#requirements)
2. [Installation with XAMPP (Windows / macOS / Linux)](#installation-with-xampp)
3. [Alternative: PHP built-in server](#alternative-php-built-in-server)
4. [Demo accounts](#demo-accounts)
5. [Authentication](#authentication)
6. [Project structure](#project-structure)
7. [How a request flows through the code (MVC)](#how-a-request-flows-through-the-code-mvc)
8. [Design system](#design-system)
9. [Security](#security)
10. [Demo features (no real SMS, payments)](#demo-features)
11. [Troubleshooting](#troubleshooting)

---

## Requirements

| Software | Version | Notes |
|---|---|---|
| PHP | 8.1 or newer | Extensions `pdo_mysql` and `mbstring` (both enabled in XAMPP) |
| MySQL or MariaDB | MySQL 8.0+ / MariaDB 10.4+ | XAMPP includes MariaDB |
| Apache | 2.4 with `mod_rewrite` | Included and enabled in XAMPP |
| Browser | Any current Chrome, Edge, Firefox or Safari | |

Internet access is only needed to load the **Inter** font from Google Fonts.
Without it, the pages fall back to the system font.

---

## Installation with XAMPP

### 1. Install and start XAMPP

1. Download XAMPP from <https://www.apachefriends.org> and install it.
2. Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**.

### 2. Put the project in `htdocs`

Copy (or `git clone`) this project into XAMPP's `htdocs` folder, named `sahayata-app`:

| System | Path |
|---|---|
| Windows | `C:\xampp\htdocs\sahayata-app` |
| macOS | `/Applications/XAMPP/htdocs/sahayata-app` |
| Linux | `/opt/lampp/htdocs/sahayata-app` |

```bash
git clone https://github.com/Zareef-hossain/Sahayata-App.git sahayata-app
```

### 3. Create the database

1. Open <http://localhost/phpmyadmin>.
2. Click the **Import** tab, choose `database/schema.sql`, and click **Import**
   (this creates the `sahayata` database and all tables).
3. Import `database/seed.sql` the same way (this adds roles, dropdown values and demo accounts).

> Importing `schema.sql` again **deletes** the existing `sahayata` database and all its data.

Command-line alternative (run inside the project folder):

```bash
mysql -u root < database/schema.sql
```

```bash
mysql -u root < database/seed.sql
```

### 4. Create your settings file

Copy `config/config.example.php` to `config/config.php`.
The defaults already match XAMPP (user `root`, empty password). If your MySQL
user has a password, set it in `config/config.php`:

```php
'db' => [
    'host'     => '127.0.0.1',
    'name'     => 'sahayata',
    'user'     => 'root',
    'password' => '',
],
```

`config/config.php` is listed in `.gitignore`, so your password is never committed.

### 5. Open the app

Go to <http://localhost/sahayata-app/>

Both of these addresses work:
- `http://localhost/sahayata-app/` (the root `.htaccess` forwards to `public/`)
- `http://localhost/sahayata-app/public/`

### 6. Check that everything works

- <http://localhost/sahayata-app/api/health> should show
  `"database":"connected"` and the five roles.
- Log in with one of the [demo accounts](#demo-accounts).
- The component preview (only while `'debug' => true`) is at
  <http://localhost/sahayata-app/dev/components>. Scroll down to **System checks** and
  click the three buttons:
  - **Check database connection** → `OK`
  - **Test CSRF-protected request** → `OK`
  - **Test request without CSRF token** → `FAILED` with status `403` (this is correct:
    requests without the security token must be refused)

---

## Alternative: PHP built-in server

If you don't use XAMPP, you can run the app with PHP's own server
(MySQL must still be running):

```bash
php -S localhost:8000 -t public public/index.php
```

Then open <http://localhost:8000>.

---

## Demo accounts

`database/seed.sql` creates one account for each role. They are clearly fake
(`@example.com` addresses; phone numbers starting with `010`, which is not a
valid Bangladeshi mobile prefix).

| Role | Email | Password |
|---|---|---|
| Manager | `manager@example.com` | `Demo@1234` |
| Admin | `admin@example.com` | `Demo@1234` |
| Donator | `donor@example.com` | `Demo@1234` |
| Volunteer | `volunteer@example.com` | `Demo@1234` |
| Victim | `victim@example.com` | `Demo@1234` |

On the login screen, choose the matching **login type** first (e.g. *Donator* for
`donor@example.com`). **Change or remove these accounts before putting the site online.**

---

## Authentication

| Screen / action | URL | Notes |
|---|---|---|
| Login (Figma "Home Page") | `/` | Choose login type, email, password. Normal form POST. |
| Sign Up | `/register` | Roles Donator, Victim, Volunteer. Admins are created by the Manager (Phase 7); the Manager account comes from the seed data. |
| Verify OTP | on `/register` | After Sign Up, enter the 4-digit demo OTP. The account becomes active and you are logged in. |
| Forgot Password (OTP) | `/forgot-password` | Email → demo OTP → new password. |
| Logout | `POST /logout` | Button on the role home page. |

JSON endpoints (all POST, all need the `X-CSRF-Token` header):
`/api/auth/register`, `/api/auth/verify-otp`, `/api/auth/resend-otp`,
`/api/auth/forgot-password`, `/api/auth/reset-password`.

Rules:
- A user must pick the login type that matches their account.
- Accounts stay **pending** until the OTP is verified. Logging in with a pending
  account opens the OTP step again.
- **Inactive** (deactivated) accounts cannot log in.
- After **5 wrong passwords** for an email within 15 minutes, logins for that email are
  blocked for the rest of the window (also 20 per IP address).
- An OTP expires after **10 minutes**, allows **5 wrong guesses**, can be used **once**,
  and a new one can be requested after **60 seconds**.
- Sessions end after **60 minutes** without activity.
- Passwords: at least 8 characters with at least one letter and one number.
- Phone numbers: Bangladeshi mobile format `01XXXXXXXXX` (`+880…` is accepted and converted).
  Each email and phone number can only be used for one account.

All limits can be changed in `config/config.php` (`session`, `login` and `otp` sections).

---

## Project structure

```
sahayata-app/
├── .htaccess                  # XAMPP: forwards every request to public/
├── app/                       # PHP application code (not reachable from the browser)
│   ├── bootstrap.php          # Starts every request: autoloader, config, errors, session
│   ├── helpers.php            # e(), url(), asset(), csrf_field(), component()
│   ├── routes.php             # List of all URLs and which controller handles them
│   ├── Core/                  # Small framework written for this project
│   │   ├── Auth.php           # Who is logged in, role checks
│   │   ├── Config.php         # Reads config/config.php
│   │   ├── Controller.php     # Base class for controllers
│   │   ├── Csrf.php           # CSRF tokens
│   │   ├── Database.php       # PDO connection + transactions
│   │   ├── ErrorHandler.php   # Error pages / JSON errors, logging
│   │   ├── HttpException.php  # Throw to show 403 / 404 etc.
│   │   ├── Model.php          # Base class for models (prepared statements)
│   │   ├── Request.php        # Current request: path, input, JSON body
│   │   ├── Response.php       # JSON responses and redirects
│   │   ├── Router.php         # URL -> controller, middleware, CSRF check
│   │   ├── Session.php        # Secure session + flash messages
│   │   ├── Validator.php      # Server-side validation rules
│   │   └── View.php           # Renders templates and components
│   ├── Controllers/           # C in MVC: handle a request, call models, choose a view
│   ├── Models/                # M in MVC: all database queries
│   └── Views/                 # V in MVC: HTML templates
│       ├── layouts/app.php    # Page skeleton used by every page
│       ├── components/        # Reusable pieces (header, tabs, fields, cards...)
│       ├── errors/            # Error page
│       └── dev/               # Component preview (debug mode only)
├── config/
│   └── config.example.php     # Copy to config.php
├── database/
│   ├── schema.sql             # Tables
│   └── seed.sql               # Lookup values + demo accounts
├── public/                    # The only folder the browser can see
│   ├── .htaccess              # Sends all URLs to index.php
│   ├── index.php              # Front controller
│   └── assets/
│       ├── css/               # base.css (design tokens), layout.css, components.css, pages/
│       ├── js/                # main.js, modules/ (api, validation, dom), pages/
│       └── images/            # Logos from the Figma export
└── storage/logs/              # app.log (errors), ignored by Git
```

---

## How a request flows through the code (MVC)

Example: the browser opens `/api/health`.

1. **`public/index.php`** loads `app/bootstrap.php` and the routes.
2. **`Router`** finds the matching line in `app/routes.php`:
   `$router->get('/api/health', [SystemController::class, 'health']);`
   It runs any middleware (`auth`, `role:admin`, ...) and checks the CSRF token on POST.
3. The **Controller** (`SystemController::health()`) asks a **Model**
   (`Role::all()`) for data.
4. The **Model** runs a prepared SQL statement through PDO.
5. The controller answers with **JSON** (`$this->json(...)`) or a **View**
   (`$this->view('page', [...])`), which is rendered inside `layouts/app.php`.

All JSON responses have the same shape:

```json
{ "success": true,  "message": "...", "data": { } }
{ "success": false, "message": "...", "errors": { "field": "message" } }
```

### Adding a page (pattern used in the next phases)

1. Add a route in `app/routes.php`, with middleware for who may open it.
2. Add a controller method in `app/Controllers/`.
3. Put database queries in a model in `app/Models/`.
4. Create the template in `app/Views/`, built from the components in `app/Views/components/`.

### Reusable components

| Component | Used for |
|---|---|
| `page-header` | "< Back" button, logo and page title |
| `back-button`, `logo` | Parts of the header |
| `tab-nav` | Light-blue tab rows (Home / My Donations / Profile ...) |
| `option-group` | One-choice button rows (login type, role, urgency) |
| `form-field` | Labelled input, textarea or select with error message |
| `card` | White card with action buttons (Donate Now, Approve / Reject ...) |
| `flash` | One-time success / error messages |
| `otp-input` | "Enter 4-digit OTP sent to mobile:" box + "Verify OTP" button |
| `logout-button` | Logout (POST form with CSRF token) |

Usage in a view:

```php
<?php component('form-field', [
    'name' => 'location', 'label' => 'Location:', 'placeholder' => 'Enter exact location',
    'required' => true, 'center' => true,
]); ?>
```

---

## Design system

All values were measured from the 11 exported Figma screens (393 × 852 px) and are
defined once in `public/assets/css/base.css`.

| Token | Value | Used for |
|---|---|---|
| `--color-bg` | `#87DBF2` | Inner screen background |
| `--color-bg-bright` | `#00C1F6` | Login screen background |
| `--color-primary` | `#3A81E4` | Main buttons |
| `--color-secondary` | `#CCE9F1` | Back button, tabs, choice buttons |
| `--color-danger` | `#E4543A` | Reject, Deactivate |
| `--color-urgency-*` | `#F3B2A6` / `#D78879` / `#D86045` | Low / Medium / High |
| `--color-map` | `#1B8235` | Map placeholder |
| Font | Inter 12px / 16px bold / 24px | Body / detail lines / titles |
| `--control-width` | 222px | Inputs, buttons, cards |

**Responsive behaviour:** on phones the app fills the screen (designed at 393px; tested
down to 320px). On tablets and desktops it is centred in a 480px column.

**Additions not in Figma** (needed for the app to be usable):
- The empty second login-type button is labelled **Manager**.
- The Figma texts "Welcome back! Please login.", "Forgot Password? (OTP)" and
  "Don't have an account?" were not placed on any exported frame; they are placed on the
  login screen in the free space between the existing elements.
- The unlabelled white box and blue button at the bottom of Sign Up are the
  **OTP input** and **Verify OTP** button (Figma texts "Enter 4-digit OTP sent to mobile:",
  "Verify OTP"). They appear after Sign Up. A small **Resend OTP** link was added below.
- **Forgot Password** has no Figma frame; it is built from the same components.
- A 2px black inner outline shows the **selected** choice button or tab.
- Validation messages appear in dark red under the field.
- Flash messages use the existing light-blue / light-red palette colours.
- A keyboard focus outline.

---

## Security

| Protection | Where |
|---|---|
| SQL injection | PDO prepared statements only (`app/Core/Model.php`), real prepares (`ATTR_EMULATE_PREPARES = false`) |
| Passwords | `password_hash()` / `password_verify()`; hashes are never stored in the session |
| CSRF | Token checked on **every** POST by the router; forms use `csrf_field()`, JavaScript sends the `X-CSRF-Token` header |
| XSS | Every value printed in HTML goes through `e()` (`htmlspecialchars`) |
| Sessions | HttpOnly + SameSite=Lax cookie, strict mode, new session ID on login |
| Access control | `auth` and `role:...` middleware on routes |
| Private files | Only `public/` is reachable; `app/`, `config/`, `database/`, `storage/` return 404 |
| Errors | Details are logged to `storage/logs/app.log`; set `'debug' => false` in `config/config.php` to hide them from visitors |
| Validation | Server: `app/Core/Validator.php`. Browser: `public/assets/js/modules/validation.js` (same rules) |

---

## Demo features

These features are **for demonstration only** and are not connected to real services:

- **OTP (one-time password):** no SMS is sent. Real SMS delivery is **not configured**.
  While `'demo_mode' => true` (in the `otp` section of `config/config.php`), the code is
  shown on screen as "Demo OTP: 1234 (SMS is not configured in this demo)". To use real
  SMS later, an SMS gateway would have to be connected where `AuthController` creates
  the code, and `demo_mode` set to `false`. Note: demo mode also reveals on the
  Forgot Password screen whether an email is registered (a code is shown only for real
  accounts); with real SMS this would not happen.
- **Payments:** donations are saved in the database with a generated demo reference.
  No real bKash, Nagad, Rocket or card payment is made.
- **Map:** OpenStreetMap is used. Relief points in the seed data are demo locations at
  approximate city centres, not real facilities.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| "Too many failed login attempts" while testing | Wait 15 minutes, or run `DELETE FROM login_attempts;` in phpMyAdmin |
| "Please choose the correct login type" | Select the login type that matches the account's role |
| "Setup needed: copy config/config.example.php..." | Create `config/config.php` (step 4) |
| `api/health` says "Database connection failed" | Start MySQL in XAMPP; check the user and password in `config/config.php`; import `schema.sql` |
| Every page except the start page shows "Not Found" from Apache | Check that `mod_rewrite` is enabled and `AllowOverride All` is set for `htdocs` (default in XAMPP) |
| Styles or links are broken | Set `'base_url' => '/sahayata-app'` in `config/config.php` |
| "Your session expired. Please reload the page" | The security token is outdated; reload the page and submit again |
| Blank page or error | Look in `storage/logs/app.log` |
