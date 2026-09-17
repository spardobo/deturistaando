<?php

namespace App\Enums;

/**
 * Defines the editorial publication states allowed for experiences.
 * Guarantees temporal availability remains independent from editorial state.
 */
enum ExperienceStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Cancelled = 'cancelled';
}
