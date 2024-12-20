<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;


/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Documentation",
 *     description="API documentation for your application.",
 *     @OA\Contact(
 *         email="support@yourdomain.com"
 *     ),
 *     @OA\License(
 *         name="Apache 2.0",
 *         url="http://www.apache.org/licenses/LICENSE-2.0.html"
 *     )
 * )
 * @OA\Server(
 *     url="http://localhost:8000/api",
 *     description="Local development server"
 * )
 * @OA\Schema(
 *     schema="User",
 *     type="object",
 *     description="User model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", example="john.doe@example.com"),
 *     @OA\Property(property="phone", type="string", example="1499823432"),
 *     @OA\Property(property="status", type="string", example="active"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-12-20T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-12-20T10:30:00Z"),
 * )
 */
class UserController extends Controller
{
    
    /**
     * @OA\Get(
     *     path="/api/users",
     *     summary="Retrieve a list of users",
     *     description="Returns a list of all registered users.",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="List of users",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/User")
     *         )
     *     )
     * )
     */

    public function index()
    {
        return User::all();
    }
    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     summary="Get user details",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User details",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="User not found")
     *         )
     *     )
     * )
     */
    public function show($id)
    {
        return User::findOrFail($id);
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     summary="Registrar novo usuário",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "email", "password", "phone"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Usuário criado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação"
     *     )
     * )
     */

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
    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     summary="Update a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="phone", type="string", example="1234567890")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Usuário alterado com sucesso!"),
     *             @OA\Property(property="user", type="object")
     *         )
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     summary="Delete a user",
     *     tags={"Users"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="User ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User deleted")
     *         )
     *     )
     * )
     */
    
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     summary="Login do usuário",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login efetuado com sucesso"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Credenciais inválidas"
     *     )
     * )
     */

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
