<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Represents a factual public participant relationship for one experience.
 * Guarantees every Eloquent-created record receives a UUIDv7 public identity.
 */
class Participant extends Model
{
    use SoftDeletes;

    protected $fillable = ['public_id', 'experience_id', 'name', 'created_by_type', 'created_by_public_id', 'updated_by_type', 'updated_by_public_id', 'deleted_by_type', 'deleted_by_public_id'];

    /**
     * Gets the attribute casts for participant lifecycle timestamps.
     * Declares no contract exceptions.
     *
     * @return array<string, string> Guarantees immutable lifecycle timestamps.
     */
    protected function casts(): array
    {
        return ['created_at' => 'immutable_datetime', 'updated_at' => 'immutable_datetime', 'deleted_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $participant): void {
            $participant->public_id ??= (string) Str::uuid7();
        });
    }

    /**
     * Gets the experience that owns this factual participant relationship.
     * Declares no contract exceptions.
     *
     * @return BelongsTo<Experience, $this> Guarantees the inverse experience relationship.
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
