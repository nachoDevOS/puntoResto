<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PosController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Pos/Index', [
            'categories' => Category::where('is_active', true)
                ->whereHas('products', fn ($query) => $query->where('is_active', true))
                ->orderBy('name')
                ->get(['id', 'name']),
            'products' => Product::with('category:id,name')
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(StoreSaleRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $products = Product::whereIn('id', collect($validated['items'])->pluck('product_id'))
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        $details = [];
        $total = 0;

        foreach ($validated['items'] as $item) {
            $product = $products->get($item['product_id']);

            if ($product === null) {
                return back()->with('error', 'Producto no disponible.');
            }

            $subtotal = round((float) $product->price * $item['quantity'], 2);
            $total += $subtotal;

            $details[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'subtotal' => $subtotal,
            ];
        }

        $cashAmount = round((float) $validated['cash_amount'], 2);
        $qrAmount = round((float) $validated['qr_amount'], 2);

        if (round($cashAmount + $qrAmount, 2) < round($total, 2)) {
            return back()->with('error', 'El monto pagado es menor al total.');
        }

        $sale = DB::transaction(function () use ($request, $validated, $details, $total, $cashAmount, $qrAmount): Sale {
            $ticketDate = now();

            $sale = Sale::create([
                'user_id' => $request->user()->id,
                'type' => $validated['type'],
                'table_number' => $validated['type'] === 'mesa' ? $validated['table_number'] : null,
                'payment_method' => $validated['payment_method'],
                'cash_amount' => $cashAmount,
                'qr_amount' => $qrAmount,
                'total' => round($total, 2),
                'ticket_date' => $ticketDate->toDateString(),
                'ticket_number' => Sale::nextTicketNumberForDate($ticketDate),
            ]);

            $sale->details()->createMany($details);

            return $sale->load('details');
        });

        return back()
            ->with('success', 'Venta registrada.')
            ->with('print_sale', $this->printSalePayload($sale));
    }

    /**
     * @return array{
     *     id: int,
     *     ticket: string,
     *     typeSale: string,
     *     observation: string|null,
     *     created_at: string|null,
     *     sale_details: array<int, array{quantity: int|float, amount: float, item: array{name: string}}>
     * }
     */
    private function printSalePayload(Sale $sale): array
    {
        return [
            'id' => $sale->id,
            'ticket' => str_pad((string) ($sale->ticket_number ?? $sale->id), 6, '0', STR_PAD_LEFT),
            'typeSale' => $sale->type,
            'observation' => $sale->table_number ? 'Mesa '.$sale->table_number : null,
            'created_at' => $sale->created_at?->toDateTimeString(),
            'sale_details' => $sale->details
                ->map(function ($detail): array {
                    $quantity = (float) $detail->quantity;

                    return [
                        'quantity' => fmod($quantity, 1.0) === 0.0 ? (int) $quantity : $quantity,
                        'amount' => (float) $detail->subtotal,
                        'item' => [
                            'name' => $detail->product_name,
                        ],
                    ];
                })
                ->values()
                ->all(),
        ];
    }
}
