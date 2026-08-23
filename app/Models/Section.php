<?php

namespace App\Models;

use Database\Factories\SectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $school_year_id
 * @property int $grade_level_id
 * @property string $name
 * @property int|null $adviser_id
 * @property int|null $max_students
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['school_year_id', 'grade_level_id', 'name', 'adviser_id', 'max_students'])]
class Section extends Model
{
    /** @use HasFactory<SectionFactory> */
    use HasFactory;

    /**
     * Get the school year this section belongs to.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * Get the grade level this section belongs to.
     */
    public function gradeLevel(): BelongsTo
    {
        return $this->belongsTo(GradeLevel::class);
    }

    /**
     * Get the adviser teacher for this section.
     */
    public function adviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser_id');
    }

    /**
     * Get all student profiles assigned to this section.
     */
    public function studentProfiles(): HasMany
    {
        return $this->hasMany(StudentProfile::class);
    }

    /**
     * Get the total count of enrolled students.
     */
    public function enrolledCount(): int
    {
        if ($this->relationLoaded('studentProfiles')) {
            return $this->studentProfiles->count();
        }

        return $this->studentProfiles()->count();
    }

    /**
     * Check if the section has reached or exceeded maximum student capacity.
     */
    public function isAtCapacity(): bool
    {
        if ($this->max_students === null || $this->max_students <= 0) {
            return false;
        }

        return $this->enrolledCount() >= $this->max_students;
    }
}
