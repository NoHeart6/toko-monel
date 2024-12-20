<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/cart.php';

$productId = $_GET['id'] ?? null;
if (!$productId) {
    header('Location: index.php');
    exit;
}

$product = getProduct($productId);
if (!$product) {
    header('Location: index.php');
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    $quantity = (int)($_POST['quantity'] ?? 1);
    if ($quantity > 0 && $quantity <= $product->stock) {
        $result = addToCart($_SESSION['user_id'], $productId, $quantity);
        if ($result['success']) {
            $success = 'Produk berhasil ditambahkan ke keranjang';
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Jumlah tidak valid';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product->name); ?> - Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">Toko Monel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Produk</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="bi bi-cart"></i> Keranjang
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <?php if (isAdmin()): ?>
                                    <li><a class="dropdown-item" href="admin/">Dashboard Admin</a></li>
                                <?php endif; ?>
                                <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                <li><a class="dropdown-item" href="orders.php">Pesanan Saya</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Masuk</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Daftar</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Product Detail -->
    <div class="container py-5">
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo htmlspecialchars($product->image); ?>" class="img-fluid rounded" alt="<?php echo htmlspecialchars($product->name); ?>">
            </div>
            <div class="col-md-6">
                <h1 class="mb-3"><?php echo htmlspecialchars($product->name); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($product->description); ?></p>
                <p class="h3 mb-4">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></p>
                
                <?php if ($product->stock > 0): ?>
                    <p class="text-success mb-4">
                        <i class="bi bi-check-circle"></i> Stok tersedia: <?php echo $product->stock; ?>
                    </p>
                    <?php if (isLoggedIn()): ?>
                        <form method="POST" class="mb-4">
                            <div class="row g-3">
                                <div class="col-auto">
                                    <input type="number" class="form-control" name="quantity" value="1" min="1" max="<?php echo $product->stock; ?>">
                                </div>
                                <div class="col-auto">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Silakan <a href="login.php">masuk</a> untuk menambahkan produk ke keranjang.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-danger mb-4">
                        <i class="bi bi-x-circle"></i> Stok habis
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-light py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Toko Monel</h5>
                    <p>Menyediakan perhiasan monel berkualitas dengan harga terjangkau.</p>
                </div>
                <div class="col-md-3">
                    <h5>Link</h5>
                    <ul class="list-unstyled">
                        <li><a href="about.php" class="text-light">Tentang Kami</a></li>
                        <li><a href="contact.php" class="text-light">Hubungi Kami</a></li>
                        <li><a href="privacy.php" class="text-light">Kebijakan Privasi</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Kontak</h5>
                    <ul class="list-unstyled">
                        <li><i class="bi bi-telephone"></i> +62 123 4567 890</li>
                        <li><i class="bi bi-envelope"></i> info@tokomonel.com</li>
                        <li><i class="bi bi-geo-alt"></i> Jl. Contoh No. 123</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Toko Monel. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 