<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Contact;
use Illuminate\Http\Request;

class FavoritesController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|exists:contacts,id',
        ]);

        $contact = Contact::findOrFail($request->contact_id);

        $response = Favorite::toggleFavorite(auth()->id(), $contact->id);

        return response()->json($response);
    }

    public function index()
    {
        $favorites = Favorite::getFavoritesForUser(auth()->id());

        return response()->json($favorites);
    }

    public function destroy($id)
    {
        $response = Favorite::removeFavorite(auth()->id(), $id);

        return response()->json($response);
    }
}
