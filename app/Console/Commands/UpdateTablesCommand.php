<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateTablesCommand extends Command
{
    protected $signature = 'tables:update';
    protected $description = 'Update all tables with data-enhance attribute';

    public function handle()
    {
        $this->info('Updating all table views...');
        
        $viewPaths = [
            // Admin views
            'admin/badges/index.blade.php',
            'admin/badges/show.blade.php',
            'admin/classes/index.blade.php',
            'admin/courses/index.blade.php',
            'admin/curriculums/index.blade.php',
            'admin/curriculums/show.blade.php',
            'admin/dashboard.blade.php',
            'admin/enrollments/index.blade.php',
            'admin/materials/index.blade.php',
            'admin/materials/show.blade.php',
            'admin/permissions/index.blade.php',
            'admin/roles/index.blade.php',
            'admin/schedules/index.blade.php',
            'admin/syllabuses/index.blade.php',
            'admin/syllabuses/show.blade.php',
            'admin/teachers/ranking.blade.php',
            // Teacher views
            'teacher/dashboard.blade.php',
            'teacher/schedules/index.blade.php',
            // Student views
            'student/reports/new.blade.php',
            'student/schedules/new-index.blade.php',
            'student/schedules/new.blade.php',
            // Common views
            'certificates/index.blade.php',
            'class-reports/index.blade.php',
            'finance/dashboard.blade.php',
            'finance/transactions/index.blade.php',
            'gamification/partials/leaderboard-table.blade.php',
            'gamification/point-history.blade.php',
            'gamification/redemptions.blade.php',
        ];
        
        $updatedCount = 0;
        
        foreach ($viewPaths as $path) {
            $fullPath = resource_path('views/' . $path);
            
            if (!File::exists($fullPath)) {
                $this->warn("File not found: $path");
                continue;
            }
            
            $content = File::get($fullPath);
            
            // Check if already updated
            if (str_contains($content, 'data-enhance="true"')) {
                $this->info("Already updated: $path");
                continue;
            }
            
            // Find all tables and add the enhancement attributes
            $pattern = '/<table\s+class="([^"]+)"/';
            $replacement = '<table class="$1" data-enhance="true" data-searchable="true" data-sortable="true" data-show-no="true"';
            
            $newContent = preg_replace($pattern, $replacement, $content);
            
            // Wrap tables in table-wrapper div if not already wrapped
            if (!str_contains($content, 'table-wrapper')) {
                $newContent = preg_replace(
                    '/(<div[^>]*>\s*<table)/s',
                    '<div class="table-wrapper">$1',
                    $newContent
                );
            }
            
            // Remove ID columns from thead if they exist
            $newContent = preg_replace(
                '/<th[^>]*>\s*ID\s*<\/th>/i',
                '',
                $newContent
            );
            
            // Remove ID cells from tbody
            $newContent = preg_replace_callback(
                '/<tbody[^>]*>(.*?)<\/tbody>/s',
                function ($matches) {
                    $tbodyContent = $matches[1];
                    // Remove first td if it contains only numbers (likely ID)
                    $tbodyContent = preg_replace(
                        '/<td[^>]*>\s*{{\s*\$[^}]+->id\s*}}\s*<\/td>/i',
                        '',
                        $tbodyContent
                    );
                    return '<tbody>' . $tbodyContent . '</tbody>';
                },
                $newContent
            );
            
            if ($content !== $newContent) {
                File::put($fullPath, $newContent);
                $this->info("Updated: $path");
                $updatedCount++;
            } else {
                $this->info("No changes needed: $path");
            }
        }
        
        $this->info("Updated $updatedCount files.");
        
        return Command::SUCCESS;
    }
}