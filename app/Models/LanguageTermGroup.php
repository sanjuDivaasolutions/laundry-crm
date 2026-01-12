<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LanguageTermGroup extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'language_term_groups';

    protected $orderable = [
        'id',
        'name',
    ];

    protected $filterable = [
        'id',
        'name',
    ];

    protected $fillable = [
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function term()
    {
        return $this->hasMany(LanguageTerm::class);
    }
}
