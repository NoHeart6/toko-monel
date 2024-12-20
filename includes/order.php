<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cart.php';
require_once __DIR__ . '/product.php';

function createOrder($userId, $shippingAddress, $paymentMethod = 'transfer') {
    global $database;
    
    $orders = $database->orders;
    $cartItems = getCartItems($userId);
    $total = getCartTotal($userId);
    
    if ($total === 0) {
        return ['success' => false, 'message' => 'Keranjang kosong'];
    }
    
    $orderItems = [];
    foreach ($cartItems as $item) {
        $orderItems[] = [
            'product_id' => $item->product_id,
            'product_name' => $item->product_name,
            'quantity' => $item->quantity,
            'price' => $item->product_price,
            'subtotal' => $item->subtotal
        ];
        
        // Update stock
        if (!updateStock($item->product_id, $item->quantity)) {
            return ['success' => false, 'message' => 'Stok tidak mencukupi'];
        }
    }
    
    // Buat order baru
    $result = $orders->insertOne([
        'user_id' => $userId,
        'items' => $orderItems,
        'total' => $total,
        'shipping_address' => $shippingAddress,
        'payment_method' => $paymentMethod,
        'payment_status' => 'pending',
        'order_status' => 'pending',
        'created_at' => new MongoDB\BSON\UTCDateTime(),
        'payment_proof' => null,
        'payment_confirmed_at' => null,
        'payment_confirmed_by' => null
    ]);
    
    if ($result->getInsertedCount() > 0) {
        // Kosongkan keranjang
        clearCart($userId);
        return [
            'success' => true,
            'message' => 'Pesanan berhasil dibuat',
            'order_id' => (string)$result->getInsertedId()
        ];
    }
    
    return ['success' => false, 'message' => 'Gagal membuat pesanan'];
}

function getOrderById($orderId) {
    global $database;
    
    try {
        $orders = $database->orders;
        return $orders->findOne(['_id' => new MongoDB\BSON\ObjectId($orderId)]);
    } catch (Exception $e) {
        return null;
    }
}

function uploadPaymentProof($orderId, $paymentProof) {
    global $database;
    
    $orders = $database->orders;
    
    $result = $orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($orderId)],
        [
            '$set' => [
                'payment_proof' => $paymentProof,
                'payment_status' => 'waiting_confirmation',
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
    
    return ['success' => true, 'message' => 'Bukti pembayaran berhasil diunggah'];
}

function confirmPayment($orderId, $adminId) {
    global $database;
    
    $orders = $database->orders;
    
    $result = $orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($orderId)],
        [
            '$set' => [
                'payment_status' => 'confirmed',
                'order_status' => 'processing',
                'payment_confirmed_at' => new MongoDB\BSON\UTCDateTime(),
                'payment_confirmed_by' => $adminId,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
    
    return ['success' => true, 'message' => 'Pembayaran berhasil dikonfirmasi'];
}

function rejectPayment($orderId, $adminId, $reason) {
    global $database;
    
    $orders = $database->orders;
    
    $result = $orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($orderId)],
        [
            '$set' => [
                'payment_status' => 'rejected',
                'payment_rejected_reason' => $reason,
                'payment_rejected_at' => new MongoDB\BSON\UTCDateTime(),
                'payment_rejected_by' => $adminId,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
    
    return ['success' => true, 'message' => 'Pembayaran ditolak'];
}

function getOrder($orderId) {
    global $database;
    
    $orders = $database->orders;
    
    return $orders->findOne([
        '_id' => new MongoDB\BSON\ObjectId($orderId)
    ]);
}

function getUserOrders($userId) {
    global $database;
    
    $orders = $database->orders;
    
    $cursor = $orders->find(
        ['user_id' => $userId],
        ['sort' => ['created_at' => -1]]
    );

    return iterator_to_array($cursor);
}

function getPendingOrders() {
    global $database;
    
    $orders = $database->orders;
    
    $cursor = $orders->find([
        'payment_status' => 'waiting_confirmation'
    ], [
        'sort' => ['created_at' => -1]
    ]);

    // Convert cursor to array untuk menghindari masalah dengan iterator
    return iterator_to_array($cursor);
}

function countPendingOrders() {
    global $database;
    
    return $database->orders->countDocuments([
        'payment_status' => 'waiting_confirmation'
    ]);
}

function updateOrderStatus($orderId, $status) {
    global $database;
    
    $orders = $database->orders;
    
    $result = $orders->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($orderId)],
        [
            '$set' => [
                'order_status' => $status,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]
        ]
    );
    
    return ['success' => true, 'message' => 'Status pesanan berhasil diupdate'];
}

function getOrderStatistics() {
    global $database;
    
    $orders = $database->orders;
    
    $pipeline = [
        [
            '$match' => [
                'payment_status' => 'confirmed'
            ]
        ],
        [
            '$group' => [
                '_id' => null,
                'total_orders' => ['$sum' => 1],
                'total_revenue' => ['$sum' => '$total'],
                'total_items' => ['$sum' => ['$size' => '$items']]
            ]
        ]
    ];
    
    $result = $orders->aggregate($pipeline)->toArray();
    
    if (empty($result)) {
        return (object)[
            'total_orders' => 0,
            'total_revenue' => 0,
            'total_items' => 0
        ];
    }
    
    return $result[0];
}

function getTopSellingProducts($limit = 5) {
    global $database;
    
    $orders = $database->orders;
    
    $pipeline = [
        [
            '$match' => [
                'payment_status' => 'confirmed'
            ]
        ],
        ['$unwind' => '$items'],
        [
            '$group' => [
                '_id' => '$items.product_id',
                'product_name' => ['$first' => '$items.product_name'],
                'total_quantity' => ['$sum' => '$items.quantity'],
                'total_revenue' => ['$sum' => '$items.subtotal']
            ]
        ],
        ['$sort' => ['total_quantity' => -1]],
        ['$limit' => $limit]
    ];
    
    return $orders->aggregate($pipeline);
}

function getMonthlyRevenue() {
    global $database;
    
    $orders = $database->orders;
    
    $pipeline = [
        [
            '$match' => [
                'payment_status' => 'confirmed'
            ]
        ],
        [
            '$group' => [
                '_id' => [
                    'year' => ['$year' => '$created_at'],
                    'month' => ['$month' => '$created_at']
                ],
                'revenue' => ['$sum' => '$total']
            ]
        ],
        ['$sort' => ['_id.year' => -1, '_id.month' => -1]],
        ['$limit' => 12]
    ];
    
    return $orders->aggregate($pipeline);
}

function addDummyOrders() {
    global $database;
    
    $orders = $database->orders;
    $products = $database->products->find([], ['limit' => 5])->toArray();
    
    $dummyOrders = [];
    foreach ($products as $product) {
        // Buat 3-5 order untuk setiap produk
        $orderCount = rand(3, 5);
        for ($i = 0; $i < $orderCount; $i++) {
            $quantity = rand(1, 3);
            $dummyOrders[] = [
                'user_id' => 'dummy_user',
                'items' => [
                    [
                        'product_id' => $product->_id,
                        'product_name' => $product->name,
                        'quantity' => $quantity,
                        'price' => $product->price,
                        'subtotal' => $product->price * $quantity
                    ]
                ],
                'total' => $product->price * $quantity,
                'shipping_address' => 'Alamat Dummy',
                'payment_method' => 'transfer_bca',
                'payment_status' => 'waiting_confirmation',
                'order_status' => 'pending',
                'created_at' => new MongoDB\BSON\UTCDateTime(time() * 1000 - rand(0, 30) * 24 * 60 * 60 * 1000),
                'payment_proof' => null,
                'payment_confirmed_at' => null,
                'payment_confirmed_by' => null
            ];
        }
    }
    
    if (!empty($dummyOrders)) {
        $result = $orders->insertMany($dummyOrders);
        return $result->getInsertedCount();
    }
    
    return 0;
} 