<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\JsonResponse;
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

    public function print(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $date = $validated['date'] ?? now()->toDateString();

        $sales = Sale::whereBetween('created_at', [$date.' 00:00:00', $date.' 23:59:59'])
            ->with('user:id,name')
            ->latest()
            ->get();

        $cashIncome = 0.0;
        $qrIncome = 0.0;

        foreach ($sales as $sale) {
            $total = (float) $sale->total;

            if ($sale->payment_method === 'qr') {
                $qrIncome += $total;
            } elseif ($sale->payment_method === 'mixto') {
                $qrPart = min((float) $sale->qr_amount, $total);
                $qrIncome += $qrPart;
                $cashIncome += $total - $qrPart;
            } else {
                $cashIncome += $total;
            }
        }

        return response()->json([
            'date' => $date,
            'sales' => $sales,
            'summary' => [
                'count' => $sales->count(),
                'total' => round((float) $sales->sum('total'), 2),
                'cash' => round($cashIncome, 2),
                'qr' => round($qrIncome, 2),
            ],
        ]);
    }
}
