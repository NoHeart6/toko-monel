<?php
require_once 'includes/auth.php';
require_once 'includes/cart.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add':
                addToCart($_SESSION['user_id'], $_POST['product_id']);
                break;
            case 'increase':
                $currentQty = (int)$_POST['current_quantity'];
                updateCartQuantity($_SESSION['user_id'], $_POST['product_id'], $currentQty + 1);
                break;
            case 'decrease':
                $currentQty = (int)$_POST['current_quantity'];
                if ($currentQty > 1) {
                    updateCartQuantity($_SESSION['user_id'], $_POST['product_id'], $currentQty - 1);
                }
                break;
            case 'update':
                updateCartQuantity($_SESSION['user_id'], $_POST['product_id'], (int)$_POST['quantity']);
                break;
            case 'remove':
                removeFromCart($_SESSION['user_id'], $_POST['product_id']);
                break;
        }
    }
    header('Location: cart.php');
    exit;
}

$cartItems = getCartItems($_SESSION['user_id']);
$total = getCartTotal($_SESSION['user_id']);

// Convert MongoDB Cursor to array untuk memeriksa jumlah item
$cartItemsArray = $cartItems ? iterator_to_array($cartItems) : [];
$isEmpty = empty($cartItemsArray);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Toko Monel</title>
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

        .cart-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 40px;
            animation: fadeInUp 0.5s ease-out;
        }

        .empty-cart {
            text-align: center;
            padding: 40px;
        }

        .empty-cart i {
            font-size: 64px;
            color: var(--accent-color);
            margin-bottom: 20px;
        }

        .empty-cart p {
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
        }

        .table td {
            vertical-align: middle;
            padding: 20px 15px;
            color: #444;
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #f8f9fa;
            padding: 5px;
            border-radius: 10px;
        }

        .quantity-control form {
            margin: 0;
        }

        .quantity-control button {
            width: 30px;
            height: 30px;
            padding: 0;
            border-radius: 8px;
            font-size: 16px;
            line-height: 1;
            border: none;
            background: white;
            color: var(--primary-color);
            transition: all 0.3s ease;
        }

        .quantity-control button:hover:not(:disabled) {
            background: var(--accent-color);
            color: white;
            transform: translateY(-2px);
        }

        .quantity-control input {
            width: 50px;
            text-align: center;
            border: none;
            background: white;
            border-radius: 8px;
            padding: 5px;
            font-weight: 500;
        }

        .btn-remove {
            background: var(--error-color);
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .btn-remove:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 82, 82, 0.3);
        }

        .cart-total {
            background: var(--primary-color);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .cart-total .total-label {
            font-size: 18px;
            font-weight: 500;
        }

        .cart-total .total-amount {
            font-size: 24px;
            font-weight: 700;
        }

        .cart-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }

        .btn-continue {
            background: white;
            color: var(--primary-color);
            border: 2px solid var(--primary-color);
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-continue:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }

        .btn-checkout {
            background: linear-gradient(45deg, var(--secondary-color), var(--accent-color));
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-checkout:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(83, 109, 254, 0.3);
        }

        .alert-custom {
            background: rgba(83, 109, 254, 0.1);
            border: none;
            border-radius: 15px;
            padding: 20px;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 30px;
        }

        .alert-custom i {
            font-size: 24px;
            color: var(--accent-color);
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
            .cart-container {
                padding: 15px;
            }
            
            .table {
                font-size: 14px;
            }
            
            .quantity-control {
                flex-wrap: wrap;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .btn-continue,
            .btn-checkout {
                width: 100%;
                text-align: center;
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
                        <a class="nav-link active" href="cart.php">Keranjang</a>
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
                <i class="fas fa-shopping-cart me-2"></i> Keranjang Belanja
            </h1>
        </div>
    </div>

    <div class="container">
        <div class="cart-container">
            <?php if ($isEmpty): ?>
                <div class="empty-cart animate__animated animate__fadeIn">
                    <i class="fas fa-shopping-basket"></i>
                    <p>Keranjang belanja Anda kosong.</p>
                    <a href="products.php" class="btn btn-checkout">
                        <i class="fas fa-store me-2"></i>Mulai Belanja
                    </a>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                    <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($cartItemsArray as $item): ?>
                                <tr>
                                    <td class="fw-500"><?php echo htmlspecialchars($item->product_name); ?></td>
                                <td>Rp <?php echo number_format($item->product_price, 0, ',', '.'); ?></td>
                                <td>
                                        <div class="quantity-control">
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="decrease">
                                                <input type="hidden" name="product_id" value="<?php echo (string)$item->product_id; ?>">
                                                <input type="hidden" name="current_quantity" value="<?php echo $item->quantity; ?>">
                                                <button type="submit" <?php echo $item->quantity <= 1 ? 'disabled' : ''; ?>>
                                                    <i class="fas fa-minus"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="post" class="d-inline">
                                        <input type="hidden" name="action" value="update">
                                        <input type="hidden" name="product_id" value="<?php echo (string)$item->product_id; ?>">
                                                <input type="number" name="quantity" value="<?php echo $item->quantity; ?>" min="1">
                                            </form>
                                            
                                            <form method="post" class="d-inline">
                                                <input type="hidden" name="action" value="increase">
                                                <input type="hidden" name="product_id" value="<?php echo (string)$item->product_id; ?>">
                                                <input type="hidden" name="current_quantity" value="<?php echo $item->quantity; ?>">
                                                <button type="submit">
                                                    <i class="fas fa-plus"></i>
                                        </button>
                                    </form>
                                        </div>
                                </td>
                                    <td class="fw-bold">Rp <?php echo number_format($item->subtotal, 0, ',', '.'); ?></td>
                                <td>
                                        <form method="post">
                                        <input type="hidden" name="action" value="remove">
                                        <input type="hidden" name="product_id" value="<?php echo (string)$item->product_id; ?>">
                                            <button type="submit" class="btn-remove">
                                                <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
                <div class="cart-total">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="total-label">Total Belanja:</span>
            </div>
                        <div class="col-md-6 text-end">
                            <span class="total-amount">Rp <?php echo number_format($total, 0, ',', '.'); ?></span>
            </div>
    </div>
                </div>
                
                <div class="cart-actions">
                    <a href="products.php" class="btn btn-continue">
                        <i class="fas fa-arrow-left me-2"></i>Lanjut Belanja
                    </a>
                    <a href="checkout.php" class="btn btn-checkout">
                        <i class="fas fa-shopping-bag me-2"></i>Checkout
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Auto-submit when quantity is changed manually
    document.querySelectorAll('input[name="quantity"]').forEach(input => {
        input.addEventListener('change', function() {
            this.form.submit();
        });
    });
    </script>
</body>
</html> 