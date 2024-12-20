<?php
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $database = $client->toko_monel;
} catch (Exception $e) {
    die("Error koneksi ke database: " . $e->getMessage());
}