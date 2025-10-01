<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Buat 5 user dengan password 123
        User::factory()->count(5)->create([
            'password' => Hash::make('123'),
        ]);

        // Buat 15 task
        Task::factory()->count(15)->create();

        // Buat 10 komentar
        TaskComment::factory()->count(10)->create();
    }
}
