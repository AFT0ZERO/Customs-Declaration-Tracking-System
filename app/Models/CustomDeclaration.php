<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomDeclaration extends Model
{

    use HasFactory, SoftDeletes;
    protected $guarded;

    protected $casts = [
        'year' => 'integer',
    ];

    public function histories()
    {
        return $this->hasMany(DeclarationHistory::class, 'declaration_id');
    }


}
