<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists categories', function () {
    Category::factory()->count(3)->create();

    $this->get('/categories')->assertOk();
});

it('creates a category', function () {
    $this->post('/categories', [
        'name' => 'Bebidas',
        'is_active' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('categories', ['name' => 'Bebidas', 'is_active' => true]);
});

it('updates a category', function () {
    $category = Category::factory()->create(['name' => 'Viejo']);

    $this->put("/categories/{$category->id}", [
        'name' => 'Nuevo',
        'is_active' => false,
    ])->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $category->id, 'name' => 'Nuevo', 'is_active' => false]);
});

it('soft deletes a category without products', function () {
    $category = Category::factory()->create();

    $this->delete("/categories/{$category->id}")->assertRedirect();

    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});

it('refuses to delete a category with products', function () {
    $product = Product::factory()->create();

    $this->delete("/categories/{$product->category_id}")->assertRedirect();

    $this->assertDatabaseHas('categories', ['id' => $product->category_id]);
});

it('toggles a category active state', function () {
    $category = Category::factory()->create(['is_active' => true]);

    $this->patch("/categories/{$category->id}/toggle-active")->assertRedirect();

    expect($category->refresh()->is_active)->toBeFalse();

    $this->patch("/categories/{$category->id}/toggle-active");

    expect($category->refresh()->is_active)->toBeTrue();
});

it('validates the category name is required', function () {
    $this->post('/categories', ['name' => '', 'is_active' => true])
        ->assertSessionHasErrors('name');
});
