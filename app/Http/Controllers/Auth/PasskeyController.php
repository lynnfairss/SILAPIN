<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use LaravelWebauthn\Facades\Webauthn;
use LaravelWebauthn\Models\WebauthnKey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\Util\Base64;

class PasskeyController extends Controller
{
    public function options(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            throw ValidationException::withMessages(['error' => 'Akun dengan email tersebut tidak ditemukan.']);
        }

        if (! $user->isSuperAdmin() && ! $user->isAdmin()) {
            throw ValidationException::withMessages(['error' => 'Akun ini tidak memiliki akses ke panel admin.']);
        }

        if ($user->webauthnKeys()->count() === 0) {
            throw ValidationException::withMessages(['error' => 'Akun ini belum memiliki passkey.']);
        }

        $request->session()->put('passkey_role', $request->input('role'));

        return response()->json([
            'publicKey' => Webauthn::prepareAssertion($user),
        ]);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'id' => 'required|string',
            'rawId' => 'required|string',
            'type' => 'required|string',
            'response.authenticatorData' => 'required|string',
            'response.clientDataJSON' => 'required|string',
            'response.signature' => 'required|string',
            'response.userHandle' => 'nullable',
        ]);

        $credentialId = Base64UrlSafe::encode(Base64::decode($credentials['id']));
        $key = WebauthnKey::where('credentialId', $credentialId)
            ->orWhere('credentialId', Base64UrlSafe::encodeUnpadded(Base64::decode($credentials['id'])))
            ->first();

        if (! $key) {
            throw ValidationException::withMessages(['error' => 'Passkey tidak dikenali.']);
        }

        $user = User::find($key->user_id);

        if (! $user) {
            throw ValidationException::withMessages(['error' => 'Akun tidak ditemukan.']);
        }

        $role = $request->session()->pull('passkey_role');

        $allowed = $role === 'super_admin'
            ? $user->isSuperAdmin()
            : ($user->isSuperAdmin() || $user->isAdmin());

        if (! $allowed) {
            throw ValidationException::withMessages(['error' => 'Akun ini tidak memiliki akses ke panel yang dituju.']);
        }

        try {
            Webauthn::validateAssertion($user, $credentials);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['error' => 'Verifikasi passkey gagal. Silakan coba lagi.']);
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return response()->json([
            'result' => true,
            'callback' => route('dashboard', absolute: false),
        ]);
    }

    public function registerOptions(Request $request): JsonResponse
    {
        return response()->json([
            'publicKey' => Webauthn::prepareAttestation($request->user()),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'id' => 'required|string',
            'rawId' => 'required|string',
            'type' => 'required|string',
            'response.clientDataJSON' => 'required|string',
            'response.attestationObject' => 'required|string',
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $key = Webauthn::validateAttestation(
                $request->user(),
                $credentials,
                $request->input('name') ?: 'Passkey'
            );
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['error' => 'Registrasi passkey gagal. Silakan coba lagi.']);
        }

        return response()->json([
            'result' => $key,
            'callback' => route('security', absolute: false),
        ], 201);
    }

    public function destroy(Request $request, WebauthnKey $key): JsonResponse
    {
        abort_unless($key->user_id === $request->user()->getAuthIdentifier(), 403);

        $key->delete();

        return response()->json(['result' => true]);
    }
}
