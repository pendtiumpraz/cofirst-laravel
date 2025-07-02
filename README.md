<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Coding First - Laravel LMS

Sistem Learning Management System (LMS) untuk Coding First - lembaga pelatihan programming dengan pengajar berpengalaman dari perusahaan Jepang.

## 🚀 Fitur Utama

### 🎨 Landing Page Fullscreen
- **Design Modern**: Fullscreen slide presentation dengan smooth scrolling
- **Interactive Navigation**: Floating navigation dan scroll indicators
- **Smooth Transitions**: Animasi yang halus antar section
- **Responsive Design**: Optimal di semua device
- **Glass Effect**: Modern glassmorphism design elements

### 👥 Role-Based System
- **Super Admin**: Full system access
- **Admin**: System administration
- **Teacher**: Course management dan reporting
- **Finance**: Financial management
- **Parent**: Monitor children progress
- **Student**: Access courses dan materials

### 🗄️ Database Architecture
- 15+ tables dengan relationships yang kompleks
- User profiles dengan data lengkap
- Course management system
- Financial transaction tracking
- Parent-student relationships

## 🔧 Installation

1. Clone repository
2. Install dependencies: `composer install && npm install`
3. Setup environment: `cp .env.example .env`
4. Generate key: `php artisan key:generate`
5. Run migrations: `php artisan migrate:fresh --seed`
6. Build assets: `npm run build`
7. Start server: `php artisan serve`

## 🎯 Demo Accounts

All accounts use password: `password`

### Admin Access
- **Super Admin**: superadmin@codingfirst.id
- **Admin**: admin@codingfirst.id
- **Finance**: finance@codingfirst.id

### Teaching Staff
- **Teacher 1**: teacher@codingfirst.id (Hiroshi Tanaka)
- **Teacher 2**: yuki.yamamoto@codingfirst.id (Yuki Yamamoto)
- **Teacher 3**: kenji.nakamura@codingfirst.id (Kenji Nakamura)

### Family Accounts
- **Parent 1**: parent@codingfirst.id (Budi Santoso) - 2 children
- **Parent 2**: siti.nurhaliza@codingfirst.id (Siti Nurhaliza) - 2 children
- **Parent 3**: ahmad.wijaya@codingfirst.id (Ahmad Wijaya) - 1 child

### Student Accounts
- **Student 1**: student@codingfirst.id (Andi Pratama)
- **Student 2**: rina.maharani@codingfirst.id (Rina Maharani)
- **Student 3**: dimas.prakoso@codingfirst.id (Dimas Prakoso)
- **Student 4**: sari.dewi@codingfirst.id (Sari Dewi)
- **Student 5**: fajar.ramadhan@codingfirst.id (Fajar Ramadhan)

## 🌟 Landing Page Features

### Fullscreen Sections
1. **Hero Section**: Animated background dengan CTA buttons
2. **About Section**: Company features dan benefits
3. **Courses Section**: Featured courses display
4. **Statistics Section**: Company achievements
5. **Contact Section**: Contact information dan CTA

### Interactive Elements
- **Scroll Indicators**: Dots navigation di sisi kanan
- **Floating Navigation**: Transparent navigation bar
- **Smooth Scrolling**: Seamless transitions antar section
- **Hover Effects**: Interactive elements dengan animations
- **Glass Morphism**: Modern translucent design elements

### Technical Features
- **100vh Sections**: Full viewport height untuk setiap section
- **CSS Animations**: Custom keyframe animations
- **Responsive Design**: Mobile-first approach
- **Performance Optimized**: Minimal JavaScript untuk smooth experience

## 🛠️ Tech Stack

- **Backend**: Laravel 12 with Breeze
- **Frontend**: Tailwind CSS + Custom CSS
- **Database**: MySQL/SQLite
- **Authentication**: Laravel Breeze
- **Authorization**: Spatie Laravel Permission
- **Assets**: Vite for bundling

## 📱 Access

- **Landing Page**: http://127.0.0.1:8000
- **Login**: http://127.0.0.1:8000/login
- **Register**: http://127.0.0.1:8000/register
- **Dashboard**: http://127.0.0.1:8000/dashboard (role-based redirect)

## 🎨 Design Highlights

- **Modern Gradient Backgrounds**: Purple, blue, indigo combinations
- **Typography**: Inter font family untuk readability
- **Color Scheme**: Professional dengan accent colors
- **Animations**: Subtle floating dan bounce effects
- **Glass Effects**: Backdrop blur untuk modern look

---

**Coding First** - Empowering the next generation of programmers with world-class education from Japanese industry experts.
