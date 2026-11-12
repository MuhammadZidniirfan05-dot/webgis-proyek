@php($sedangEdit = $user->exists)

<label class="block text-sm">Nama
    <input name="name" value="{{ old('name', $user->name) }}" required maxlength="100"
           class="w-full border-gray-300 rounded">
</label>

<label class="block text-sm">Email
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255"
           class="w-full border-gray-300 rounded">
    @if ($sedangEdit && str_ends_with($user->email, '@contoh.test'))
        <span class="text-xs text-amber-700">Akun demo: mengubah emailnya merusak tombol masuk sekali klik.</span>
    @endif
</label>

<label class="block text-sm">Role
    <select name="role" class="w-full border-gray-300 rounded">
        @foreach ($roles as $kode => $nama)
            <option value="{{ $kode }}" @selected(old('role', $roleSekarang ?? 'klien') === $kode)>{{ $nama }}</option>
        @endforeach
    </select>
</label>

<label class="block text-sm">Password
    @if ($sedangEdit)
        <span class="text-gray-400 text-xs">(kosongkan bila tidak diganti)</span>
    @endif
    <input type="password" name="password" autocomplete="new-password" @required(! $sedangEdit)
           class="w-full border-gray-300 rounded">
    <span class="text-xs text-gray-400">Minimal 8 karakter.</span>
</label>

<label class="block text-sm">Ulangi password
    <input type="password" name="password_confirmation" autocomplete="new-password"
           class="w-full border-gray-300 rounded">
</label>