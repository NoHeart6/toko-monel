<?php
require_once '../includes/auth.php';
require_once '../includes/order.php';

requireAdmin();

// Handle konfirmasi pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $orderId = $_POST['order_id'];
        
        switch ($_POST['action']) {
            case 'confirm':
                $result = confirmPayment($orderId, $_SESSION['user_id']);
                break;
            case 'reject':
                $result = rejectPayment($orderId, $_SESSION['user_id'], $_POST['reason']);
                break;
        }
    }
}

// Ambil daftar pesanan yang menunggu konfirmasi
$pendingOrders = getPendingOrders();
$pendingCount = countPendingOrders();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Pembayaran - Admin Toko Monel</title>
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

        .card-body {
            padding: 30px;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            border-top: none;
            border-bottom: 2px solid var(--accent-color);
            color: var(--primary-color);
            font-weight: 600;
            padding: 15px;
            white-space: nowrap;
        }

        .table td {
            vertical-align: middle;
            padding: 20px 15px;
            color: #444;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 12px;
        }

        .badge.bg-warning {
            background-color: var(--warning-color) !important;
            color: #000;
        }

        .badge.bg-info {
            background-color: var(--info-color) !important;
        }

        .badge.bg-success {
            background-color: var(--success-color) !important;
            color: #004d40;
        }

        .badge.bg-danger {
            background-color: var(--error-color) !important;
        }

        .btn-action {
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0 2px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-info {
            background: var(--info-color);
            border: none;
            color: white;
        }

        .btn-success {
            background: var(--success-color);
            border: none;
            color: #004d40;
        }

        .btn-danger {
            background: var(--error-color);
            border: none;
        }

        .modal-content {
            border: none;
            border-radius: 20px;
            overflow: hidden;
        }

        .modal-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
            padding: 20px;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            border-top: none;
            padding: 20px;
        }

        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .order-info h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .order-info p {
            margin-bottom: 10px;
            color: #666;
        }

        .payment-proof {
            max-width: 100%;
            border-radius: 10px;
            margin-top: 10px;
        }

        .alert {
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: none;
            animation: fadeInDown 0.5s ease-out;
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

        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
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
            
            .btn-action {
                padding: 6px 12px;
                font-size: 12px;
                margin-bottom: 5px;
                display: block;
                width: 100%;
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
                        <a class="nav-link active" href="payment-confirmation.php">
                            <i class="fas fa-credit-card me-1"></i>Konfirmasi Pembayaran
                            <?php if ($pendingCount > 0): ?>
                                <span class="badge bg-danger"><?php echo $pendingCount; ?></span>
                            <?php endif; ?>
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
                <i class="fas fa-credit-card me-2"></i>Konfirmasi Pembayaran
            </h1>
        </div>
    </div>

    <div class="container">
        <div class="card animate__animated animate__fadeInUp">
            <div class="card-header">
                <h5 class="card-title">
                    <i class="fas fa-list me-2"></i>Daftar Pesanan Menunggu Konfirmasi
                </h5>
            </div>
            <div class="card-body">
                <?php if ($pendingCount === 0): ?>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Tidak ada pesanan yang menunggu konfirmasi pembayaran.
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Total</th>
                                    <th>Metode Pembayaran</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingOrders as $order): ?>
                                    <tr>
                                        <td><small class="text-muted"><?php echo (string)$order->_id; ?></small></td>
                                        <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                                        <td><?php echo htmlspecialchars($order->user_id); ?></td>
                                        <td class="fw-bold">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                        <td>
                                            <?php
                                            switch ($order->payment_method) {
                                                case 'transfer_bca':
                                                    echo '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Transfer BCA</span>';
                                                    break;
                                                case 'transfer_mandiri':
                                                    echo '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Transfer Mandiri</span>';
                                                    break;
                                                case 'transfer_bni':
                                                    echo '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Transfer BNI</span>';
                                                    break;
                                                case 'transfer_bri':
                                                    echo '<span class="badge bg-info"><i class="fas fa-university me-1"></i>Transfer BRI</span>';
                                                    break;
                                                default:
                                                    echo '<span class="badge bg-secondary"><i class="fas fa-money-bill me-1"></i>' . ucfirst($order->payment_method) . '</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-info btn-action" data-bs-toggle="modal" data-bs-target="#orderDetailModal" 
                                                    data-order-id="<?php echo (string)$order->_id; ?>"
                                                    data-order-items='<?php echo htmlspecialchars(json_encode($order->items)); ?>'
                                                    data-order-address="<?php echo htmlspecialchars($order->shipping_address); ?>"
                                                    data-payment-method="<?php echo $order->payment_method; ?>"
                                                    data-payment-proof="<?php echo isset($order->payment_proof) ? htmlspecialchars($order->payment_proof) : ''; ?>">
                                                <i class="fas fa-eye me-1"></i>Detail
                                            </button>
                                            <button type="button" class="btn btn-success btn-action" data-bs-toggle="modal" data-bs-target="#confirmModal" data-order-id="<?php echo (string)$order->_id; ?>">
                                                <i class="fas fa-check me-1"></i>Konfirmasi
                                            </button>
                                            <button type="button" class="btn btn-danger btn-action" data-bs-toggle="modal" data-bs-target="#rejectModal" data-order-id="<?php echo (string)$order->_id; ?>">
                                                <i class="fas fa-times me-1"></i>Tolak
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>Detail Pesanan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="order-info">
                        <h6><i class="fas fa-box me-2"></i>Daftar Produk</h6>
                        <div class="table-responsive">
                            <table class="table" id="orderItemsTable">
                                <thead>
                                    <tr>
                                        <th>Produk</th>
                                        <th>Harga</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="order-info">
                        <h6><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h6>
                        <p id="shippingAddress" class="mb-0"></p>
                    </div>

                    <div class="order-info">
                        <h6><i class="fas fa-credit-card me-2"></i>Metode Pembayaran</h6>
                        <p id="paymentMethod" class="mb-0"></p>
                    </div>

                    <div class="order-info">
                        <h6><i class="fas fa-image me-2"></i>Bukti Pembayaran</h6>
                        <div id="paymentProofContainer">
                            <!-- Bukti pembayaran akan ditampilkan di sini -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-check-circle me-2"></i>Konfirmasi Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Apakah Anda yakin ingin mengkonfirmasi pembayaran ini?
                        </div>
                        <input type="hidden" name="action" value="confirm">
                        <input type="hidden" name="order_id" id="confirmOrderId">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check me-2"></i>Konfirmasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-times-circle me-2"></i>Tolak Pembayaran
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" name="order_id" id="rejectOrderId">
                        <div class="mb-3">
                            <label for="reason" class="form-label">
                                <i class="fas fa-comment me-2"></i>Alasan Penolakan
                            </label>
                            <textarea class="form-control" id="reason" name="reason" rows="3" required
                                placeholder="Masukkan alasan penolakan pembayaran..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times me-2"></i>Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('orderDetailModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var items = JSON.parse(button.getAttribute('data-order-items'));
            var address = button.getAttribute('data-order-address');
            var paymentMethod = button.getAttribute('data-payment-method');
            var paymentProof = button.getAttribute('data-payment-proof');
            
            // Update items table
            var tbody = document.querySelector('#orderItemsTable tbody');
            tbody.innerHTML = '';
            items.forEach(function(item) {
                var tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${item.product_name}</td>
                    <td>Rp ${new Intl.NumberFormat('id-ID').format(item.price)}</td>
                    <td>${item.quantity}</td>
                    <td class="fw-bold">Rp ${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                `;
                tbody.appendChild(tr);
            });
            
            // Update shipping address
            document.getElementById('shippingAddress').textContent = address;
            
            // Update payment method
            var paymentMethodText = '';
            switch (paymentMethod) {
                case 'transfer_bca':
                    paymentMethodText = 'Transfer Bank BCA';
                    break;
                case 'transfer_mandiri':
                    paymentMethodText = 'Transfer Bank Mandiri';
                    break;
                case 'transfer_bni':
                    paymentMethodText = 'Transfer Bank BNI';
                    break;
                case 'transfer_bri':
                    paymentMethodText = 'Transfer Bank BRI';
                    break;
                default:
                    paymentMethodText = paymentMethod;
            }
            document.getElementById('paymentMethod').textContent = paymentMethodText;
            
            // Update payment proof
            var proofContainer = document.getElementById('paymentProofContainer');
            if (paymentProof) {
                proofContainer.innerHTML = `<img src="${paymentProof}" class="img-fluid payment-proof" alt="Bukti Pembayaran">`;
            } else {
                proofContainer.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Bukti pembayaran belum diunggah</div>';
            }
        });

        document.getElementById('confirmModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var orderId = button.getAttribute('data-order-id');
            document.getElementById('confirmOrderId').value = orderId;
        });

        document.getElementById('rejectModal').addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var orderId = button.getAttribute('data-order-id');
            document.getElementById('rejectOrderId').value = orderId;
        });
    </script>
</body>
</html> 