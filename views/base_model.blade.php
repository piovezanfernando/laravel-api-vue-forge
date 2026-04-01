@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Models;

use App\Traits\CustomSoftDelete;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use DateTimeInterface;

abstract class BaseModel extends Model
{
    use CustomSoftDelete;
    use HasFactory;
    use HasUlids;

    /**
     * Add the is_active field to make it easier to validate it on the front
     */
    protected $appends = [
        'is_active',
    ];

    /**
     * This attribute checks if the table is multi tenancy
     */
    protected bool $hasCompanyId = true;

    /**
     * Informs which fields should not be saved in uppercase if the trait is used
     */
    protected array $noUpper = [];

    /**
     * Informs which relations should be used in the search
     */
    protected array $relationsBySearch = [];

    /**
     * Returns the field types to be used in queries
     */
    public static function getCastsStatic(): array
    {
        return (new static())->getCasts();
    }

    /**
     * Function responsible for returning the type of the field for the query
     */
    public static function getFieldType(string $field): string
    {
        if (array_key_exists($field, static::getCastsStatic())) {
            return (new static())->getCastType($field);
        }
        return false;
    }

    /**
     * Method to return the relationships that can be queried
     */
    public function getRelationsBySearch(): array
    {
        return $this->relationsBySearch;
    }

    /**
     * Returns if the company ID is used in the model
     */
    public function hasCompanyId(): bool
    {
        return $this->hasCompanyId;
    }

    /**
     * Returns if the record is active according to the deleted_at field
     */
    protected function isActive(): Attribute
    {
        return new Attribute(
            get: fn() => empty($this->deleted_at),
        );
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

     * Get the relationships of the model
     * @return Collection
     */
    public function getRelationShip(): Collection
    {
        $model = $this;
        $relationships = collect();
        foreach ((new \ReflectionClass($model))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (
                $method->class != get_class($model) ||
                !empty($method->getParameters()) ||
                $method->getName() == __FUNCTION__
            ) {
                continue;
            }
            try {
                $return = $method->invoke($model);
                if ($return instanceof Relation) {
                    $relationships->push([
                                             'name' => $method->getName(),
                                             'type' => (new \ReflectionClass($return))->getShortName(),
                                             'model' => (new \ReflectionClass($return->getRelated()))->getName()
                                         ]);
                }
            } catch (\Throwable $e) {
            }
        }
        return $relationships;
    }
}
