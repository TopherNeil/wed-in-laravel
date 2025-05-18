<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    use Response;
    
    private $messages = [
            'name.required' => 'Name is required, please fill out the field.',
            'name.min' => 'Name does not meet the minimun required length.',
            'name.max' => 'Name does not meet the maximum required length.',
            'email.required' => 'Email is required, please fill out the field.',
            'email.unique' => 'Email is already taken, please use another valid email address.',
            'email.email' => 'Email is not a valid email address.',
            'password.required' => 'Password is required, please fill out the field.',
            'password.confirmed' => 'Password confirmation does not match the Password.'
    ];

    public function register(Request $request)
    {
        $validatedForm = Validator::make($request->only([
            'name', 'email', 'password', 'password_confirmation',
        ]), [
            'name' => 'required|min:3|max:75',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed'
        ], $this->messages);

        try {
            if ($validatedForm->fails()) {
                return $this->error(['Error message' => $validatedForm->errors()], 422);
            }

            $data = [
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ];

            User::create($data);
            return $this->success(['Message' => 'Sucessfully created an account!', 'Data' => $data], 201);

        } catch (\Exception $e) {
            report($e);
            return $this->error(['Error message' => $e->getMessage()]);
        }
    }

    public function login(Request $request)
    {
        $validatedForm = Validator::make($request->only([
            'email', 'password'
        ]), [
            'email' => 'required|email',
            'password' => 'required'
        ], $this->messages);
        
        try {
            $data = [
                'email' => $request->email,
                'password' => $request->password,
            ];

            if ($validatedForm->fails()) {
                return $this->error(['Error message' => $validatedForm->errors()], 422);
            }
            if (!Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
                return $this->error(['message' => 'You have entered invalid credentials.'], 401);
            } else {
                $user = $request->user();
                $token = $user->createToken('user_token')->plainTextToken;
                return $this->success(['Message' => 'Successful login.', 'token' => $token]);
            }
        } catch (\Exception $e) {
            report($e);
            return $this->error(['Error message' => $e->getMessage()]);
        }
    }
}
