<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class BukuSakuFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'file_path' => 'buku-saku/dummy.pdf',
        ];
    }
}