<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        $this->call([
             LevelSeeder::class,
            UserSeeder::class,
            GedungSeeder::class,
            LantaiSeeder::class,
            RuangSeeder::class,
            KategoriSeeder::class,
            KlasifikasiSeeder::class,
            BarangSeeder::class,
            FasilitasSeeder::class,
            PeriodeSeeder::class,
            BobotPrioritasSeeder::class,
            LaporanSeeder::class,
            LaporanHistorySeeder::class,
            PerbaikanSeeder::class,
            PerbaikanDetailSeeder::class,
            KriteriaSeeder::class,
            FeedbackSeeder::class,
            // KriteriaSeeder::class,
          
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
