<?php

namespace Database\Seeders\Data;

/**
 * Curriculum content per course: meeting topics plus the detailed first meetings
 * that the original EnhancedCurriculumSeeder shipped. Regular courses have 8
 * meetings, the "(1 Pertemuan)" workshops have 1.
 */
final class CurriculumContent
{
    /**
     * Meeting topics per course: [title, focus].
     *
     * @return array<string, array<int, array{0: string, 1: string}>>
     */
    public static function topics(): array
    {
        return [
            'Scratch' => [
                ['Pengenalan Scratch', 'antarmuka Scratch, sprite dan stage'], ['Konsep Programming Dasar', 'urutan, perulangan dan percabangan'],
                ['Membuat Game Sederhana', 'game interaktif dengan skor'], ['Variabel dan Skor', 'variabel untuk menyimpan nilai'],
                ['Animasi dan Cerita Interaktif', 'kostum, suara dan dialog'], ['Kloning dan Musuh', 'blok clone untuk banyak objek'],
                ['Merancang Game Sendiri', 'desain level dan aturan permainan'], ['Proyek Akhir & Presentasi', 'menyelesaikan dan mempresentasikan game'],
            ],
            'Lego Spike (Robotik)' => [
                ['Mengenal Lego Spike Prime', 'hub, motor dan sensor'], ['Gerak Maju dan Belok', 'mengendalikan dua motor'],
                ['Sensor Jarak', 'menghindari rintangan'], ['Sensor Warna', 'mengikuti garis'],
                ['Perulangan dan Kondisi', 'logika robot otomatis'], ['Lengan Robot', 'mekanik gir dan tuas'],
                ['Misi Robot', 'menyelesaikan tantangan arena'], ['Kompetisi Mini & Presentasi', 'robot terbaik tim'],
            ],
            'Microbit' => [
                ['Mengenal micro:bit', 'LED, tombol dan MakeCode'], ['Animasi LED', 'gambar dan teks berjalan'],
                ['Input Tombol dan Gerak', 'akselerometer dan tombol'], ['Sensor Suhu dan Cahaya', 'membaca sensor bawaan'],
                ['Komunikasi Radio', 'mengirim pesan antar micro:bit'], ['Game Sederhana', 'game refleks dengan LED'],
                ['Proyek IoT Mini', 'alat pengukur sederhana'], ['Presentasi Proyek', 'demo dan refleksi'],
            ],
            'Arduino' => [
                ['Elektronika Dasar', 'arus, tegangan dan breadboard'], ['Blink dan Output Digital', 'LED dan pinMode'],
                ['Input Digital', 'tombol dan pull-up'], ['Input Analog', 'potensiometer dan sensor cahaya'],
                ['PWM dan Servo', 'mengatur kecerahan dan sudut'], ['Sensor Ultrasonik', 'mengukur jarak'],
                ['Serial Monitor dan Debugging', 'membaca data sensor'], ['Proyek Smart Device', 'alat otomatis sederhana'],
            ],
            'AI Generatif (1 Pertemuan)' => [
                ['AI Generatif Workshop', 'AI generatif, ChatGPT dan etika penggunaan AI'],
            ],
            'Wordpress x DIVI x AI (1 Pertemuan)' => [
                ['Workshop Website WordPress x DIVI x AI', 'membangun website dengan WordPress, DIVI dan bantuan AI'],
            ],
            'Python' => [
                ['Python Fundamentals', 'sintaks, tipe data dan variabel'], ['Control Flow dan Functions', 'if, perulangan dan fungsi'],
                ['Object-Oriented Programming', 'class, object dan inheritance'], ['List dan Dictionary', 'struktur data bawaan'],
                ['File dan Exception', 'membaca file dan menangani error'], ['Modul dan Library', 'pip dan library populer'],
                ['Mini Project Otomasi', 'skrip otomasi sederhana'], ['Proyek Akhir Python', 'aplikasi konsol lengkap'],
            ],
            'Laravel' => [
                ['Laravel Introduction & Setup', 'arsitektur MVC dan instalasi'], ['Routing dan Controllers', 'route, controller dan middleware'],
                ['Database dan Eloquent ORM', 'migration, model dan relasi'], ['Blade dan Komponen', 'template dan komponen tampilan'],
                ['Validasi dan Form Request', 'validasi input pengguna'], ['Autentikasi', 'login, registrasi dan otorisasi'],
                ['REST API', 'membuat API JSON'], ['Proyek Akhir Laravel', 'aplikasi CRUD lengkap'],
            ],
            'Javascript' => [
                ['Dasar JavaScript', 'variabel, tipe data dan operator'], ['Fungsi dan Scope', 'function, arrow function dan scope'],
                ['Array dan Object', 'method array dan destructuring'], ['DOM Manipulation', 'mengubah halaman web'],
                ['Event dan Interaksi', 'event listener dan form'], ['Asynchronous JavaScript', 'promise, async/await dan fetch'],
                ['Pengenalan Framework', 'komponen dan state'], ['Proyek Akhir Web App', 'aplikasi web interaktif'],
            ],
            'Minecraft EDU' => [
                ['Mengenal Minecraft Education', 'dunia, blok dan Code Builder'], ['Agent dan Perintah Dasar', 'menggerakkan agent'],
                ['Perulangan Membangun', 'membangun dengan loop'], ['Kondisi dan Deteksi', 'agent mendeteksi blok'],
                ['Fungsi dan Event', 'chat command dan event'], ['Membangun Kota', 'kolaborasi membangun'],
                ['Misi Tim', 'menyelesaikan tantangan bersama'], ['Pameran Dunia Minecraft', 'presentasi karya'],
            ],
            'Roblox' => [
                ['Mengenal Roblox Studio', 'antarmuka dan part'], ['Dasar Lua Scripting', 'variabel dan fungsi Lua'],
                ['Event dan Interaksi', 'Touched dan ClickDetector'], ['Membuat Obby', 'rintangan dan checkpoint'],
                ['GUI dan Skor', 'leaderstats dan antarmuka'], ['Efek dan Suara', 'partikel, animasi dan audio'],
                ['Uji Coba dan Perbaikan', 'debugging dan playtest'], ['Publikasi Game', 'mempublikasikan game di Roblox'],
            ],
            'Unity 2D' => [
                ['Mengenal Unity 2D', 'editor, scene dan GameObject'], ['Dasar C#', 'variabel, fungsi dan class'],
                ['Gerak Karakter', 'Rigidbody2D dan input'], ['Collision dan Trigger', 'interaksi objek'],
                ['Animasi Sprite', 'animator dan transisi'], ['UI dan Skor', 'canvas dan teks skor'],
                ['Level dan Musuh', 'desain level dan AI sederhana'], ['Build dan Publikasi', 'build game untuk dibagikan'],
            ],
        ];
    }

    /**
     * Course objectives (curriculum level), one item per line.
     *
     * @return array<string, array<int, string>>
     */
    public static function objectives(): array
    {
        return [
            'Scratch' => ['Memahami konsep dasar pemrograman', 'Membuat animasi dan game sederhana', 'Mengembangkan logical thinking', 'Belajar problem solving'],
            'Lego Spike (Robotik)' => ['Memahami konsep robotika dasar', 'Belajar programming dengan Spike Prime', 'Membuat robot yang dapat bergerak', 'Mengintegrasikan sensor dan aktuator'],
            'Microbit' => ['Memahami konsep mikrokontroler', 'Programming dengan MakeCode/MicroPython', 'Membuat proyek IoT sederhana', 'Belajar sensor dan LED programming'],
            'Arduino' => ['Memahami elektronika dasar', 'Programming dengan C/C++', 'Membuat proyek IoT dan otomasi', 'Integrasi sensor dan aktuator'],
            'AI Generatif (1 Pertemuan)' => ['Memahami konsep AI Generatif', 'Menggunakan ChatGPT dan tools AI', 'Membuat konten dengan AI', 'Memahami etika penggunaan AI'],
            'Wordpress x DIVI x AI (1 Pertemuan)' => ['Membuat website dengan WordPress', 'Menggunakan theme DIVI', 'Integrasi AI dalam website', 'Optimasi website modern'],
            'Python' => ['Menguasai sintaks Python', 'Object-oriented programming', 'Data dan otomasi', 'Membangun proyek nyata'],
            'Laravel' => ['Menguasai framework Laravel', 'Pola arsitektur MVC', 'Manajemen database dengan Eloquent', 'Pengembangan RESTful API'],
            'Javascript' => ['Menguasai JavaScript ES6+', 'DOM manipulation dan events', 'Asynchronous programming', 'Dasar framework modern'],
            'Minecraft EDU' => ['Computational thinking', 'Problem solving dalam game', 'Kolaborasi dan teamwork', 'Kreativitas membangun dan programming'],
            'Roblox' => ['Game development dengan Roblox Studio', 'Bahasa scripting Lua', 'Prinsip desain game', 'Publikasi game'],
            'Unity 2D' => ['Game development dengan Unity', 'Pemrograman C# untuk game', 'Fisika dan animasi game', 'Publikasi dan distribusi game'],
        ];
    }

    /**
     * Full data for one meeting.
     *
     * Args:
     *     course (string): Course name.
     *     number (int): Meeting number (1-based).
     *     total (int): Number of meetings of the course.
     *
     * Returns:
     *     array{title: string, description: string, learning_objectives: array, activities: array, duration_minutes: int, materials: array}
     */
    public static function meeting(string $course, int $number, int $total): array
    {
        [$title, $focus] = self::topics()[$course][$number - 1] ?? ['Pertemuan '.$number, 'materi lanjutan'];
        $workshop = $total === 1;

        $materials = [
            ['title' => 'Modul: '.$title, 'type' => 'document'],
            ['title' => 'Latihan: '.$title, 'type' => 'exercise'],
        ];

        if ($number === 1 && ! $workshop) {
            $materials[] = ['title' => 'Video Pengantar: '.$title, 'type' => 'video'];
        }

        if ($number === intdiv($total, 2) && $total > 2) {
            $materials[] = ['title' => 'Kuis Tengah Kursus', 'type' => 'quiz'];
        }

        if ($number === $total) {
            $materials[] = ['title' => $workshop ? 'Proyek Workshop' : 'Proyek Akhir', 'type' => 'project'];
        }

        return [
            'title' => $title,
            'description' => ucfirst($focus).'.',
            'learning_objectives' => ['Memahami '.$focus, 'Menerapkan '.mb_strtolower($title).' dalam latihan', 'Menjelaskan hasil karya kepada teman dan mentor'],
            'activities' => ['Review singkat pertemuan sebelumnya', 'Demo dan praktik: '.$title, 'Tantangan mini dan refleksi'],
            'duration_minutes' => $workshop ? 240 : 90,
            'materials' => $materials,
        ];
    }

    /**
     * Markdown body for a material.
     */
    public static function materialContent(string $title, string $type): string
    {
        return match ($type) {
            'document' => "# {$title}\n\nRingkasan materi pertemuan ini.\n\n## Konsep utama\nPenjelasan langkah demi langkah dengan contoh.\n\n## Contoh\nContoh praktis yang dikerjakan bersama mentor.",
            'video' => "Video demonstrasi: {$title}.\n\nTonton sebelum kelas, lalu catat pertanyaanmu.",
            'exercise' => "# {$title}\n\n## Instruksi\n1. Ikuti langkah di modul\n2. Selesaikan tantangan\n3. Simpan dan tunjukkan hasilnya ke mentor",
            'quiz' => "# {$title}\n\n1. Apa konsep utama yang sudah dipelajari?\n2. Bagaimana cara menerapkannya?\n3. Apa kesalahan yang sering terjadi?",
            'project' => "# {$title}\n\n## Tujuan\n- Membangun karya lengkap\n- Menerapkan semua konsep\n- Mempresentasikan hasil",
            default => "Materi {$title}",
        };
    }
}
