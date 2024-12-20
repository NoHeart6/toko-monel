<?php
require_once __DIR__ . '/../config/database.php';

function addProduct($name, $description, $price, $stock, $image = '') {
    global $database;
    
    $products = $database->products;
    
    $result = $products->insertOne([
        'name' => $name,
        'description' => $description,
        'price' => (int)$price,
        'stock' => (int)$stock,
        'image' => $image,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    
    return $result->getInsertedCount() > 0;
}

function addDummyProducts() {
    $products = [
        [
            'name' => 'Cincin Monel Ukir',
            'description' => 'Cincin monel dengan ukiran tradisional, cocok untuk pria dan wanita',
            'price' => 75000,
            'stock' => 20,
            'image' => 'uploads/products/cincin-ukir.jpg'
        ],
        [
            'name' => 'Gelang Monel Rantai',
            'description' => 'Gelang monel model rantai, tahan karat dan anti luntur',
            'price' => 85000,
            'stock' => 15,
            'image' => 'uploads/products/gelang-rantai.jpg'
        ],
        [
            'name' => 'Kalung Monel Titanium',
            'description' => 'Kalung monel kombinasi titanium, elegan dan tahan lama',
            'price' => 120000,
            'stock' => 10,
            'image' => 'uploads/products/kalung-titanium.jpg'
        ],
        [
            'name' => 'Anting Monel Permata',
            'description' => 'Anting monel dengan hiasan permata, cocok untuk acara formal',
            'price' => 65000,
            'stock' => 25,
            'image' => 'uploads/products/anting-permata.jpg'
        ],
        [
            'name' => 'Cincin Couple Monel',
            'description' => 'Sepasang cincin couple monel dengan desain minimalis',
            'price' => 150000,
            'stock' => 8,
            'image' => 'uploads/products/cincin-couple.jpg'
        ],
        [
            'name' => 'Gelang Tangan Monel Premium',
            'description' => 'Gelang tangan monel kualitas premium dengan pengait magnet',
            'price' => 95000,
            'stock' => 12,
            'image' => 'uploads/products/gelang-premium.jpg'
        ],
        [
            'name' => 'Set Perhiasan Monel',
            'description' => 'Set lengkap perhiasan monel termasuk kalung, gelang, dan anting',
            'price' => 250000,
            'stock' => 5,
            'image' => 'uploads/products/set-perhiasan.jpg'
        ],
        [
            'name' => 'Cincin Monel Black Edition',
            'description' => 'Cincin monel dengan lapisan hitam elegan',
            'price' => 89000,
            'stock' => 18,
            'image' => 'uploads/products/cincin-black.jpg'
        ],
        [
            'name' => 'Kalung Liontin Monel',
            'description' => 'Kalung monel dengan liontin unik, cocok untuk kado',
            'price' => 135000,
            'stock' => 7,
            'image' => 'uploads/products/kalung-liontin.jpg'
        ],
        [
            'name' => 'Gelang Kaki Monel',
            'description' => 'Gelang kaki monel dengan hiasan manik-manik',
            'price' => 55000,
            'stock' => 30,
            'image' => 'uploads/products/gelang-kaki.jpg'
        ]
    ];

    global $database;
    $result = $database->products->insertMany($products);
    return $result->getInsertedCount();
}

function updateStock($productId, $quantity, $operation = 'decrease') {
    global $database;
    
    $products = $database->products;
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($productId)]);
    
    if (!$product) {
        return false;
    }
    
    $newStock = $operation === 'decrease' ? 
        $product->stock - $quantity : 
        $product->stock + $quantity;
    
    if ($newStock < 0) {
        return false;
    }
    
    $result = $products->updateOne(
        ['_id' => new MongoDB\BSON\ObjectId($productId)],
        ['$set' => ['stock' => $newStock]]
    );
    
    return $result->getModifiedCount() > 0;
}

function getAllProducts($limit = null, $skip = null, $sort = 'newest') {
    global $database;
    
    $products = $database->products;
    
    $options = [];
    if ($limit !== null) {
        $options['limit'] = $limit;
    }
    if ($skip !== null) {
        $options['skip'] = $skip;
    }
    
    switch ($sort) {
        case 'price_low':
            $options['sort'] = ['price' => 1];
            break;
        case 'price_high':
            $options['sort'] = ['price' => -1];
            break;
        case 'stock_low':
            $options['sort'] = ['stock' => 1];
            break;
        default: // newest
            $options['sort'] = ['created_at' => -1];
    }
    
    $cursor = $products->find([], $options);
    $result = [];
    
    foreach ($cursor as $product) {
        // Pastikan semua properti ada
        if (!isset($product->image_url) && !isset($product->image)) {
            $product->image_url = null;
        } elseif (isset($product->image) && !isset($product->image_url)) {
            // Jika hanya ada field image, gunakan itu sebagai image_url
            $product->image_url = $product->image;
        }
        
        // Perbaiki path gambar jika dimulai dengan ../
        if (isset($product->image_url) && strpos($product->image_url, '../') === 0) {
            $product->image_url = substr($product->image_url, 3);
        }
        
        if (!isset($product->name)) {
            $product->name = 'Produk Tanpa Nama';
        }
        
        if (!isset($product->price)) {
            $product->price = 0;
        }
        
        if (!isset($product->stock)) {
            $product->stock = 0;
        }
        
        // Format harga jika belum diformat
        if ($product->price < 1000) {
            $product->price *= 1000;
        }
        
        $result[] = $product;
    }
    
    return $result;
}

function searchProducts($query) {
    global $database;
    
    $products = $database->products;
    
    return $products->find([
        '$or' => [
            ['name' => ['$regex' => $query, '$options' => 'i']],
            ['description' => ['$regex' => $query, '$options' => 'i']]
        ]
    ]);
}

function getProduct($productId) {
    global $database;
    
    $products = $database->products;
    
    return $products->findOne([
        '_id' => new MongoDB\BSON\ObjectId($productId)
    ]);
}

function updateProductStock($productId, $newStock) {
    global $database;
    
    try {
        $result = $database->products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($productId)],
            ['$set' => ['stock' => (int)$newStock]]
        );
        
        if ($result->getModifiedCount() > 0) {
            $_SESSION['success_message'] = 'Stok berhasil diperbarui';
            return true;
        } else {
            $_SESSION['error_message'] = 'Gagal memperbarui stok';
            return false;
        }
    } catch (Exception $e) {
        error_log("Error updating product stock: " . $e->getMessage());
        $_SESSION['error_message'] = 'Terjadi kesalahan saat memperbarui stok';
        return false;
    }
}

function deleteProduct($productId) {
    global $database;
    
    try {
        $result = $database->products->deleteOne([
            '_id' => new MongoDB\BSON\ObjectId($productId)
        ]);
        
        if ($result->getDeletedCount() > 0) {
            $_SESSION['success_message'] = 'Produk berhasil dihapus';
            return true;
        } else {
            $_SESSION['error_message'] = 'Gagal menghapus produk';
            return false;
        }
    } catch (Exception $e) {
        error_log("Error deleting product: " . $e->getMessage());
        $_SESSION['error_message'] = 'Terjadi kesalahan saat menghapus produk';
        return false;
    }
}

function seedProducts() {
    $products = [
        [
            'name' => 'Cincin Monel Ukir',
            'description' => 'Cincin monel dengan ukiran tradisional yang elegan',
            'price' => 75000,
            'stock' => 5,
            'image_url' => 'https://images.unsplash.com/photo-1605100804763-247f67b3557e?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Gelang Monel Rantai',
            'description' => 'Gelang monel model rantai dengan finishing mengkilap',
            'price' => 85000,
            'stock' => 1,
            'image_url' => 'https://images.unsplash.com/photo-1611591437281-460bfbe1220a?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Kalung Monel Titanium',
            'description' => 'Kalung monel dengan paduan titanium, tahan karat',
            'price' => 120000,
            'stock' => 10,
            'image_url' => 'https://images.unsplash.com/photo-1589128777073-263566ae5e4d?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Anting Monel Permata',
            'description' => 'Anting monel dengan hiasan permata sintetis',
            'price' => 65000,
            'stock' => 25,
            'image_url' => 'https://images.unsplash.com/photo-1535632066927-ab7c9ab60908?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Cincin Couple Monel',
            'description' => 'Sepasang cincin couple dengan desain minimalis',
            'price' => 150000,
            'stock' => 8,
            'image_url' => 'https://images.unsplash.com/photo-1515377905703-c4788e51af15?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Gelang Tangan Monel Premium',
            'description' => 'Gelang tangan monel kualitas premium dengan desain eksklusif',
            'price' => 95000,
            'stock' => 12,
            'image_url' => 'https://images.unsplash.com/photo-1573408301185-9146fe634ad0?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Set Perhiasan Monel',
            'description' => 'Set lengkap perhiasan monel termasuk kalung, gelang, dan anting',
            'price' => 250000,
            'stock' => 5,
            'image_url' => 'https://images.unsplash.com/photo-1576723417715-6b408c988c23?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Cincin Monel Black Edition',
            'description' => 'Cincin monel dengan lapisan hitam elegan',
            'price' => 89000,
            'stock' => 18,
            'image_url' => 'https://images.unsplash.com/photo-1598560917505-59a3ad559071?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Kalung Liontin Monel',
            'description' => 'Kalung monel dengan liontin unik, cocok untuk kado',
            'price' => 135000,
            'stock' => 7,
            'image_url' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Gelang Kaki Monel',
            'description' => 'Gelang kaki monel dengan hiasan manik-manik',
            'price' => 55000,
            'stock' => 30,
            'image_url' => 'https://images.unsplash.com/photo-1602173574767-37ac01994b2a?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Cincin Monel Klasik',
            'description' => 'Cincin monel dengan desain klasik yang timeless',
            'price' => 95000,
            'stock' => 15,
            'image_url' => 'https://images.unsplash.com/photo-1603561596112-0a132b757442?w=500&auto=format&fit=crop'
        ],
        [
            'name' => 'Kalung Choker Monel',
            'description' => 'Kalung choker monel dengan desain modern',
            'price' => 110000,
            'stock' => 20,
            'image_url' => 'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?w=500&auto=format&fit=crop'
        ]
    ];

    global $database;
    $result = $database->products->insertMany($products);
    return $result->getInsertedCount();
}