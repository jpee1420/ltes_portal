<?php

namespace App\Models;

use App\Enums\EventCategory;
use App\Enums\UserRole;
use Database\Factories\CalendarEventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property EventCategory $category
 * @property Carbon $start_date
 * @property Carbon|null $end_date
 * @property int $school_year_id
 * @property int|null $section_id
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'description', 'category', 'start_date', 'end_date', 'school_year_id', 'section_id', 'created_by'])]
class CalendarEvent extends Model
{
    /** @use HasFactory<CalendarEventFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => EventCategory::class,
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * The school year this event belongs to.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * The optional section this event is scoped to.
     */
    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    /**
     * The user who created this event.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include events visible to the given user.
     */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->role === UserRole::Admin) {
            return $query;
        }

        if ($user->role === UserRole::Teacher) {
            $advisedSectionIds = Section::query()
                ->where('adviser_id', $user->id)
                ->pluck('id');

            return $query->where(function (Builder $q) use ($advisedSectionIds) {
                $q->whereNull('section_id')
                    ->orWhereIn('section_id', $advisedSectionIds);
            });
        }

        // Student role
        $studentSectionId = $user->studentProfile?->section_id;

        return $query->where(function (Builder $q) use ($studentSectionId) {
            $q->whereNull('section_id');

            if ($studentSectionId) {
                $q->orWhere('section_id', $studentSectionId);
            }
        });
    }

    /**
     * Scope a query to events within a specific calendar month.
     */
    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        $startOfMonth = Carbon::createFromDate($year, $month, 1)->startOfMonth()->toDateString();
        $endOfMonth = Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

        return $query->where(function (Builder $q) use ($startOfMonth, $endOfMonth) {
            $q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
                ->orWhere(function (Builder $sub) use ($startOfMonth, $endOfMonth) {
                    $sub->whereNotNull('end_date')
                        ->where('start_date', '<=', $endOfMonth)
                        ->where('end_date', '>=', $startOfMonth);
                });
        });
    }

    /**
     * Scope a query to upcoming events starting from today onwards.
     */
    public function scopeUpcoming(Builder $query, ?Carbon $fromDate = null, int $limit = 5): Builder
    {
        $today = ($fromDate ?? Carbon::today())->toDateString();

        return $query->where(function (Builder $q) use ($today) {
            $q->where('start_date', '>=', $today)
                ->orWhere(function (Builder $sub) use ($today) {
                    $sub->whereNotNull('end_date')
                        ->where('end_date', '>=', $today);
                });
        })
            ->orderBy('start_date')
            ->limit($limit);
    }
}
