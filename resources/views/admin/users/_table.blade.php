<div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-5 py-4">Nama</th>
                    <th class="px-5 py-4">Email</th>
                    @if ($showRole)
                        <th class="px-5 py-4">Peran</th>
                    @endif
                    <th class="px-5 py-4">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse ($users as $user)
                    <tr>
                        <td class="px-5 py-4 font-bold text-stone-800">{{ $user->name }}</td>
                        <td class="px-5 py-4 text-stone-600">{{ $user->email }}</td>
                        @if ($showRole)
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-extrabold text-amber-700">{{ $user->roleLabel() }}</span>
                            </td>
                        @endif
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('admin.users.edit', $user) }}" class="font-bold text-amber-700 hover:underline">Ubah</a>
                                @if (! $user->is(auth()->user()))
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus akun {{ $user->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="font-bold text-red-600 hover:underline">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ $showRole ? 4 : 3 }}" class="px-5 py-10 text-center text-stone-500">{{ $emptyMessage }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($users->hasPages())
        <div class="border-t border-stone-100 p-4">{{ $users->links() }}</div>
    @endif
</div>
