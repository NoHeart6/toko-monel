<?php
require_once '../includes/auth.php';
require_once '../includes/product.php';

requireAdmin();

// Handle update stok
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_stock'])) {
        $productId = $_POST['product_id'];
        $newStock = $_POST['new_stock'];
        updateProductStock($productId, $newStock);
    } elseif (isset($_POST['delete_product'])) {
        $productId = $_POST['product_id'];
        deleteProduct($productId);
    }
}

// Ambil semua produk
$products = getAllProducts();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Stok - Admin Toko Monel</title>
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
            --warning-color: #ffd740;
            --info-color: #40c4ff;
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

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            overflow: hidden;
            animation: fadeInUp 0.5s ease-out;
        }

        .card-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 20px;
        }

        .card-header h5 {
            margin: 0;
            font-weight: 700;
            font-size: 20px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--accent-color);
            color: var(--text-color);
            font-weight: 600;
            padding: 15px;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            padding: 20px 15px;
            color: #444;
        }

        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .stock-input {
            width: 100px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 8px;
            transition: all 0.3s ease;
        }

        .stock-input:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(83, 109, 254, 0.2);
            outline: none;
        }

        .btn-update {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-update:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 109, 254, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(45deg, #dc3545, #ff4444);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
            color: white;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 12px;
        }

        .badge-stock {
            background-color: var(--info-color);
            color: white;
        }

        .badge-low {
            background-color: var(--warning-color);
            color: #000;
        }

        .badge-out {
            background-color: var(--error-color);
            color: white;
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

        @media (max-width: 768px) {
            .card-body {
                padding: 15px;
            }
            
            .table {
                font-size: 14px;
            }
            
            .stock-input {
                width: 80px;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand animate__animated animate__fadeIn" href="dashboard.php">
                <i class="fas fa-chart-line me-2"></i>Admin Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="payment-confirmation.php">
                            <i class="fas fa-credit-card me-1"></i>Konfirmasi Pembayaran
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_stock.php">
                            <i class="fas fa-boxes me-1"></i>Kelola Stok
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_product.php">
                            <i class="fas fa-plus me-1"></i>Tambah Produk
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php">
                            <i class="fas fa-store me-1"></i>Lihat Toko
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1 class="animate__animated animate__slideInDown">
                <i class="fas fa-boxes me-2"></i>Kelola Stok Produk
            </h1>
        </div>
    </div>

    <div class="container">
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i class="fas fa-check-circle me-2"></i><?php echo $_SESSION['success_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show animate__animated animate__fadeInDown" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo $_SESSION['error_message']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-list me-2"></i>Daftar Produk
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Stok Saat Ini</th>
                                <th>Status</th>
                                <th>Stok Baru</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $product): ?>
                                <tr>
                                    <td>
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
                                        
                                        $productName = htmlspecialchars($product->name);
                                        ?>
                                        <img src="<?php echo $imageUrl ?: $defaultImage; ?>" 
                                             alt="<?php echo $productName; ?>" 
                                             class="product-image"
                                             onerror="this.src='<?php echo $defaultImage; ?>'">
                                    </td>
                                    <td><?php echo $productName; ?></td>
                                    <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                                    <td><?php echo $product->stock; ?></td>
                                    <td>
                                        <?php if ($product->stock <= 0): ?>
                                            <span class="badge badge-out">
                                                <i class="fas fa-times-circle me-1"></i>Habis
                                            </span>
                                        <?php elseif ($product->stock <= 5): ?>
                                            <span class="badge badge-low">
                                                <i class="fas fa-exclamation-circle me-1"></i>Stok Menipis
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-stock">
                                                <i class="fas fa-check-circle me-1"></i>Tersedia
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="post" class="d-flex align-items-center">
                                            <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                                            <input type="number" name="new_stock" class="stock-input" 
                                                   value="<?php echo $product->stock; ?>" min="0" required>
                                    </td>
                                    <td>
                                            <button type="submit" name="update_stock" class="btn btn-update me-2">
                                                <i class="fas fa-sync-alt me-1"></i>Update
                                            </button>
                                        </form>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                            <input type="hidden" name="product_id" value="<?php echo $product->_id; ?>">
                                            <button type="submit" name="delete_product" class="btn btn-danger">
                                                <i class="fas fa-trash-alt me-1"></i>Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animasi untuk pesan sukses
        document.addEventListener('DOMContentLoaded', function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.classList.add('animate__animated', 'animate__fadeOut');
                    setTimeout(() => {
                        alert.remove();
                    }, 500);
                }, 3000);
            });
        });
    </script>
</body>
</html> 