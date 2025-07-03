# Struktur Schedule Mingguan

## Overview
Schedule dalam sistem ini dirancang sebagai **schedule mingguan** yang berulang, bukan schedule berdasarkan tanggal spesifik. Ini memungkinkan admin untuk membuat template jadwal yang akan otomatis diterapkan ke calendar dashboard guru dan murid.

## Struktur Tabel Schedules

### Kolom-kolom Utama:

1. **`class_id`** - Foreign key ke tabel `class_names`
2. **`day_of_week`** - Hari dalam seminggu (1=Senin, 2=Selasa, ..., 7=Minggu)
3. **`start_time`** - Jam mulai (format: HH:MM)
4. **`end_time`** - Jam selesai (format: HH:MM)
5. **`room`** - Ruangan/lokasi kelas (nullable)
6. **`enrollment_id`** - Foreign key ke tabel `enrollments` (nullable)
7. **`teacher_assignment_id`** - Foreign key ke tabel `teacher_assignments` (nullable)
8. **`is_active`** - Status aktif schedule (boolean)

## Cara Kerja Schedule Mingguan

### 1. Admin Menambah Schedule
Ketika admin menambah schedule, mereka mengisi:
- **Hari**: Pilih hari dalam seminggu (Senin-Minggu)
- **Jam**: Tentukan jam mulai dan selesai
- **Kelas**: Pilih kelas yang akan dijadwalkan
- **Course**: Otomatis dari kelas yang dipilih
- **Jenis Kelas**: Online/Offline (dari delivery_method di class_names)
- **Guru**: Dari teacher_assignment
- **Murid**: Dari enrollment
- **Ruangan**: Opsional, bisa diisi jika offline

### 2. Penerapan ke Calendar
Schedule mingguan ini akan otomatis diterapkan ke:
- **Calendar Dashboard Guru**: Menampilkan jadwal mengajar berdasarkan hari dan jam
- **Calendar Dashboard Murid**: Menampilkan jadwal belajar berdasarkan enrollment
- **Calendar Admin**: Menampilkan semua schedule untuk monitoring

### 3. Contoh Data Schedule

```php
// Contoh schedule untuk kelas "Web Development Basics"
[
    'class_id' => 1,
    'day_of_week' => 1, // Senin
    'start_time' => '08:00',
    'end_time' => '10:00',
    'room' => 'Room A',
    'enrollment_id' => 5,
    'teacher_assignment_id' => 3,
    'is_active' => true
],
[
    'class_id' => 1,
    'day_of_week' => 3, // Rabu
    'start_time' => '13:00',
    'end_time' => '15:00',
    'room' => 'Online',
    'enrollment_id' => 5,
    'teacher_assignment_id' => 3,
    'is_active' => true
]
```

## Model Relationships

### Schedule Model memiliki relasi:
- `className()` - belongsTo ClassName
- `enrollment()` - belongsTo Enrollment
- `teacherAssignment()` - belongsTo TeacherAssignment

### Scope dan Helper Methods:
- `scopeActive()` - Filter schedule yang aktif
- `getDayNameAttribute()` - Mendapatkan nama hari dalam bahasa Indonesia

## Factory dan Seeder

### ScheduleFactory
Membuat schedule dengan:
- Hari acak (1-7)
- Jam mulai dan selesai acak
- Ruangan acak (Room A, Room B, Online, Lab 1, Lab 2)
- Status aktif default true

### ScheduleSeeder
Membuat schedule realistis dengan:
- 2-3 pertemuan per minggu untuk setiap kelas
- Slot waktu yang umum (08:00-10:00, 10:00-12:00, dst.)
- Distribusi hari Senin-Jumat
- Ruangan yang bervariasi

## Keuntungan Struktur Ini

1. **Fleksibilitas**: Admin bisa membuat template jadwal yang berulang
2. **Efisiensi**: Tidak perlu input jadwal setiap minggu
3. **Konsistensi**: Jadwal yang sama akan muncul setiap minggu
4. **Mudah Dikelola**: Perubahan jadwal cukup dilakukan sekali
5. **Otomatis**: Calendar akan otomatis menampilkan jadwal berdasarkan hari ini

## Implementasi di Calendar

Untuk menampilkan di calendar, sistem akan:
1. Ambil schedule berdasarkan `day_of_week`
2. Generate tanggal untuk minggu/bulan yang ditampilkan
3. Kombinasikan dengan `start_time` dan `end_time`
4. Tampilkan di calendar dengan informasi lengkap kelas, guru, dan murid