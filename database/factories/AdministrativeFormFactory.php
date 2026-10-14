<?php

namespace Database\Factories;

use App\Models\AdministrativeForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdministrativeForm>
 */
class AdministrativeFormFactory extends Factory
{
    protected $model = AdministrativeForm::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'domain' => 'work_permit',
            'form_code' => 'NAG-'.fake()->unique()->numerify('###'),
            'title' => 'Antrag auf Erteilung eines Aufenthaltstitels',
            'title_en' => 'Application for a Residence Title',
            'description' => 'Offizieller Antrag gem. NAG.',
            'authority' => 'Bundesministerium für Inneres (BMI)',
            'download_url' => 'https://www.bmi.gv.at/312/60a/files/b/239-2026-antragsformular-b-aufenthaltstitel-drittstaatsangehoerige-de-v20260915-bf.pdf',
            'online_url' => 'https://www.bmi.gv.at/312/60a/start.html',
            'keywords' => ['aufenthaltstitel', 'antrag', 'visum', 'rot-weiß-rot'],
            'is_verified' => true,
            'hit_count' => 0,
        ];
    }
}
