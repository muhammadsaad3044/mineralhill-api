# Mineral Hill Shilajit — Backend API (Laravel)

RESTful backend API for Mineral Hill Shilajit storefront and admin management portal, built with Laravel 12, Laravel Sanctum, and MySQL.

---

## Prerequisites (Windows)
- **PHP** >= 8.2 (with `pdo_mysql`, `openssl`, `mbstring`, `curl`, `fileinfo`)
- **Composer** (v2.x)
- **MySQL / MariaDB** (running on port 3306, e.g. via XAMPP)

---

## Setup Steps (Windows)

### 1. Open Terminal & Navigate to Project
```powershell
cd "D:\MineralHillShilajit Full Stack\mineralhill-api"
```

### 2. Install Dependencies
```powershell
composer install
```

### 3. Environment Configuration
Create your `.env` from `.env.example`:
```powershell
copy .env.example .env
php artisan key:generate
```

Verify your database settings in `.env`:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=mineralhill_db
DB_USERNAME=root
DB_PASSWORD=

APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

ADMIN_EMAIL=admin@mineralhill.com
ADMIN_PASSWORD=admin123
```

*(Ensure MySQL database `mineralhill_db` exists: `mysql -u root -e "CREATE DATABASE IF NOT EXISTS mineralhill_db;"`)*

### 4. Run Migrations & Seeders
Populates the admin user, site settings, default social/contact links, and 10 mock testimonials:
```powershell
php artisan migrate:fresh --seed
```

### 5. Link Public Storage (for Logo & Avatars)
```powershell
php artisan storage:link
```

### 6. Run Feature Test Suite
```powershell
php artisan test
```

### 7. Start the Development Server
```powershell
php artisan serve
```
The API is now running at:
```
http://localhost:8000
```
API endpoints are under:
```
http://localhost:8000/api/v1
```

---

## Admin Credentials
- **Email:** `admin@mineralhill.com`
- **Password:** `admin123`

---

## Documentation
Complete endpoint specifications and JSON payload schemas are documented in:
[API_DOCS.md](file:///D:/MineralHillShilajit%20Full%20Stack/mineralhill-api/API_DOCS.md)
