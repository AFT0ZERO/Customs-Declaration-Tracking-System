## Customs Declaration Tracking System

An internal web application to manage and track customs declarations. It provides authenticated users and administrators with tools to create, update, and monitor declarations, view change history, and manage users and profiles.

### Table of Contents

-   [Features](#features)
-   [Tech Stack](#tech-stack)
-   [Prerequisites](#prerequisites)
-   [Installation](#installation)
-   [Usage](#usage)
-   [Testing](#testing)
-   [Project Structure](#project-structure)


### Features

-   **User authentication**: Login, password reset, and profile management (Laravel Breeze flows under `resources/views/auth` and `resources/views/profile`).
-   **Custom declarations**: Create and manage customs declarations (`app/Models/CustomDeclaration.php`) with corresponding controllers and views.
-   **Declaration history**: Track changes and view historical records (`app/Models/DeclarationHistory.php`, `resources/views/history.blade.php`).
-   **Admin controls**: Role-based access with middleware for admin-only routes (`app/Http/Middleware/AdminMiddleware.php`) and user management views (`resources/views/users`).
-   **RESTful controllers**: Organized under `app/Http/Controllers` with routes in `routes/web.php` and `routes/api.php`.
-   **Localization-ready**: Arabic translations available under `resources/lang/ar`.
-   **Modern frontend**: Tailwind CSS, Vite, and Alpine.js for interactive UI.

### Tech Stack

-   **Backend**: Laravel 10 (PHP ^8.1), Eloquent ORM, Sanctum, Scout
-   **Frontend**: Vite, Tailwind CSS, Alpine.js, Axios
-   **Database**: MySQL/PostgreSQL/SQLite (configurable)

### Prerequisites

-   PHP ^8.1
-   Composer
-   Node.js (LTS) and npm
-   A database (MySQL, PostgreSQL, or SQLite)

### Installation

1. Clone the repository

```bash
git clone https://github.com/Customs-Declaration-Tracking-System.git
cd Customs-Declaration-Tracking-System
```

2. Install PHP dependencies

```bash
composer install
```

3. Install JS dependencies

```bash
npm install
```

4. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` and configure your database connection (e.g., `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`). Optionally set `APP_URL`.

5. Run migrations (and optional seeders)

```bash
php artisan migrate
php artisan db:seed    # optional if seeders are configured
```

6. Build frontend assets (dev server)

```bash
npm run dev
```

7. Start the application

```bash
php artisan serve
```

Visit the app at the URL shown (typically `http://127.0.0.1:8000`).

### Usage

-   Register or log in to access the dashboard (`resources/views/dashboard.blade.php`).
-   Create and manage customs declarations; updates will automatically record entries in declaration history.
-   Admin users can manage other users and access restricted routes protected by `AdminMiddleware`.
-   Profile management (update info and password) is available under the profile section.

### Testing

Run the test suite:

```bash
php artisan test
```

### Project Structure

-   `app/Http/Controllers` — Controllers for auth, declarations, history, and users
-   `app/Models` — Eloquent models for users, declarations, and histories
-   `resources/views` — Blade templates (auth, dashboard, users, history, layouts)
-   `routes/web.php` — Web routes; `routes/api.php` — API endpoints
-   `database/migrations` — Schema definitions for users, declarations, and histories
-   `public/` — Public assets


