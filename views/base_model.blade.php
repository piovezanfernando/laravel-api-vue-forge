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
     * Retorna os relacionamentos disponíveis para busca
     *
     * @return array
     */
    public function getRelationsBySearch(): array
    {
        return $this->relationsBySearch ?? [];
    }

    /**
     * Retorna o tipo de cast para um campo específico (usado na busca dinâmica)
     *
     * @param  string  $field
     * @return string
     */
    public static function getFieldType(string $field): string
    {
        $instance = new static();
        $casts = $instance->getCasts();

        if (array_key_exists($field, $casts)) {
            return $casts[$field];
        }

        return '';
    }

    /**
     * Cache for model relationships
     */
    protected ?Collection $relationshipsCache = null;

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
                $method->getName() === __FUNCTION__
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
}
