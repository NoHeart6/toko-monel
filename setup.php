<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';

// Hapus data yang ada
$database->products->deleteMany([]);
$database->users->deleteMany([]);

// Buat data pengguna default
$users = [
    [
        'email' => 'admin@tokomonel.com',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'name' => 'Admin Toko',
        'role' => 'admin',
        'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
    ],
    [
        'email' => 'demo@tokomonel.com',
        'password' => password_hash('demo123', PASSWORD_DEFAULT),
        'name' => 'Demo User',
        'role' => 'user',
        'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000)
    ]
];

$insertedUsers = $database->users->insertMany($users);
echo "Berhasil menambahkan " . count($insertedUsers->getInsertedIds()) . " pengguna default.\n";

// Jalankan seeder produk
$insertedCount = seedProducts();
echo "Berhasil menambahkan $insertedCount produk dummy dengan gambar dari Unsplash.\n";
?>