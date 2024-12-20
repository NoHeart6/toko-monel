<?php
require_once 'includes/auth.php';
require_once 'includes/order.php';

requireLogin();

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['order_id']) || !isset($_FILES['payment_proof'])) {
        $response['message'] = 'Data tidak lengkap';
    } else {
        $orderId = $_POST['order_id'];
        $file = $_FILES['payment_proof'];
        
        // Validasi order
        $order = getOrderById($orderId);
        if (!$order || $order->user_id != $_SESSION['user_id']) {
            $response['message'] = 'Pesanan tidak ditemukan';
        } else if ($order->payment_status !== 'pending') {
            $response['message'] = 'Status pembayaran tidak valid';
        } else {
            // Validasi file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file['type'], $allowedTypes)) {
                $response['message'] = 'Format file tidak didukung. Gunakan JPG, JPEG, atau PNG';
            } else if ($file['size'] > $maxSize) {
                $response['message'] = 'Ukuran file terlalu besar. Maksimal 2MB';
            } else if ($file['error'] !== UPLOAD_ERR_OK) {
                $response['message'] = 'Gagal mengupload file';
            } else {
                // Buat direktori jika belum ada
                $uploadDir = 'uploads/payment_proofs/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                
                // Generate nama file unik
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid() . '_' . time() . '.' . $extension;
                $destination = $uploadDir . $filename;
                
                // Upload file
                if (move_uploaded_file($file['tmp_name'], $destination)) {
                    // Simpan path relatif untuk akses via URL
                    $relativePath = '../' . $destination; // Path relatif dari folder admin
                    
                    // Update database
                    $result = uploadPaymentProof($orderId, $relativePath);
                    if ($result['success']) {
                        $response = [
                            'success' => true,
                            'message' => 'Bukti pembayaran berhasil diupload'
                        ];
                    } else {
                        unlink($destination); // Hapus file jika gagal update database
                        $response['message'] = 'Gagal menyimpan data bukti pembayaran';
                    }
                } else {
                    $response['message'] = 'Gagal mengupload file';
                }
            }
        }
    }
}

// Redirect kembali ke halaman orders dengan pesan
$status = $response['success'] ? 'success' : 'error';
$message = urlencode($response['message']);
header("Location: orders.php?status={$status}&message={$message}");
exit; 