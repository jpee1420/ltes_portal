<?php

namespace App\Enums;

enum EventCategory: string
{
    case Exam = 'exam';
    case Holiday = 'holiday';
    case SchoolEvent = 'school_event';
    case DepedActivity = 'deped_activity';
    case SectionEvent = 'section_event';

    /**
     * Get the human-readable display label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Exam => 'Examination',
            self::Holiday => 'Holiday / No Classes',
            self::SchoolEvent => 'School Event',
            self::DepedActivity => 'DepEd Activity',
            self::SectionEvent => 'Section Event',
        };
    }

    /**
     * Get the semantic color key for UI tags/badges.
     */
    public function color(): string
    {
        return match ($this) {
            self::Exam => 'red',
            self::Holiday => 'amber',
            self::SchoolEvent => 'blue',
            self::DepedActivity => 'purple',
            self::SectionEvent => 'emerald',
        };
    }

    /**
     * Determine if this category represents a school-wide event.
     */
    public function isSchoolWide(): bool
    {
        return $this !== self::SectionEvent;
    }
}
