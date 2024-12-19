<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try{

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);
            
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);
    
            return response()->json([
                'user' => $user,
                'token' => $user->createToken('auth_token')->plainTextToken,
                'message' => 'User criado com sucesso',
            ]);
        }
        catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
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
        }
        catch(\Illuminate\Validation\ValidationException $e){
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
        }catch(\Illuminate\Validation\ValidationException $e){
            return response()->json([
                'errors' => $e->errors(),
            ], 422);
        }
    }
}
