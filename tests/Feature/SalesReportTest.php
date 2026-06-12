<?php

use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('shows today sales by default', function () {
    Sale::factory()->create(['total' => 50, 'cash_amount' => 50]);
    Sale::factory()->create(['total' => 30, 'cash_amount' => 30, 'created_at' => now()->subDays(2)]);

    $this->get('/reports/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Reports/Index')
            ->has('sales.data', 1)
            ->where('summary.count', 1)
        );
});

it('filters sales by date range', function () {
    Sale::factory()->create(['total' => 50, 'created_at' => now()->subDays(5)]);
    Sale::factory()->create(['total' => 30, 'created_at' => now()->subDays(3)]);
    Sale::factory()->create(['total' => 20]);

    $from = now()->subDays(6)->toDateString();
    $to = now()->subDays(2)->toDateString();

    $this->get("/reports/sales?from={$from}&to={$to}")
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sales.data', 2)
            ->where('summary.count', 2)
        );
});

it('paginates sales server side', function () {
    Sale::factory()->count(20)->create();

    $this->get('/reports/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sales.data', 15)
            ->where('sales.total', 20)
            ->where('sales.last_page', 2)
            ->where('summary.count', 20)
        );

    $this->get('/reports/sales?page=2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('sales.data', 5));
});

it('sums totals by payment type', function () {
    Sale::factory()->create(['total' => 40, 'cash_amount' => 40, 'qr_amount' => 0]);
    Sale::factory()->create(['total' => 60, 'cash_amount' => 20, 'qr_amount' => 40, 'payment_method' => 'mixto']);

    $this->get('/reports/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('summary.total', 100)
            ->where('summary.cash', 60)
            ->where('summary.qr', 40)
        );
});

it('rejects an invalid date range', function () {
    $this->get('/reports/sales?from=2026-06-10&to=2026-06-01')
        ->assertSessionHasErrors('to');
});
