<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Curriculum;
use App\Models\Syllabus;
use App\Models\Material;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class EnhancedCurriculumSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing courses
        $courses = Course::all();
        
        if ($courses->isEmpty()) {
            $this->command->info('No courses found. Please run CourseSeeder first.');
            return;
        }
        
        foreach ($courses as $course) {
            // Create enhanced curriculum for each course
            $curriculum = Curriculum::updateOrCreate(
                ['course_id' => $course->id],
                [
                    'title' => $course->name . ' Curriculum',
                    'description' => 'Complete curriculum for ' . $course->name,
                    'type' => $this->getCurriculumType($course->name),
                    'status' => 'active',
                    'duration_weeks' => $this->getDurationWeeks($course->name),
                    'objectives' => $this->getCurriculumObjectives($course->name)
                ]
            );
            
            $this->command->info("Created/Updated curriculum: {$curriculum->title}");
            
            // Create syllabuses for the curriculum
            $this->createSyllabuses($curriculum, $course);
        }
    }
    
    private function getCurriculumType($courseName)
    {
        if (stripos($courseName, '1 Pertemuan') !== false) {
            return 'fast-track';
        } elseif (in_array($courseName, ['Laravel', 'Python', 'Javascript', 'Unity 2D'])) {
            return 'expert';
        } elseif (in_array($courseName, ['Scratch', 'Lego Spike', 'Minecraft EDU'])) {
            return 'beginner';
        }
        return 'regular';
    }
    
    private function getDurationWeeks($courseName)
    {
        if (stripos($courseName, '1 Pertemuan') !== false) {
            return 1;
        } elseif (in_array($courseName, ['Laravel', 'Python', 'Javascript', 'Unity 2D'])) {
            return 16;
        } elseif (in_array($courseName, ['Arduino', 'Lego Spike (Robotik)', 'Roblox'])) {
            return 12;
        }
        return 8;
    }
    
    private function getCurriculumObjectives($courseName)
    {
        switch ($courseName) {
            case 'Scratch':
                return [
                    'Memahami konsep dasar pemrograman',
                    'Membuat animasi dan game sederhana',
                    'Mengembangkan logical thinking',
                    'Belajar problem solving'
                ];
            case 'Lego Spike (Robotik)':
                return [
                    'Memahami konsep robotika dasar',
                    'Belajar programming dengan Spike Prime',
                    'Membuat robot yang dapat bergerak',
                    'Mengintegrasikan sensor dan actuator'
                ];
            case 'Microbit':
                return [
                    'Memahami konsep mikrokontroler',
                    'Programming dengan MicroPython/JavaScript',
                    'Membuat project IoT sederhana',
                    'Belajar sensor dan LED programming'
                ];
            case 'Arduino':
                return [
                    'Memahami elektronika dasar',
                    'Programming dengan C/C++',
                    'Membuat project IoT dan automation',
                    'Integrasi sensor dan actuator'
                ];
            case 'AI Generatif (1 Pertemuan)':
                return [
                    'Memahami konsep AI Generatif',
                    'Menggunakan ChatGPT dan tools AI',
                    'Membuat content dengan AI',
                    'Memahami etika penggunaan AI'
                ];
            case 'Wordpress x DIVI x AI (1 Pertemuan)':
                return [
                    'Membuat website dengan WordPress',
                    'Menggunakan theme DIVI',
                    'Integrasi AI dalam website',
                    'Optimasi website modern'
                ];
            case 'Python':
                return [
                    'Menguasai syntax Python',
                    'Object-oriented programming',
                    'Data science dan automation',
                    'Web development dengan framework'
                ];
            case 'Laravel':
                return [
                    'Menguasai framework Laravel',
                    'MVC architecture pattern',
                    'Database management dengan Eloquent',
                    'RESTful API development'
                ];
            case 'Javascript':
                return [
                    'Menguasai JavaScript ES6+',
                    'DOM manipulation dan events',
                    'Asynchronous programming',
                    'Modern framework (React/Vue)'
                ];
            case 'Minecraft EDU':
                return [
                    'Computational thinking',
                    'Problem solving dalam game',
                    'Collaboration dan teamwork',
                    'Creative building dan programming'
                ];
            case 'Roblox':
                return [
                    'Game development dengan Roblox Studio',
                    'Lua scripting language',
                    'Game design principles',
                    'Publishing dan monetization'
                ];
            case 'Unity 2D':
                return [
                    'Game development dengan Unity',
                    'C# programming untuk games',
                    'Game physics dan animation',
                    'Game publishing dan distribution'
                ];
            default:
                return [
                    'Memahami konsep fundamental',
                    'Mengembangkan keterampilan praktis',
                    'Membangun project nyata',
                    'Menerapkan best practices'
                ];
        }
    }
    
    private function createSyllabuses($curriculum, $course)
    {
        $syllabusData = $this->getSyllabusData($course->name);
        
        foreach ($syllabusData as $index => $data) {
            $syllabus = Syllabus::updateOrCreate(
                [
                    'curriculum_id' => $curriculum->id,
                    'meeting_number' => $index + 1
                ],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'learning_objectives' => $data['learning_objectives'],
                    'activities' => $data['activities'],
                    'duration_minutes' => $data['duration_minutes'],
                    'status' => 'active'
                ]
            );
            
            $this->command->info("  Created/Updated syllabus: Meeting {$syllabus->meeting_number} - {$syllabus->title}");
            
            // Create materials for each syllabus
            $this->createMaterials($syllabus, $data['materials']);
        }
    }
    
    private function getSyllabusData($courseName)
    {
        switch ($courseName) {
            case 'Scratch':
                return [
                    [
                        'title' => 'Pengenalan Scratch',
                        'description' => 'Mengenal interface Scratch dan konsep dasar programming',
                        'learning_objectives' => [
                            'Memahami interface Scratch',
                            'Mengenal sprite dan stage',
                            'Membuat animasi sederhana'
                        ],
                        'activities' => [
                            'Eksplorasi interface Scratch',
                            'Membuat sprite bergerak',
                            'Menambahkan suara dan efek'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Panduan Interface Scratch', 'type' => 'document'],
                            ['title' => 'Video Tutorial Dasar', 'type' => 'video'],
                            ['title' => 'Latihan Animasi Pertama', 'type' => 'exercise']
                        ]
                    ],
                    [
                        'title' => 'Konsep Programming Dasar',
                        'description' => 'Belajar sequence, loop, dan conditional',
                        'learning_objectives' => [
                            'Memahami sequence dalam programming',
                            'Menggunakan loop (repeat)',
                            'Menerapkan conditional (if-then)'
                        ],
                        'activities' => [
                            'Membuat program dengan sequence',
                            'Menggunakan repeat blocks',
                            'Membuat decision making'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Konsep Programming', 'type' => 'document'],
                            ['title' => 'Latihan Loop dan Conditional', 'type' => 'exercise'],
                            ['title' => 'Quiz Programming Logic', 'type' => 'quiz']
                        ]
                    ],
                    [
                        'title' => 'Membuat Game Sederhana',
                        'description' => 'Project membuat game interaktif',
                        'learning_objectives' => [
                            'Menggabungkan konsep yang dipelajari',
                            'Membuat game dengan scoring',
                            'Menambahkan interaksi user'
                        ],
                        'activities' => [
                            'Design game concept',
                            'Implementasi game mechanics',
                            'Testing dan debugging'
                        ],
                        'duration_minutes' => 150,
                        'materials' => [
                            ['title' => 'Template Game Sederhana', 'type' => 'document'],
                            ['title' => 'Project Game Pertama', 'type' => 'project'],
                            ['title' => 'Panduan Debugging', 'type' => 'document']
                        ]
                    ]
                ];
                
            case 'Python':
                return [
                    [
                        'title' => 'Python Fundamentals',
                        'description' => 'Syntax dasar Python dan data types',
                        'learning_objectives' => [
                            'Memahami syntax Python',
                            'Mengenal data types dan variables',
                            'Menggunakan operators'
                        ],
                        'activities' => [
                            'Setup Python environment',
                            'Latihan syntax dasar',
                            'Membuat program sederhana'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Python Syntax Guide', 'type' => 'document'],
                            ['title' => 'Latihan Variables dan Data Types', 'type' => 'exercise'],
                            ['title' => 'Python Installation Guide', 'type' => 'document']
                        ]
                    ],
                    [
                        'title' => 'Control Flow dan Functions',
                        'description' => 'Conditional statements, loops, dan functions',
                        'learning_objectives' => [
                            'Menggunakan if-elif-else',
                            'Menerapkan for dan while loops',
                            'Membuat dan menggunakan functions'
                        ],
                        'activities' => [
                            'Latihan conditional logic',
                            'Implementasi loops',
                            'Membuat custom functions'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Control Flow Tutorial', 'type' => 'document'],
                            ['title' => 'Functions Best Practices', 'type' => 'document'],
                            ['title' => 'Latihan Logic Programming', 'type' => 'exercise']
                        ]
                    ],
                    [
                        'title' => 'Object-Oriented Programming',
                        'description' => 'Classes, objects, inheritance, dan polymorphism',
                        'learning_objectives' => [
                            'Memahami konsep OOP',
                            'Membuat classes dan objects',
                            'Menerapkan inheritance'
                        ],
                        'activities' => [
                            'Membuat class sederhana',
                            'Implementasi inheritance',
                            'Project OOP'
                        ],
                        'duration_minutes' => 150,
                        'materials' => [
                            ['title' => 'OOP Concepts', 'type' => 'document'],
                            ['title' => 'Class Design Exercise', 'type' => 'exercise'],
                            ['title' => 'OOP Project', 'type' => 'project']
                        ]
                    ]
                ];
                
            case 'Laravel':
                return [
                    [
                        'title' => 'Laravel Introduction & Setup',
                        'description' => 'Pengenalan Laravel framework dan environment setup',
                        'learning_objectives' => [
                            'Memahami MVC architecture',
                            'Setup Laravel development environment',
                            'Mengenal struktur folder Laravel'
                        ],
                        'activities' => [
                            'Install Laravel dan dependencies',
                            'Eksplorasi struktur project',
                            'Membuat route dan view pertama'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Laravel Installation Guide', 'type' => 'document'],
                            ['title' => 'MVC Architecture Explained', 'type' => 'document'],
                            ['title' => 'First Laravel App', 'type' => 'exercise']
                        ]
                    ],
                    [
                        'title' => 'Routing dan Controllers',
                        'description' => 'Mengelola routes dan membuat controllers',
                        'learning_objectives' => [
                            'Membuat dan mengelola routes',
                            'Membuat controllers',
                            'Menerapkan middleware'
                        ],
                        'activities' => [
                            'Membuat berbagai jenis routes',
                            'Implementasi resource controllers',
                            'Setup middleware authentication'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Routing Best Practices', 'type' => 'document'],
                            ['title' => 'Controller Patterns', 'type' => 'document'],
                            ['title' => 'Routing Exercise', 'type' => 'exercise']
                        ]
                    ],
                    [
                        'title' => 'Database dan Eloquent ORM',
                        'description' => 'Database management dengan Eloquent',
                        'learning_objectives' => [
                            'Setup database connections',
                            'Membuat migrations dan models',
                            'Menggunakan Eloquent relationships'
                        ],
                        'activities' => [
                            'Membuat database schema',
                            'Implementasi CRUD operations',
                            'Setup model relationships'
                        ],
                        'duration_minutes' => 150,
                        'materials' => [
                            ['title' => 'Eloquent ORM Guide', 'type' => 'document'],
                            ['title' => 'Database Design Exercise', 'type' => 'exercise'],
                            ['title' => 'CRUD Application Project', 'type' => 'project']
                        ]
                    ]
                ];
                
            case 'AI Generatif (1 Pertemuan)':
                return [
                    [
                        'title' => 'AI Generatif Workshop',
                        'description' => 'Workshop intensif tentang AI Generatif dan aplikasinya',
                        'learning_objectives' => [
                            'Memahami konsep AI Generatif',
                            'Menggunakan ChatGPT dan tools AI',
                            'Membuat content dengan AI',
                            'Memahami etika penggunaan AI'
                        ],
                        'activities' => [
                            'Pengenalan AI Generatif',
                            'Hands-on dengan ChatGPT',
                            'Eksplorasi tools AI lainnya',
                            'Diskusi etika AI'
                        ],
                        'duration_minutes' => 240,
                        'materials' => [
                            ['title' => 'AI Generatif Overview', 'type' => 'document'],
                            ['title' => 'ChatGPT Best Practices', 'type' => 'document'],
                            ['title' => 'AI Tools Comparison', 'type' => 'document'],
                            ['title' => 'AI Ethics Guidelines', 'type' => 'document'],
                            ['title' => 'Hands-on AI Workshop', 'type' => 'exercise']
                        ]
                    ]
                ];
                
            default:
                return [
                    [
                        'title' => 'Course Introduction',
                        'description' => 'Pengenalan course dan fundamental concepts',
                        'learning_objectives' => [
                            'Memahami tujuan course',
                            'Mengenal konsep dasar',
                            'Setup learning environment'
                        ],
                        'activities' => [
                            'Course overview',
                            'Environment setup',
                            'First practical exercise'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Course Introduction', 'type' => 'document'],
                            ['title' => 'Getting Started Guide', 'type' => 'document']
                        ]
                    ],
                    [
                        'title' => 'Fundamentals',
                        'description' => 'Core concepts dan principles',
                        'learning_objectives' => [
                            'Menguasai konsep fundamental',
                            'Menerapkan prinsip dasar',
                            'Membangun foundation knowledge'
                        ],
                        'activities' => [
                            'Concept explanation',
                            'Hands-on practice',
                            'Q&A session'
                        ],
                        'duration_minutes' => 120,
                        'materials' => [
                            ['title' => 'Fundamentals Overview', 'type' => 'document'],
                            ['title' => 'Practice Exercises', 'type' => 'exercise'],
                            ['title' => 'Reference Materials', 'type' => 'document']
                        ]
                    ]
                ];
        }
    }
    
    private function createMaterials($syllabus, $materialsData)
    {
        foreach ($materialsData as $index => $data) {
            $material = Material::updateOrCreate(
                [
                    'syllabus_id' => $syllabus->id,
                    'title' => $data['title']
                ],
                [
                    'content' => $this->generateMaterialContent($data['title'], $data['type']),
                    'description' => 'Material for ' . $data['title'],
                    'meeting_start' => $syllabus->meeting_number,
                    'meeting_end' => $syllabus->meeting_number,
                    'type' => $data['type'],
                    'file_path' => null,
                    'external_url' => $this->getExternalUrl($data['type']),
                    'status' => 'active',
                    'order' => $index + 1
                ]
            );
            
            $this->command->info("    Created/Updated material: {$material->title} ({$material->type})");
        }
    }
    
    private function generateMaterialContent($title, $type)
    {
        switch ($type) {
            case 'document':
                return "# {$title}\n\nDokumen ini membahas {$title} secara detail.\n\n## Overview\nPenjelasan lengkap tentang topik.\n\n## Examples\nContoh praktis dan use cases.";
            case 'video':
                return "Video content: {$title}\n\nVideo ini mendemonstrasikan aplikasi praktis dari {$title}.";
            case 'exercise':
                return "# {$title}\n\nLatihan praktis untuk {$title}.\n\n## Instructions\n1. Ikuti langkah-langkah di bawah\n2. Selesaikan tugas yang diberikan\n3. Submit hasil pekerjaan\n\n## Tasks\n- Task 1: Implementasi dasar\n- Task 2: Fitur lanjutan";
            case 'quiz':
                return "# {$title}\n\nKuis tentang {$title}.\n\n## Questions\n1. Apa konsep utama?\n2. Bagaimana cara implementasi?\n3. Apa best practices yang direkomendasikan?";
            case 'project':
                return "# {$title}\n\nProject assignment untuk {$title}.\n\n## Objectives\n- Membangun solusi lengkap\n- Menerapkan konsep yang dipelajari\n- Mendemonstrasikan pemahaman\n\n## Requirements\n- Feature 1\n- Feature 2\n- Feature 3";
            default:
                return "Content untuk {$title}";
        }
    }
    
    private function getExternalUrl($type)
    {
        switch ($type) {
            case 'video':
                return 'https://www.youtube.com/watch?v=example';
            case 'document':
                return 'https://docs.google.com/document/d/example';
            default:
                return null;
        }
    }
}