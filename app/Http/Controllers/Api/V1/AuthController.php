<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register', 'me']]);
    }

    /**
     * Register a new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|min:3|max:255',
            'last_name' => 'nullable|string|min:3|max:255',
            'user_name' => 'required|string|min:3|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if ($validator->fails()) {
            return $this->respondWithError($validator->errors()->first());
        }

        try {
            User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'user_name' => $request->user_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 2,
            ]);

            return $this->respondWithSuccess(null, 'User signed up successfully!');
        } catch (\Throwable $th) {
            return $this->respondWithError('Error occurred while signing up.');
        }
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login' => 'required|string|max:255',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->respondWithError($validator->errors()->first());
        }

        $user = User::where(function ($query) use ($request) {
            $query->where('email', $request->login)
                ->orWhere('user_name', $request->login)
                ->orWhere('phone', $request->login);
        })->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->respondWithError('Invalid email or password!');
        }

        $credentials = [
            'email' => $user->email,
            'password' => $request->password,
        ];

        if (! $token = auth('user')->attempt($credentials)) {
            return response()->json(['success' => false, 'message' => 'Invalid Credentials'], 401);
        }

        $user = auth('user')->user()->only('id', 'first_name', 'last_name', 'user_name', 'email', 'phone', 'image', 'status');

        return response()->json([
            'success' => true,
            'access_token' => $token,
            'data' => $user,
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        // return response()->json(auth('user')->user());
        return $this->respondWithSuccess(auth('user')->user(), 'User Profile!');
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth('user')->logout();

        return $this->respondWithSuccess(null, 'User successfully logged out!');
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithSuccess(auth('user')->refresh(), 'Refresh token');
        // return $this->respondWithToken(auth('user')->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            // 'expires_in' => auth('user')->factory()->getTTL() * 60
            // 'expires_in' => Auth::user()->tokens()->latest()->first()->expires_at->timestamp,
        ]);
    }
}
