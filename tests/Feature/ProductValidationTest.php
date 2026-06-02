<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Seller;
use App\Enums\SellerStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage; 
use Tests\TestCase;

class ProductValidationTest extends TestCase
{
    use RefreshDatabase; 

    public function test_sistem_menolak_harga_dan_stok_negatif()
    {
        // Mencegah file dummy masuk ke database
        Storage::fake('public'); 

        // 1. Persiapan data
        $user = User::factory()->create(['role' => 'seller']);

        Seller::create([
            'user_id' => $user->id,
            'store_name' => 'Toko Unit Test',
            'store_description' => 'Toko untuk kebutuhan unit test',
            'pic_name' => 'Agathan Khairy',
            'pic_phone' => '081234567890',
            'pic_email' => 'seller-test@example.com',
            'password' => 'Password123',
            'pic_street' => 'Jl. Testing No. 1',
            'pic_rt' => '001',
            'pic_rw' => '002',
            'pic_village' => 'Testing',
            'pic_district' => 'Semarang Selatan',
            'pic_city' => 'Semarang',
            'pic_province' => 'Jawa Tengah',
            'pic_ktp_number' => 'KTP1234567890',
            'status' => SellerStatus::ACTIVE,
            'city' => 'Semarang',
            'province' => 'Jawa Tengah',
            'district' => 'Semarang Selatan',
        ]);

        $category = Category::create([
            'name' => 'Elektronik Test',
            'slug' => 'elektronik-test',
            'icon' => 'fas fa-tv', 
            'is_active' => true,
        ]);

        // 2. Input data dengan harga dan stok negatif
        $response = $this->actingAs($user)->post(route('seller.products.store'), [
            'name' => 'Tes Negatif Produk',
            'category_id' => $category->id, 
            'description' => 'Produk ini untuk tes data dengan harga dan stok negatif',
            'primary_image' => UploadedFile::fake()->image('dummy.jpg'), 
            
            // Nilai negatif untuk harga dan stok
            'price' => -50000, 
            'stock' => -10,    
            
            'min_order' => 1,
            'is_active' => 1,
        ]);

        // 3. Verifikasi hasil
        $response->assertSessionHasErrors(['price', 'stock']);
        
        $this->assertDatabaseMissing('products', [
            'name' => 'Tes Negatif Produk'
        ]);
    }
}