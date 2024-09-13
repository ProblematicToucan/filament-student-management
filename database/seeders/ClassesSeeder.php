<?php

namespace Database\Seeders;

use App\Models\Classes;
use App\Models\Section;
use App\Models\Student;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class ClassesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Classes::factory()
            ->count(10)
            ->sequence(fn($sequence) => ['name' => 'Class ' . ($sequence->index + 1)]) // Moved parenthesis to ensure correct increment
            ->has(
                Section::factory()
                    ->count(2)
                    ->state(new Sequence(
                        ['name' => 'Section A'],
                        ['name' => 'Section B']
                    ))
                    ->has(
                        Student::factory()
                            ->count(5)
                            ->state(fn(array $attributes, Section $section) => [
                                'classes_id' => $section->classes_id // Fixing this to return an array
                            ])
                    )
            )
            ->create();
    }
}
