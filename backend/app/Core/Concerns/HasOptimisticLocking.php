<?php

declare(strict_types=1);

namespace App\Core\Concerns;

use App\Core\Exceptions\StaleModelException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @phpstan-require-extends Model
 */
trait HasOptimisticLocking
{
    public static function bootHasOptimisticLocking(): void
    {
        static::creating(static function (Model $model): void {
            if ($model->getAttribute('version') === null) {
                $model->setAttribute('version', 1);
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function performUpdate(Builder $query): bool
    {
        if ($this->fireModelEvent('updating') === false) {
            return false;
        }

        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
        }

        $dirty = $this->getDirtyForUpdate();

        if ($dirty === []) {
            return true;
        }

        $original = $this->getOriginal('version');

        if ($original === null) {
            $this->setKeysForSaveQuery($query)->update($dirty);
            $this->syncChanges();
            $this->fireModelEvent('updated', false);

            return true;
        }

        $expected = (int) $original;
        $next = $expected + 1;
        $this->setAttribute('version', $next);
        $dirty['version'] = $next;

        $affected = $this->setKeysForSaveQuery($query)
            ->where('version', $expected)
            ->update($dirty);

        if ($affected === 0) {
            $key = $this->getKey();
            throw new StaleModelException(
                model: static::class,
                id: \is_int($key) || \is_string($key) ? $key : 'unknown',
            );
        }

        $this->syncChanges();
        $this->fireModelEvent('updated', false);

        return true;
    }
}
