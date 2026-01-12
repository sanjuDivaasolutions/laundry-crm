<?php

namespace App\Models;

use App\Support\HasAdvancedFilter;
use App\Traits\Searchable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    use HasAdvancedFilter, HasFactory, Searchable;

    public $table = 'translations';

    protected $orderable = [
        'id',
        'language.name',
        'language_term.name',
        'translation',
    ];

    protected $filterable = [
        'id',
        'language.name',
        'language_term.name',
        'translation',
    ];

    protected $fillable = [
        'language_id',
        'language_term_id',
        'translation',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function languageTerm()
    {
        return $this->belongsTo(LanguageTerm::class);
    }
}
