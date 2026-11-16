<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;
use LogicException;

final class SecurityEventBuilder extends Builder
{
    public function update(array $values)
    {
        $this->rejectMutation();
    }

    public function upsert(array $values, $uniqueBy, $update = null)
    {
        $this->rejectMutation();
    }

    public function increment($column, $amount = 1, array $extra = [])
    {
        $this->rejectMutation();
    }

    public function decrement($column, $amount = 1, array $extra = [])
    {
        $this->rejectMutation();
    }

    public function incrementEach(array $columns, array $extra = [])
    {
        $this->rejectMutation();
    }

    public function decrementEach(array $columns, array $extra = [])
    {
        $this->rejectMutation();
    }

    public function delete()
    {
        $this->rejectMutation();
    }

    public function forceDelete()
    {
        $this->rejectMutation();
    }

    public function touch($column = null)
    {
        $this->rejectMutation();
    }

    public function __call($method, $parameters)
    {
        if (in_array($method, [
            'insert',
            'insertGetId',
            'insertOrIgnore',
            'insertOrIgnoreReturning',
            'insertOrIgnoreUsing',
            'insertUsing',
            'truncate',
            'updateFrom',
            'updateOrInsert',
        ], true)) {
            $this->rejectMutation();
        }

        return parent::__call($method, $parameters);
    }

    private function rejectMutation(): never
    {
        throw new LogicException('Los eventos de seguridad solo se escriben mediante el registrador autorizado.');
    }
}
