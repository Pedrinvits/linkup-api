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

    public function user()
    {
        return $this->belongsTo(User::class); 
    }

    public function contact()
    {
        return $this->belongsTo(Contact::class); 
    }

    public static function toggleFavorite($userId, $contactId)
    {
        $favorite = self::where('user_id', $userId)
                        ->where('contact_id', $contactId)
                        ->first();

        if ($favorite) {
            $favorite->delete();
            return ['message' => 'Contato removido dos favoritos com sucesso!', 'status' => 201];
        }

        $newFavorite = new self();
        $newFavorite->user_id = $userId;
        $newFavorite->contact_id = $contactId;
        $newFavorite->save();

        return ['message' => 'Contato adicionado aos favoritos com sucesso!', 'status' => 201];
    }

    public static function getFavoritesForUser($userId)
    {
        return self::where('user_id', $userId)
                    ->with('contact')
                    ->get();
    }

    public static function removeFavorite($userId, $contactId)
    {
        $favorite = self::where('user_id', $userId)
                        ->where('contact_id', $contactId)
                        ->firstOrFail();
        $favorite->delete();

        return ['message' => 'Contato removido dos favoritos com sucesso!'];
    }
}
