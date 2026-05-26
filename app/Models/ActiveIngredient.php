<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ActiveIngredient extends Model
{
    protected $table = 'active_ingredients';

    protected $fillable = [
        'name',
        'description',
        'side_effect',
        'created_by_company_id',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'created_by_company_id');
    }

    public function drugs(): BelongsToMany
    {
        return $this->belongsToMany(Drug::class, 'drug_ingredients', 'ingredient_id', 'drug_id');
    }
}

