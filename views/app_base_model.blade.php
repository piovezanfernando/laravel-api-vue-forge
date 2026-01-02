@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Models;

use App\Traits\CustomSoftDelete;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Vinkla\Hashids\Facades\Hashids;

abstract class BaseModel extends Model
{
    use CustomSoftDelete;
    use HasFactory;

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
     * Function responsible for writing log where defined
     */
    public function saveLog(Model $model, string $event): array
    {
        if ($event == 'saving' && $model->exists) {
            $log = array_diff_assoc($model->getAttributes(), $model->getOriginal());
        } elseif ($event == 'deleting' && !$model->exists) {
            $log = $model->getAttributes();
        } else {
            $log = $model->getAttributes();
        }
        return $log;
    }

    /**
     * Convert the model instance to an array.
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();
        $array['created_by'] = $array['created_by']['name'] ?? null;
        $array['updated_by'] = $array['updated_by']['name'] ?? null;
        $array['deleted_by'] = $array['deleted_by']['name'] ?? null;
        if (isset($array['id']) && env('USE_HASH', true)) {
            $array['id'] = Hashids::connection('main')->encodeHex($array['id']);
        }
        return $array;
    }

    /**
     * Bootstrap the model and its traits
     */
    protected static function boot(): void
    {
        static::saving(function ($model) {
            if ($model->exists) {
                $model->updated_by = auth('api')->user() ? auth('api')->user()->getAuthIdentifier() : 1;
            } else {
                $model->created_by = auth('api')->user() ? auth('api')->user()->getAuthIdentifier() : 1;
                if ($model->hasCompanyId) {
                    if (
                        config('app.env') === 'testing' ||
                        config('app.env') === 'documentation' ||
                        PHP_SAPI === 'cli'
                    ) {
                        Log::info('entrou aqui ');
                        $model->company_id = 1;
                    } else {
                        $model->company_id = config('current_company_id') ?? auth('api')->user()->company_id;
                    }
                }
            }
        });
        parent::boot();
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
}
