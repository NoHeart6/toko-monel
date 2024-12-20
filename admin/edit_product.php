<?php
session_start();
require_once '../config/database.php';

// Cek apakah user adalah admin
if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../index.php?page=login');
    exit;
}

$products = $database->getDB()->products;

// Ambil data produk
if(isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $product = $products->findOne(['_id' => new MongoDB\BSON\ObjectId($product_id)]);
    
    if(!$product) {
        header('Location: products.php');
        exit;
    }
} else {
    header('Location: products.php');
    exit;
}

// Update produk
if(isset($_POST['update_product'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $description = filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $image_url = filter_input(INPUT_POST, 'image_url', FILTER_SANITIZE_URL);
    
    if($name && $price && $stock) {
        $result = $products->updateOne(
            ['_id' => new MongoDB\BSON\ObjectId($product_id)],
            ['$set' => [
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'stock' => $stock,
                'image_url' => $image_url,
                'updated_at' => new MongoDB\BSON\UTCDateTime()
            ]]
        );
        
        if($result->getModifiedCount() > 0) {
            header('Location: products.php?updated=1');
            exit;
        } else {
            $error = "Gagal mengupdate produk. Silakan coba lagi.";
        }
    } else {
        $error = "Semua field harus diisi dengan benar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Admin Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Kelola Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan</a>
                    </li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Keluar</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Edit Produk</h4>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error)): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo $product->name; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" 
                                          rows="3" required><?php echo $product->description; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Harga (Rp)</label>
                                <input type="number" class="form-control" id="price" name="price" 
                                       value="<?php echo $product->price; ?>" min="0" step="1000" required>
                            </div>

                            <div class="mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       value="<?php echo $product->stock; ?>" min="0" required>
                            </div>

                            <div class="mb-3">
                                <label for="image_url" class="form-label">URL Gambar</label>
                                <input type="url" class="form-control" id="image_url" name="image_url" 
                                       value="<?php echo $product->image_url; ?>" required>
                                <div class="form-text">Masukkan URL gambar produk (disarankan ukuran 500x500 pixel)</div>
                            </div>

                            <div class="mb-3">
                                <img src="<?php echo $product->image_url; ?>" alt="Preview" 
                                     class="img-thumbnail" style="max-width: 200px;">
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update_product" class="btn btn-primary">Update Produk</button>
                                <a href="products.php" class="btn btn-secondary">Batal</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 