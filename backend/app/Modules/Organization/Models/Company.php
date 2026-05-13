<?php

declare(strict_types=1);

namespace App\Modules\Organization\Models;

use App\Support\Traits\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name', 'logo_path', 'registration_number', 'tax_id',
    'email', 'phone', 'address', 'website', 'currency', 'timezone'
])]
final class Company extends Model
{
    use Auditable;

    /**
     * @return HasMany<Branch, $this>
     */
    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }
}
