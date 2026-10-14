<?php

namespace Database\Seeders\Data;

/**
 * Static, deterministic description of the demo dataset.
 *
 * People are addressed by a short key (e.g. "raka"), classes by a code
 * (e.g. "SCR-A"). Seeders resolve keys through DemoContext. Dates are relative:
 * a class starts on weekday `day` of the week that is `start_weeks` weeks before
 * the current week (negative = in the future) and has one meeting per week.
 *
 * All names are fictional. The six role demo accounts keep their historical
 *
 * @codingfirst.com e-mails (password "password"); every other demo account uses
 * the reserved .test domain so no real mailbox can receive e-mail.
 */
final class DemoBlueprint
{
    /** Password of every demo account. */
    public const PASSWORD = 'password';

    /** Fixed Faker seed so every run produces the same data. */
    public const FAKER_SEED = 20261013;

    /**
     * Staff accounts (one per staff role).
     *
     * @return array<string, array{name: string, email: string, role: string, gender: string, age: int, bio: string}>
     */
    public static function staff(): array
    {
        return [
            'superadmin' => ['name' => 'Super Admin', 'email' => 'superadmin@codingfirst.com', 'role' => 'superadmin', 'gender' => 'male', 'age' => 38, 'bio' => 'Pengelola sistem Coding First.'],
            'admin' => ['name' => 'Admin Akademik', 'email' => 'admin@codingfirst.com', 'role' => 'admin', 'gender' => 'female', 'age' => 32, 'bio' => 'Mengatur kelas, jadwal dan pendaftaran siswa.'],
            'finance' => ['name' => 'Staf Keuangan', 'email' => 'finance@codingfirst.com', 'role' => 'finance', 'gender' => 'male', 'age' => 35, 'bio' => 'Mengelola tagihan dan pembayaran.'],
        ];
    }

    /**
     * Teachers. The first one is the demo teacher login.
     *
     * @return array<string, array{name: string, email: string, gender: string, age: int, bio: string}>
     */
    public static function teachers(): array
    {
        return [
            'budi' => ['name' => 'Budi Santoso', 'email' => 'teacher@codingfirst.com', 'gender' => 'male', 'age' => 34, 'bio' => 'Mentor Scratch dan Python, 8 tahun mengajar anak-anak. Pernah bekerja sebagai software engineer di Tokyo.'],
            'andi' => ['name' => 'Andi Wijaya', 'email' => 'andi.wijaya@example.test', 'gender' => 'male', 'age' => 29, 'bio' => 'Insinyur elektro yang senang mengajar robotika, Lego Spike dan Arduino.'],
            'sari' => ['name' => 'Sari Kusumawati', 'email' => 'sari.kusumawati@example.test', 'gender' => 'female', 'age' => 27, 'bio' => 'Game designer; mengajar Scratch, Roblox dan Minecraft Education dengan pendekatan proyek.'],
            'rizky' => ['name' => 'Rizky Hidayat', 'email' => 'rizky.hidayat@example.test', 'gender' => 'male', 'age' => 31, 'bio' => 'Backend developer Python & PHP, berpengalaman di startup teknologi Jepang.'],
            'maya' => ['name' => 'Maya Anggraini', 'email' => 'maya.anggraini@example.test', 'gender' => 'female', 'age' => 30, 'bio' => 'Web developer dan praktisi AI generatif; pembicara workshop WordPress dan AI.'],
            'fajar' => ['name' => 'Fajar Nugroho', 'email' => 'fajar.nugroho@example.test', 'gender' => 'male', 'age' => 28, 'bio' => 'Game developer Unity dan Roblox Studio, suka membuat game edukasi.'],
            'intan' => ['name' => 'Intan Permatasari', 'email' => 'intan.permatasari@example.test', 'gender' => 'female', 'age' => 26, 'bio' => 'Pengajar micro:bit dan Arduino untuk ekstrakurikuler sekolah dasar.'],
            'yoga' => ['name' => 'Yoga Pratama', 'email' => 'yoga.pratama@example.test', 'gender' => 'male', 'age' => 33, 'bio' => 'Full-stack Laravel developer; mentor kelas dewasa dan bootcamp malam.'],
        ];
    }

    /**
     * Parents and their children. The first family is the demo parent/student pair.
     *
     * @return array<string, array{name: string, email: string, gender: string, age: int, children: array<string, array{name: string, email: string, gender: string, age: int}>}>
     */
    public static function families(): array
    {
        return [
            'dewi' => ['name' => 'Dewi Lestari', 'email' => 'parent@codingfirst.com', 'gender' => 'female', 'age' => 39, 'children' => [
                'raka' => ['name' => 'Raka Pratama', 'email' => 'student@codingfirst.com', 'gender' => 'male', 'age' => 11],
                'nadia' => ['name' => 'Nadia Pratama', 'email' => 'nadia.pratama@example.test', 'gender' => 'female', 'age' => 8],
            ]],
            'hendra' => ['name' => 'Hendra Gunawan', 'email' => 'hendra.gunawan@example.test', 'gender' => 'male', 'age' => 44, 'children' => [
                'kevin' => ['name' => 'Kevin Gunawan', 'email' => 'kevin.gunawan@example.test', 'gender' => 'male', 'age' => 13],
                'clarissa' => ['name' => 'Clarissa Gunawan', 'email' => 'clarissa.gunawan@example.test', 'gender' => 'female', 'age' => 10],
            ]],
            'siti' => ['name' => 'Siti Rahmawati', 'email' => 'siti.rahmawati@example.test', 'gender' => 'female', 'age' => 36, 'children' => [
                'aisyah' => ['name' => 'Aisyah Putri', 'email' => 'aisyah.putri@example.test', 'gender' => 'female', 'age' => 9],
            ]],
            'agus' => ['name' => 'Agus Setiawan', 'email' => 'agus.setiawan@example.test', 'gender' => 'male', 'age' => 46, 'children' => [
                'bima' => ['name' => 'Bima Setiawan', 'email' => 'bima.setiawan@example.test', 'gender' => 'male', 'age' => 15],
                'dimas' => ['name' => 'Dimas Setiawan', 'email' => 'dimas.setiawan@example.test', 'gender' => 'male', 'age' => 12],
            ]],
            'ratna' => ['name' => 'Ratna Sari', 'email' => 'ratna.sari@example.test', 'gender' => 'female', 'age' => 41, 'children' => [
                'alya' => ['name' => 'Alya Nabila', 'email' => 'alya.nabila@example.test', 'gender' => 'female', 'age' => 14],
            ]],
            'bambang' => ['name' => 'Bambang Hermawan', 'email' => 'bambang.hermawan@example.test', 'gender' => 'male', 'age' => 48, 'children' => [
                'farel' => ['name' => 'Farel Hermawan', 'email' => 'farel.hermawan@example.test', 'gender' => 'male', 'age' => 16],
            ]],
            'lina' => ['name' => 'Lina Marlina', 'email' => 'lina.marlina@example.test', 'gender' => 'female', 'age' => 37, 'children' => [
                'keisha' => ['name' => 'Keisha Ramadhani', 'email' => 'keisha.ramadhani@example.test', 'gender' => 'female', 'age' => 10],
            ]],
            'joko' => ['name' => 'Joko Susilo', 'email' => 'joko.susilo@example.test', 'gender' => 'male', 'age' => 43, 'children' => [
                'arka' => ['name' => 'Arka Susilo', 'email' => 'arka.susilo@example.test', 'gender' => 'male', 'age' => 12],
            ]],
            'putri' => ['name' => 'Putri Ayu Lestari', 'email' => 'putri.ayu@example.test', 'gender' => 'female', 'age' => 38, 'children' => [
                'naura' => ['name' => 'Naura Azzahra', 'email' => 'naura.azzahra@example.test', 'gender' => 'female', 'age' => 11],
                'zidan' => ['name' => 'Zidan Akbar', 'email' => 'zidan.akbar@example.test', 'gender' => 'male', 'age' => 9],
            ]],
            'rudi' => ['name' => 'Rudi Hartono', 'email' => 'rudi.hartono@example.test', 'gender' => 'male', 'age' => 50, 'children' => [
                'gilang' => ['name' => 'Gilang Ramadhan', 'email' => 'gilang.ramadhan@example.test', 'gender' => 'male', 'age' => 17],
            ]],
            'wulan' => ['name' => 'Wulan Dari', 'email' => 'wulan.dari@example.test', 'gender' => 'female', 'age' => 40, 'children' => [
                'salsa' => ['name' => 'Salsabila Putri', 'email' => 'salsabila.putri@example.test', 'gender' => 'female', 'age' => 13],
            ]],
            'teguh' => ['name' => 'Teguh Prakoso', 'email' => 'teguh.prakoso@example.test', 'gender' => 'male', 'age' => 45, 'children' => [
                'rafi' => ['name' => 'Rafi Prakoso', 'email' => 'rafi.prakoso@example.test', 'gender' => 'male', 'age' => 14],
            ]],
        ];
    }

    /**
     * Adult students (18+), who pay for themselves and have no parent account.
     *
     * @return array<string, array{name: string, email: string, gender: string, age: int}>
     */
    public static function adults(): array
    {
        return [
            'dian' => ['name' => 'Dian Purnama', 'email' => 'dian.purnama@example.test', 'gender' => 'female', 'age' => 24],
            'eko' => ['name' => 'Eko Prasetyo', 'email' => 'eko.prasetyo@example.test', 'gender' => 'male', 'age' => 31],
            'fitri' => ['name' => 'Fitri Handayani', 'email' => 'fitri.handayani@example.test', 'gender' => 'female', 'age' => 27],
            'galang' => ['name' => 'Galang Saputra', 'email' => 'galang.saputra@example.test', 'gender' => 'male', 'age' => 19],
            'hana' => ['name' => 'Hana Pertiwi', 'email' => 'hana.pertiwi@example.test', 'gender' => 'female', 'age' => 22],
            'irfan' => ['name' => 'Irfan Maulana', 'email' => 'irfan.maulana@example.test', 'gender' => 'male', 'age' => 35],
            'jessica' => ['name' => 'Jessica Tanoto', 'email' => 'jessica.tanoto@example.test', 'gender' => 'female', 'age' => 29],
            'kurniawan' => ['name' => 'Kurniawan Adi', 'email' => 'kurniawan.adi@example.test', 'gender' => 'male', 'age' => 41],
        ];
    }

    /**
     * Classes. status must agree with the dates produced by start_weeks/meetings.
     * `free` classes produce no invoice.
     *
     * @return array<string, array<string, mixed>>
     */
    public static function classes(): array
    {
        return [
            'SCR-A' => ['name' => 'Scratch Kids A (Sabtu Pagi)', 'course' => 'Scratch', 'type' => 'group_class_3_5_kids', 'delivery' => 'offline', 'status' => 'active', 'day' => 6, 'start' => '09:00', 'end' => '10:30', 'room' => 'Ruang Kelas A', 'start_weeks' => 5, 'meetings' => 8, 'max' => 5, 'teachers' => ['budi', 'sari']],
            'SCR-B' => ['name' => 'Scratch Kids B (Online)', 'course' => 'Scratch', 'type' => 'group_class_3_5_kids', 'delivery' => 'online', 'status' => 'active', 'day' => 3, 'start' => '16:00', 'end' => '17:30', 'room' => 'Online (Zoom)', 'start_weeks' => 3, 'meetings' => 8, 'max' => 5, 'teachers' => ['sari']],
            'PY-TEEN' => ['name' => 'Python Remaja', 'course' => 'Python', 'type' => 'group_class_3_5_kids', 'delivery' => 'online', 'status' => 'active', 'day' => 2, 'start' => '19:00', 'end' => '20:30', 'room' => 'Online (Google Meet)', 'start_weeks' => 4, 'meetings' => 8, 'max' => 5, 'teachers' => ['budi', 'rizky']],
            'PY-PRIV' => ['name' => 'Python Privat Dewasa', 'course' => 'Python', 'type' => 'private_online_1on1', 'delivery' => 'online', 'status' => 'active', 'day' => 4, 'start' => '20:00', 'end' => '21:30', 'room' => 'Online (Zoom)', 'start_weeks' => 6, 'meetings' => 8, 'max' => 1, 'teachers' => ['rizky']],
            'LEGO-A' => ['name' => 'Lego Spike Robotik A', 'course' => 'Lego Spike (Robotik)', 'type' => 'group_class_3_5_kids', 'delivery' => 'offline', 'status' => 'active', 'day' => 6, 'start' => '13:00', 'end' => '14:30', 'room' => 'Lab Robotik', 'start_weeks' => 6, 'meetings' => 8, 'max' => 5, 'teachers' => ['andi', 'intan']],
            'MB-SCH' => ['name' => 'Ekskul Microbit SD Tunas Bangsa', 'course' => 'Microbit', 'type' => 'public_school_extracurricular', 'delivery' => 'offline', 'status' => 'active', 'day' => 5, 'start' => '14:00', 'end' => '15:30', 'room' => 'SD Tunas Bangsa', 'start_weeks' => 2, 'meetings' => 8, 'max' => 15, 'teachers' => ['intan']],
            'ARD-PRIV' => ['name' => 'Arduino Privat (Kunjungan Rumah)', 'course' => 'Arduino', 'type' => 'private_home_call', 'delivery' => 'offline', 'status' => 'active', 'day' => 7, 'start' => '10:00', 'end' => '11:30', 'room' => 'Kunjungan rumah', 'start_weeks' => 3, 'meetings' => 8, 'max' => 1, 'teachers' => ['andi']],
            'LAR-NIGHT' => ['name' => 'Laravel Kelas Malam (Online)', 'course' => 'Laravel', 'type' => 'online_webinar', 'delivery' => 'online', 'status' => 'active', 'day' => 1, 'start' => '19:30', 'end' => '21:00', 'room' => 'Online (Zoom)', 'start_weeks' => 5, 'meetings' => 8, 'max' => 10, 'teachers' => ['yoga', 'rizky']],
            'MC-EXP' => ['name' => 'Minecraft EDU Explorer', 'course' => 'Minecraft EDU', 'type' => 'group_class_3_5_kids', 'delivery' => 'online', 'status' => 'active', 'day' => 7, 'start' => '09:00', 'end' => '10:30', 'room' => 'Online (Minecraft Education)', 'start_weeks' => 4, 'meetings' => 8, 'max' => 5, 'teachers' => ['budi']],
            'RBX-GM' => ['name' => 'Roblox Game Maker', 'course' => 'Roblox', 'type' => 'group_class_3_5_kids', 'delivery' => 'offline', 'status' => 'active', 'day' => 6, 'start' => '15:00', 'end' => '16:30', 'room' => 'Ruang Kelas B', 'start_weeks' => 3, 'meetings' => 8, 'max' => 5, 'teachers' => ['fajar', 'sari']],
            'JS-BASIC' => ['name' => 'Javascript Dasar (Online)', 'course' => 'Javascript', 'type' => 'online_webinar', 'delivery' => 'online', 'status' => 'planned', 'day' => 3, 'start' => '19:30', 'end' => '21:00', 'room' => 'Online (Zoom)', 'start_weeks' => -1, 'meetings' => 8, 'max' => 10, 'teachers' => ['maya']],
            'UNITY-PRIV' => ['name' => 'Unity 2D Privat (Online)', 'course' => 'Unity 2D', 'type' => 'private_online_1on1', 'delivery' => 'online', 'status' => 'planned', 'day' => 2, 'start' => '16:00', 'end' => '17:30', 'room' => 'Online (Zoom)', 'start_weeks' => -2, 'meetings' => 8, 'max' => 1, 'teachers' => ['fajar']],
            'SCR-JUL' => ['name' => 'Scratch Kids Batch Juli', 'course' => 'Scratch', 'type' => 'group_class_3_5_kids', 'delivery' => 'offline', 'status' => 'completed', 'day' => 6, 'start' => '09:00', 'end' => '10:30', 'room' => 'Ruang Kelas A', 'start_weeks' => 16, 'meetings' => 8, 'max' => 5, 'teachers' => ['budi']],
            'LEGO-HOL' => ['name' => 'Lego Spike Liburan Sekolah', 'course' => 'Lego Spike (Robotik)', 'type' => 'group_class_3_5_kids', 'delivery' => 'offline', 'status' => 'completed', 'day' => 3, 'start' => '10:00', 'end' => '11:30', 'room' => 'Lab Robotik', 'start_weeks' => 14, 'meetings' => 8, 'max' => 5, 'teachers' => ['andi']],
            'AI-WS' => ['name' => 'Workshop AI Generatif', 'course' => 'AI Generatif (1 Pertemuan)', 'type' => 'offline_seminar', 'delivery' => 'offline', 'status' => 'completed', 'day' => 6, 'start' => '09:00', 'end' => '13:00', 'room' => 'Aula Coding First', 'start_weeks' => 5, 'meetings' => 1, 'max' => 20, 'teachers' => ['maya']],
            'WP-WS' => ['name' => 'Workshop WordPress x DIVI x AI', 'course' => 'Wordpress x DIVI x AI (1 Pertemuan)', 'type' => 'online_webinar', 'delivery' => 'online', 'status' => 'planned', 'day' => 6, 'start' => '09:00', 'end' => '12:00', 'room' => 'Online (Zoom)', 'start_weeks' => -2, 'meetings' => 1, 'max' => 20, 'teachers' => ['maya', 'yoga']],
            'PY-MEI' => ['name' => 'Python Dasar Batch Mei', 'course' => 'Python', 'type' => 'online_webinar', 'delivery' => 'online', 'status' => 'completed', 'day' => 4, 'start' => '19:30', 'end' => '21:00', 'room' => 'Online (Zoom)', 'start_weeks' => 20, 'meetings' => 8, 'max' => 10, 'teachers' => ['rizky']],
            'MB-TRIAL' => ['name' => 'Trial Gratis Microbit', 'course' => 'Microbit', 'type' => 'free_trial_30min', 'delivery' => 'online', 'status' => 'planned', 'day' => 5, 'start' => '16:00', 'end' => '16:30', 'room' => 'Online (Zoom)', 'start_weeks' => -1, 'meetings' => 1, 'max' => 3, 'teachers' => ['intan'], 'free' => true],
        ];
    }

    /**
     * Enrollments: student key => [class code => status]. Statuses follow the
     * class: completed classes -> completed; planned classes -> pending or active
     * (registered and paid); one dropped (refunded) and one cancelled registration.
     *
     * @return array<string, array<string, string>>
     */
    public static function enrollments(): array
    {
        return [
            'raka' => ['SCR-A' => 'active', 'PY-TEEN' => 'active', 'LEGO-HOL' => 'completed'],
            'nadia' => ['MC-EXP' => 'active', 'SCR-JUL' => 'completed'],
            'kevin' => ['PY-TEEN' => 'active', 'LEGO-A' => 'active'],
            'clarissa' => ['SCR-A' => 'active', 'RBX-GM' => 'active'],
            'aisyah' => ['SCR-B' => 'active', 'MC-EXP' => 'active', 'MB-TRIAL' => 'pending'],
            'bima' => ['PY-TEEN' => 'active', 'LEGO-HOL' => 'completed'],
            'dimas' => ['LEGO-A' => 'active', 'RBX-GM' => 'active'],
            'alya' => ['MB-SCH' => 'active', 'JS-BASIC' => 'pending'],
            'farel' => ['ARD-PRIV' => 'active', 'UNITY-PRIV' => 'pending'],
            'keisha' => ['SCR-A' => 'active', 'MB-SCH' => 'active'],
            'arka' => ['LEGO-A' => 'active', 'LEGO-HOL' => 'completed'],
            'naura' => ['SCR-B' => 'active', 'MB-SCH' => 'active'],
            'zidan' => ['MC-EXP' => 'active', 'SCR-JUL' => 'completed', 'RBX-GM' => 'dropped'],
            'gilang' => ['PY-TEEN' => 'active', 'AI-WS' => 'completed'],
            'salsa' => ['RBX-GM' => 'active', 'MB-SCH' => 'active', 'LEGO-HOL' => 'completed'],
            'rafi' => ['LEGO-A' => 'active', 'RBX-GM' => 'active'],
            'dian' => ['LAR-NIGHT' => 'active', 'PY-MEI' => 'completed'],
            'eko' => ['PY-PRIV' => 'active', 'AI-WS' => 'completed'],
            'fitri' => ['LAR-NIGHT' => 'active', 'JS-BASIC' => 'pending'],
            'galang' => ['JS-BASIC' => 'pending', 'PY-MEI' => 'completed', 'AI-WS' => 'completed'],
            'hana' => ['WP-WS' => 'pending', 'AI-WS' => 'completed'],
            'irfan' => ['LAR-NIGHT' => 'active', 'WP-WS' => 'active'],
            'jessica' => ['LAR-NIGHT' => 'active', 'PY-MEI' => 'completed', 'JS-BASIC' => 'cancelled'],
            'kurniawan' => ['LAR-NIGHT' => 'active', 'WP-WS' => 'active'],
        ];
    }

    /**
     * Invoices that are NOT simply "paid on time", keyed "student:CLASS".
     * Values: overdue | pending | failed_then_paid | second_installment_pending.
     *
     * @return array<string, string>
     */
    public static function paymentExceptions(): array
    {
        return [
            'clarissa:SCR-A' => 'overdue',
            'rafi:RBX-GM' => 'overdue',
            'fitri:LAR-NIGHT' => 'overdue',
            'raka:PY-TEEN' => 'second_installment_pending',
            'kevin:PY-TEEN' => 'second_installment_pending',
            'dian:LAR-NIGHT' => 'failed_then_paid',
            'naura:MB-SCH' => 'pending',
        ];
    }
}
