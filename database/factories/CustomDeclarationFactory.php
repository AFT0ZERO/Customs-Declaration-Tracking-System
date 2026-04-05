<?php

namespace Database\Factories;

use App\Models\CustomDeclaration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomDeclaration>
 */
class CustomDeclarationFactory extends Factory
{
    protected $model = CustomDeclaration::class;

    public function definition(): array
    {
        return [
            'declaration_number' => (string) $this->faker->unique()->numberBetween(10000000, 99999999),
            'declaration_type'   => $this->faker->randomElement(['220', '224', '900']),
            'year'               => $this->faker->randomElement([2025, 2026]),
            'status'             => $this->faker->randomElement([
                'قيد التخليص',
                'تم التخليص',
                'معلق',
                'مرفوض',
            ]),
        ];
    }

    /**
     * A declaration that has been soft-deleted (archived).
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'العقبة الارشيف',
        ])->afterCreating(function (CustomDeclaration $declaration) {
            $declaration->delete();
        });
    }
}
