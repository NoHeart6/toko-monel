<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';
require_once 'includes/order.php';

// Ambil produk best seller
$topProducts = getTopSellingProducts(4);
$topProductIds = [];
foreach ($topProducts as $product) {
    $topProductIds[] = $product->_id;
}

// Ambil detail produk best seller
$bestSellers = [];
if (!empty($topProductIds)) {
    $bestSellers = $database->products->find(['_id' => ['$in' => $topProductIds]]);
}

// Ambil produk dengan stok menipis (kurang dari 5)
$lowStockProducts = $database->products->find(['stock' => ['$lt' => 5]], ['limit' => 4]);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Toko Monel - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #304ffe;
            --accent-color: #536dfe;
            --text-color: #1a237e;
            --background-color: #e8eaf6;
            --error-color: #ff5252;
            --success-color: #69f0ae;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--background-color) 0%, #c5cae9 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(26, 35, 126, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 24px;
            color: white !important;
            letter-spacing: 1px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: white !important;
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 30px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 2px;
        }

        .hero-section {
            padding: 80px 0;
            text-align: center;
            background: linear-gradient(45deg, rgba(26, 35, 126, 0.9), rgba(48, 79, 254, 0.9));
            color: white;
            margin-bottom: 40px;
            border-radius: 0 0 50px 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1s ease-out;
        }

        .hero-section h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
            animation: slideInDown 1s ease-out;
        }

        .hero-section p {
            font-size: 20px;
            opacity: 0.9;
            margin-bottom: 30px;
            animation: slideInUp 1s ease-out;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 12px 30px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 109, 254, 0.3);
        }

        .section-title {
            color: var(--primary-color);
            font-weight: 700;
            font-size: 32px;
            margin-bottom: 30px;
            position: relative;
            padding-bottom: 15px;
            text-align: center;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(to right, var(--secondary-color), var(--accent-color));
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
            transition: all 0.3s ease;
        }

        .card:hover .card-img-top {
            transform: scale(1.1);
        }

        .card-body {
            padding: 25px;
        }

        .card-title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 15px;
            font-size: 20px;
        }

        .card-text {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }

        .price {
            font-size: 24px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 20px;
        }

        .stock {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .alert-warning {
            background: rgba(255, 193, 7, 0.1);
            border: none;
            color: #856404;
            border-radius: 12px;
            padding: 12px 20px;
            margin-bottom: 20px;
            animation: headShake 1s;
        }

        .product-section {
            padding: 40px 0;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 1s ease-out forwards;
        }

        .product-section:nth-child(2) {
            animation-delay: 0.2s;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate__animated {
            animation-duration: 1s;
            animation-fill-mode: both;
        }

        .animate__fadeIn {
            animation-name: fadeIn;
        }

        .animate__slideInDown {
            animation-name: slideInDown;
        }

        .animate__slideInUp {
            animation-name: slideInUp;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="index.php">Toko Monel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Keranjang</a>
                    </li>
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/dashboard.php">Admin Panel</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <span class="nav-link">Selamat datang, <?php echo $_SESSION['username']; ?></span>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container">
            <h1 class="animate__animated animate__slideInDown">Selamat Datang di Toko Monel</h1>
            <p class="animate__animated animate__slideInUp">Temukan perhiasan monel berkualitas dengan harga terjangkau</p>
            <a href="products.php" class="btn btn-primary btn-lg animate__animated animate__fadeIn">Lihat Semua Produk</a>
        </div>
    </div>

    <div class="container">
        <!-- Best Sellers -->
        <div class="product-section">
            <h2 class="section-title">Produk Terlaris</h2>
            <div class="row">
                <?php foreach ($bestSellers as $product): ?>
                <div class="col-md-3">
                    <div class="card h-100">
                            <?php if (!empty($product->image_url)): ?>
                        <img src="<?php echo htmlspecialchars($product->image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product->name); ?>">
                            <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product->description); ?></p>
                                <div class="price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></div>
                                <p class="stock">Stok: <?php echo $product->stock; ?></p>
                                <?php if ($product->stock < 5): ?>
                                    <div class="alert alert-warning">Stok Menipis!</div>
                                <?php endif; ?>
                                <?php if (isLoggedIn()): ?>
                                    <form action="cart.php" method="post">
                                        <input type="hidden" name="product_id" value="<?php echo (string)$product->_id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-primary w-100" <?php echo $product->stock < 1 ? 'disabled' : ''; ?>>
                                            <?php echo $product->stock < 1 ? 'Stok Habis' : 'Tambah ke Keranjang'; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary w-100">Login untuk Membeli</a>
                                <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

        <!-- Low Stock Products -->
        <div class="product-section">
            <h2 class="section-title">Buruan Beli! Stok Menipis</h2>
            <div class="row">
                <?php foreach ($lowStockProducts as $product): ?>
                <div class="col-md-3">
                        <div class="card h-100">
                            <?php if (!empty($product->image_url)): ?>
                                <img src="<?php echo htmlspecialchars($product->image_url); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product->name); ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($product->description); ?></p>
                                <div class="price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></div>
                                <p class="stock">Stok: <?php echo $product->stock; ?></p>
                                <div class="alert alert-warning">Sisa <?php echo $product->stock; ?> item!</div>
                                <?php if (isLoggedIn()): ?>
                                    <form action="cart.php" method="post">
                                        <input type="hidden" name="product_id" value="<?php echo (string)$product->_id; ?>">
                                        <input type="hidden" name="action" value="add">
                                        <button type="submit" class="btn btn-primary w-100" <?php echo $product->stock < 1 ? 'disabled' : ''; ?>>
                                            <?php echo $product->stock < 1 ? 'Stok Habis' : 'Tambah ke Keranjang'; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-primary w-100">Login untuk Membeli</a>
                                <?php endif; ?>
                </div>
                </div>
            </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 