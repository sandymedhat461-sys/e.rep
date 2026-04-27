<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Drug extends Model
{
    protected $table = 'drugs';

    protected $fillable = [
        'company_id',
        'category_id',
        'name',
        'market_name',
        'description',
        'price',
        'dosage',
        'side_effects',
        'image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'status' => 'string',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function drugSamples(): HasMany
    {
        return $this->hasMany(DrugSample::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DrugCategory::class, 'category_id');
    }

    public function drugIngredients(): HasMany
    {
        return $this->hasMany(DrugIngredient::class);
    }

    public function drugReviews(): HasMany
    {
        return $this->hasMany(DrugReview::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function repDrugAssignments(): HasMany
    {
        return $this->hasMany(RepDrugAssignment::class);
    }

    public function repAssignments(): HasMany
    {
        return $this->hasMany(\App\Models\RepDrugAssignment::class, 'drug_id');
    }

    public function activeIngredients(): BelongsToMany
    {
        return $this->belongsToMany(ActiveIngredient::class, 'drug_ingredients', 'drug_id', 'ingredient_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->activeIngredients();
    }

    public function medicalReps(): BelongsToMany
    {
        return $this->belongsToMany(MedicalRep::class, 'rep_drug_assignments', 'drug_id', 'rep_id');
    }
}
