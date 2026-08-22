<?php

namespace Database\Factories;

use App\Enums\EventCategory;
use App\Models\CalendarEvent;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<CalendarEvent>
 */
class CalendarEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = Carbon::today()->addDays(fake()->numberBetween(1, 30));

        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'category' => fake()->randomElement(EventCategory::cases()),
            'start_date' => $startDate->toDateString(),
            'end_date' => null,
            'school_year_id' => SchoolYear::factory(),
            'section_id' => null,
            'created_by' => User::factory()->admin(),
        ];
    }

    /**
     * Set category to school event.
     */
    public function schoolEvent(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => EventCategory::SchoolEvent,
            'section_id' => null,
        ]);
    }

    /**
     * Set category to exam.
     */
    public function exam(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => EventCategory::Exam,
            'section_id' => null,
        ]);
    }

    /**
     * Set category to holiday.
     */
    public function holiday(): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => EventCategory::Holiday,
            'section_id' => null,
        ]);
    }

    /**
     * Set category to section event.
     */
    public function sectionEvent(int $sectionId): static
    {
        return $this->state(fn (array $attributes) => [
            'category' => EventCategory::SectionEvent,
            'section_id' => $sectionId,
        ]);
    }
}
