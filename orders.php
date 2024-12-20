<?php
require_once 'includes/auth.php';
require_once 'includes/order.php';

requireLogin();

// Ambil daftar pesanan pengguna
$orders = getUserOrders($_SESSION['user_id']);
$ordersArray = iterator_to_array($orders);
$isEmpty = empty($ordersArray);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Toko Monel</title>
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

        .orders-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 40px;
            animation: fadeInUp 0.5s ease-out;
        }

        .empty-orders {
            text-align: center;
            padding: 40px;
        }

        .empty-orders i {
            font-size: 64px;
            color: var(--accent-color);
            margin-bottom: 20px;
        }

        .empty-orders p {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
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
        }

        .badge.bg-danger {
            background-color: var(--error-color) !important;
        }

        .btn-detail {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-detail:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 109, 254, 0.3);
            color: white;
        }

        .modal-content {
            border: none;
            border-radius: 20px;
        }

        .modal-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 20px;
        }

        .modal-title {
            font-weight: 700;
            font-size: 24px;
        }

        .modal-body {
            padding: 30px;
        }

        .modal-body h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 15px;
        }

        .modal-footer {
            border-top: none;
            padding: 20px;
        }

        .btn-close {
            color: white;
            opacity: 1;
        }

        .btn-close:hover {
            opacity: 0.8;
        }

        .order-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
        }

        .order-info p {
            margin-bottom: 10px;
            color: #666;
        }

        .order-info strong {
            color: var(--primary-color);
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
            .orders-container {
                padding: 15px;
            }
            
            .table {
                font-size: 14px;
            }
            
            .badge {
                font-size: 11px;
                padding: 6px 10px;
            }
            
            .btn-detail {
                padding: 6px 12px;
                font-size: 12px;
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
                        <a class="nav-link" href="products.php">Produk</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Keranjang</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">Pesanan Saya</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
                </ul>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <div class="container">
            <h1 class="animate__animated animate__slideInDown">
                <i class="fas fa-shopping-bag me-2"></i> Riwayat Pesanan
            </h1>
        </div>
    </div>

    <div class="container">
        <div class="orders-container">
            <?php if ($isEmpty): ?>
                <div class="empty-orders animate__animated animate__fadeIn">
                    <i class="fas fa-box-open"></i>
                    <p>Anda belum memiliki pesanan.</p>
                    <a href="products.php" class="btn btn-detail">
                        <i class="fas fa-store me-2"></i>Mulai Belanja
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Pesanan</th>
                                <th>Tanggal</th>
                                <th>Total</th>
                                <th>Status Pembayaran</th>
                                <th>Status Pesanan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ordersArray as $order): ?>
                                <tr>
                                    <td><small class="text-muted"><?php echo (string)$order->_id; ?></small></td>
                                    <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                                    <td class="fw-bold">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                    <td>
                                        <?php
                                        switch ($order->payment_status) {
                                            case 'pending':
                                                echo '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i> Menunggu Pembayaran</span>';
                                                break;
                                            case 'waiting_confirmation':
                                                echo '<span class="badge bg-info"><i class="fas fa-hourglass-half me-1"></i> Menunggu Konfirmasi</span>';
                                                break;
                                            case 'confirmed':
                                                echo '<span class="badge bg-success"><i class="fas fa-check me-1"></i> Pembayaran Diterima</span>';
                                                break;
                                            case 'rejected':
                                                echo '<span class="badge bg-danger"><i class="fas fa-times me-1"></i> Pembayaran Ditolak</span>';
                                                if (isset($order->payment_rejected_reason)) {
                                                    echo '<br><small class="text-danger mt-1"><i class="fas fa-info-circle me-1"></i> ' . htmlspecialchars($order->payment_rejected_reason) . '</small>';
                                                }
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">' . ucfirst($order->payment_status) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        switch ($order->order_status) {
                                            case 'pending':
                                                echo '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i> Menunggu Pembayaran</span>';
                                                break;
                                            case 'processing':
                                                echo '<span class="badge bg-info"><i class="fas fa-cog me-1"></i> Sedang Diproses</span>';
                                                break;
                                            case 'shipped':
                                                echo '<span class="badge bg-primary"><i class="fas fa-shipping-fast me-1"></i> Dikirim</span>';
                                                break;
                                            case 'completed':
                                                echo '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Selesai</span>';
                                                break;
                                            case 'cancelled':
                                                echo '<span class="badge bg-danger"><i class="fas fa-ban me-1"></i> Dibatalkan</span>';
                                                break;
                                            default:
                                                echo '<span class="badge bg-secondary">' . ucfirst($order->order_status) . '</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-detail" data-bs-toggle="modal" data-bs-target="#orderDetailModal" 
                                                data-order-id="<?php echo (string)$order->_id; ?>"
                                                data-order-items='<?php echo htmlspecialchars(json_encode($order->items)); ?>'
                                                data-order-address="<?php echo htmlspecialchars($order->shipping_address); ?>"
                                                data-payment-method="<?php echo $order->payment_method; ?>">
                                            <i class="fas fa-eye me-1"></i> Detail
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

    <!-- Order Detail Modal -->
    <div class="modal fade" id="orderDetailModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-file-invoice me-2"></i>
                        Detail Pesanan
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Tutup
                    </button>
                </div>
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
        });
    </script>
</body>
</html> 