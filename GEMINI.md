
# Gemini Project Brief: Coding First - Laravel LMS

This document provides a high-level overview of the "Coding First" Learning Management System (LMS) to guide development and maintenance tasks.

## 1. Project Purpose & Domain

- **Project Name**: Coding First - Laravel LMS
- **Domain**: Education Technology (Ed-Tech)
- **Core Purpose**: A comprehensive platform for managing programming courses, students, teachers, and finances for the "Coding First" institution. The system is designed to support a modern, interactive learning experience with features tailored to the needs of students, parents, teachers, and administrative staff.

## 2. Core Technologies & Frameworks

- **Backend**: Laravel 12
- **Frontend**: Tailwind CSS, Blade templates, vanilla JavaScript
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission
- **Database**: MySQL or SQLite
- **Build Tool**: Vite

## 3. Key Architectural Patterns & Conventions

- **MVC**: Follows the standard Model-View-Controller pattern.
- **RESTful Routes**: Routes are primarily RESTful and defined in `routes/web.php`.
- **Role-Based Access Control (RBAC)**: Implemented using the Spatie Laravel Permission library. User roles (Super Admin, Admin, Teacher, Finance, Parent, Student) dictate access to different parts of the application.
- **Service & Repository Patterns**: While not explicitly stated, the presence of complex controller logic suggests that a move towards service and repository layers for business logic and data access would be beneficial for future development.
- **Blade Components**: The project uses Blade components for reusable UI elements.
- **Eloquent ORM**: Database interactions are handled through Laravel's Eloquent ORM.

## 4. Project Structure & Key Files

- **`app/Http/Controllers`**: Contains controllers for handling HTTP requests.
- **`app/Models`**: Contains Eloquent models for database tables.
- **`app/Policies`**: Contains authorization policies.
- **`config`**: Contains application configuration files.
- **`database/migrations`**: Contains database migration files.
- **`database/seeders`**: Contains database seeder files.
- **`resources/views`**: Contains Blade templates.
- **`routes/web.php`**: Contains web routes.
- **`composer.json`**: Defines PHP dependencies.
- **`package.json`**: Defines JavaScript dependencies.

## 5. Development & Deployment

- **Installation**: Standard Laravel installation process (`composer install`, `npm install`, `php artisan migrate`, etc.).
- **Development Server**: `php artisan serve`
- **Asset Bundling**: `npm run dev` or `npm run build`
- **Testing**: PHPUnit is configured, but no specific testing commands are defined in the documentation.

## 6. Future Task Guidance

When working on this project, pay close attention to the following:

- **Role-Based Permissions**: Ensure that any new features or changes to existing features respect the defined user roles and permissions.
- **Database Schema**: Be mindful of the existing database schema and relationships when making changes.
- **Code Style**: Adhere to the existing code style and conventions.
- **Testing**: Write tests for new features and bug fixes to ensure the stability of the application.
