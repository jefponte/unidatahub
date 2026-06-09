<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/login",
     *     summary="Authenticate user and return token",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User authenticated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string"),
     *             @OA\Property(property="user", ref="#/components/schemas/User")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error"
     *     )
     * )
     */
    public function auth(Request $request)
    {
        $input = $request->all();

        if (isset($input['senha']) && !isset($input['password'])) {
            $input['password'] = $input['senha'];
        }

        $validation = Validator::make($input, [
            'password' => 'required',
            'email' => 'nullable|email',
            'login' => 'nullable|string',
            'cpf' => 'nullable|string',
        ]);

        if ($validation->fails()) {
            return response()->json(['error' => $validation->errors()], 422);
        }

        $field = null;
        if (!empty($input['email'])) {
            $field = 'email';
        } elseif (!empty($input['login'])) {
            $field = 'login';
        } elseif (!empty($input['cpf'])) {
            $field = 'cpf_cnpj';
        }

        if (!$field) {
            return response()->json(['error' => 'You must provide either email, login, or CPF'], 422);
        }

        $userData = DB::connection('sistemas_comum')
            ->table('vw_usuarios_autenticacao_catraca')
            ->where($field, $input[$field])
            ->first();

        if (!$userData) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Verificar se a senha já está em MD5
        $providedPassword = $input['password'];
        $hashedPassword = strlen($providedPassword) === 32 && ctype_xdigit($providedPassword)
            ? $providedPassword // Senha já está em MD5
            : md5($providedPassword); // Gerar o hash MD5 se não estiver

        if ($hashedPassword !== $userData->senha) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
        $user = User::updateOrCreate(
            ['id' => $userData->id_usuario],
            [
                'id' => $userData->id_usuario,
                'name' => $userData->nome,
                'email' => $userData->email,
                'login' => $userData->login,
                'password' => null,
            ]
        );

        $abilities = is_string($user->role) ? [$user->role] : ['client'];
        $token = $user->createToken('api-web', $abilities)->plainTextToken;
        $token = $user->createToken('api-web', [$user->role])->plainTextToken;

        return response()->json([
            "id" => $user->id,
            "name" => $user->name,
            "access_token" => $token
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/logout",
     *     summary="Logout user",
     *     tags={"Auth"},
     *     security={{"bearerAuth":{}}},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=204,
     *         description="Logged out successfully"
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }

    /**
     * @OA\Get(
     *     path="/api/me",
     *     summary="Get authenticated user",
     *     tags={"Auth"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Authenticated user data",
     *         @OA\JsonContent(ref="#/components/schemas/User")
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json(
            ['me' => $user]
        );
    }



    public function user(Request $request)
    {
        $user = $request->user();
        $userData = DB::connection('sistemas_comum')
            ->table('vw_usuarios_autenticacao_catraca')
            ->where('id_usuario', $user->id)
            ->first();
        return response()->json(
            $userData
        );
    }

    public function bond(Request $request)
    {
        $user = $request->user();
        $userData = DB::connection('sigaa')
            ->table('vw_usuarios_catraca')
            ->where('id_usuario', $user->id)
            ->get();
        if (config('app.env') !== 'production') {
            $userData = $userData->map(function ($item) {
                $item->cpf_cnpj = substr($item->cpf_cnpj, 0, 3) . '*****' . substr($item->cpf_cnpj, -2);
                $item->email = '*****' . strstr($item->email, '@');
                $item->telefone_celular = '*****' . substr($item->telefone_celular, -4);
                $item->siape = '*****';
                $item->passaporte = $item->passaporte ? substr($item->passaporte, 0, 2) . '*****' . substr($item->passaporte, -2) : null;
                return $item;
            });
        }
        return response()->json(
            $userData
        );
    }
}
