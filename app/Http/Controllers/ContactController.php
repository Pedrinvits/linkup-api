<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|min:11',
            'city' => 'required|string|max:255',
            'email' => 'required|string',
        ]);
       
        $contactExists = Contact::where('user_id', auth()->id())
                            ->where('name', $request->name)
                            ->where('phone', $request->phone)
                            ->first();
                            
        if($contactExists){
            return response()->json("Contato já cadastrado!", 400);
        }
       
        $user = User::where('email', $request->email)
                    ->first();

        if(!isset($user)){
            return response()->json(['message' => 'Usuário não encontrado!'], 400);
        }
       
        $contact = new Contact();
        $contact->user_id = auth()->id();  
        $contact->name = $request->name;
        $contact->phone = $request->phone;
        $contact->city = $request->city;
        $contact->email = $request->email;
        $contact->status = 'ACTIVE'; // retirar dps
        $contact->save();

        return response()->json([
            'message' => 'Contato adicionado com sucesso!',
            'status'  => 201,
            'contato' => $contact
        ]);
        
    }

    public function index()
    {
        $contacts = Contact::where('user_id', auth()->id())  
            ->with(['favorites' => function ($query) {
                $query->where('user_id', auth()->id());
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

        return response()->json($contacts);
    }

    public function show($id)
    {
        $current_user_id = auth()->id();
        $contact = Contact::where('user_id', $current_user_id);
        return response()->json($contact);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'string|max:255',
            'phone' => 'string|max:15',
            'city' => 'string|max:255',
        ]);

        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);
        $contact->name = $request->name ?? $contact->name;
        $contact->phone = $request->phone ?? $contact->phone;
        $contact->city = $request->city ?? $contact->city;
        $contact->status = $request->status ?? $contact->status;
        $contact->save();

        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);
        $contact->delete();

        return response()->json(['message' => 'Contato deletado com sucesso!']);
    }
}
