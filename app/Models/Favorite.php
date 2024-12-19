<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',  
        'contact_id', 
    ];

    /**
     * Relação com o usuário.
     */
    public function user()
    {
        return $this->belongsTo(User::class); 
    }

    /**
     * Relação com o contato.
     */
    public function contact()
    {
        return $this->belongsTo(Contact::class); 
    }
}
