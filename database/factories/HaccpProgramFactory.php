<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ProgramStatus;
use App\Models\HaccpProgram;
use App\Models\HaccpProgramGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HaccpProgram>
 */
class HaccpProgramFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => 'PRP-'.fake()->unique()->numberBetween(50, 99),
            'group_id' => fn (): int => HaccpProgramGroup::query()->value('id') ?? HaccpProgramGroup::create([
                'key' => 'infra', 'label' => ['ar' => 'البنية التحتية', 'en' => 'Infrastructure'], 'icon' => 'fa-building',
            ])->id,
            'name' => ['ar' => 'برنامج '.fake()->word(), 'en' => fake()->words(2, true)],
            'icon' => 'fa-layer-group',
            'summary' => ['ar' => 'وصف مختصر'],
            'status' => ProgramStatus::Active,
            'is_custom' => true,
            'sort_order' => 99,
        ];
    }
}
