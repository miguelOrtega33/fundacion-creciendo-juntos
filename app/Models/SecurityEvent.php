<?php

namespace App\Models;

use App\Enums\SecurityEventAction;
use App\Models\Builders\SecurityEventBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

final class SecurityEvent extends Model
{
    public $timestamps = false;

    protected static string $builder = SecurityEventBuilder::class;

    protected $guarded = ['*'];

    public function save(array $options = [])
    {
        $this->rejectMutation();
    }

    public function delete()
    {
        $this->rejectMutation();
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

    private function rejectMutation(): never
    {
        throw new LogicException('Los eventos de seguridad son de solo lectura.');
    }
}
