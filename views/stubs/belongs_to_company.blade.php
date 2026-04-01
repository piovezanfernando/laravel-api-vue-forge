@php
    echo "<?php".PHP_EOL;
@endphp

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCompany
{
    /**
     * Boot the trait: adds a global scope to filter by company_id
     * and auto-fills company_id on creation
     */
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $query) {
            $query->when(
                auth('api')->user()?->company_id,
                fn (Builder $q, int $companyId) => $q->where(
                    $query->getModel()->getTable() . '.company_id',
                    $companyId,
                ),
            );
        });

        static::creating(function (Model $model) {
            $model->company_id ??= auth('api')->user()?->company_id;
        });
    }

    /**
     * Initialize the trait: ensures company_id is fillable
     */
    public function initializeBelongsToCompany(): void
    {
        $this->mergeFillable(['company_id']);
    }

    /**
     * Execute a query without the company scope (e.g. admin/reports)
     */
    public static function withoutCompanyScope(): Builder
    {
        return static::withoutGlobalScope('company');
    }
}
