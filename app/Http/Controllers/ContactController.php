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
        
        if (Contact::contactExists($request->name, $request->phone)) {
            return response()->json("Contato já cadastrado!", 400);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Usuário não encontrado!'], 400);
        }

        $contact = Contact::createContact($request->all());

        return response()->json([
            'message' => 'Contato adicionado com sucesso!',
            'status'  => 201,
            'contato' => $contact
        ]);
    }

    public function index()
    {
        $contacts = Contact::getUserContacts();

        return response()->json($contacts);
    }

    public function show($id)
    {
        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);
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
        $contact->updateContact($request->all());

        return response()->json($contact);
    }

    public function destroy($id)
    {
        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);
        $contact->deleteContact();

        return response()->json(['message' => 'Contato deletado com sucesso!']);
    }
}
