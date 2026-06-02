<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail; 
use Tests\TestCase;

class ReviewValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sistem_menolak_ulasan_melebihi_batas_karakter()
    {
        Mail::fake();

        // 1. Persiapan data
        
        // Buat buyer
        $buyer = User::factory()->create(['role' => 'user']);

        // Buat seller  
        $sellerUser = User::factory()->create(['role' => 'seller']);
        $seller = Seller::create([
            'user_id' => $sellerUser->id,
            'store_name' => 'Toko Unit Test',
            'pic_name' => 'Agathan Khairy',
            'pic_phone' => '081234567890',
            'pic_email' => 'seller-test@example.com',
            'password' => 'Password123',
            'pic_street' => 'Jl. Testing No. 1',
            'pic_rt' => '001',
            'pic_rw' => '002',
            'pic_village' => 'Testing',
            'pic_district' => 'Semarang',
            'pic_city' => 'Semarang',
            'pic_province' => 'Jawa Tengah',
            'pic_ktp_number' => 'KTP1234567890',
            'status' => 'ACTIVE',
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
        ]);

        // Buat category
        $category = Category::create([
            'name' => 'Elektronik',
            'slug' => 'elektronik',
            'is_active' => true,
        ]);

        // Buat produk
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $category->id,
            'name' => 'Produk Untuk Direview',
            'slug' => 'produk-untuk-direview',
            'description' => 'Deskripsi produk ini',
            'image_url' => 'dummy.jpg',
            'has_variants' => false,
            'price' => 50000,
            'stock' => 10,
            'min_order' => 1,
            'province' => 'Jawa Tengah',
            'city' => 'Semarang',
            'is_active' => true,
            'average_rating' => 0,
            'total_reviews' => 0,
        ]);

        // Membuat teks komentar super panjang
        $longComment = str_repeat('A', 3000);

        // 2. Input data dengan komentar super panjang
        $response = $this->actingAs($buyer)->post("/reviews/{$product->id}", [
            'product_id' => $product->id, 
            'rating' => 5,
            'name' => 'Agathan Uji Coba',
            'email' => 'buyer@test.com',
            'phone' => '081234567890',
            'province' => 'Jawa Tengah',

            'comment' => $longComment, 
        ]);

        // 3. Vrifikasi hasil
        
        // Seharusnya controler menolak komentar super panjang
        $response->assertSessionHasErrors(['comment']);

        // Seharusnya ulasan tidak masuk ke database
        $this->assertDatabaseMissing('reviews', [
            'email' => 'buyer@test.com',
            'comment' => $longComment
        ]);
    }
}