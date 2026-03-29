<?php

namespace Database\Factories;

use App\Models\Email;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmailFactory extends Factory
{
    protected $model = Email::class;

    public function definition(): array
    {
        return [
            'direction' => fake()->randomElement(['inbound', 'outbound']),
            'status' => fake()->randomElement(['queued', 'sent', 'received']),
            'from_address' => fake()->safeEmail(),
            'from_name' => fake()->name(),
            'to_addresses' => [fake()->safeEmail()],
            'subject' => fake()->sentence(4),
            'html_body' => '<p>' . fake()->paragraph() . '</p>',
            'text_body' => fake()->paragraph(),
            'message_id' => '<' . Str::uuid() . '@clom.test>',
            'is_read' => false,
            'is_starred' => false,
            'folder' => 'inbox',
        ];
    }
}
