<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';

// Hapus semua produk yang ada
$database->products->deleteMany([]);

// Jalankan seeder
$insertedCount = seedProducts();

echo "Berhasil menambahkan $insertedCount produk dummy dengan gambar dari Unsplash.";
?>