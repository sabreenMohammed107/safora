<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Stores an event key plus its parameters, rendered via lang/{locale}/activity.php
 * so the same entry reads correctly in Arabic and English.
 */
#[Fillable(['client_id', 'actor_name', 'event', 'properties'])]
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * The admin or client user who performed the action (null for system events).
     *
     * @return MorphTo<Model, $this>
     */
    public function actor(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The entry in the given (or current) language; stored plan codes and billing cycles
     * are shown as their translated names.
     */
    public function description(?string $locale = null): string
    {
        $properties = $this->properties ?? [];

        if (isset($properties['plan'])) {
            $plan = once(fn () => Plan::all()->keyBy('code'))->get($properties['plan']);
            $properties['plan'] = $plan?->getTranslation('name', $locale ?? app()->getLocale()) ?? $properties['plan'];
        }

        if (isset($properties['cycle'])) {
            $properties['cycle'] = __('common.cycles.'.$properties['cycle'], [], $locale);
        }

        return __('activity.'.$this->event, $properties, $locale);
    }
}
