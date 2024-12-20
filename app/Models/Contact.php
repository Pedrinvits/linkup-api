<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'name',    
        'phone',   
        'city',   
        'email'
    ];

    public function user()
    {
        return $this->belongsTo(User::class); 
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public static function contactExists($name, $phone)
    {
        return self::where('user_id', Auth::id())
                   ->where('name', $name)
                   ->where('phone', $phone)
                   ->first();
    }

    public static function createContact($data)
    {
        $contact = new self();
        $contact->user_id = Auth::id();
        $contact->name = $data['name'];
        $contact->phone = $data['phone'];
        $contact->city = $data['city'];
        $contact->email = $data['email'];
        $contact->save();
        return $contact;
    }

    public function updateContact($data)
    {
        $this->name = $data['name'] ?? $this->name;
        $this->phone = $data['phone'] ?? $this->phone;
        $this->city = $data['city'] ?? $this->city;
        $this->save();
        return $this;
    }
    public function deleteContact()
    {
        $this->delete();
    }

    public static function getUserContacts()
    {
        return self::where('user_id', Auth::id())
                   ->with(['favorites' => function ($query) {
                       $query->where('user_id', Auth::id());
                   }])
                   ->get()
                   ->map(function ($contact) {
                       $contact->is_favorite = $contact->favorites->isNotEmpty(); 
                       unset($contact->favorites);

                       $user = User::where('email', $contact->email)->first();

                       if ($user) {
                           $contact->user_info = [
                               'status' => $user->status,
                           ];
                       } else {
                           $contact->user_info = null;
                       }

                       return $contact;
                   });
    }
}
