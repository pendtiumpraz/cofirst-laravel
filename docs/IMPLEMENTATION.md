# Coding First LMS - Implementation Guide

## Prerequisites

Before starting implementation, ensure you have:
- PHP 8.2+
- MySQL 8.0+
- Node.js 18+
- Composer
- Laravel 12 installed
- Laravel Breeze installed
- Spatie Laravel Permission installed

## Phase 1: Database Setup

### 1.1 Create Migrations

```bash
# Create all required migrations
php artisan make:migration create_roles_table
php artisan make:migration create_user_roles_table  
php artisan make:migration create_profiles_table
php artisan make:migration create_parent_student_table
php artisan make:migration create_courses_table
php artisan make:migration create_curriculums_table
php artisan make:migration create_syllabuses_table
php artisan make:migration create_materials_table
php artisan make:migration create_class_names_table
php artisan make:migration create_schedules_table
php artisan make:migration create_enrollments_table
php artisan make:migration create_attendance_table
php artisan make:migration create_reports_table
php artisan make:migration create_teacher_handovers_table
php artisan make:migration create_financial_transactions_table
php artisan make:migration create_announcements_table
php artisan make:migration create_bootcamp_batches_table
```

### 1.2 Create Models with Relationships

```bash
# Generate models
php artisan make:model Role
php artisan make:model Profile
php artisan make:model ParentStudent
php artisan make:model Course
php artisan make:model Curriculum
php artisan make:model Syllabus
php artisan make:model Material
php artisan make:model ClassName
php artisan make:model Schedule
php artisan make:model Enrollment
php artisan make:model Attendance
php artisan make:model Report
php artisan make:model TeacherHandover
php artisan make:model FinancialTransaction
php artisan make:model Announcement
php artisan make:model BootcampBatch
```

### 1.3 Create Seeders

```bash
# Create seeders for initial data
php artisan make:seeder RoleSeeder
php artisan make:seeder UserSeeder
php artisan make:seeder CourseSeeder
php artisan make:seeder CurriculumSeeder
```

### 1.4 Create Factories

```bash
# Create factories for testing
php artisan make:factory ProfileFactory
php artisan make:factory CourseFactory
php artisan make:factory ClassNameFactory
php artisan make:factory ScheduleFactory
php artisan make:factory EnrollmentFactory
php artisan make:factory AttendanceFactory
php artisan make:factory ReportFactory
php artisan make:factory FinancialTransactionFactory
```

## Phase 2: Authentication & Authorization

### 2.1 Configure User Model

Update `app/Models/User.php` to use Spatie Laravel Permission:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function teachingClasses()
    {
        return $this->hasMany(ClassName::class, 'teacher_id');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class, 'student_id');
    }

    public function parentStudents()
    {
        return $this->hasMany(ParentStudent::class, 'parent_id');
    }

    public function studentParents()
    {
        return $this->hasMany(ParentStudent::class, 'student_id');
    }
}
```

### 2.2 Create Role-based Middleware

```bash
php artisan make:middleware RoleMiddleware
```

### 2.3 Set up Registration Flow

Create custom registration forms for different user types.

## Phase 3: Core Controllers

### 3.1 Landing Page Controller

```bash
php artisan make:controller LandingController
```

### 3.2 Dashboard Controllers

```bash
php artisan make:controller DashboardController
php artisan make:controller Parent/DashboardController
php artisan make:controller Student/DashboardController
php artisan make:controller Teacher/DashboardController
php artisan make:controller Admin/DashboardController
php artisan make:controller Finance/DashboardController
php artisan make:controller SuperAdmin/DashboardController
```

### 3.3 Feature Controllers

```bash
# Parent Controllers
php artisan make:controller Parent/ReportController
php artisan make:controller Parent/CourseController
php artisan make:controller Parent/ChildController

# Student Controllers  
php artisan make:controller Student/ScheduleController
php artisan make:controller Student/CourseController
php artisan make:controller Student/MaterialController

# Teacher Controllers
php artisan make:controller Teacher/ScheduleController
php artisan make:controller Teacher/AttendanceController
php artisan make:controller Teacher/ReportController
php artisan make:controller Teacher/HandoverController
php artisan make:controller Teacher/CurriculumController

# Admin Controllers
php artisan make:controller Admin/UserController
php artisan make:controller Admin/ScheduleController
php artisan make:controller Admin/CourseController
php artisan make:controller Admin/ClassController
php artisan make:controller Admin/ReportController

# Finance Controllers
php artisan make:controller Finance/TransactionController
php artisan make:controller Finance/ReportController

# SuperAdmin Controllers
php artisan make:controller SuperAdmin/SystemController
php artisan make:controller SuperAdmin/CurriculumController
php artisan make:controller SuperAdmin/AnalyticsController
```

## Phase 4: API Controllers

```bash
# API Controllers
php artisan make:controller Api/AuthController
php artisan make:controller Api/UserController
php artisan make:controller Api/CourseController
php artisan make:controller Api/ScheduleController
php artisan make:controller Api/ReportController
php artisan make:controller Api/FinancialController
```

## Phase 5: Form Requests

```bash
# Create form requests for validation
php artisan make:request StoreUserRequest
php artisan make:request UpdateUserRequest
php artisan make:request StoreCourseRequest
php artisan make:request UpdateCourseRequest
php artisan make:request StoreScheduleRequest
php artisan make:request UpdateScheduleRequest
php artisan make:request StoreReportRequest
php artisan make:request UpdateReportRequest
php artisan make:request StoreTransactionRequest
```

## Phase 6: Resources (API)

```bash
# API Resources for consistent JSON responses
php artisan make:resource UserResource
php artisan make:resource CourseResource
php artisan make:resource ScheduleResource
php artisan make:resource ReportResource
php artisan make:resource TransactionResource
```

## Phase 7: Routes Configuration

### 7.1 Web Routes (`routes/web.php`)

```php
<?php

use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [LandingController::class, 'index'])->name('landing');
Route::get('/courses', [LandingController::class, 'courses'])->name('courses');
Route::get('/about', [LandingController::class, 'about'])->name('about');

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Parent routes
    Route::middleware(['role:parent'])->prefix('parent')->name('parent.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Parent\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('reports', \App\Http\Controllers\Parent\ReportController::class)->only(['index', 'show']);
        Route::resource('children', \App\Http\Controllers\Parent\ChildController::class)->only(['index', 'show']);
    });
    
    // Student routes
    Route::middleware(['role:student'])->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Student\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('schedules', \App\Http\Controllers\Student\ScheduleController::class)->only(['index', 'show']);
        Route::resource('materials', \App\Http\Controllers\Student\MaterialController::class)->only(['index', 'show']);
    });
    
    // Teacher routes
    Route::middleware(['role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Teacher\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('schedules', \App\Http\Controllers\Teacher\ScheduleController::class);
        Route::resource('attendance', \App\Http\Controllers\Teacher\AttendanceController::class);
        Route::resource('reports', \App\Http\Controllers\Teacher\ReportController::class);
        Route::resource('handovers', \App\Http\Controllers\Teacher\HandoverController::class);
    });
    
    // Admin routes
    Route::middleware(['role:admin|superadmin'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('users', \App\Http\Controllers\Admin\UserController::class);
        Route::resource('courses', \App\Http\Controllers\Admin\CourseController::class);
        Route::resource('classes', \App\Http\Controllers\Admin\ClassController::class);
        Route::resource('schedules', \App\Http\Controllers\Admin\ScheduleController::class);
    });
    
    // Finance routes
    Route::middleware(['role:finance|admin|superadmin'])->prefix('finance')->name('finance.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Finance\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('transactions', \App\Http\Controllers\Finance\TransactionController::class);
        Route::get('/reports', [\App\Http\Controllers\Finance\ReportController::class, 'index'])->name('reports.index');
    });
    
    // SuperAdmin routes
    Route::middleware(['role:superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
        Route::resource('curriculums', \App\Http\Controllers\SuperAdmin\CurriculumController::class);
        Route::get('/analytics', [\App\Http\Controllers\SuperAdmin\AnalyticsController::class, 'index'])->name('analytics');
        Route::get('/system', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'index'])->name('system');
    });
});

require __DIR__.'/auth.php';
```

### 7.2 API Routes (`routes/api.php`)

```php
<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Public API routes
Route::post('/auth/login', [AuthController::class, 'login']);

// Protected API routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    
    // User management
    Route::apiResource('users', UserController::class);
    
    // Course management
    Route::apiResource('courses', CourseController::class);
    
    // Schedule management
    Route::apiResource('schedules', ScheduleController::class);
    Route::get('/schedules/user/{user}', [ScheduleController::class, 'userSchedules']);
    
    // Reports
    Route::get('/reports/student/{student}', [\App\Http\Controllers\Api\ReportController::class, 'studentReports']);
    Route::post('/reports', [\App\Http\Controllers\Api\ReportController::class, 'store']);
    
    // Financial
    Route::middleware(['role:finance|admin|superadmin'])->group(function () {
        Route::apiResource('transactions', \App\Http\Controllers\Api\FinancialController::class);
        Route::get('/reports/financial', [\App\Http\Controllers\Api\FinancialController::class, 'reports']);
    });
});
```

## Phase 8: Views Implementation

### 8.1 Create Layout Files

```bash
# Main application layout
resources/views/layouts/app.blade.php
resources/views/layouts/guest.blade.php
resources/views/layouts/navigation.blade.php
```

### 8.2 Create Landing Page

```bash
resources/views/landing.blade.php
resources/views/courses.blade.php
resources/views/about.blade.php
```

### 8.3 Create Dashboard Views

Create dashboard views for each role with appropriate widgets and information.

### 8.4 Create Feature Views

Implement all CRUD views for each feature according to the role permissions.

## Phase 9: Frontend Assets

### 9.1 Tailwind CSS Configuration

Update `tailwind.config.js` with custom colors and components for Coding First branding.

### 9.2 Custom Components

Create reusable Blade components:
- Form inputs
- Buttons
- Cards
- Tables
- Modals
- Alerts

### 9.3 JavaScript Enhancement

Add interactive features using Alpine.js (included with Breeze):
- Dynamic forms
- Real-time notifications
- Interactive charts
- Date pickers

## Phase 10: Testing

### 10.1 Feature Tests

```bash
php artisan make:test AuthenticationTest
php artisan make:test UserManagementTest
php artisan make:test CourseManagementTest
php artisan make:test ScheduleManagementTest
php artisan make:test ReportingTest
```

### 10.2 Unit Tests

```bash
php artisan make:test --unit UserTest
php artisan make:test --unit CourseTest
php artisan make:test --unit ScheduleTest
```

## Phase 11: Performance Optimization

### 11.1 Database Optimization
- Add proper indexes
- Optimize queries with eager loading
- Implement database caching

### 11.2 Application Caching
- Configure Redis/Memcached
- Implement view caching
- Set up route caching

### 11.3 Asset Optimization
- Optimize images
- Minify CSS/JS
- Set up CDN

## Phase 12: Security Hardening

### 12.1 Input Validation
- Comprehensive form validation
- API input sanitization
- File upload security

### 12.2 Access Control
- Role-based permissions
- Route protection
- API rate limiting

### 12.3 Data Protection
- Encrypt sensitive data
- Secure file storage
- Audit logging

## Phase 13: Documentation

### 13.1 API Documentation
- Generate OpenAPI specification
- Create API usage examples
- Document authentication flow

### 13.2 User Documentation
- User manuals for each role
- Feature tutorials
- FAQ section

### 13.3 Developer Documentation
- Code documentation
- Deployment guide
- Maintenance procedures

## Phase 14: Deployment

### 14.1 Production Setup
- Configure production environment
- Set up database backups
- Configure monitoring

### 14.2 CI/CD Pipeline
- Automated testing
- Deployment automation
- Rolling updates

### 14.3 Monitoring
- Application monitoring
- Performance monitoring
- Error tracking

## Execution Commands Summary

```bash
# Initial setup
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# Development
php artisan serve
npm run dev

# Testing
php artisan test

# Production build
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

This implementation guide provides a comprehensive roadmap for building the Coding First LMS system. Each phase builds upon the previous ones, ensuring a systematic and maintainable development process. 