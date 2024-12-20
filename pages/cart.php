<?php
if(!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit;
}

$cart = $database->getDB()->cart;
$products = $database->getDB()->products;

// Tambah ke keranjang
if(isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
    
    if($product) {
        $cartItem = $cart->findOne([
            'user_id' => $_SESSION['user_id'],
            'product_id' => $product_id
        ]);
        
        if($cartItem) {
            $cart->updateOne(
                ['_id' => $cartItem->_id],
                ['$inc' => ['quantity' => 1]]
            );
        } else {
            $cart->insertOne([
                'user_id' => $_SESSION['user_id'],
                'product_id' => $product_id,
                'quantity' => 1,
                'price' => $product->price
            ]);
        }
    }
}

// Update quantity
if(isset($_POST['update_quantity'])) {
    $cart_id = $_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    
    if($quantity > 0) {
        $cart->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($cart_id)],
            ['$set' => ['quantity' => $quantity]]
        );
    }
}

// Hapus item
if(isset($_POST['remove_item'])) {
    $cart_id = $_POST['cart_id'];
    $cart->deleteOne(['_id' => new MongoDB\BSON\ObjectId($cart_id)]);
}

// Ambil items di keranjang
$cartItems = $cart->find(['user_id' => $_SESSION['user_id']]);
$total = 0;
?>

<div class="container mt-4">
    <h2>Keranjang Belanja</h2>
    
    <?php if(iterator_count($cartItems->rewind()) > 0): ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Harga</th>
                        <th>Jumlah</th>
                        <th>Subtotal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($cartItems as $item): 
                        $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($item->product_id)]);
                        $subtotal = $item->price * $item->quantity;
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td><?php echo $product->name; ?></td>
                            <td>Rp <?php echo number_format($item->price, 0, ',', '.'); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?php echo (string)$item->_id; ?>">
                                    <input type="number" name="quantity" value="<?php echo $item->quantity; ?>" min="1" class="form-control d-inline" style="width: 80px">
                                    <button type="submit" name="update_quantity" class="btn btn-sm btn-secondary">Update</button>
                                </form>
                            </td>
                            <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="cart_id" value="<?php echo (string)$item->_id; ?>">
                                    <button type="submit" name="remove_item" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        
        <div class="text-end mt-3">
            <a href="index.php?page=checkout" class="btn btn-primary">Lanjut ke Pembayaran</a>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            Keranjang belanja Anda kosong. <a href="index.php?page=products">Belanja sekarang</a>
        </div>
    <?php endif; ?>
</div> 