<?php

use App\Models\Product;
use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('shows the pos screen with active products only', function () {
    Product::factory()->create(['name' => 'Activo']);
    Product::factory()->create(['name' => 'Inactivo', 'is_active' => false]);

    $this->get('/pos')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Pos/Index')
            ->has('products', 1)
        );
});

it('registers a table sale with cash payment', function () {
    $product = Product::factory()->create(['price' => 15.00]);

    $this->post('/pos/sales', [
        'type' => 'mesa',
        'table_number' => 4,
        'payment_method' => 'efectivo',
        'cash_amount' => 30.00,
        'qr_amount' => 0,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 2],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $sale = Sale::first();

    expect($sale->total)->toBe('30.00')
        ->and($sale->type)->toBe('mesa')
        ->and($sale->table_number)->toBe(4)
        ->and($sale->user_id)->toBe($this->user->id)
        ->and($sale->details)->toHaveCount(1)
        ->and($sale->details->first()->subtotal)->toBe('30.00');
});

it('registers a takeaway sale with mixed payment', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    $this->post('/pos/sales', [
        'type' => 'llevar',
        'table_number' => null,
        'payment_method' => 'mixto',
        'cash_amount' => 5.00,
        'qr_amount' => 5.00,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    $sale = Sale::first();

    expect($sale->payment_method)->toBe('mixto')
        ->and($sale->table_number)->toBeNull();
});

it('snapshots product name and price in the sale detail', function () {
    $product = Product::factory()->create(['name' => 'Lomo', 'price' => 18.00]);

    $this->post('/pos/sales', [
        'type' => 'llevar',
        'table_number' => null,
        'payment_method' => 'efectivo',
        'cash_amount' => 18.00,
        'qr_amount' => 0,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ]);

    $this->assertDatabaseHas('sale_details', [
        'product_id' => $product->id,
        'product_name' => 'Lomo',
        'price' => 18.00,
    ]);
});

it('rejects a sale when payment is insufficient', function () {
    $product = Product::factory()->create(['price' => 20.00]);

    $this->post('/pos/sales', [
        'type' => 'llevar',
        'table_number' => null,
        'payment_method' => 'efectivo',
        'cash_amount' => 10.00,
        'qr_amount' => 0,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertSessionHas('error');

    expect(Sale::count())->toBe(0);
});

it('rejects a sale with an inactive product', function () {
    $product = Product::factory()->create(['price' => 10.00, 'is_active' => false]);

    $this->post('/pos/sales', [
        'type' => 'llevar',
        'table_number' => null,
        'payment_method' => 'efectivo',
        'cash_amount' => 10.00,
        'qr_amount' => 0,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertSessionHas('error');

    expect(Sale::count())->toBe(0);
});

it('allows a table sale without a table number', function () {
    $product = Product::factory()->create(['price' => 10.00]);

    $this->post('/pos/sales', [
        'type' => 'mesa',
        'table_number' => null,
        'payment_method' => 'efectivo',
        'cash_amount' => 10.00,
        'qr_amount' => 0,
        'items' => [
            ['product_id' => $product->id, 'quantity' => 1],
        ],
    ])->assertRedirect()->assertSessionHas('success');

    expect(Sale::first()->table_number)->toBeNull();
});

it('requires at least one item', function () {
    $this->post('/pos/sales', [
        'type' => 'llevar',
        'table_number' => null,
        'payment_method' => 'efectivo',
        'cash_amount' => 10,
        'qr_amount' => 0,
        'items' => [],
    ])->assertSessionHasErrors('items');
});
