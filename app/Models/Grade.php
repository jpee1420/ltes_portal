<?php

namespace App\Models;

use Database\Factories\GradeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $student_id
 * @property int $subject_id
 * @property int $school_year_id
 * @property string $quarter
 * @property int $score
 * @property int $encoded_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['student_id', 'subject_id', 'school_year_id', 'quarter', 'score', 'encoded_by'])]
class Grade extends Model
{
    /** @use HasFactory<GradeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'integer',
        ];
    }

    /**
     * Get the student profile this grade belongs to.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(StudentProfile::class, 'student_id');
    }

    /**
     * Get the subject for this grade.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Get the school year this grade was recorded in.
     */
    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }

    /**
     * Get the user who encoded this grade.
     */
    public function encodedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'encoded_by');
    }

    /**
     * Check if the score is passing (75 is the passing grade).
     */
    public function isPassing(): bool
    {
        return $this->score >= 75;
    }

    /**
     * Get DepEd descriptive descriptor based on standard grading scale.
     */
    public function getDescriptorAttribute(): string
    {
        return match (true) {
            $this->score >= 90 => 'Outstanding',
            $this->score >= 85 => 'Very Satisfactory',
            $this->score >= 80 => 'Satisfactory',
            $this->score >= 75 => 'Fairly Satisfactory',
            default => 'Did Not Meet Expectations',
        };
    }
}
