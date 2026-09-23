<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /** Counter backing the generated user_code values. */
    protected static int $codeSeq = 1000;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'username' => fake()->unique()->userName(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'role_id' => User::ROLE_STUDENT,
            // users.user_code is NOT NULL with no default — the factory must
            // supply one or every create() call fails.
            'user_code' => static::sequentialCode(),
            'student_id' => fn (array $attrs) => $attrs['user_code'],
            'is_active' => true,
            'profile_completed' => false,
        ];
    }

    /**
     * Unique YY-LN-NNNN style code, matching UserCodeService's format.
     */
    protected static function sequentialCode(string $prefix = 'LN'): string
    {
        static::$codeSeq++;

        return sprintf('%s-%s-%04d', now()->format('y'), $prefix, static::$codeSeq);
    }

    /** Admin account state. */
    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => User::ROLE_ADMIN,
            'user_code' => static::sequentialCode('AD'),
            'profile_completed' => true,
        ]);
    }

    /** Faculty account state. */
    public function faculty(): static
    {
        return $this->state(fn () => [
            'role_id' => User::ROLE_FACULTY,
            'user_code' => static::sequentialCode('FC'),
            'profile_completed' => true,
        ]);
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'email_verified_at' => null,
            ];
        });
    }
}
