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
    <?php include 'includes/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1>Katalog Produk</h1>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['cart_message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['cart_message']['type']; ?> animate__animated animate__fadeIn">
                <?php 
                echo $_SESSION['cart_message']['text'];
                unset($_SESSION['cart_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($products as $product): ?>
            <div class="col-md-4 col-lg-3">
                <div class="card">
                    <?php 
                    // Cek dan tampilkan gambar
                    $imageUrl = null;
                    if (!empty($product->image_url)) {
                        $imageUrl = $product->image_url;
                    } elseif (!empty($product->image)) {
                        $imageUrl = $product->image;
                    }
                    
                    // Gambar default jika tidak ada gambar
                    $defaultImage = 'assets/images/no-image.jpg';
                    ?>
                    
                    <img src="<?php echo $imageUrl ?: $defaultImage; ?>" 
                         class="card-img-top" 
                         alt="<?php echo htmlspecialchars($product->name); ?>"
                         onerror="this.src='<?php echo $defaultImage; ?>'">
                    
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product->name); ?></h5>
                        <p class="price">Rp <?php echo number_format($product->price, 0, ',', '.'); ?></p>
                        <p class="stock">Stok: <?php echo $product->stock; ?></p>
                        
                        <?php if ($product->stock > 0): ?>
                            <form action="cart.php" method="POST">
                                <input type="hidden" name="action" value="add">
                                <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-cart-plus me-2"></i>Tambah ke Keranjang
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>Stok Habis
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
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