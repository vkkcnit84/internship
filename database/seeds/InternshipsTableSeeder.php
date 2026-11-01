<?php

use App\Internship;
use Illuminate\Database\Seeder;

/**
 * Starter programmes. Prices and durations are placeholders: edit them in
 * Admin > Internship management > Internships. Safe to re-run (it never
 * overwrites a programme that already exists).
 */
class InternshipsTableSeeder extends Seeder
{
    public function run()
    {
        $internships = [
            ['name' => 'UGC Internship', 'duration_weeks' => null, 'price' => 999, 'tax_percent' => 0],
            ['name' => 'UGC Internship + Project', 'duration_weeks' => null, 'price' => 1499, 'tax_percent' => 0],
        ];

        foreach ($internships as $internship) {
            Internship::firstOrCreate(['name' => $internship['name']], $internship + ['is_active' => true]);
        }
    }
}
