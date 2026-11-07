# Sahayata Unified Platform

**Sahayata** ("help") is a disaster relief management web application for Bangladesh.
Victims request help, admins approve requests and assign deliveries, donors make
(demo) donations, volunteers deliver relief and report progress, and a manager
oversees the whole system.

The interface reproduces the project's Figma design (11 mobile screens, 393 × 852 px).

---

## Contents

1. [Features](#features)
2. [Technology stack](#technology-stack)
3. [Installation with XAMPP](#installation-with-xampp)
4. [Alternative: PHP built-in server](#alternative-php-built-in-server)
5. [Demo accounts and roles](#demo-accounts-and-roles)
6. [How the relief workflow works](#how-the-relief-workflow-works)
7. [Demo OTP, demo payments and the map](#demo-otp-demo-payments-and-the-map)
8. [MVC architecture](#mvc-architecture)
9. [Project structure](#project-structure)
10. [Database](#database)
11. [Routes and JSON API](#routes-and-json-api)
12. [Security](#security)
13. [Design system](#design-system)
14. [Testing](#testing)
15. [Known limitations](#known-limitations)
16. [Troubleshooting](#troubleshooting)

---

## Features

| Module | Features |
|---|---|
| **Authentication** | Login with login type (Admin, Manager, Donor, Volunteer, Victim) · Sign Up (Donor, Victim, Volunteer) · demo OTP verification · Forgot Password with demo OTP · Logout · session timeout · login lockout after repeated wrong passwords |
| **Victim** | Request for Help (urgency, disaster type, location, "📍 My Location", contact number, description) · submission confirmation · Track My Request: status timeline, OpenStreetMap map, nearest relief point, assigned volunteer and contact · list of all own requests |
| **Donor** | Dashboard with approved requests · Donate Now · Make Donation (type, amount in BDT, payment method, mobile number) · demo payment confirmation · My Donations history with totals · Profile |
| **Admin** | Request Approvals dashboard: approve / reject pending requests, approved requests waiting for delivery, delivery monitoring · Assign Delivery to an available volunteer · Users · Volunteers (availability, active and delivered tasks) · Donations (with totals) |
| **Volunteer** | Volunteer Tasks: Available / Busy toggle, pending deliveries with Accept Task, own active tasks · Delivery Progress: Picked Up → In Transit → Delivered · Delivery History · Profile |
| **Manager** | Dashboard with live statistics (users, admins, donations, delivered) · administrator list · create administrators · activate / deactivate administrators · My Profile |
| **All roles** | Profile: change name and phone, change password, logout |

---

## Technology stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, vanilla JavaScript (ES modules, ES6/ES7: `const`/`let`, arrow functions, template literals, destructuring, spread/rest, Promises, `async`/`await`, Fetch API) |
| Backend | PHP 8.1+ (no framework), MVC architecture written for this project |
| Database | MySQL 8.0+ or MariaDB 10.4+ through PDO with prepared statements |
| Data exchange | JSON for all asynchronous requests |
| Map | OpenStreetMap embed (no JavaScript map library) |
| Font | Inter from Google Fonts (falls back to the system font when offline) |

No React, Vue, Angular, jQuery, TypeScript, Bootstrap, Tailwind, Laravel or other
framework or package is used. There is no build step and nothing to install with
npm or Composer.

---

## Installation with XAMPP

Tested setup: Apache 2.4 with `mod_rewrite`, PHP 8.3, MariaDB 10.11 (the same
components XAMPP uses). Any XAMPP version with **PHP 8.1 or newer** works.

### 1. Install and start XAMPP

1. Download XAMPP from <https://www.apachefriends.org> and install it.
2. Open the **XAMPP Control Panel** and click **Start** for **Apache** and **MySQL**.

### 2. Copy the project into `htdocs`

Extract the project and rename the folder to `sahayata-app` (lower case), then put it here:

| System | Folder |
|---|---|
| Windows | `C:\xampp\htdocs\sahayata-app` |
| macOS | `/Applications/XAMPP/htdocs/sahayata-app` |
| Linux | `/opt/lampp/htdocs/sahayata-app` |

Any folder name works; it just becomes part of the address
(`http://localhost/<folder-name>/`).

### 3. Create the database and import the SQL files

**With phpMyAdmin** (easiest):

1. Open <http://localhost/phpmyadmin>.
2. Click the **Import** tab → **Choose File** → select `database/schema.sql` → **Import**.
   This creates the `sahayata` database and all tables (you do not need to create the
   database yourself).
3. Import `database/seed.sql` the same way. This adds roles, dropdown values, demo
   relief points and the demo accounts.

**With the command line** (from inside the project folder; on Windows use
`C:\xampp\mysql\bin\mysql.exe`):

```bash
mysql -u root < database/schema.sql
```

```bash
mysql -u root < database/seed.sql
```

> ⚠️ `schema.sql` starts with `DROP DATABASE IF EXISTS sahayata;` — importing it again
> deletes all existing Sahayata data. Use it to reset the demo.

### 4. Create the configuration file

Copy `config/config.example.php` to **`config/config.php`**. The defaults already match
a fresh XAMPP installation (user `root`, empty password, database `sahayata`). If your
MySQL user has a password, edit the `db` section:

```php
'db' => [
    'host'     => '127.0.0.1',
    'port'     => 3306,
    'name'     => 'sahayata',
    'user'     => 'root',
    'password' => '',
],
```

`config/config.php` is listed in `.gitignore`, so your password is never committed.
It sits outside the `public/` folder and cannot be opened from the browser.

Other settings in the same file: `app.debug` (set to `false` on a public server),
`session.idle_minutes`, `login.max_attempts`, and the `otp` section.

### 5. Open the application

<http://localhost/sahayata-app/>

(`http://localhost/sahayata-app/public/` also works.)

### 6. Quick check

- <http://localhost/sahayata-app/api/health> should answer with
  `"database":"connected"` and the five roles.
- Log in with a [demo account](#demo-accounts-and-roles).

---

## Alternative: PHP built-in server

Without Apache (MySQL must still be running and `config/config.php` must exist):

```bash
php -S localhost:8000 -t public public/index.php
```

Then open <http://localhost:8000>.

---

## Demo accounts and roles

`database/seed.sql` creates one **clearly fake** account per role. They are synthetic
test accounts, not real people: `@example.com` addresses, and phone numbers starting
with `010`, which is not a valid Bangladeshi mobile prefix.

| Role | Login type to choose | Email | Password | Home screen |
|---|---|---|---|---|
| Manager | Manager | `manager@example.com` | `Demo@1234` | Manager Dashboard |
| Admin | Admin | `admin@example.com` | `Demo@1234` | Request Approvals |
| Donor | Donor | `donor@example.com` | `Demo@1234` | Donor Dashboard |
| Volunteer | Volunteer | `volunteer@example.com` | `Demo@1234` | Volunteer Tasks |
| Victim | Victim | `victim@example.com` | `Demo@1234` | Request for Help |

- You must choose the **login type** that matches the account.
- New **Donor, Victim and Volunteer** accounts are created with **Sign Up**.
- **Admin** accounts are created by the Manager. The Manager account only comes from the seed data.
- Because the demo phone numbers are deliberately invalid, enter a real-format number
  (e.g. `01712345678`) before saving a demo account's **Profile**.
- **Change or delete the demo accounts before putting the site online.**

**Navigation:** each role's first screen has the Figma "**< Back**" button. Since there
is no earlier screen inside the app, it **logs out** and returns to the login screen.
On all other screens "< Back" returns to the role's home screen. The Profile screen
also has a Logout button.

---

## How the relief workflow works

```
Victim submits request ──► Pending
Admin approves / rejects ──► Approved / Rejected
Donor donates (demo) to an approved request ──► a delivery is created: Pending
Admin assigns a volunteer  ─┐
  or a volunteer accepts   ─┴► Assigned   (volunteer becomes Busy)
Volunteer updates progress ──► Picked Up ──► In Transit ──► Delivered
                               (volunteer becomes Available again)
```

- The victim sees every step on **Track My Request** with dates, plus the assigned
  volunteer's name and phone number.
- An admin can **assign** any approved request (with or without a donation) to an
  **available** volunteer, and can reassign it until it is picked up.
- A donation to an approved request creates a **pending delivery** that appears under
  "Pending Deliveries" for all volunteers. The first available volunteer to press
  **Accept Task** gets it.
- Statuses only move **forward**. Skipping a step (e.g. Assigned → In Transit) is allowed.
- Every status change is recorded in `request_status_history`.
- All numbers on the Manager Dashboard are counted from the database on each visit.

---

## Demo OTP, demo payments and the map

### Demo OTP (no SMS)

**Real SMS delivery is not configured.** No message is sent to any phone.

1. After **Sign Up**, or after **Send OTP** on Forgot Password, the server creates a
   random 4-digit code. Only a **hash** of it is stored in `otp_codes`.
2. Because `otp.demo_mode` is `true` in `config/config.php`, the code is returned to
   the page and shown under the OTP box: `Demo OTP: 1234 (SMS is not configured in this demo).`
3. Type the code and press **Verify OTP**.

Rules: a code expires after **10 minutes**, allows **5 wrong guesses**, works **once**,
and a new one (**Resend OTP**) can be requested after **60 seconds**. A new code
cancels the previous one. If someone logs in with an account that was never verified,
the OTP step opens again.

To use real SMS later, an SMS gateway must be called where `AuthController` creates the
code, and `demo_mode` set to `false`. Note: in demo mode the Forgot Password screen
shows a code only for registered emails, so it reveals whether an email is registered.
With real SMS this would not happen.

### Demo payments

**No real bKash, Nagad, Rocket or card payment is made.** "Confirm Payment" stores the
donation in the `donations` table with a generated reference such as
`DEMO-3F9A1C22B0`, and the screen says so. Amounts must be between 0.01 and
1,000,000 BDT.

### Map (OpenStreetMap)

- **📍 My Location** on Request for Help uses the browser's Geolocation API to save the
  victim's coordinates. Browsers only allow this on `https://` or `http://localhost`,
  and the user must allow it.
- **Track My Request** then shows the location in an embedded **OpenStreetMap** map
  (222 × 76 px as in the design, with an "Open larger map" link), and the **nearest
  relief point** (straight-line distance to the demo relief points).
- **Fallbacks:**
  - Without coordinates, the green Figma map area is shown with the text "Location was
    not shared". A typed address is not converted to a map position.
  - Without internet, the map area stays green but everything else works.

---

## MVC architecture

```
Browser ──► public/index.php (front controller)
              │  app/bootstrap.php: autoloader, config, error handler, session
              ▼
            Router (app/routes.php)
              │  • matches the URL
              │  • checks the CSRF token on every POST
              │  • runs middleware: guest / auth / role:<name>
              ▼
            Controller (app/Controllers)        ◄── validates input (Validator)
              │  asks Models for data / changes
              ▼
            Model (app/Models)                  ◄── the ONLY place with SQL
              │  PDO prepared statements, transactions with row locks
              ▼
            MySQL / MariaDB
              ▲
Controller answers with either
  • a View (app/Views/<page>.php inside layouts/app.php, built from components), or
  • JSON: { "success": true, "message": "...", "data": {...} }
          { "success": false, "message": "...", "errors": { "field": "message" } }
```

**JavaScript side:**
- `assets/js/main.js` loads three modules on every page:
  - `validation.js` checks fields before sending;
  - `api-forms.js` sends `<form data-api-form="...">` as JSON with `fetch`;
  - `actions.js` sends buttons with `data-api-action="..."` (Approve, Reject, Accept, Assign, Activate…).
- Both send the CSRF token in the `X-CSRF-Token` header. On success they follow
  `data.redirect` or reload the page, so the screen always shows the database state.
- Page-specific scripts are in `assets/js/pages/`.

### Adding a new screen

1. Add a route in `app/routes.php` with its middleware (e.g. `['role:admin']`).
2. Add a controller method.
3. Put any SQL in a model method.
4. Build the view from the components in `app/Views/components/`.

---

## Project structure

```
sahayata-app/
├── .htaccess                     XAMPP: forwards every request to public/
├── .gitignore
├── README.md
├── app/                          PHP code (not reachable from the browser)
│   ├── bootstrap.php             Starts every request
│   ├── helpers.php               e(), url(), asset(), csrf_field(), component(), ref(), money(), short_date()
│   ├── routes.php                All URLs and who may open them
│   ├── Core/                     Small framework for this project
│   │   ├── Auth.php              Logged-in user, role checks, idle timeout, account re-check
│   │   ├── Config.php            Reads config/config.php
│   │   ├── Controller.php        Base controller: view(), json(), validateOrFail(), attempt()
│   │   ├── Csrf.php              CSRF tokens
│   │   ├── Database.php          PDO connection, transactions
│   │   ├── ErrorHandler.php      Error pages / JSON errors, logging
│   │   ├── HttpException.php
│   │   ├── Model.php             Base model: fetchAll(), fetchOne(), execute()
│   │   ├── Navigation.php        Tab rows of each role
│   │   ├── Request.php / Response.php
│   │   ├── Router.php            Routing, middleware, CSRF check
│   │   ├── Session.php           Secure session, flash messages
│   │   ├── Validator.php         Server-side validation rules
│   │   └── View.php              Templates and components
│   ├── Controllers/
│   │   ├── AuthController.php    Login, Sign Up, OTP, Forgot Password, Logout
│   │   ├── VictimController.php  Request for Help, Track My Request
│   │   ├── DonorController.php   Dashboard, Make Donation, My Donations
│   │   ├── AdminController.php   Approvals, Assign Delivery, Users, Volunteers, Donations
│   │   ├── VolunteerController.php Tasks, Delivery Progress, History, availability
│   │   ├── ManagerController.php Statistics, admin management
│   │   ├── ProfileController.php Profile for all roles
│   │   ├── SystemController.php  /api/health
│   │   └── DevController.php     Component preview (debug mode only)
│   ├── Models/
│   │   ├── User.php, Role.php, Otp.php, LoginAttempt.php
│   │   ├── HelpRequest.php       Requests + status history
│   │   ├── Donation.php          Demo donations
│   │   ├── Delivery.php          Delivery workflow (assign, accept, progress)
│   │   ├── Volunteer.php         Availability
│   │   ├── ReliefPoint.php       Nearest relief point
│   │   └── Lookup.php            Dropdown lists
│   └── Views/
│       ├── layouts/app.php       Page skeleton
│       ├── components/           page-header, back-button, logo, tab-nav, option-group,
│       │                         form-field, card, flash, otp-input, logout-button,
│       │                         status-timeline, osm-map
│       ├── auth/  victim/  donor/  admin/  volunteer/  manager/  profile/
│       ├── errors/error.php
│       └── dev/components.php
├── config/
│   └── config.example.php        Copy to config.php
├── database/
│   ├── schema.sql                Tables, keys, indexes
│   └── seed.sql                  Lookup data, demo accounts, demo relief points
├── public/                       The only folder the browser can reach
│   ├── .htaccess                 Sends all URLs to index.php
│   ├── index.php                 Front controller
│   └── assets/
│       ├── css/                  base.css (design tokens), layout.css, components.css,
│       │                         pages/auth.css, pages/screens.css, pages/dev.css
│       ├── js/                   main.js, modules/ (api, actions, api-forms, forms,
│       │                         validation, otp, dom), pages/ (register, forgot-password,
│       │                         request-help, dev-components)
│       └── images/               Logos from the Figma export
└── storage/logs/                 app.log (errors; ignored by Git)
```

---

## Database

`database/schema.sql` creates 13 InnoDB tables (utf8mb4) with foreign keys and indexes:

| Table | Purpose |
|---|---|
| `roles` | manager, admin, donor, volunteer, victim |
| `users` | All accounts. `password_hash` only. Unique email and phone. `status`: pending / active / inactive. `created_by` = manager who created an admin |
| `volunteer_profiles` | Volunteer availability (available / busy), 1-to-1 with users |
| `otp_codes` | Demo OTP **hashes**: purpose, attempts, expiry, consumed time |
| `login_attempts` | Failed logins for the lockout |
| `disaster_types` | Flood, Fire, Cyclone, Earthquake, Landslide, Other |
| `donation_types` | Money, Food, Water, Medicine, Clothing, Shelter |
| `payment_methods` | bKash, Nagad, Rocket, Card (demo only) |
| `relief_points` | Demo relief points with coordinates |
| `help_requests` | Victim requests: urgency, location, optional coordinates, status, reviewer |
| `request_status_history` | Every status change with time and user |
| `donations` | Demo donations with generated `demo_reference` (amount > 0 enforced by a CHECK constraint) |
| `deliveries` | One per request: volunteer, assigning admin, donation, status, timestamps |

Critical operations run in **transactions** with `SELECT … FOR UPDATE` row locks:
- approving or rejecting;
- donating, which also creates or links the delivery;
- assigning, accepting and updating deliveries;
- registering, which also creates the volunteer profile and the OTP.

So two people can never accept or decide the same item at once.

---

## Routes and JSON API

| Method | URL | Access |
|---|---|---|
| GET | `/` (login), `/register`, `/forgot-password` | logged-out users |
| POST | `/login` (form), `/api/auth/register`, `/api/auth/verify-otp`, `/api/auth/resend-otp`, `/api/auth/forgot-password`, `/api/auth/reset-password` | logged-out users |
| POST | `/logout` | logged in |
| GET / POST | `/profile`, `/api/profile`, `/api/profile/password` | any role |
| GET | `/victim/request-help`, `/victim/track?request=ID` | victim |
| POST | `/api/victim/requests` | victim |
| GET | `/donor/dashboard`, `/donor/donate?request=ID`, `/donor/donations` | donor |
| POST | `/api/donor/donations` | donor |
| GET | `/admin/dashboard`, `/admin/requests/{id}/assign`, `/admin/users`, `/admin/volunteers`, `/admin/donations` | admin |
| POST | `/api/admin/requests/{id}/approve`, `…/reject`, `…/assign` (`{volunteer_id}`) | admin |
| GET | `/volunteer/tasks`, `/volunteer/deliveries/{id}`, `/volunteer/history` | volunteer |
| POST | `/api/volunteer/availability`, `/api/volunteer/deliveries/{id}/accept`, `/api/volunteer/deliveries/{id}/status` (`{status}`) | volunteer |
| GET | `/manager/dashboard`, `/manager/admins/create` | manager |
| POST | `/api/manager/admins`, `/api/manager/admins/{id}/status` (`{status}`) | manager |
| GET | `/api/health` | anyone |

Every POST needs the CSRF token, either as the `X-CSRF-Token` header or a `_csrf` form field.

---

## Security

| Protection | How |
|---|---|
| Passwords | `password_hash()` / `password_verify()`; rehash when needed; minimum 8 characters with a letter and a number |
| SQL injection | Only PDO prepared statements, with native prepares (`ATTR_EMULATE_PREPARES = false`) |
| XSS | Every value printed in HTML goes through `e()` (`htmlspecialchars`) |
| CSRF | Token checked by the router on **every** POST; `hash_equals` comparison |
| Sessions | HttpOnly + SameSite=Lax cookie, strict mode, **new session ID after login**, idle timeout (60 min), logout destroys the session |
| Authorization | Every route has server-side middleware (`role:admin`, …). Roles are never taken from the browser. Each protected request re-reads the account from the database, so a **deactivated** admin is logged out immediately |
| Ownership | Victims only see their own requests; volunteers only their own deliveries; the manager can only change **admin** accounts |
| Privilege escalation | Sign Up only allows donor / victim / volunteer; admins are created server-side with role `admin` fixed in code |
| Brute force | Login lockout (5 wrong passwords per email in 15 min, 20 per IP); OTP: 5 guesses, expiry, single use, resend wait |
| Account discovery | Same login error for unknown email and wrong password (constant-time check); same Forgot Password message for all emails |
| Validation | Server: `app/Core/Validator.php` (all input). Browser: `validation.js` mirrors the rules for instant feedback |
| Private files | Only `public/` is reachable. `app/`, `config/`, `database/` and `storage/` return 404 |
| Errors | Logged to `storage/logs/app.log`. Details are shown only when `app.debug` is `true` |

---

## Design system

All values were measured from the exported Figma frames and are defined once in
`public/assets/css/base.css`.

| Token | Value | Used for |
|---|---|---|
| `--color-bg` | `#87DBF2` | Background of inner screens |
| `--color-bg-bright` | `#00C1F6` | Login screen background |
| `--color-primary` | `#3A81E4` | Main buttons |
| `--color-secondary` | `#CCE9F1` | Back button, tabs, choice buttons |
| `--color-danger` | `#E4543A` | Reject, Deactivate |
| `--color-urgency-*` | `#F3B2A6` / `#D78879` / `#D86045` | Low / Medium / High |
| `--color-map` | `#1B8235` | Map area |
| Font | Inter 12 px / 16 px bold / 24 px | Text / detail lines / titles |
| Sizes | inputs 222 × 20, buttons 222 × 27, tabs 29 high, Back 98 × 42, logo 108 | |

**Responsive:** on phones the app fills the screen. It was designed at 393 px and
tested at 320–393 px. On tablets and desktops it is centred in a **480 px** column.

**Design decisions** (agreed during development):
- The empty login-type button is **Manager**.
- "Donator" is shown as **Donor** everywhere.
- Spelling fixes: "Vounteers" → Volunteers, "Contract Number" → Contact Number.
- Figma texts not placed on any exported screen were placed where they belong:
  - on the login screen: "Welcome back! Please login.", "Forgot Password? (OTP)", "Don't have an account?";
  - the OTP box on Sign Up;
  - "📍 My Location" on Request for Help;
  - "Delivery Status:" with "🟡 Pending → 🔵 Assigned → 🟢 Done" on Track.
- Tabs without a Figma screen (My Donations, Profile, History, Users, Volunteers,
  Donations, My Profile) and the screens Forgot Password and Create New Admin use the
  same components and styles.
- Selected tabs and choice buttons get a 2 px black inner outline, because Figma has no
  selected state. Validation messages are dark red.

---

## Testing

The following was tested during development (Ubuntu, Apache 2.4 + PHP 8.3 + MariaDB
10.11, and PHP's built-in server; browser tests with Chromium via Playwright):

- **PHP:** syntax check of every file; every page opened for its role (17 pages);
  pages of other roles answer 403; 404 / 405 handling.
- **Database:** schema and seed import; foreign keys, CHECK and ENUM constraints reject
  invalid data; transactions roll back on errors.
- **End-to-end workflow (browser):**
  1. Victim signs up with demo OTP.
  2. Victim submits a request using My Location.
  3. Admin approves it.
  4. Donor sees it.
  5. Donor makes a demo donation.
  6. Admin assigns the volunteer.
  7. Volunteer sees the task.
  8. Volunteer updates Picked Up → In Transit → Delivered.
  9. Victim sees each step and the volunteer's contact.
  10. Manager statistics match the database.

  Also: a volunteer accepting a pending task, the rejection path, creating,
  deactivating and re-activating an admin (a deactivated admin is logged out at once),
  profile and password changes, and logout.
- **Security:**
  - Refused: wrong-role API calls; a second review of the same request; donations to
    closed requests; updating another volunteer's delivery; backward status changes;
    assigning busy or invalid volunteers; manager changes to non-admin accounts.
  - Script text is shown as plain text (escaped); requests without a CSRF token are refused.
  - Login lockout, OTP expiry, wrong-guess limit and reuse.
- **Responsive:** no horizontal scrolling at 393, 768, 1024 and 1440 px (and 320 px for
  login); the app column is 480 px wide on tablet and desktop. No JavaScript console errors.

**How to test it yourself:**
1. Re-import `schema.sql` and `seed.sql` for a clean start.
2. Then follow the workflow above in two browser windows (or one normal and one private window) with different demo accounts:
   victim → admin → donor → admin → volunteer → victim → manager.

---

## Known limitations

- **OTP** codes are shown on screen (demo mode); no SMS is sent.
- **Payments** are demo records only; no money moves.
- **Map:**
  - A map appears only when the victim used **📍 My Location**; typed addresses are not geocoded.
  - The embedded map needs internet access to openstreetmap.org.
  - Geolocation needs `localhost` or HTTPS.
- **"Pending Deliveries near you"** lists **all** pending deliveries; they are not
  filtered by distance. The nearest relief point is a straight-line distance to four
  demo points.
- **One delivery per request.** More donations to the same request are recorded and
  counted, but the delivery shows the first linked donation.
- **Volunteer availability:**
  - A volunteer becomes **Busy** when given a task and Available again after delivering it.
  - Busy volunteers cannot take tasks; they can switch themselves back to Available.
- **Admin screens:**
  - Admins can view users but cannot edit or delete them.
  - Lists are not paginated, which suits a demo or class project.
- **Demo accounts** have placeholder phone numbers (`010…`); enter a valid number to save their profile.
- **Logos** come from the 1× Figma export (about 108–152 px), so they look soft on
  high-resolution screens; a higher-resolution export can replace the two files in
  `public/assets/images/` with the same names.
- **Testing environment:**
  - Tested on Linux (Apache + PHP 8.3 + MariaDB), not on Windows XAMPP itself.
  - Browser testing used Chromium only.

---

## Troubleshooting

| Problem | Fix |
|---|---|
| "Setup needed: copy config/config.example.php …" | Create `config/config.php` (installation step 4) |
| `api/health` says "Database connection failed" | Start MySQL in XAMPP; check user and password in `config/config.php`; import `schema.sql` |
| Only the login page works, other pages show Apache "Not Found" | Enable `mod_rewrite` and `AllowOverride All` for `htdocs` (both are on by default in XAMPP) |
| Styles or links broken | Set `'base_url' => '/sahayata-app'` (your folder name) in `config/config.php` |
| "Your session expired. Please reload the page" | The security token is outdated: reload and try again |
| "Too many failed login attempts" | Wait 15 minutes, or run `DELETE FROM login_attempts;` in phpMyAdmin |
| "Please choose the correct login type" | Choose the login type that matches the account's role |
| 📍 My Location does nothing / is denied | Allow location access in the browser; use `http://localhost` (not an IP address) or HTTPS |
| Map area stays green | The computer has no internet access to openstreetmap.org; everything else still works |
| Blank page or error | Look in `storage/logs/app.log` |
