<?php
require_once '../includes/auth.php';
require_once '../includes/product.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    // Hapus titik ribuan dan ganti koma desimal dengan titik
    $price = (float) str_replace(['.', ','], ['', '.'], $_POST['price']);
    $stock = (int) $_POST['stock']; // Konversi langsung ke integer
    
    // Handle file upload
    $uploadDir = '../uploads/products/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $image_url = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileExtension = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFilename = uniqid() . '.' . $fileExtension;
            $uploadPath = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
                // Simpan URL relatif ke database dengan format yang benar
                $image_url = '../uploads/products/' . $newFilename;
            }
        }
    }
    
    $product = [
        'name' => $name,
        'description' => $description,
        'price' => $price,
        'stock' => $stock,
        'image_url' => $image_url,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ];
    
    global $database;
    try {
        $result = $database->products->insertOne($product);
        if ($result->getInsertedCount() > 0) {
            $_SESSION['success_message'] = 'Produk berhasil ditambahkan';
            header('Location: manage_stock.php');
            exit;
        } else {
            $_SESSION['error_message'] = 'Gagal menambahkan produk';
        }
    } catch (Exception $e) {
        $_SESSION['error_message'] = 'Terjadi kesalahan saat menambahkan produk';
        error_log($e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Admin Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #1a237e;
            --secondary-color: #304ffe;
            --accent-color: #536dfe;
            --text-color: #1a237e;
            --background-color: #e8eaf6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--background-color) 0%, #c5cae9 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(26, 35, 126, 0.95) !important;
            backdrop-filter: blur(10px);
        }

        .navbar-brand {
            font-weight: 700;
            color: white !important;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.9) !important;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: white !important;
            transform: translateY(-2px);
        }

        .nav-link.active {
            color: white !important;
        }

        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px 20px 0 0 !important;
            border: none;
            padding: 20px;
        }

        .form-label {
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(83, 109, 254, 0.2);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 109, 254, 0.3);
        }

        #imagePreview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 10px;
            display: none;
            margin-top: 10px;
        }

        .price-input {
            position: relative;
        }

        .price-input::before {
            content: 'Rp';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            z-index: 1;
        }

        .price-input input {
            padding-left: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">
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
                        <a class="nav-link" href="manage_stock.php">
                            <i class="fas fa-boxes me-1"></i>Kelola Stok
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="add_product.php">
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

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-plus-circle me-2"></i>Tambah Produk Baru
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error_message'];
                                unset($_SESSION['error_message']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Harga</label>
                                <div class="price-input">
                                    <input type="text" class="form-control" id="price" name="price" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="stock" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stock" name="stock" required>
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                                <img id="imagePreview" class="mt-2">
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus-circle me-2"></i>Tambah Produk
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Preview gambar yang dipilih
        document.getElementById('image').addEventListener('change', function(e) {
            const preview = document.getElementById('imagePreview');
            preview.style.display = 'block';
            preview.src = URL.createObjectURL(e.target.files[0]);
        });

        // Format input harga
        $(document).ready(function() {
            $('#price').on('input', function() {
                // Hapus semua karakter non-digit
                let value = $(this).val().replace(/\D/g, '');
                
                // Format dengan titik sebagai pemisah ribuan
                value = new Intl.NumberFormat('id-ID').format(value);
                
                // Update nilai input
                $(this).val(value);
            });
        });
    </script>
</body>
</html> 