<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MessageController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'all');

        $messages = ContactMessage::query()
            ->when($filter === 'unread', fn ($query) => $query->where('is_read', false))
            ->when($filter === 'read', fn ($query) => $query->where('is_read', true))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $unreadCount = ContactMessage::query()->where('is_read', false)->count();

        return view('admin.messages.index', compact('messages', 'filter', 'unreadCount'));
    }

    public function show(ContactMessage $message): View
    {
        if (! $message->is_read) {
            $message->update(['is_read' => true]);
        }

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }
}
