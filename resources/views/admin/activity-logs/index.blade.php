@extends('layouts.admin')

@section('title', 'Audit Trail')
@section('heading', 'Audit Trail')
@section('subheading', 'Riwayat login dan perubahan penting pada akun, toko, stok, dan pesanan.')

@section('content')
    <div class="mb-5 grid gap-3 sm:grid-cols-3">
        <article class="rounded-3xl border border-stone-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-stone-500">Aktivitas hari ini</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['today']) }}</p>
        </article>
        <article class="rounded-3xl border border-red-100 bg-red-50 p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-red-700">Login gagal hari ini</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['failed_logins']) }}</p>
        </article>
        <article class="rounded-3xl border border-amber-100 bg-amber-50 p-5 shadow-sm">
            <p class="text-xs font-extrabold uppercase tracking-wide text-amber-700">Akun aktif hari ini</p>
            <p class="mt-2 font-display text-2xl font-extrabold text-stone-900">{{ number_format($stats['unique_actors']) }}</p>
        </article>
    </div>

    <form action="{{ route('admin.audit-trail.index') }}" method="GET" class="mb-5 grid gap-3 rounded-3xl border border-stone-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-6">
        <div>
            <label for="from" class="mb-1 block text-xs font-extrabold text-stone-500">Dari tanggal</label>
            <input id="from" type="date" name="from" value="{{ $filters['from'] }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="to" class="mb-1 block text-xs font-extrabold text-stone-500">Sampai tanggal</label>
            <input id="to" type="date" name="to" value="{{ $filters['to'] }}" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div>
            <label for="actor_role" class="mb-1 block text-xs font-extrabold text-stone-500">Jenis pelaku</label>
            <select id="actor_role" name="actor_role" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Semua pelaku</option>
                @foreach (\App\Models\User::ROLE_LABELS as $role => $label)
                    <option value="{{ $role }}" @selected($filters['actor_role'] === $role)>{{ $label }}</option>
                @endforeach
                <option value="guest" @selected($filters['actor_role'] === 'guest')>Pengunjung / belum login</option>
            </select>
        </div>
        <div>
            <label for="action" class="mb-1 block text-xs font-extrabold text-stone-500">Jenis aktivitas</label>
            <select id="action" name="action" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
                <option value="">Semua aktivitas</option>
                @foreach ($actions as $action)
                    <option value="{{ $action }}" @selected($filters['action'] === $action)>{{ \App\Models\ActivityLog::labelForAction($action) }}</option>
                @endforeach
            </select>
        </div>
        <div class="xl:col-span-2">
            <label for="q" class="mb-1 block text-xs font-extrabold text-stone-500">Cari aktivitas</label>
            <input id="q" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Nama, email, pesanan, objek, atau IP" class="w-full rounded-xl border-stone-200 text-sm focus:border-amber-300 focus:ring-amber-200">
        </div>
        <div class="flex items-end gap-2 sm:col-span-2 xl:col-span-6">
            <button type="submit" class="rounded-xl bg-stone-900 px-5 py-2.5 text-xs font-extrabold text-white transition hover:bg-stone-700">Terapkan filter</button>
            <a href="{{ route('admin.audit-trail.index') }}" class="rounded-xl border border-stone-200 px-5 py-2.5 text-xs font-extrabold text-stone-600 transition hover:bg-stone-50">Reset</a>
        </div>
    </form>

    <section class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-sm">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-stone-100 px-5 py-4">
            <div>
                <h2 class="font-display text-lg font-extrabold text-stone-900">Riwayat aktivitas</h2>
                <p class="text-xs font-semibold text-stone-400">{{ number_format($logs->total()) }} catatan ditemukan · data mulai direkam setelah fitur audit diaktifkan</p>
            </div>
            <span class="rounded-full bg-stone-100 px-3 py-1 text-[11px] font-bold text-stone-500">{{ \Carbon\Carbon::parse($filters['from'])->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($filters['to'])->format('d/m/Y') }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[1050px] text-left text-sm">
                <thead><tr class="border-b border-stone-100 bg-stone-50 text-[10px] font-extrabold uppercase tracking-wider text-stone-400">
                    <th class="px-5 py-3">Waktu</th><th class="px-5 py-3">Pelaku</th><th class="px-5 py-3">Aktivitas</th><th class="px-5 py-3">Objek</th><th class="px-5 py-3">Sumber</th><th class="px-5 py-3">Detail</th>
                </tr></thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($logs as $log)
                        <tr class="align-top">
                            <td class="whitespace-nowrap px-5 py-4 text-xs font-semibold text-stone-500">{{ $log->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') }} WIB</td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-extrabold text-stone-800">{{ $log->actor_name ?: ($log->subject_label ?: 'Pengunjung') }}</p>
                                @if ($log->actor_email)
                                    <p class="mt-0.5 text-[11px] text-stone-500">{{ $log->actor_email }}</p>
                                @endif
                                <span class="mt-1 inline-flex rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-bold text-stone-500">{{ $log->actor_role ? (\App\Models\User::ROLE_LABELS[$log->actor_role] ?? $log->actor_role) : 'Tamu / belum login' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="text-xs font-extrabold text-stone-800">{{ $log->actionLabel() }}</p>
                                <p class="mt-1 max-w-sm text-xs leading-5 text-stone-500">{{ $log->description }}</p>
                            </td>
                            <td class="px-5 py-4 text-xs font-semibold text-stone-600">{{ $log->subject_label ?: '—' }}</td>
                            <td class="px-5 py-4 text-xs text-stone-500">
                                <p>{{ $log->ip_address ?: 'IP tidak tersedia' }}</p>
                                @if ($log->session_hash)
                                    <p class="mt-1 font-mono text-[10px] text-stone-400">Sesi {{ substr($log->session_hash, 0, 10) }}</p>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if ($log->properties)
                                    <details class="max-w-xs">
                                        <summary class="cursor-pointer text-xs font-extrabold text-amber-700">Lihat rincian</summary>
                                        <pre class="mt-2 max-h-56 overflow-auto rounded-xl bg-stone-50 p-3 text-[10px] leading-4 text-stone-600">{{ json_encode($log->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                    </details>
                                @else
                                    <span class="text-xs text-stone-400">—</span>
                                @endif
                                @if ($log->user_agent)
                                    <p class="mt-2 max-w-xs break-all text-[10px] leading-4 text-stone-400">{{ $log->user_agent }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-sm font-semibold text-stone-400">Tidak ada aktivitas yang cocok dengan filter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-5">{{ $logs->links('vendor.pagination.mamikhevan') }}</div>
    </section>
@endsection
