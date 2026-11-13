<?php

namespace App\Http\Controllers;

use App\Services\PromotionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GiveawayController extends Controller
{
    public const SESSION_KEY = 'giveaway_voucher';

    public function __construct(private readonly PromotionService $promotions) {}

    public function index(Request $request): View
    {
        $voucherCode = $request->session()->get(self::SESSION_KEY);
        $giveaway = $this->promotions->giveawayFor($voucherCode);

        return view('giveaway.claim', compact('giveaway', 'voucherCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:40'],
        ]);

        $giveaway = $this->promotions->giveawayFor($validated['code']);

        if ($giveaway === null) {
            return back()
                ->withErrors(['code' => 'Kode giveaway tidak ditemukan, sudah berakhir, atau stok hadiah habis.'])
                ->withInput();
        }

        $request->session()->put(self::SESSION_KEY, mb_strtoupper(trim($validated['code'])));

        return redirect()->route('checkout.index')
            ->with('success', 'Hadiah "'.$giveaway['gift_product']->name.'" berhasil diklaim. Lengkapi alamat untuk menghitung ongkir.');
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget(self::SESSION_KEY);

        return redirect()->route('giveaway.claim')->with('success', 'Klaim hadiah dibatalkan.');
    }
}
