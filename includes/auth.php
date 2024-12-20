<?php
session_start();
require_once __DIR__ . '/../config/database.php';

function register($username, $password, $email, $role = 'user') {
    global $database;
    
    $users = $database->users;
    
    // Cek apakah username sudah ada
    $existingUser = $users->findOne(['username' => $username]);
    if ($existingUser) {
        return ['success' => false, 'message' => 'Username sudah digunakan'];
    }
    
    // Cek apakah email sudah ada
    $existingEmail = $users->findOne(['email' => $email]);
    if ($existingEmail) {
        return ['success' => false, 'message' => 'Email sudah digunakan'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user baru
    $result = $users->insertOne([
        'username' => $username,
        'password' => $hashedPassword,
        'email' => $email,
        'role' => $role,
        'created_at' => new MongoDB\BSON\UTCDateTime()
    ]);
    
    return ['success' => true, 'message' => 'Registrasi berhasil'];
}

function login($email, $password) {
    global $database;
    
    $users = $database->users;
    
    $user = $users->findOne(['email' => $email]);
    if (!$user) {
        return ['success' => false, 'message' => 'Email tidak terdaftar'];
    }
    
    if (password_verify($password, $user->password)) {
        $_SESSION['user_id'] = (string)$user->_id;
        $_SESSION['username'] = $user->username;
        $_SESSION['role'] = $user->role;
        return ['success' => true, 'message' => 'Login berhasil'];
    }
    
    return ['success' => false, 'message' => 'Password salah'];
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit;
    }
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}