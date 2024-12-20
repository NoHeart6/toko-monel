<?php
require_once '../includes/auth.php';
require_once '../includes/product.php';

requireLogin();
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                $name = $_POST['name'] ?? '';
                $description = $_POST['description'] ?? '';
                $price = $_POST['price'] ?? 0;
                $stock = $_POST['stock'] ?? 0;
                $image = '';
                
                // Handle image upload
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../assets/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                        $image = 'assets/products/' . $fileName;
                    } else {
                        $error = 'Gagal mengupload gambar';
                        break;
                    }
                }
                
                if ($name && $description && $price > 0 && $stock >= 0 && $image) {
                    $result = addProduct($name, $description, $price, $stock, $image);
                    if ($result['success']) {
                        $success = 'Produk berhasil ditambahkan';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'Semua field harus diisi dengan benar';
                }
                break;
                
            case 'update':
                $id = $_POST['id'] ?? '';
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'price' => $_POST['price'] ?? 0,
                    'stock' => $_POST['stock'] ?? 0
                ];
                
                // Handle image upload if provided
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = '../assets/products/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $uploadFile = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadFile)) {
                        $data['image'] = 'assets/products/' . $fileName;
                    } else {
                        $error = 'Gagal mengupload gambar';
                        break;
                    }
                }
                
                if ($id && $data['name'] && $data['description'] && $data['price'] > 0 && $data['stock'] >= 0) {
                    $result = updateProduct($id, $data);
                    if ($result['success']) {
                        $success = 'Produk berhasil diupdate';
                    } else {
                        $error = $result['message'];
                    }
                } else {
                    $error = 'Semua field harus diisi dengan benar';
                }
                break;
                
            case 'delete':
                $id = $_POST['id'] ?? '';
                if ($id) {
                    $result = deleteProduct($id);
                    if ($result['success']) {
                        $success = 'Produk berhasil dihapus';
                    } else {
                        $error = $result['message'];
                    }
                }
                break;
        }
    }
}

$products = getAllProducts();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../index.php">Toko Monel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="../profile.php">Profil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Keluar</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Content -->
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Kelola Produk</h1>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="bi bi-plus-lg"></i> Tambah Produk
            </button>
        </div>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <img src="../<?php echo htmlspecialchars($product->image); ?>" alt="<?php echo htmlspecialchars($product->name); ?>" class="img-thumbnail" style="width: 64px;">
                            </td>
                            <td><?php echo htmlspecialchars($product->name); ?></td>
                            <td><?php echo htmlspecialchars($product->description); ?></td>
                            <td>Rp <?php echo number_format($product->price, 0, ',', '.'); ?></td>
                            <td><?php echo $product->stock; ?></td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-primary me-1" data-bs-toggle="modal" data-bs-target="#editProductModal<?php echo (string)$product->_id; ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (string)$product->_id; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        
                        <!-- Edit Product Modal -->
                        <div class="modal fade" id="editProductModal<?php echo (string)$product->_id; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Produk</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <input type="hidden" name="action" value="update">
                                            <input type="hidden" name="id" value="<?php echo (string)$product->_id; ?>">
                                            <div class="mb-3">
                                                <label for="name<?php echo (string)$product->_id; ?>" class="form-label">Nama Produk</label>
                                                <input type="text" class="form-control" id="name<?php echo (string)$product->_id; ?>" name="name" value="<?php echo htmlspecialchars($product->name); ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="description<?php echo (string)$product->_id; ?>" class="form-label">Deskripsi</label>
                                                <textarea class="form-control" id="description<?php echo (string)$product->_id; ?>" name="description" rows="3" required><?php echo htmlspecialchars($product->description); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label for="price<?php echo (string)$product->_id; ?>" class="form-label">Harga</label>
                                                <input type="number" class="form-control" id="price<?php echo (string)$product->_id; ?>" name="price" value="<?php echo $product->price; ?>" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="stock<?php echo (string)$product->_id; ?>" class="form-label">Stok</label>
                                                <input type="number" class="form-control" id="stock<?php echo (string)$product->_id; ?>" name="stock" value="<?php echo $product->stock; ?>" min="0" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="image<?php echo (string)$product->_id; ?>" class="form-label">Gambar</label>
                                                <input type="file" class="form-control" id="image<?php echo (string)$product->_id; ?>" name="image" accept="image/*">
                                                <small class="form-text text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
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
                            <input type="number" class="form-control" id="price" name="price" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="stock" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="stock" name="stock" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Gambar</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 