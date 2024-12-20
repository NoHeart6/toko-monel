<?php
if(isset($_POST['login'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $users = $database->getDB()->users;
    $user = $users->findOne(['email' => $email]);
    
    if($user && password_verify($password, $user->password)) {
        $_SESSION['user_id'] = (string)$user->_id;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_role'] = $user->role;
        
        if($user->role === 'admin') {
            header('Location: admin/index.php');
        } else {
            header('Location: index.php');
        }
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center">Masuk</h4>
                </div>
                <div class="card-body">
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <p>Belum punya akun? <a href="index.php?page=register">Daftar disini</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> 