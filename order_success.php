<?php
require_once 'includes/auth.php';
require_once 'includes/order.php';

requireLogin();

if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$order = getOrderById($_GET['order_id']);
if (!$order || $order->user_id != $_SESSION['user_id']) {
    header('Location: index.php');
    exit;
}

// Informasi rekening bank
$bankAccounts = [
    'transfer_bca' => [
        'bank' => 'BCA',
        'account_number' => '1234567890',
        'account_name' => 'Toko Monel'
    ],
    'transfer_mandiri' => [
        'bank' => 'Mandiri',
        'account_number' => '0987654321',
        'account_name' => 'Toko Monel'
    ],
    'transfer_bni' => [
        'bank' => 'BNI',
        'account_number' => '1122334455',
        'account_name' => 'Toko Monel'
    ],
    'transfer_bri' => [
        'bank' => 'BRI',
        'account_number' => '5544332211',
        'account_name' => 'Toko Monel'
    ]
];

$selectedBank = $bankAccounts[$order->payment_method];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - Toko Monel</title>
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

        .success-icon {
            font-size: 64px;
            color: var(--success-color);
            margin-bottom: 20px;
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

        .order-info strong {
            color: var(--primary-color);
        }

        .payment-instructions {
            background: rgba(83, 109, 254, 0.1);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .payment-instructions ol {
            margin: 0;
            padding-left: 20px;
        }

        .payment-instructions li {
            margin-bottom: 10px;
            color: #444;
        }

        .bank-info {
            background: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            border: 2px solid rgba(83, 109, 254, 0.2);
        }

        .bank-info p {
            margin-bottom: 10px;
        }

        .copy-button {
            background: var(--accent-color);
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .copy-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 109, 254, 0.3);
        }

        .btn-action {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 109, 254, 0.3);
            color: white;
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
        }

        .table td {
            vertical-align: middle;
            padding: 15px;
            color: #444;
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
            .card {
                margin-bottom: 20px;
            }
            
            .card-body {
                padding: 20px;
            }
            
            .table {
                font-size: 14px;
            }
            
            .btn-action {
                width: 100%;
                text-align: center;
                margin-bottom: 10px;
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
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
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
                <i class="fas fa-check-circle me-2"></i> Pesanan Berhasil
            </h1>
        </div>
    </div>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="text-center mb-4 animate__animated animate__fadeIn">
                    <i class="fas fa-check-circle success-icon"></i>
                    <h2 class="mb-3">Terima Kasih atas Pesanan Anda!</h2>
                    <p class="text-muted">Pesanan Anda telah berhasil dibuat. Silakan lakukan pembayaran sesuai instruksi di bawah ini.</p>
                </div>

                <div class="card animate__animated animate__fadeInUp">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-info-circle me-2"></i>
                            Detail Pesanan
                        </h5>
                    </div>
                <div class="card-body">
                        <div class="order-info">
                            <h6><i class="fas fa-shopping-cart me-2"></i>Informasi Pesanan</h6>
                            <p><strong>ID Pesanan:</strong> <?php echo $order->_id; ?></p>
                            <p><strong>Tanggal Pesanan:</strong> <?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></p>
                            <p><strong>Status Pembayaran:</strong> <span class="badge bg-warning">Menunggu Pembayaran</span></p>
                            <p><strong>Total Pembayaran:</strong> <span class="fw-bold">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></span></p>
                        </div>

                        <div class="order-info">
                            <h6><i class="fas fa-box me-2"></i>Detail Produk</h6>
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
                                                <td><?php echo htmlspecialchars($item->product_name); ?></td>
                                                <td>Rp <?php echo number_format($item->price, 0, ',', '.'); ?></td>
                                                <td><?php echo $item->quantity; ?></td>
                                                <td>Rp <?php echo number_format($item->subtotal, 0, ',', '.'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="order-info">
                            <h6><i class="fas fa-map-marker-alt me-2"></i>Alamat Pengiriman</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($order->shipping_address)); ?></p>
                        </div>

                        <div class="payment-instructions">
                            <h6><i class="fas fa-info-circle me-2"></i>Instruksi Pembayaran</h6>
                            <ol>
                                <li>Transfer tepat sampai 3 digit terakhir untuk memudahkan verifikasi</li>
                                <li>Simpan bukti pembayaran</li>
                                <li>Upload bukti pembayaran di halaman pesanan</li>
                                <li>Tunggu konfirmasi dari admin (maksimal 1x24 jam)</li>
                            </ol>
                        </div>

                        <div class="bank-info">
                            <h6><i class="fas fa-university me-2"></i>Informasi Rekening</h6>
                            <p>
                                <strong>Bank:</strong> <?php echo $selectedBank['bank']; ?><br>
                                <strong>No. Rekening:</strong> 
                                <span id="accountNumber"><?php echo $selectedBank['account_number']; ?></span>
                                <button class="copy-button ms-2" onclick="copyToClipboard('accountNumber')">
                                    <i class="fas fa-copy"></i> Salin
                                </button><br>
                                <strong>Atas Nama:</strong> <?php echo $selectedBank['account_name']; ?><br>
                                <strong>Jumlah Transfer:</strong> 
                                <span id="transferAmount">Rp <?php echo number_format($order->total, 0, ',', '.'); ?></span>
                                <button class="copy-button ms-2" onclick="copyToClipboard('transferAmount')">
                                    <i class="fas fa-copy"></i> Salin
                                </button>
                            </p>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="orders.php" class="btn-action">
                                <i class="fas fa-list me-2"></i>Lihat Pesanan Saya
                            </a>
                            <a href="index.php" class="btn-action">
                                <i class="fas fa-home me-2"></i>Kembali ke Home
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copyToClipboard(elementId) {
            const element = document.getElementById(elementId);
            const text = element.innerText;
            navigator.clipboard.writeText(text).then(() => {
                const button = element.nextElementSibling;
                const icon = button.querySelector('i');
                const originalIcon = icon.className;
                
                icon.className = 'fas fa-check';
                button.innerHTML = '<i class="fas fa-check"></i> Tersalin';
                
                setTimeout(() => {
                    icon.className = originalIcon;
                    button.innerHTML = '<i class="fas fa-copy"></i> Salin';
                }, 2000);
            });
        }
    </script>
</body>
</html> 