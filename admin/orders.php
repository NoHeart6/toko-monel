<?php
require_once '../includes/auth.php';
require_once '../includes/order.php';

requireLogin();
requireAdmin();

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_status') {
        $orderId = $_POST['order_id'] ?? '';
        $status = $_POST['status'] ?? '';
        
        if ($orderId && $status) {
            $result = updateOrderStatus($orderId, $status);
            if ($result['success']) {
                $success = 'Status pesanan berhasil diupdate';
            } else {
                $error = $result['message'];
            }
        }
    }
}

// Get all orders with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$skip = ($page - 1) * $limit;

$orders = $database->orders->find(
    [],
    [
        'sort' => ['created_at' => -1],
        'limit' => $limit,
        'skip' => $skip
    ]
);

$totalOrders = $database->orders->countDocuments();
$totalPages = ceil($totalOrders / $limit);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - Admin Toko Monel</title>
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
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">Pesanan</a>
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
        <h1 class="mb-4">Kelola Pesanan</h1>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Pelanggan</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td><?php echo (string)$order->_id; ?></td>
                                    <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($order->shipping_address['name']); ?><br>
                                        <small class="text-muted"><?php echo htmlspecialchars($order->shipping_address['phone']); ?></small>
                                    </td>
                                    <td>Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                        $statusClass = [
                                            'pending' => 'warning',
                                            'processing' => 'info',
                                            'shipped' => 'primary',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusText = [
                                            'pending' => 'Menunggu Pembayaran',
                                            'processing' => 'Diproses',
                                            'shipped' => 'Dikirim',
                                            'delivered' => 'Diterima',
                                            'cancelled' => 'Dibatalkan'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass[$order->status]; ?>">
                                            <?php echo $statusText[$order->status]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#orderDetailModal<?php echo (string)$order->_id; ?>">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo (string)$order->_id; ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    </td>
                                </tr>
                                
                                <!-- Order Detail Modal -->
                                <div class="modal fade" id="orderDetailModal<?php echo (string)$order->_id; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Pesanan #<?php echo (string)$order->_id; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <h6>Informasi Pengiriman</h6>
                                                        <p class="mb-0">
                                                            <?php echo htmlspecialchars($order->shipping_address['name']); ?><br>
                                                            <?php echo htmlspecialchars($order->shipping_address['phone']); ?><br>
                                                            <?php echo htmlspecialchars($order->shipping_address['address']); ?><br>
                                                            <?php echo htmlspecialchars($order->shipping_address['city']); ?>, <?php echo htmlspecialchars($order->shipping_address['postal_code']); ?>
                                                        </p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Informasi Pesanan</h6>
                                                        <p class="mb-0">
                                                            Tanggal: <?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?><br>
                                                            Status: <span class="badge bg-<?php echo $statusClass[$order->status]; ?>"><?php echo $statusText[$order->status]; ?></span>
                                                        </p>
                                                    </div>
                                                </div>
                                                <h6>Produk</h6>
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Produk</th>
                                                                <th>Harga</th>
                                                                <th>Jumlah</th>
                                                                <th>Subtotal</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($order->items as $item): ?>
                                                                <tr>
                                                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                                                    <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                                                    <td><?php echo $item['quantity']; ?></td>
                                                                    <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr>
                                                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                                                <td class="fw-bold">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Update Status Modal -->
                                <div class="modal fade" id="updateStatusModal<?php echo (string)$order->_id; ?>" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Update Status Pesanan #<?php echo (string)$order->_id; ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="action" value="update_status">
                                                    <input type="hidden" name="order_id" value="<?php echo (string)$order->_id; ?>">
                                                    <div class="mb-3">
                                                        <label for="status<?php echo (string)$order->_id; ?>" class="form-label">Status</label>
                                                        <select class="form-select" id="status<?php echo (string)$order->_id; ?>" name="status" required>
                                                            <option value="pending" <?php echo $order->status === 'pending' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                                            <option value="processing" <?php echo $order->status === 'processing' ? 'selected' : ''; ?>>Diproses</option>
                                                            <option value="shipped" <?php echo $order->status === 'shipped' ? 'selected' : ''; ?>>Dikirim</option>
                                                            <option value="delivered" <?php echo $order->status === 'delivered' ? 'selected' : ''; ?>>Diterima</option>
                                                            <option value="cancelled" <?php echo $order->status === 'cancelled' ? 'selected' : ''; ?>>Dibatalkan</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-primary">Update Status</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $page === $i ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 