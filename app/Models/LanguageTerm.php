<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageTerm extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'language_terms';

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $filterable = [
        'id',
        'name',
        'language_term_group.name',
    ];

    protected $orderable = [
        'id',
        'name',
        'active',
        'language_term_group.name',
    ];

    protected $fillable = [
        'name',
        'active',
        'language_term_group_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function languageTermGroup()
    {
        return $this->belongsTo(LanguageTermGroup::class);
    }

    public function translations()
    {
        return $this->hasMany(Translation::class, 'language_term_id');
    }
}
