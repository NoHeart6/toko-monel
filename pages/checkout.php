<?php
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$cart = $database->getDB()->cart;
$products = $database->getDB()->products;
$orders = $database->getDB()->orders;

$cartItems = $cart->find(['user_id' => $_SESSION['user_id']]);
$total = 0;

// Proses checkout
if(isset($_POST['place_order'])) {
    $address = filter_input(INPUT_POST, 'address', FILTER_SANITIZE_STRING);
    $phone = filter_input(INPUT_POST, 'phone', FILTER_SANITIZE_STRING);
    
    // Ambil items dari keranjang
    $items = [];
    $cartItems = $cart->find(['user_id' => $_SESSION['user_id']]);
    
    foreach($cartItems as $item) {
        $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($item->product_id)]);
        
        // Cek stok
        if($product->stock < $item->quantity) {
            $error = "Stok produk {$product->name} tidak mencukupi!";
            break;
        }
        
        $items[] = [
            'product_id' => $item->product_id,
            'name' => $product->name,
            'price' => $item->price,
            'quantity' => $item->quantity,
            'subtotal' => $item->price * $item->quantity
        ];
        
        // Update stok
        $products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($item->product_id)],
            ['$inc' => ['stock' => -$item->quantity]]
        );
        
        $total += $item->price * $item->quantity;
    }
    
    if(!isset($error)) {
        // Buat order baru
        $order = [
            'user_id' => $_SESSION['user_id'],
            'items' => $items,
            'total' => $total,
            'address' => $address,
            'phone' => $phone,
            'status' => 'pending',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ];
        
        $result = $orders->insertOne($order);
        
        if($result->getInsertedCount() > 0) {
            // Kosongkan keranjang
            $cart->deleteMany(['user_id' => $_SESSION['user_id']]);
            
            header('Location: index.php?page=order_success&order_id=' . (string)$result->getInsertedId());
            exit;
        } else {
            $error = "Gagal membuat pesanan. Silakan coba lagi.";
        }
    }
}
?>

<div class="container mt-4">
    <h2>Checkout</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Detail Pengiriman</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="mb-3">
                            <label for="address" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <h5 class="mt-4">Ringkasan Pesanan</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $cartItems->rewind();
                                    foreach($cartItems as $item): 
                                        $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($item->product_id)]);
                                        $subtotal = $item->price * $item->quantity;
                                        $total += $subtotal;
                                    ?>
                                        <tr>
                                            <td><?php echo $product->name; ?></td>
                                            <td><?php echo $item->quantity; ?></td>
                                            <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="2" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" name="place_order" class="btn btn-primary">Buat Pesanan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Metode Pembayaran</h5>
                </div>
                <div class="card-body">
                    <p>Untuk saat ini, kami hanya menerima pembayaran melalui transfer bank.</p>
                    <p>Silakan transfer ke rekening berikut:</p>
                    <p><strong>Bank BCA</strong><br>
                    No. Rek: 1234567890<br>
                    A/N: Toko Monel</p>
                    <p class="text-muted">Detail pembayaran akan dikirim ke email Anda setelah pesanan dibuat.</p>
                </div>
            </div>
        </div>
    </div>
</div> 