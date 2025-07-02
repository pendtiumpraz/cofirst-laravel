# Troubleshooting Guide - Coding First LMS

## Common Issues and Solutions

### 1. Route [superadmin.dashboard] not defined

**Error Message:**
```
Symfony\Component\Routing\Exception\RouteNotFoundException
Route [superadmin.dashboard] not defined.
```

**Root Cause:**
- DashboardController was trying to redirect to routes that were removed from `routes/web.php`
- Role-specific dashboard routes were cleaned up but controller still referenced them

**Solution:**
Updated `app/Http/Controllers/DashboardController.php` to use views directly instead of redirects:

```php
// OLD (Causes error)
if ($user->isSuperAdmin()) {
    return redirect()->route('superadmin.dashboard');
}

// NEW (Fixed)
if ($user->isSuperAdmin()) {
    return view('superadmin.dashboard', compact('user'));
}
```

**Files Modified:**
- `app/Http/Controllers/DashboardController.php`

**Commands to Clear Cache:**
```bash
php artisan route:clear
php artisan config:clear
```

---

### 2. UNIQUE constraint failed: users.email

**Error Message:**
```
SQLSTATE[23000]: Integrity constraint violation: 19 UNIQUE constraint 
failed: users.email
```

**Root Cause:**
- UserSeeder trying to create users that already exist
- Database not properly reset between seeding attempts

**Solution 1 - Complete Reset:**
```bash
php artisan db:wipe
php artisan migrate --seed
```

**Solution 2 - Robust Seeder:**
Updated `database/seeders/UserSeeder.php` to use `firstOrCreate()` instead of `create()`:

```php
// OLD (Error-prone)
$user = User::create(['email' => 'test@example.com', ...]);

// NEW (Safe)
$user = User::firstOrCreate(
    ['email' => 'test@example.com'],
    [...other data...]
);
```

**Benefits:**
- ✅ Idempotent seeding (can run multiple times)
- ✅ No duplicate constraint violations
- ✅ Updates existing records if needed

---

### 3. Navigation Route Errors

**Error Messages:**
```
Route [superadmin.users.index] not defined
Route [users.index] not defined  
Route [teacher.classes.index] not defined
Route [finance.transactions] not defined
```

**Root Cause:**
- Navigation links in Blade templates using incorrect route names
- Route names in views don't match actual routes defined in `routes/web.php`

**Solution:**
Updated navigation routes in views to match actual defined routes:

**Files Modified:**
- `resources/views/superadmin/dashboard.blade.php`
- `resources/views/layouts/app.blade.php`

**Correct Route Names:**
```php
// Admin Routes ✅
route('admin.users.index')
route('admin.courses.index') 
route('admin.classes.index')
route('admin.enrollments.index')

// Teacher Routes ✅
route('teacher.classes')           // NOT teacher.classes.index
route('teacher.reports.index')

// Student Routes ✅  
route('student.classes')           // NOT student.classes.index
route('student.schedules.index')   // NOT student.schedule.index
route('student.reports')           // NOT student.reports.index

// Finance Routes ✅
route('finance.transactions.list')  // NOT finance.transactions or finance.transactions.index

// Parent Routes (Temporary) ✅
route('dashboard')                 // Redirected to dashboard
```

**Commands to Apply Fixes:**
```bash
php artisan view:clear
php artisan route:clear
```

---

### 4. Landing Page Not Fullscreen

**Issue:**
Landing page sections not taking full viewport height

**Solution:**
Added CSS classes and styling in `resources/views/landing.blade.php`:

```css
.section {
    height: 100vh;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}
```

**Features Added:**
- ✅ 5 fullscreen sections (100vh each)
- ✅ Smooth scrolling navigation
- ✅ Interactive scroll indicators
- ✅ Floating navigation bar
- ✅ Glass morphism effects

---

## Quick Fixes

### Clear All Caches
```bash
php artisan route:clear
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Reset Database
```bash
php artisan db:wipe
php artisan migrate --seed
```

### Rebuild Assets
```bash
npm run build
# or for development
npm run dev
```

### Check Routes
```bash
php artisan route:list
php artisan route:list --name=dashboard
```

### Verify Users
```bash
php artisan tinker --execute="echo App\Models\User::count();"
```

---

## Demo Accounts

All accounts use password: `password`

### Admin Access
- superadmin@codingfirst.id (Super Admin)
- admin@codingfirst.id (Admin)  
- finance@codingfirst.id (Finance)

### Teaching Staff
- teacher@codingfirst.id (Hiroshi Tanaka)
- yuki.yamamoto@codingfirst.id (Yuki Yamamoto)
- kenji.nakamura@codingfirst.id (Kenji Nakamura)

### Family Accounts
- parent@codingfirst.id (Budi Santoso - 2 children)
- siti.nurhaliza@codingfirst.id (Siti Nurhaliza - 2 children)
- ahmad.wijaya@codingfirst.id (Ahmad Wijaya - 1 child)

### Student Accounts  
- student@codingfirst.id (Andi Pratama)
- rina.maharani@codingfirst.id (Rina Maharani)
- dimas.prakoso@codingfirst.id (Dimas Prakoso)
- sari.dewi@codingfirst.id (Sari Dewi)
- fajar.ramadhan@codingfirst.id (Fajar Ramadhan)

---

## Development Notes

### System Status
- ✅ Database: Clean and seeded
- ✅ Routes: All working properly
- ✅ Authentication: Role-based access
- ✅ Landing Page: Fullscreen slides
- ✅ Dashboard: Role-specific views

### Access Points
- **Landing**: http://127.0.0.1:8000
- **Login**: http://127.0.0.1:8000/login
- **Dashboard**: http://127.0.0.1:8000/dashboard (auto-redirect based on role)

### Tech Stack
- Laravel 12 with Breeze
- Tailwind CSS + Custom CSS
- MySQL/SQLite database
- Spatie Laravel Permission
- Vite for asset bundling 