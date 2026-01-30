# Docx Tamplater Of Leave Request

## Requirements

- PHP >= 8.2
- Composer
- Node.js >=22.12.0
- Laravel 12

## Setup Instructions
### 1. Clone the repository
- git clone git@github.com:Chhounseangly/docx-templater-leave-request.git
- cd docx-templater-leave-request

### 2. Install dependencies
- npm install
- composer install

### 3. Set up environment variables
- cp .env.example .env
# Example for PostgreSQL:
- DB_CONNECTION=pgsql
- DB_HOST=127.0.0.1
- DB_PORT=5432
- DB_DATABASE=db_name
- DB_USERNAME=postgres
- DB_PASSWORD=your_password_here

### 4. Generate app key (Laravel)
- php artisan key:generate

### 5. Run migrations and seeders
- php artisan migrate

### 6. Optional: start the local server
- php artisan serve
