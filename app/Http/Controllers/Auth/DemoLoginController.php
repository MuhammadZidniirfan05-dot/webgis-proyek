<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    // Admin sengaja tidak ada di sini: admin hanya lewat izin email
    private const EMAIL = [
        'manajer_proyek' => 'manajer@contoh.test',
        'petugas_lapangan' => 'petugas@contoh.test',
        'klien' => 'klien@contoh.test',
    ];

    public function __invoke(Request $request, string $role): RedirectResponse
    {
        abort_unless(config('app.show_demo_accounts'), 404);
        abort_unless(
            isset(self::EMAIL[$role]) && in_array($role, config('app.demo_roles'), true),
            404
        );

        $user = User::where('email', self::EMAIL[$role])->firstOrFail();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }
}