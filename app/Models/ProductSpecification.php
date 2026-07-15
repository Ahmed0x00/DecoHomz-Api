<?php

namespace App\Models;

use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSpecification extends Model
{
    use LogsActivity;

    use HasFactory;

    protected $fillable = [
        'product_id',
        'materials',
        'dimensions_length',
        'dimensions_width',
        'dimensions_height',
        'weight_kg',
        'available_colors',
        'finishes',
        'packaging_details',
        'production_time_days',
        'warranty_months',
        'care_instructions',
        'additional_notes',
    ];

    protected $casts = [
        'dimensions_length' => 'decimal:2',
        'dimensions_width' => 'decimal:2',
        'dimensions_height' => 'decimal:2',
        'weight_kg' => 'decimal:2',
        'available_colors' => 'array',
        'production_time_days' => 'integer',
        'warranty_months' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('ProductSpecifications')
            ->logAll()
            ->logOnlyDirty();
    }
}
