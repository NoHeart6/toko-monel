<?php
require_once '../includes/auth.php';
require_once '../includes/order.php';
require_once '../includes/product.php';

requireAdmin();

// Ambil statistik pesanan
$stats = getOrderStatistics();
$pendingOrders = getPendingOrders();
$topProducts = getTopSellingProducts(5);
$monthlyRevenue = getMonthlyRevenue();

// Format data untuk grafik
$monthlyRevenueData = [];
foreach ($monthlyRevenue as $data) {
    $monthlyRevenueData[] = [
        'month' => date('M Y', mktime(0, 0, 0, $data->_id->month, 1, $data->_id->year)),
        'revenue' => $data->revenue
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Toko Monel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            animation: fadeInUp 0.5s ease-out;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
            color: white;
        }

        .stats-icon.orders {
            background: linear-gradient(45deg, #4CAF50, #8BC34A);
        }

        .stats-icon.revenue {
            background: linear-gradient(45deg, #2196F3, #03A9F4);
        }

        .stats-icon.products {
            background: linear-gradient(45deg, #9C27B0, #E91E63);
        }

        .stats-icon.pending {
            background: linear-gradient(45deg, #FF9800, #FFC107);
        }

        .stats-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 5px;
        }

        .stats-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }

        .chart-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease-out;
        }

        .chart-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 20px;
        }

        .table-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.5s ease-out;
        }

        .table-card .card-header {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 20px 25px;
            border: none;
        }

        .table-card .card-title {
            margin: 0;
            font-size: 18px;
            font-weight: 600;
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
        }

        .table td {
            vertical-align: middle;
            padding: 15px;
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

        .btn-action {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(83, 109, 254, 0.3);
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
            .stats-card {
                margin-bottom: 20px;
            }
            
            .stats-value {
                font-size: 24px;
            }
            
            .table {
                font-size: 14px;
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
                        <a class="nav-link active" href="dashboard.php">
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
                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
            </h1>
        </div>
    </div>

    <div class="container">
        <!-- Statistik -->
        <div class="row">
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon orders">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats->total_orders; ?></div>
                    <div class="stats-label">Total Pesanan</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stats-value">Rp <?php echo number_format($stats->total_revenue, 0, ',', '.'); ?></div>
                    <div class="stats-label">Total Pendapatan</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon products">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stats-value"><?php echo $stats->total_items; ?></div>
                    <div class="stats-label">Total Produk Terjual</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stats-card">
                    <div class="stats-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stats-value"><?php echo count($pendingOrders); ?></div>
                    <div class="stats-label">Pesanan Menunggu</div>
                </div>
            </div>
        </div>

        <!-- Grafik dan Tabel -->
        <div class="row">
            <!-- Grafik Pendapatan Bulanan -->
            <div class="col-md-8">
                <div class="chart-card">
                    <h5 class="chart-title">
                        <i class="fas fa-chart-line me-2"></i>Pendapatan Bulanan
                    </h5>
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Produk Terlaris -->
            <div class="col-md-4">
                <div class="chart-card">
                    <h5 class="chart-title">
                        <i class="fas fa-star me-2"></i>Produk Terlaris
                    </h5>
                    <canvas id="productsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Pesanan Terbaru -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-clock me-2"></i>Pesanan Menunggu Konfirmasi
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Total</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingOrders)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada pesanan yang menunggu konfirmasi</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingOrders as $order): ?>
                                        <tr>
                                            <td><small class="text-muted"><?php echo (string)$order->_id; ?></small></td>
                                            <td><?php echo $order->created_at->toDateTime()->format('d/m/Y H:i'); ?></td>
                                            <td>Rp <?php echo number_format($order->total, 0, ',', '.'); ?></td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>
                                                    Menunggu Konfirmasi
                                                </span>
                                            </td>
                                            <td>
                                                <a href="payment-confirmation.php" class="btn btn-action">
                                                    <i class="fas fa-eye me-1"></i>
                                                    Lihat Detail
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grafik Pendapatan Bulanan
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($monthlyRevenueData, 'month')); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?php echo json_encode(array_column($monthlyRevenueData, 'revenue')); ?>,
                    borderColor: '#304ffe',
                    backgroundColor: 'rgba(83, 109, 254, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                            }
                        }
                    }
                }
            }
        });

        // Grafik Produk Terlaris
        const productsCtx = document.getElementById('productsChart').getContext('2d');
        new Chart(productsCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    foreach ($topProducts as $product) {
                        echo "'" . $product->product_name . "',";
                    }
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        foreach ($topProducts as $product) {
                            echo $product->total_quantity . ",";
                        }
                        ?>
                    ],
                    backgroundColor: [
                        '#304ffe',
                        '#536dfe',
                        '#69f0ae',
                        '#40c4ff',
                        '#ffd740'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
</body>
</html>