<?php
$products = $database->getDB()->products->find(
    ['stock' => ['$gt' => 0]],
    ['limit' => 6, 'sort' => ['created_at' => -1]]
);
?>

<div class="container mt-4">
    <!-- Hero Section -->
    <div class="p-5 mb-4 bg-light rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Selamat Datang di Toko Monel</h1>
            <p class="col-md-8 fs-4">Temukan koleksi perhiasan monel berkualitas tinggi dengan harga terjangkau.</p>
            <?php if(!isset($_SESSION['user_id'])): ?>
                <a href="index.php?page=register" class="btn btn-primary btn-lg">Daftar Sekarang</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Featured Products -->
    <h2 class="mb-4">Produk Terbaru</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach($products as $product): ?>
            <div class="col">
                <div class="card h-100">
                    <img src="<?php echo $product->image_url; ?>" class="card-img-top" alt="<?php echo $product->name; ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $product->name; ?></h5>
                        <p class="card-text"><?php echo $product->description; ?></p>
                        <p class="card-text">
                            <strong>Harga: Rp <?php echo number_format($product->price, 0, ',', '.'); ?></strong>
                        </p>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <form action="index.php?page=cart" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo (string)$product->_id; ?>">
                                <button type="submit" name="add_to_cart" class="btn btn-primary">
                                    Tambah ke Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <a href="index.php?page=login" class="btn btn-primary">Login untuk Membeli</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div> 