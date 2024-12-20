<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return User::all();
    }

    public function show($id)
    {
        return User::findOrFail($id);
    }

    public function store(Request $request)
    {
        try{
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email',
                'password' => 'required|min:6',
                'phone' => 'required|min:11',
            ]);
            
            if (User::where('email', $validated['email'])->exists()) {
                return response()->json([
                    'errors' => 'E-mail já está em uso.',
                ], 422);
            }
    
            if (User::where('phone', $validated['phone'])->exists()) {
                return response()->json([
                    'errors' => 'Telefone já está em uso.',
                ], 422);
            }
    
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'],
            ]);
    
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'message' => 'Usuário criado com sucesso',
            ]);
        }
        catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
                'message' => 'Os dados fornecidos são inválidos.',
            ], 422);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'status' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string',
        ]);
       
        $user->update($validated);

        return response()->json([
            'message' => 'Usuário alterado com sucesso!',
            'user' => $user,
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);
    
            $user = User::where('email', $validated['email'])->first();
    
            if (! $user || ! Hash::check($validated['password'], $user->password)) {
                return response()->json([
                    'error' => 'Credenciais inválidas',
                ]);
            }
    
            return response()->json([
                'token' => $user->createToken('auth_token')->plainTextToken,
                'data' => $user,
                'message' => 'Login efetuado com sucesso',
            ]);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response()->json(['message' => 'Logged out']);
        } catch(\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
