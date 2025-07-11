<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Tutor;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Hash;
use Illuminate\Database\Seeder;
use Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Utilisateur tutor
        $tutorUser = User::factory()->create([
            'name' => 'Test1 User',
            'email' => 'test1@example.com',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'tutor'
        ]);

        // Insertion associée dans la table tutors
        Tutor::create([
            'user_id' => $tutorUser->id,
        ]);

        // Creation de l'admin
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'email_verified_at' => now(),
            'password' =>  Hash::make('password'),
            'remember_token' => Str::random(10),
            'role' => 'admin'
        ]);

        $this->call([
            CategorySeeder::class,
        ]);
        $this->call([
            AnnouncementSeeder::class
        ]);


    }
}
