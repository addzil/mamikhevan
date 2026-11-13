@if ($errors->any())
    <div class="rounded-2xl bg-red-50 p-4 text-sm text-red-700">
        <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
    </div>
@endif

<label class="block text-sm font-bold text-stone-700">Nama
    <input name="name" value="{{ old('name', $user->name) }}" required maxlength="255" class="mt-2 w-full rounded-xl border-stone-200">
</label>
<label class="block text-sm font-bold text-stone-700">Email login
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="255" class="mt-2 w-full rounded-xl border-stone-200">
</label>
<label class="block text-sm font-bold text-stone-700">Peran
    <select name="role" required class="mt-2 w-full rounded-xl border-stone-200">
        @foreach ($roles as $value => $label)
            <option value="{{ $value }}" @selected(old('role', $user->role ?? 'customer') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
<label class="block text-sm font-bold text-stone-700">Kata sandi{{ $user->exists ? ' baru (opsional)' : '' }}
    <input type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="12" autocomplete="new-password" class="mt-2 w-full rounded-xl border-stone-200">
</label>
<label class="block text-sm font-bold text-stone-700">Ulangi kata sandi
    <input type="password" name="password_confirmation" {{ $user->exists ? '' : 'required' }} minlength="12" autocomplete="new-password" class="mt-2 w-full rounded-xl border-stone-200">
</label>
<div class="flex justify-end gap-3">
    <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-stone-200 px-4 py-3 text-sm font-bold text-stone-600">Kembali</a>
    <button class="rounded-xl bg-amber-500 px-5 py-3 text-sm font-extrabold text-white hover:bg-amber-600">{{ $submitLabel }}</button>
</div>
