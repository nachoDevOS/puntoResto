<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\SaleDetail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->actingAs(User::factory()->create());
});

it('lists products', function () {
    Product::factory()->count(3)->create();

    $this->get('/products')->assertOk();
});

it('creates a product without photo', function () {
    $category = Category::factory()->create();

    $this->post('/products', [
        'category_id' => $category->id,
        'name' => 'Lomo',
        'price' => 18.00,
        'is_active' => true,
    ])->assertRedirect();

    $this->assertDatabaseHas('products', ['name' => 'Lomo', 'category_id' => $category->id]);
});

it('creates a product with photo', function () {
    Storage::fake('public');
    $category = Category::factory()->create();

    $this->post('/products', [
        'category_id' => $category->id,
        'name' => 'Pechuga Broaster',
        'price' => 15.00,
        'photo' => UploadedFile::fake()->image('pechuga.jpg'),
        'is_active' => true,
    ])->assertRedirect();

    $product = Product::firstWhere('name', 'Pechuga Broaster');

    expect($product->photo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($product->photo_path);
});

it('updates a product and replaces its photo', function () {
    Storage::fake('public');
    $product = Product::factory()->create([
        'photo_path' => UploadedFile::fake()->image('old.jpg')->store('products', 'public'),
    ]);
    $oldPath = $product->photo_path;

    $this->put("/products/{$product->id}", [
        'category_id' => $product->category_id,
        'name' => 'Actualizado',
        'price' => 20.00,
        'photo' => UploadedFile::fake()->image('new.jpg'),
        'is_active' => false,
    ])->assertRedirect();

    $product->refresh();

    expect($product->name)->toBe('Actualizado')
        ->and($product->is_active)->toBeFalse()
        ->and($product->photo_path)->not->toBe($oldPath);

    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($product->photo_path);
});

it('soft deletes a product', function () {
    $product = Product::factory()->create();

    $this->delete("/products/{$product->id}")->assertRedirect();

    $this->assertSoftDeleted('products', ['id' => $product->id]);
});

it('keeps sale history when a sold product is deleted', function () {
    $detail = SaleDetail::factory()->create();

    $this->delete("/products/{$detail->product_id}")->assertRedirect();

    $this->assertSoftDeleted('products', ['id' => $detail->product_id]);
    $this->assertDatabaseHas('sale_details', ['id' => $detail->id]);
});

it('hides soft deleted products from the pos', function () {
    $product = Product::factory()->create();
    $product->delete();

    $this->get('/pos')->assertInertia(fn ($page) => $page->has('products', 0));
});

it('toggles a product active state', function () {
    $product = Product::factory()->create(['is_active' => true]);

    $this->patch("/products/{$product->id}/toggle-active")->assertRedirect();

    expect($product->refresh()->is_active)->toBeFalse();

    $this->patch("/products/{$product->id}/toggle-active");

    expect($product->refresh()->is_active)->toBeTrue();
});

it('validates required fields', function () {
    $this->post('/products', [])
        ->assertSessionHasErrors(['category_id', 'name', 'price', 'is_active']);
});
