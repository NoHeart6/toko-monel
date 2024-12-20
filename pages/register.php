<?php
if(isset($_POST['register'])) {
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $users = $database->getDB()->users;
    
    // Cek apakah email sudah terdaftar
    $existingUser = $users->findOne(['email' => $email]);
    
    if($existingUser) {
        $error = "Email sudah terdaftar!";
    } else {
        $result = $users->insertOne([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => 'customer',
            'created_at' => new MongoDB\BSON\UTCDateTime()
        ]);
        
        if($result->getInsertedCount() > 0) {
            header('Location: index.php?page=login&registered=1');
            exit;
        } else {
            $error = "Gagal mendaftar. Silakan coba lagi.";
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Daftar Akun Baru</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="register" class="btn btn-primary w-100">Daftar</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Sudah punya akun? <a href="index.php?page=login">Masuk disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 