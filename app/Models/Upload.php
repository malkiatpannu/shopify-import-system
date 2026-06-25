<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Upload extends Model
{
    protected $fillable     =   [
        'original_filename',
        'stored_filename',
        'file_path',
        'total_records',
        'processed_records',
        'successful_records',
        'failed_records',
        'status',
        'started_at',
        'completed_at',
    ];

    protected $casts        =   [
        'started_at'    =>  'datetime',
        'completed_at'  =>  'datetime',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function importRecords(): HasMany
    {
        return $this->hasMany(ImportRecord::class);
    }

    public function errorLogs(): HasMany
    {
        return $this->hasMany(ErrorLog::class);
    }
}