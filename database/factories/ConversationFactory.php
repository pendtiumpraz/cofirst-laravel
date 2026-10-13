<?php

namespace Database\Factories;

use App\Models\Conversation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 *
 * Add people with ->hasParticipants() or Conversation::addParticipant().
 */
class ConversationFactory extends Factory
{
    protected $model = Conversation::class;

    /**
     * Define the model's default state (a private conversation).
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => 'private',
            'name' => null,
            'class_id' => null,
            'last_message_at' => null,
            'is_active' => true,
        ];
    }
}
