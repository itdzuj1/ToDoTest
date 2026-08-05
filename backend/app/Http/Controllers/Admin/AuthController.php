<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\AdminResource;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class AuthController extends Controller
{
    public function csrfCookie(): Response
    {
        return response()->noContent();
    }

    public function login(LoginRequest $request): AdminResource
    {
        $authenticated = Auth::guard('admin')->attempt(
            [
                'email' => $request->string('email')->toString(),
                'password' => $request->string('password')->toString(),
            ],
        );

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are invalid.'],
            ]);
        }

        $request->session()->regenerate();

        return new AdminResource($this->authenticatedAdmin());
    }

    public function show(): AdminResource
    {
        return new AdminResource($this->authenticatedAdmin());
    }

    public function logout(Request $request): Response
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }

    private function authenticatedAdmin(): Admin
    {
        $admin = Auth::guard('admin')->user();

        if (! $admin instanceof Admin) {
            throw new RuntimeException('The admin guard returned an unexpected user type.');
        }

        return $admin;
    }
}
