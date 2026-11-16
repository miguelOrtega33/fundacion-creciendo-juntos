<?php

namespace App\Models;

use App\Enums\SecurityEventAction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class SecurityEvent extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected static function booted(): void
    {
        self::updating(static fn () => throw new LogicException('Los eventos de seguridad son inmutables.'));
        self::deleting(static fn () => throw new LogicException('Los eventos de seguridad no se pueden eliminar.'));
    }

    protected function casts(): array
    {
        return [
            'action' => SecurityEventAction::class,
            'new_values' => 'array',
            'occurred_at' => 'immutable_datetime',
            'previous_values' => 'array',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /** @return BelongsTo<User, $this> */
    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }
}
