<?php
require_once __DIR__ . '/../config/database.php';

function addToCart($userId, $productId, $quantity = 1) {
    global $database;
    
    $carts = $database->carts;
    
    // Cek apakah produk sudah ada di keranjang
    $existingItem = $carts->findOne([
        'user_id' => $userId,
        'product_id' => new MongoDB\BSON\ObjectId($productId),
        'status' => 'active'
    ]);
    
    if ($existingItem) {
        // Update quantity jika sudah ada
        $result = $carts->updateOne(
            ['_id' => $existingItem->_id],
            ['$inc' => ['quantity' => $quantity]]
        );
    } else {
        // Tambah item baru ke keranjang
        $result = $carts->insertOne([
            'user_id' => $userId,
            'product_id' => new MongoDB\BSON\ObjectId($productId),
            'quantity' => $quantity,
            'status' => 'active',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
    }
    
    return ['success' => true, 'message' => 'Produk berhasil ditambahkan ke keranjang'];
}

function updateCartQuantity($userId, $productId, $quantity) {
    global $database;
    
    $carts = $database->carts;
    
    $result = $carts->updateOne(
        [
            'user_id' => $userId,
            'product_id' => new MongoDB\BSON\ObjectId($productId),
            'status' => 'active'
        ],
        ['$set' => ['quantity' => $quantity]]
    );
    
    return ['success' => true, 'message' => 'Quantity berhasil diupdate'];
}

function removeFromCart($userId, $productId) {
    global $database;
    
    $carts = $database->carts;
    
    $result = $carts->deleteOne([
        'user_id' => $userId,
        'product_id' => new MongoDB\BSON\ObjectId($productId),
        'status' => 'active'
    ]);
    
    return ['success' => true, 'message' => 'Produk berhasil dihapus dari keranjang'];
}

function getCartItems($userId) {
    global $database;
    
    $carts = $database->carts;
    
    $pipeline = [
        [
            '$match' => [
                'user_id' => $userId,
                'status' => 'active'
            ]
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'product_id',
                'foreignField' => '_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ],
        [
            '$project' => [
                'product_id' => 1,
                'quantity' => 1,
                'product_name' => '$product.name',
                'product_price' => '$product.price',
                'product_image' => '$product.image',
                'subtotal' => ['$multiply' => ['$quantity', '$product.price']]
            ]
        ]
    ];
    
    return $carts->aggregate($pipeline);
}

function getCartTotal($userId) {
    global $database;
    
    $carts = $database->carts;
    
    $pipeline = [
        [
            '$match' => [
                'user_id' => $userId,
                'status' => 'active'
            ]
        ],
        [
            '$lookup' => [
                'from' => 'products',
                'localField' => 'product_id',
                'foreignField' => '_id',
                'as' => 'product'
            ]
        ],
        [
            '$unwind' => '$product'
        ],
        [
            '$group' => [
                '_id' => null,
                'total' => ['$sum' => ['$multiply' => ['$quantity', '$product.price']]]
            ]
        ]
    ];
    
    $result = $carts->aggregate($pipeline)->toArray();
    return $result[0]->total ?? 0;
}

function clearCart($userId) {
    global $database;
    
    $carts = $database->carts;
    
    $result = $carts->deleteMany([
        'user_id' => $userId,
        'status' => 'active'
    ]);
    
    return ['success' => true, 'message' => 'Keranjang berhasil dikosongkan'];
} 