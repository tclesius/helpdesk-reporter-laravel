<?php

namespace Database\Seeders;

use App\Models\Faculty;
use Illuminate\Database\Seeder;

class FacultySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faculties = [
            'Mathematik',
            'Architektur und Bauingenieurwesen',
            'Sozialwesen',
            'Design Informatik Medien (DCSM)',
            'Wiesbaden Business School',
            'Ingenieurwissenschaften',
            'Keine Angabe',
        ];

        foreach ($faculties as $faculty) {
            Faculty::create([
                'name' => $faculty,
            ]);
        }
    }
}
