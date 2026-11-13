<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MessageReplyRequest;
use App\Mail\ContactReplyMail;
use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

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

    public function reply(MessageReplyRequest $request, ContactMessage $message): RedirectResponse
    {
        $validated = $request->validated();

        try {
            Mail::to($message->email, $message->name)->send(new ContactReplyMail(
                $message,
                $validated['reply_subject'],
                $validated['reply_body'],
            ));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->with('error', 'Balasan gagal dikirim. Periksa konfigurasi email server lalu coba lagi.');
        }

        $message->update(['replied_at' => now()]);

        return back()->with('success', 'Balasan berhasil dikirim ke '.$message->email.'.');
    }

    public function destroy(ContactMessage $message): RedirectResponse
    {
        $message->delete();

        return redirect()->route('admin.messages.index')
            ->with('success', 'Pesan berhasil dihapus.');
    }
}
