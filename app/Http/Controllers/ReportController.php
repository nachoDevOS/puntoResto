<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    public function index(Request $request): Response
    {
        $validated = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $from = $validated['from'] ?? now()->toDateString();
        $to = $validated['to'] ?? $from;

        $query = Sale::whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']);

        $totals = (clone $query)
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(total), 0) as total, COALESCE(SUM(cash_amount), 0) as cash, COALESCE(SUM(qr_amount), 0) as qr')
            ->first();

        $sales = (clone $query)
            ->with(['details', 'user:id,name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Reports/Index', [
            'sales' => $sales,
            'filters' => ['from' => $from, 'to' => $to],
            'summary' => [
                'count' => (int) $totals->count,
                'total' => (float) $totals->total,
                'cash' => (float) $totals->cash,
                'qr' => (float) $totals->qr,
            ],
        ]);
    }
}
