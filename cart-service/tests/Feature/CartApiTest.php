<?php

namespace Tests\Feature;

use App\Models\CartItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_an_item_and_adding_it_again_increases_its_quantity(): void
    {
        $first = $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 2,
            'quantity' => 2,
        ]);
        $first->assertCreated()
            ->assertJsonPath('message', 'Item berhasil ditambahkan ke keranjang')
            ->assertJsonPath('data.quantity', 2);

        $second = $this->postJson('/carts', [
            'user_id' => 1,
            'product_id' => 2,
            'quantity' => 3,
        ]);
        $second->assertOk()->assertJsonPath('data.quantity', 5);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_getting_a_cart_returns_its_items_and_total_quantity(): void
    {
        CartItem::create(['user_id' => 7, 'product_id' => 10, 'quantity' => 2]);
        CartItem::create(['user_id' => 7, 'product_id' => 11, 'quantity' => 4]);

        $this->getJson('/carts/7')
            ->assertOk()
            ->assertJsonPath('data.user_id', 7)
            ->assertJsonPath('data.total_quantity', 6)
            ->assertJsonCount(2, 'data.items');
    }

    public function test_invalid_add_item_payload_returns_json_validation_errors(): void
    {
        $this->postJson('/carts', ['user_id' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['user_id', 'product_id']);
    }

    public function test_updating_and_deleting_a_missing_item_returns_not_found(): void
    {
        $this->putJson('/carts/items/999', ['quantity' => 2])
            ->assertNotFound()
            ->assertJsonPath('message', 'Item keranjang dengan id 999 tidak ditemukan');

        $this->deleteJson('/carts/items/999')
            ->assertNotFound()
            ->assertJsonPath('message', 'Item keranjang dengan id 999 tidak ditemukan');
    }

    public function test_an_item_quantity_can_be_updated_and_the_item_can_be_deleted(): void
    {
        $item = CartItem::create([
            'user_id' => 7,
            'product_id' => 12,
            'quantity' => 2,
        ]);

        $this->putJson("/carts/items/{$item->id}", ['quantity' => 5])
            ->assertOk()
            ->assertJsonPath('data.quantity', 5);

        $this->deleteJson("/carts/items/{$item->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Item berhasil dihapus dari keranjang');

        $this->assertDatabaseMissing('cart_items', ['id' => $item->id]);
    }

    public function test_clearing_a_cart_reports_the_number_of_deleted_items(): void
    {
        CartItem::create(['user_id' => 8, 'product_id' => 20, 'quantity' => 1]);
        CartItem::create(['user_id' => 8, 'product_id' => 21, 'quantity' => 3]);

        $this->deleteJson('/carts/8')
            ->assertOk()
            ->assertJsonPath('data.deleted_items', 2);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_non_numeric_cart_ids_return_a_json_not_found_response(): void
    {
        $this->get('/carts/abc')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }

    public function test_unknown_endpoints_return_a_json_not_found_response(): void
    {
        $this->get('/unknown-endpoint')
            ->assertNotFound()
            ->assertJsonStructure(['message']);
    }
}
