<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Badge;
use App\Models\Reward;
use Illuminate\Support\Str;

class GamificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create badges
        $badges = [
            // Academic badges
            [
                'name' => 'First Assignment',
                'description' => 'Complete your first assignment',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 10,
                'icon' => 'document-text',
                'image_path' => 'images/badges/first-assignment.svg',
                'criteria' => ['assignments_completed' => 1],
            ],
            [
                'name' => 'Quiz Master',
                'description' => 'Master quizzes with high scores',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'brain',
                'image_path' => 'images/badges/quiz-master.svg',
                'criteria' => ['quiz_scores_90_plus' => 5],
            ],
            [
                'name' => 'Perfect Score',
                'description' => 'Achieve perfect scores (100%)',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 100,
                'icon' => 'star',
                'image_path' => 'images/badges/perfect-score.svg',
                'criteria' => ['perfect_scores' => 3],
            ],
            [
                'name' => 'Study Streak',
                'description' => 'Maintain a 7-day study streak',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 75,
                'icon' => 'fire',
                'image_path' => 'images/badges/study-streak.svg',
                'criteria' => ['study_streak' => 7],
            ],
            [
                'name' => 'Reading Champion',
                'description' => 'Complete reading many materials',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 60,
                'icon' => 'book-open',
                'image_path' => 'images/badges/reading-champion.svg',
                'criteria' => ['materials_read' => 20],
            ],
            
            // Social badges
            [
                'name' => 'Team Player',
                'description' => 'Show excellent teamwork',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 30,
                'icon' => 'users',
                'image_path' => 'images/badges/team-player.svg',
                'criteria' => ['team_activities' => 5],
            ],
            [
                'name' => 'Helpful Hand',
                'description' => 'Help fellow students',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'hand-heart',
                'image_path' => 'images/badges/helpful-hand.svg',
                'criteria' => ['help_given' => 10],
            ],
            [
                'name' => 'Mentor',
                'description' => 'Guide and mentor junior students',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_PLATINUM,
                'points_required' => 200,
                'icon' => 'user-graduate',
                'image_path' => 'images/badges/mentor.svg',
                'criteria' => ['mentorship_hours' => 20],
            ],
            
            // Attendance badges
            [
                'name' => 'Perfect Attendance',
                'description' => 'Maintain perfect attendance record',
                'category' => Badge::CATEGORY_ATTENDANCE,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 100,
                'icon' => 'calendar-check',
                'image_path' => 'images/badges/perfect-attendance.svg',
                'criteria' => ['perfect_attendance_months' => 3],
            ],
            [
                'name' => 'Early Bird',
                'description' => 'Consistently arrive early to class',
                'category' => Badge::CATEGORY_ATTENDANCE,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 25,
                'icon' => 'sunrise',
                'image_path' => 'images/badges/early-bird.svg',
                'criteria' => ['early_arrivals' => 10],
            ],
            
            // Special badges
            [
                'name' => 'First Login',
                'description' => 'Welcome! First time logging in',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 5,
                'icon' => 'login',
                'image_path' => 'images/badges/first-login.svg',
                'criteria' => ['first_login' => true],
            ],
            [
                'name' => 'Legend',
                'description' => 'Achieve legendary status',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_PLATINUM,
                'points_required' => 500,
                'icon' => 'crown',
                'image_path' => 'images/badges/legend.svg',
                'criteria' => ['total_points' => 1000],
            ],
            [
                'name' => 'Milestone',
                'description' => 'Reach important milestones',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 150,
                'icon' => 'trophy',
                'image_path' => 'images/badges/milestone.svg',
                'criteria' => ['milestones_reached' => 5],
            ],
            [
                'name' => 'Creative Genius',
                'description' => 'Show exceptional creativity',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 200,
                'icon' => 'palette',
                'image_path' => 'images/badges/creative-genius.svg',
                'criteria' => ['creative_projects' => 3],
            ],
        ];
        
        foreach ($badges as $index => $badgeData) {
            $badgeData['slug'] = Str::slug($badgeData['name']);
            $badgeData['is_active'] = true;
            $badgeData['sort_order'] = $index;
            Badge::create($badgeData);
        }
        
        // Create rewards
        $rewards = [
            // Digital rewards
            [
                'name' => 'Extra Assignment Time',
                'description' => 'Get 24 hours extra time for your next assignment',
                'type' => Reward::TYPE_PRIVILEGE,
                'points_cost' => 100,
                'quantity_available' => null, // Unlimited
                'metadata' => ['hours' => 24],
            ],
            [
                'name' => 'Skip One Quiz',
                'description' => 'Skip one quiz without penalty (lowest grade quiz)',
                'type' => Reward::TYPE_PRIVILEGE,
                'points_cost' => 300,
                'quantity_available' => null,
                'metadata' => ['quiz_count' => 1],
            ],
            [
                'name' => 'Certificate Customization',
                'description' => 'Add custom design to your next certificate',
                'type' => Reward::TYPE_DIGITAL,
                'points_cost' => 150,
                'quantity_available' => null,
                'metadata' => ['customization' => true],
            ],
            
            // Physical rewards
            [
                'name' => 'School T-Shirt',
                'description' => 'Official school branded t-shirt',
                'type' => Reward::TYPE_PHYSICAL,
                'points_cost' => 500,
                'quantity_available' => 50,
                'metadata' => ['sizes' => ['S', 'M', 'L', 'XL']],
            ],
            [
                'name' => 'Notebook Set',
                'description' => 'Set of 3 premium notebooks with school logo',
                'type' => Reward::TYPE_PHYSICAL,
                'points_cost' => 200,
                'quantity_available' => 100,
                'metadata' => [],
            ],
            [
                'name' => 'School Mug',
                'description' => 'Ceramic mug with school branding',
                'type' => Reward::TYPE_PHYSICAL,
                'points_cost' => 250,
                'quantity_available' => 75,
                'metadata' => [],
            ],
            
            // Discount rewards
            [
                'name' => '10% Course Discount',
                'description' => 'Get 10% off your next course enrollment',
                'type' => Reward::TYPE_DISCOUNT,
                'points_cost' => 400,
                'quantity_available' => null,
                'metadata' => ['discount_percentage' => 10],
            ],
            [
                'name' => '25% Course Discount',
                'description' => 'Get 25% off your next course enrollment',
                'type' => Reward::TYPE_DISCOUNT,
                'points_cost' => 800,
                'quantity_available' => 20,
                'metadata' => ['discount_percentage' => 25],
            ],
            
            // Privilege rewards
            [
                'name' => 'Priority Support',
                'description' => 'Get priority support for 1 month',
                'type' => Reward::TYPE_PRIVILEGE,
                'points_cost' => 350,
                'quantity_available' => null,
                'metadata' => ['duration_days' => 30],
            ],
            [
                'name' => 'Early Course Access',
                'description' => 'Get early access to new courses for 3 months',
                'type' => Reward::TYPE_PRIVILEGE,
                'points_cost' => 600,
                'quantity_available' => null,
                'metadata' => ['duration_days' => 90],
            ],
        ];
        
        foreach ($rewards as $rewardData) {
            $rewardData['quantity_redeemed'] = 0;
            $rewardData['is_active'] = true;
            Reward::create($rewardData);
        }
    }
}