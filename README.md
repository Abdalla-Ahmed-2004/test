# Laravel Project Setup

## Prerequisites

Before starting, ensure the following are installed on your system:

- **PHP**: Version 8.0 or higher.
- **Composer**: Dependency manager for PHP.
- **Node.js**: For frontend asset management.
- **MySQL**: For the database.
- **Git**: To clone the repository.

---

## Installation Steps

### Step 1: Clone the Repository

Run the following command to clone the project:

```bash
git clone https://github.com/ABDALLAPEPO/focus_platform.git
cd focus_platform
```

### Step 2: Install PHP Dependencies

Install the required PHP packages using Composer:

```bash
composer install
```

### Step 3: Set Up the `.env` File

1. Copy the `.env.example` file to `.env`:
    ```bash
    cp .env.example .env
    ```
2. Open the `.env` file and configure the following:
    - **App Settings**:
        ```env
        APP_NAME=Laravel
        APP_ENV=local
        APP_KEY=
        APP_DEBUG=true
        APP_URL=http://127.0.0.1:8000
        ```
    - **Database Settings**:
        ```env
        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=your_database_name
        DB_USERNAME=your_username
        DB_PASSWORD=your_password
        ```
    - **JWT Secret**:
      Leave this blank for now; it will be generated in the next step.

### Step 4: Generate the Application Key

Run the following command to generate the application key:

```bash
php artisan key:generate
```

### Step 5: Generate the JWT Secret

Run the following command to generate the JWT secret:

```bash
php artisan jwt:secret
```

### Step 6: Run Migrations and Seeders

Run the database migrations and seeders to set up the database:

```bash
php artisan migrate --seed
```
<!-- 
### Step 7: Install Node.js Dependencies

Install the required Node.js packages:

```bash
npm install
```

### Step 8: Build Frontend Assets

Build the frontend assets using Vite:

```bash
npm run dev
``` -->

### Step 9: Start the Development Server

Start the Laravel development server:

```bash
php artisan serve
```

The application will now be accessible at `http://127.0.0.1:8000`.

---

## Additional Notes

### Clear Caches

If you encounter issues, clear the Laravel caches:

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### Database Setup

Ensure the database is created in MySQL before running migrations.

### Permissions

Ensure the `storage` and `bootstrap/cache` directories are writable:

```bash
chmod -R 775 storage bootstrap/cache
```

---

## Installing Additional Packages

### Install JWT Authentication Package

The project uses the `tymon/jwt-auth` package for JSON Web Token authentication. If you need to install it manually, follow these steps:

1. Require the package via Composer:

    ```bash
    composer require tymon/jwt-auth
    ```

2. Publish the configuration file:

    ```bash
    php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
    ```

3. Generate the JWT secret:
    ```bash
    php artisan jwt:secret
    ```

### Install Spatie Laravel Permission Package

The project uses the `spatie/laravel-permission` package for role and permission management. If you need to install it manually, follow these steps:

1. Require the package via Composer:

    ```bash
    composer require spatie/laravel-permission
    ```

2. Publish the configuration and migration files:

    ```bash
    php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
    ```

3. Run the migrations to create the necessary tables:
    ```bash
    php artisan migrate
    ```

For more details, refer to the official documentation of each package:

- [JWT Auth Documentation](https://jwt-auth.readthedocs.io/)
- [Spatie Laravel Permission Documentation](https://spatie.be/docs/laravel-permission/)

---

## Summary of Commands

Here’s a quick list of all commands:

```bash
git https://github.com/Abdalla-Ahmed-2004/focus_platform.git;
cd focus_platform;
composer install;
cp .env.example .env;
php artisan key:generate;
php artisan jwt:secret;
php artisan migrate --seed;
# npm install
# npm run dev
php artisan serve
```
