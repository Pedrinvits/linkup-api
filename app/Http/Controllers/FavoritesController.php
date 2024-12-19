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

        $favorite = Favorite::where('user_id', auth()->id())
                            ->where('contact_id', $contact->id)
                            ->first();

        if ($favorite) {

            $Rmfavorite = Favorite::where('user_id', auth()->id())
                            ->where('contact_id', $contact->id)
                            ->firstOrFail();
                            
            $Rmfavorite->delete();

            return response()->json([
                'message' => 'Contato removido dos favoritos com sucesso!',
                'status' => 201,
            ]);
        }

        $favorite = new Favorite();
        $favorite->user_id = auth()->id();
        $favorite->contact_id = $contact->id;
        $favorite->save();

        return response()->json([
            'message' => 'Contato adicionado aos favoritos com sucesso!',
            'status' => 201,
        ]);
    }

    public function index()
    {
        $favorites = Favorite::where('user_id', auth()->id())
                             ->with('contact')
                             ->get();
                             
        return response()->json($favorites);
    }

    public function destroy($id)
    {
        $favorite = Favorite::where('user_id', auth()->id())
                            ->where('contact_id', $id)
                            ->firstOrFail();
                            
        $favorite->delete();

        return response()->json(['message' => 'Contato removido dos favoritos com sucesso!']);
    }
}
