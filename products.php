<?php
require_once 'includes/auth.php';
require_once 'includes/product.php';

// Filter dan pencarian
$search = $_GET['search'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$skip = ($page - 1) * $limit;

if (!empty($search)) {
    $products = searchProducts($search);
} else {
    $products = getAllProducts($limit, $skip, $sort);
}

// Hitung total produk untuk pagination
$totalProducts = $database->products->countDocuments();
$totalPages = ceil($totalProducts / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Toko Monel</title>
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

        .search-form {
            position: relative;
            margin-right: 1rem;
        }

        .search-form input {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            padding-right: 40px;
            transition: all 0.3s ease;
        }

        .search-form input:focus {
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 0 0 0 0.25rem rgba(255, 255, 255, 0.1);
            color: white;
        }

        .search-form input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: white;
            padding: 5px 10px;
        }

        .search-form button:hover {
            color: var(--accent-color);
        }

        .page-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 40px 0;
            margin-bottom: 40px;
            border-radius: 0 0 50px 50px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .page-header h1 {
            font-weight: 800;
            margin: 0;
            font-size: 36px;
        }

        .filter-buttons {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .filter-buttons .btn {
            border-radius: 10px;
            padding: 8px 20px;
            margin: 0 5px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .filter-buttons .btn:hover {
            transform: translateY(-2px);
        }

        .filter-buttons .btn.active {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
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

        .pagination {
            margin-top: 40px;
            margin-bottom: 60px;
        }

        .pagination .page-link {
            border: none;
            padding: 12px 20px;
            margin: 0 5px;
            border-radius: 10px;
            color: var(--primary-color);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        .pagination .page-item.active .page-link {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(26, 35, 126, 0.2);
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

        @media (max-width: 768px) {
            .filter-buttons {
                overflow-x: auto;
                white-space: nowrap;
                padding: 15px 5px;
            }
            
            .filter-buttons .btn {
                margin-bottom: 10px;
            }
            
            .card-img-top {
                height: 150px;
            }
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
                        <a class="nav-link" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Produk</a>
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
                <form class="search-form d-flex" method="get">
                    <input class="form-control" type="search" name="search" placeholder="Cari produk..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
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

    <div class="page-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="animate__animated animate__slideInDown">Semua Produk</h1>
                </div>
                <div class="col-md-6">
                    <div class="filter-buttons text-end animate__animated animate__slideInUp">
                        <a href="?sort=newest" class="btn <?php echo $sort === 'newest' ? 'active' : 'btn-light'; ?>">
                            <i class="fas fa-clock"></i> Terbaru
                        </a>
                        <a href="?sort=price_low" class="btn <?php echo $sort === 'price_low' ? 'active' : 'btn-light'; ?>">
                            <i class="fas fa-sort-amount-down"></i> Harga Terendah
                        </a>
                        <a href="?sort=price_high" class="btn <?php echo $sort === 'price_high' ? 'active' : 'btn-light'; ?>">
                            <i class="fas fa-sort-amount-up"></i> Harga Tertinggi
                        </a>
                        <a href="?sort=stock_low" class="btn <?php echo $sort === 'stock_low' ? 'active' : 'btn-light'; ?>">
                            <i class="fas fa-box"></i> Stok Menipis
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <?php foreach ($products as $product): ?>
                <div class="col-md-4">
                    <div class="card h-100">
                        <?php 
                        // Base64 image untuk default "no image"
                        $defaultImage = 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMjgiIGhlaWdodD0iMTI4IiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHN0cm9rZT0iI2NjY2NjYyIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiPjxyZWN0IHg9IjMiIHk9IjMiIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgcng9IjIiIHJ5PSIyIj48L3JlY3Q+PGNpcmNsZSBjeD0iOC41IiBjeT0iOC41IiByPSIxLjUiPjwvY2lyY2xlPjxwb2x5bGluZSBwb2ludHM9IjIxIDE1IDEzIDEwIDQgMjEiPjwvcG9seWxpbmU+PC9zdmc+';
                        
                        // Cek image_url atau image (untuk kompatibilitas)
                        $imageUrl = null;
                        if (isset($product->image_url) && !empty($product->image_url)) {
                            $imageUrl = $product->image_url;
                        } elseif (isset($product->image) && !empty($product->image)) {
                            $imageUrl = $product->image;
                        }
                        ?>
                        <img src="<?php echo $imageUrl ?: $defaultImage; ?>" 
                             class="card-img-top" 
                             alt="<?php echo htmlspecialchars($product->name); ?>"
                             onerror="this.src='<?php echo $defaultImage; ?>'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars($product->description); ?></p>
                            <div class="price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></div>
                            <div class="stock">
                                Stok: <?php echo $product->stock; ?>
                                <?php if ($product->stock <= 5 && $product->stock > 0): ?>
                                    <div class="alert alert-warning mt-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Stok menipis!
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if ($product->stock > 0): ?>
                                <form action="cart.php" method="post">
                                    <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                                    <button type="submit" name="add_to_cart" class="btn btn-primary w-100">
                                        <i class="fas fa-shopping-cart me-2"></i>Tambah ke Keranjang
                                    </button>
                                </form>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100" disabled>
                                    <i class="fas fa-times-circle me-2"></i>Stok Habis
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav aria-label="Page navigation" class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>&sort=<?php echo $sort; ?>&search=<?php echo urlencode($search); ?>">
                                <?php echo $i; ?>
                            </a>
                        </li>
                    <?php endfor; ?>
                </ul>
            </nav>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 