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
                'name' => 'First Steps',
                'description' => 'Complete your first assignment',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 10,
                'icon' => 'academic-cap',
                'criteria' => ['assignments_completed' => 1],
            ],
            [
                'name' => 'Assignment Starter',
                'description' => 'Complete 5 assignments',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 20,
                'icon' => 'clipboard-check',
                'criteria' => ['assignments_completed' => 5],
            ],
            [
                'name' => 'Assignment Achiever',
                'description' => 'Complete 25 assignments',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'clipboard-list',
                'criteria' => ['assignments_completed' => 25],
            ],
            [
                'name' => 'Assignment Master',
                'description' => 'Complete 100 assignments',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 100,
                'icon' => 'star',
                'criteria' => ['assignments_completed' => 100],
            ],
            [
                'name' => 'Perfectionist',
                'description' => 'Achieve perfect scores on 5 quizzes',
                'category' => Badge::CATEGORY_ACADEMIC,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 75,
                'icon' => 'check-circle',
                'criteria' => ['perfect_quizzes' => 5],
            ],
            
            // Attendance badges
            [
                'name' => 'Regular Attendee',
                'description' => 'Attend 10 classes',
                'category' => Badge::CATEGORY_ATTENDANCE,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 25,
                'icon' => 'calendar',
                'criteria' => ['classes_attended' => 10],
            ],
            [
                'name' => 'Punctual Student',
                'description' => 'Attend 50 classes',
                'category' => Badge::CATEGORY_ATTENDANCE,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'clock',
                'criteria' => ['classes_attended' => 50],
            ],
            [
                'name' => 'Perfect Attendance',
                'description' => 'Attend 100 classes',
                'category' => Badge::CATEGORY_ATTENDANCE,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 100,
                'icon' => 'shield-check',
                'criteria' => ['classes_attended' => 100],
            ],
            
            // Social badges
            [
                'name' => 'First Login',
                'description' => 'Login to the platform for the first time',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 5,
                'icon' => 'login',
                'criteria' => ['first_login' => true],
            ],
            [
                'name' => 'Week Warrior',
                'description' => 'Maintain a 7-day login streak',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_BRONZE,
                'points_required' => 25,
                'icon' => 'fire',
                'criteria' => ['login_streak' => 7],
            ],
            [
                'name' => 'Dedicated Learner',
                'description' => 'Maintain a 30-day login streak',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'trending-up',
                'criteria' => ['login_streak' => 30],
            ],
            [
                'name' => 'Century Streak',
                'description' => 'Maintain a 100-day login streak',
                'category' => Badge::CATEGORY_SOCIAL,
                'level' => Badge::LEVEL_GOLD,
                'points_required' => 100,
                'icon' => 'lightning-bolt',
                'criteria' => ['login_streak' => 100],
            ],
            
            // Special badges
            [
                'name' => 'Rising Star',
                'description' => 'Reach Level 5',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_SILVER,
                'points_required' => 50,
                'icon' => 'sparkles',
                'criteria' => ['level' => 5],
            ],
            [
                'name' => 'Elite Learner',
                'description' => 'Reach Level 10',
                'category' => Badge::CATEGORY_SPECIAL,
                'level' => Badge::LEVEL_PLATINUM,
                'points_required' => 200,
                'icon' => 'crown',
                'criteria' => ['level' => 10],
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