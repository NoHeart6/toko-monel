<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';
require_once 'includes/order.php';

requireLogin();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $result = createOrder(
        $_SESSION['user_id'], 
        $_POST['shipping_address'],
        $_POST['payment_method']
    );
        if ($result['success']) {
            header('Location: order_success.php?order_id=' . $result['order_id']);
            exit;
        } else {
            $error = $result['message'];
        }
}

$cartItems = getCartItems($_SESSION['user_id']);
$total = getCartTotal($_SESSION['user_id']);

// Convert MongoDB Cursor to array untuk memeriksa jumlah item
$cartItemsArray = $cartItems ? iterator_to_array($cartItems) : [];
$isEmpty = empty($cartItemsArray);

if ($isEmpty) {
    header('Location: cart.php');
    exit;
}

// Daftar metode pembayaran yang tersedia
$paymentMethods = [
    'transfer_bca' => 'Transfer Bank BCA',
    'transfer_mandiri' => 'Transfer Bank Mandiri',
    'transfer_bni' => 'Transfer Bank BNI',
    'transfer_bri' => 'Transfer Bank BRI'
];

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Toko Monel</title>
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
        }

        .table td {
            vertical-align: middle;
            padding: 20px 15px;
            color: #444;
        }

        .form-check {
            padding: 15px;
            border-radius: 15px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .form-check:hover {
            background: rgba(83, 109, 254, 0.1);
        }

        .form-check-input {
            cursor: pointer;
        }

        .form-check-label {
            cursor: pointer;
            font-weight: 500;
            color: var(--text-color);
        }

        .bank-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
        }

        .bank-info small {
            color: #666;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 12px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.2rem rgba(83, 109, 254, 0.25);
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 109, 254, 0.3);
        }

        .alert {
            border: none;
            border-radius: 15px;
            padding: 20px;
        }

        .alert-danger {
            background: rgba(255, 82, 82, 0.1);
            color: var(--error-color);
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
            
            .form-check {
                padding: 10px;
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
                    <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Pesanan Saya</a>
                    </li>
                    <?php endif; ?>
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
                <i class="fas fa-shopping-bag me-2"></i> Checkout
            </h1>
        </div>
    </div>

    <div class="container">
        <?php if ($error): ?>
            <div class="alert alert-danger animate__animated animate__fadeIn">
                <i class="fas fa-exclamation-circle me-2"></i>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="card animate__animated animate__fadeInLeft">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="fas fa-clipboard-list me-2"></i>
                            Ringkasan Pesanan
                        </h5>
                    </div>
                    <div class="card-body">
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
                                    <?php foreach ($cartItemsArray as $item): ?>
                                        <tr>
                                            <td class="fw-500"><?php echo htmlspecialchars($item->product_name); ?></td>
                                            <td>Rp <?php echo number_format($item->product_price, 0, ',', '.'); ?></td>
                                            <td><?php echo $item->quantity; ?></td>
                                            <td class="fw-bold">Rp <?php echo number_format($item->subtotal, 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>Rp <?php echo number_format($total, 0, ',', '.'); ?></strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                            </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <form method="post" class="animate__animated animate__fadeInRight">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-credit-card me-2"></i>
                                Metode Pembayaran
                            </h5>
                        </div>
                    <div class="card-body">
                            <?php foreach ($paymentMethods as $value => $label): ?>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="<?php echo $value; ?>" value="<?php echo $value; ?>" required>
                                    <label class="form-check-label" for="<?php echo $value; ?>">
                                        <i class="fas fa-university me-2"></i>
                                        <?php echo $label; ?>
                                    </label>
                                </div>
                                <div class="bank-info mb-3 ps-4" style="display: none;" id="<?php echo $value; ?>_info">
                                    <small>
                                        <i class="fas fa-info-circle me-2"></i>
                                        <strong>Informasi Rekening:</strong><br>
                                        <i class="fas fa-credit-card me-2"></i>
                                        No. Rekening: <?php echo $bankAccounts[$value]['account_number']; ?><br>
                                        <i class="fas fa-user me-2"></i>
                                        Atas Nama: <?php echo $bankAccounts[$value]['account_name']; ?>
                                    </small>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                Informasi Pengiriman
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">
                                    <i class="fas fa-home me-2"></i>
                                    Alamat Pengiriman
                                </label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required 
                                    placeholder="Masukkan alamat lengkap pengiriman"></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-check me-2"></i>
                                    Buat Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show bank account info when payment method is selected
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', function() {
                // Hide all bank info
                document.querySelectorAll('.bank-info').forEach(info => {
                    info.style.display = 'none';
                });
                // Show selected bank info with animation
                if (this.checked) {
                    const bankInfo = document.getElementById(this.value + '_info');
                    bankInfo.style.display = 'block';
                    bankInfo.classList.add('animate__animated', 'animate__fadeIn');
                }
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            const shippingAddress = document.querySelector('#shipping_address');
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Silakan pilih metode pembayaran');
                return;
            }
            
            if (!shippingAddress.value.trim()) {
                e.preventDefault();
                alert('Silakan masukkan alamat pengiriman');
                shippingAddress.focus();
                return;
            }
        });
    </script>
</body>
</html> 