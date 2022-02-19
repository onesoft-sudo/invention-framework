<?php


namespace OSN\Framework\ORM;


use App\Http\Controllers\APIController;
use OSN\Framework\Core\Model;
use OSN\Framework\ORM\Relationships\BelongsTo;
use OSN\Framework\ORM\Relationships\BelongsToMany;
use OSN\Framework\ORM\Relationships\HasMany;
use OSN\Framework\ORM\Relationships\HasManyThrough;
use OSN\Framework\ORM\Relationships\HasOne;
use OSN\Framework\ORM\Relationships\HasOneThrough;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphMany;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphOne;
use OSN\Framework\ORM\Relationships\Polymorphic\MorphTo;

trait ORMBaseTrait
{
    public function hasMany(string $class): HasMany
    {
        /** @var Model $this */
        return new HasMany($this, new $class());
    }

    public function hasOne(string $class): HasOne
    {
        /** @var Model $this */
        return new HasOne($this, new $class());
    }

    public function hasOneThrough(string $relating, string $bridge): HasOneThrough
    {
        /** @var Model $this */
        return new HasOneThrough($this, new $relating(), new $bridge());
    }

    public function hasManyThrough(string $relating, string $bridge): HasManyThrough
    {
        /** @var Model $this */
        return new HasManyThrough($this, new $relating(), new $bridge());
    }

    public function belongsTo(string $class): BelongsTo
    {
        /** @var Model $this */
        return new BelongsTo($this, new $class());
    }

    public function belongsToMany(string $class, string $helper_table = ''): BelongsToMany
    {
        /** @var Model $this */
        return new BelongsToMany($this, new $class(), $helper_table);
    }

    public function morphOne(string $class, string $keyword = ''): MorphOne
    {
        /** @var Model $this */
        return new MorphOne($this, new $class(), $keyword);
    }

    public function morphTo(string $keyword = ''): MorphTo
    {
        /** @var Model $this */
        return new MorphTo($this, $keyword);
    }

    public function morphMany(string $class, string $keyword = ''): MorphMany
    {
        /** @var Model $this */
        return new MorphMany($this, new $class(), $keyword);
    }
}
