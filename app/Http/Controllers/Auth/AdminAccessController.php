<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminAccessRequest;
use App\Models\User;
use App\Notifications\AdminAccessRequested;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

class AdminAccessController extends Controller
{
    private function aktif(): void
    {
        abort_unless(
            config('app.admin_request_enabled') && config('app.admin_approver_email'),
            404
        );
    }

    public function form()
    {
        $this->aktif();

        return view('admin-access.form');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->aktif();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'reason' => ['nullable', 'string', 'max:300'],
        ]);

        $req = AdminAccessRequest::create($data + ['ip_address' => $request->ip()]);

        $url = URL::temporarySignedRoute(
            'admin-access.review', now()->addMinutes(60), ['accessRequest' => $req->id]
        );

        try {
            Notification::route('mail', config('app.admin_approver_email'))
                ->notify(new AdminAccessRequested($req, $url));
        } catch (\Throwable $e) {
            report($e);
            $req->delete();

            return back()->withInput()->withErrors(['name' => 'Email permintaan gagal dikirim. Coba lagi nanti.']);
        }

        $request->session()->put('admin_request_id', $req->id);

        return redirect()->route('admin-access.waiting');
    }

    public function waiting()
    {
        $this->aktif();

        $req = AdminAccessRequest::find(session('admin_request_id'));
        abort_unless($req, 404);

        return view('admin-access.waiting', ['req' => $req]);
    }

    public function status(Request $request): JsonResponse
    {
        $this->aktif();

        $req = AdminAccessRequest::find($request->session()->get('admin_request_id'));

        return response()->json(['status' => $req?->effectiveStatus() ?? 'none']);
    }

    public function claim(Request $request): RedirectResponse
    {
        $this->aktif();

        $req = AdminAccessRequest::find($request->session()->get('admin_request_id'));

        if (! $req || $req->effectiveStatus() !== 'approved') {
            return redirect()->route('login')->withErrors(['email' => 'Izin akses admin belum disetujui atau sudah kedaluwarsa.']);
        }

        $admin = User::role('admin')->orderBy('id')->first();
        abort_unless($admin, 404);

        $req->update(['status' => 'used', 'used_at' => now()]);

        Auth::login($admin);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    // Halaman tinjau dari emailmu (GET menampilkan, POST memutuskan; keduanya tautan bertanda tangan)
    public function review(AdminAccessRequest $accessRequest)
    {
        $this->aktif();

        return view('admin-access.review', ['req' => $accessRequest]);
    }

    public function decide(Request $request, AdminAccessRequest $accessRequest)
    {
        $this->aktif();

        $request->validate(['decision' => ['required', 'in:approve,deny']]);

        if ($accessRequest->status !== 'pending') {
            return view('admin-access.review', ['req' => $accessRequest, 'selesai' => 'Permintaan ini sudah diproses.']);
        }

        if ($request->input('decision') === 'approve') {
            $accessRequest->update(['status' => 'approved', 'approved_at' => now()]);
            $pesan = 'Disetujui. Pengunjung akan masuk otomatis (berlaku 15 menit).';
        } else {
            $accessRequest->update(['status' => 'denied']);
            $pesan = 'Ditolak.';
        }

        return view('admin-access.review', ['req' => $accessRequest->fresh(), 'selesai' => $pesan]);
    }
}