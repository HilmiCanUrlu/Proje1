<?php
require_once 'config/config.php';
require_once 'config/database.php';
require_once 'includes/auth.php';

session_start();

// Zaten giriş yapmış kullanıcıyı yönlendir
if(isset($_SESSION['user_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

$error = '';
$success = '';

// POST isteği kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if(empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        $db = new Database();
        $conn = $db->getConnection();
        $auth = new Auth($conn);
        
        if($auth->login($username, $password)) {
            // Başarılı giriş
            header('Location: admin/dashboard.php');
            exit();
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - Büro Otomasyon</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            margin: 0 auto;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: #fff;
            padding: 20px;
            border-bottom: 1px solid #eee;
        }
        .card-body {
            padding: 30px;
        }
        .form-control {
            height: 46px;
            border-radius: 5px;
        }
        .btn-primary {
            height: 46px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="card">
                <div class="card-header">
                    <h4 class="text-center mb-0">Büro Otomasyon</h4>
                </div>
                <div class="card-body">
                    <?php if($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="login.php">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <input type="text" 
                                   class="form-control" 
                                   id="username" 
                                   name="username" 
                                   value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                   required 
                                   autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Şifre</label>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="remember" 
                                   name="remember">
                            <label class="form-check-label" for="remember">
                                Beni Hatırla
                            </label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Giriş Yap
                        </button>
                    </form>
                </div>
            </div>
            <div class="text-center mt-3">
                <small class="text-muted">&copy; <?php echo date('Y'); ?> Büro Otomasyon</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 