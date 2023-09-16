<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Builder;

class Note extends Model
{
    use HasFactory, MassPrunable;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['hash', 'data', 'destruction_time'];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::created(function (Model $model) {
            $model->hash = base64_encode(random_bytes(10));
        });
    }
 
    /**
     * Get the prunable model query.
     */
    public function prunable(): Builder
    {
        return static::where('destruction_time', '>', now());
    }

    public function link()
    {
        return env('APP_URL') . '/share/' . $this->hash;
    }
}
