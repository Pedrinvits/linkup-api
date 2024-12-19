<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name',    
        'phone',   
        'city',   
        'status',  
    ];

    /**
     * Relação com o usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class); 
    }

    /**
     * Relação com os favoritos.
     */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }
}
