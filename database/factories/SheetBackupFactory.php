<?php

namespace Database\Factories;

use App\Models\SheetBackup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SheetBackupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'created_by' => User::factory(),
            'status' => SheetBackup::STATUS_PENDING,
            'file_path' => null,
            'file_size' => null,
            'sheet_count' => null,
            'error_message' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    public function ready(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SheetBackup::STATUS_READY,
            'file_path' => 'sheet-backups/'.$this->faker->uuid().'.zip',
            'file_size' => $this->faker->numberBetween(1_000_000, 100_000_000),
            'sheet_count' => $this->faker->numberBetween(10, 200),
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);
    }

    public function inProgress(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SheetBackup::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function failed(): self
    {
        return $this->state(fn (array $attributes): array => [
            'status' => SheetBackup::STATUS_FAILED,
            'error_message' => $this->faker->sentence(),
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }
}
