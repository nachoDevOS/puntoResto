<?php

use App\Models\Sale;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists all sales regardless of date', function () {
    Sale::factory()->create();
    Sale::factory()->create(['created_at' => now()->subMonths(3)]);

    $this->get('/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('Sales/Index')
            ->has('sales.data', 2)
        );
});

it('paginates sales server side at 15 per page', function () {
    Sale::factory()->count(20)->create();

    $this->get('/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sales.data', 15)
            ->where('sales.total', 20)
            ->where('sales.last_page', 2)
        );

    $this->get('/sales?page=2')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('sales.data', 5));
});

it('soft deletes a sale', function () {
    $sale = Sale::factory()->create();

    $this->delete("/sales/{$sale->id}")->assertRedirect();

    $this->assertSoftDeleted('sales', ['id' => $sale->id]);
});

it('hides deleted sales from the active list', function () {
    $sale = Sale::factory()->create();
    $sale->delete();

    $this->get('/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page->has('sales.data', 0));
});

it('shows only deleted sales when filtered', function () {
    Sale::factory()->create();
    $deleted = Sale::factory()->create();
    $deleted->delete();

    $this->get('/sales?status=eliminadas')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('sales.data', 1)
            ->where('sales.data.0.id', $deleted->id)
            ->where('filters.status', 'eliminadas')
        );
});

it('excludes deleted sales from the report totals', function () {
    Sale::factory()->create(['total' => 50, 'cash_amount' => 50]);
    $deleted = Sale::factory()->create(['total' => 30, 'cash_amount' => 30]);
    $deleted->delete();

    $this->get('/reports/sales')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('summary.count', 1)
            ->where('summary.total', 50)
        );
});

it('rejects an invalid status filter', function () {
    $this->get('/sales?status=foo')->assertSessionHasErrors('status');
});

it('redirects guests to the login page', function () {
    auth()->logout();

    $this->get('/sales')->assertRedirect('/login');
});
