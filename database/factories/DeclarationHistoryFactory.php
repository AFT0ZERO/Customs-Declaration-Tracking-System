<?php

namespace Database\Factories;

use App\Models\CustomDeclaration;
use App\Models\DeclarationHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeclarationHistory>
 */
class DeclarationHistoryFactory extends Factory
{
    protected $model = DeclarationHistory::class;

    public function definition(): array
    {
        return [
            'user_id'        => User::factory(),
            'declaration_id' => CustomDeclaration::factory(),
            'action'         => $this->faker->randomElement(['قيد التخليص', 'تم التخليص', 'معلق']),
            'description'    => $this->faker->optional()->sentence() ?? 'لا يوجد',
        ];
    }
}
