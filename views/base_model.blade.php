@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use DateTimeInterface;
use ReflectionClass;
use ReflectionMethod;
use Throwable;

abstract class BaseModel extends Model
{
    use HasFactory;

    /**
     * Add the is_active field to make it easier to validate it on the front
     */
    protected $appends = [
        'is_active',
    ];

    /**
     * Informs which fields should not be saved in uppercase if the trait is used
     */
    protected array $noUpper = [];

    /**
     * Informs which relations should be used in the search
     */
    protected array $relationsBySearch = [];

    /**
     * Legacy property previously used for multi-tenancy check
     */
    protected bool $hasCompanyId = false;

    /**
     * Cache for model relationships
     */
    protected ?Collection $relationshipsCache = null;

    /**
     * Returns the casts array (memoized to avoid repeated instantiation)
     */
    public static function getCastsStatic(): array
    {
        return (new static())->getCasts();
    }

    /**
     * Returns the cast type for a given field, or null if the field has no cast
     */
    public static function getFieldType(string $field): ?string
    {
        $casts = static::getCastsStatic();
        if (! is_array($casts) || ! array_key_exists($field, $casts)) {
            return null;
        }

        return (new static())->getCastType($field);
    }

    /**
     * Returns the description of the fields for API documentation
     *
     * @return array<string, array<string, string>>
     */
    public static function getFieldDescription(): array
    {
        return [];
    }

    /**
     * Method to return the relationships that can be queried
     *
     * @return array<int, string>
     */
    public function getRelationsBySearch(): array
    {
        return $this->relationsBySearch;
    }

    /**
     * Returns if the record is active according to the deleted_at field
     */
    protected function isActive()
    {
        return Attribute::get(fn () => is_null($this->deleted_at));
    }

    /**
     * Legacy method for attachment classification check (unblocks swop-api tests)
     */
    public static function getAttClassification()
    {
        return (new static())->attClassification ?? [];
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * Get the relationships of the model via reflection (memoized)
     */
    public function getRelationShip(): Collection
    {
        if ($this->relationshipsCache !== null) {
            return $this->relationshipsCache;
        }

        $relationships = collect();

        foreach ((new ReflectionClass($this))->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->class !== $this::class ||
                $method->getNumberOfParameters() > 0 ||
                $method->getName() === __FUNCTION__ ||
                $method->getName() === 'getRelationShip'
            ) {
                continue;
            }

            try {
                // Ensure we don't call some standard model methods that might cause issues
                if (in_array($method->getName(), ['getAttribute', 'getRelations', 'getRelationships'])) {
                    continue;
                }

                $return = $method->invoke($this);

                if ($return instanceof Relation) {
                    $relationships->push([
                        'name'  => $method->getName(),
                        'type'  => (new ReflectionClass($return))->getShortName(),
                        'model' => $return->getRelated()::class,
                    ]);
                }
            } catch (Throwable) {
            }
        }

        return $this->relationshipsCache = $relationships;
    }

    /**
     * Legacy method for multi-tenancy compatibility
     */
    public function hasCompanyId(): bool
    {
        return $this->hasCompanyId;
    }
}
