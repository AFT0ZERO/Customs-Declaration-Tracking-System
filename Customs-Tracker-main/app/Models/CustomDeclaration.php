<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;

class CustomDeclaration extends Model
{

    use HasFactory , Searchable ,SoftDeletes;
protected $guarded ;


    public function histories()
    {
        return $this->hasMany(DeclarationHistory::class, 'declaration_id');
    }

    public function toSearchableArray()
    {
        return [
            'declaration_number'=>$this->declaration_number,
            'status'=>$this->status,
            'created_at'=>$this->created_at,
        ];
    }
}
