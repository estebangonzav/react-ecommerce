<?php

namespace App\Http\Controllers;

use App\Models\User as ModelsUser;
use Illuminate\Foundation\Auth\User;
// use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|max:191|unique:users,email,except,id',
            'password' => 'required|min:8',
        ]);


        if ($validator->fails()) {

            return response()->json([
                'validation_errors' => $validator->messages(),
                // 'validation_errors' => $validator,
            ]);
        } else {

            $user = ModelsUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);
            $token = $user->createToken($user->email . '_Token')->plainTextToken;

            return response()->json([
                'status' => 200,
                'username' => $user->name,
                'token' => $token,
                'message' => 'Regitrado correctamente',
            ]);
        }
    }

    public function login(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'email' => 'required|max:191',
            'password' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json([
                'validation_errors' => $validator->messages(),
                // 'validation_errors' => $validator,
            ]);
        } else {

            $user = ModelsUser::where('email', $request->email)->first();
            // var_dump($user);

            if (!$user || !Hash::check($request->password, $user->password)) {
                throw ValidationException::withMessages([
                    'status' => 401,
                    'message' => 'Datos Incorrectos',

                ]);
            } else {

                $token = $user->createToken($user->email . '_Token')->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'username' => $user->name,
                    'token' => $token,
                    'message' => 'Logged Success',
                ]);
            }
        }
    }

    public function logout()
    {

        auth()->user()->tokens()->delete();
        return response()->json([
            'status' => 200,
            'message' => 'Logged out success',
        ]);
    }
}
