<?php

namespace App\Models;

use App\Enums\ExperienceEditorialStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use InvalidArgumentException;

/**
 * Represents an editorially managed experience with authoritative schedule data.
 * Guarantees every Eloquent-created record receives a UUIDv7 public identity.
 */
class Experience extends Model
{
    use SoftDeletes;

    protected $fillable = ['public_id', 'title', 'locality', 'category', 'audience', 'editorial_status', 'starts_at', 'ends_at', 'timezone', 'created_by_type', 'created_by_public_id', 'updated_by_type', 'updated_by_public_id', 'deleted_by_type', 'deleted_by_public_id'];

    /**
     * Gets the attribute casts for authoritative experience data.
     * Declares no contract exceptions.
     *
     * @return array<string, string> Guarantees immutable timestamps and editorial enum values.
     */
    protected function casts(): array
    {
        return ['editorial_status' => ExperienceEditorialStatus::class, 'starts_at' => 'immutable_datetime', 'ends_at' => 'immutable_datetime', 'created_at' => 'immutable_datetime', 'updated_at' => 'immutable_datetime', 'deleted_at' => 'immutable_datetime'];
    }

    protected static function booted(): void
    {
        static::creating(function (self $experience): void {
            $experience->public_id ??= (string) Str::uuid7();
        });

        static::saving(function (self $experience): void {
            if (! in_array($experience->timezone, timezone_identifiers_list(), true)) {
                throw new InvalidArgumentException('Experience timezone must be a valid IANA identifier.');
            }
        });
    }

    /**
     * Gets the participants factually associated with this experience.
     * Declares no contract exceptions.
     *
     * @return HasMany<Participant, $this> Guarantees the inverse participant relationship.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }
}
