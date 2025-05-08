<?php
session_start();
require_once "database.php";
require_once "Logger.php";

class Login {
    private $conn;
    private $table_name = "personel";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($kullanici_adi, $sifre) {
        try {
            // SQL sorgusunu hazırla
            $query = "SELECT personel_id, ad, soyad, kullanici_adi, sifre 
                     FROM " . $this->table_name . " 
                     WHERE kullanici_adi = :kullanici_adi 
                     LIMIT 1";

            $stmt = $this->conn->prepare($query);

            // Parametreleri bağla
            $stmt->bindParam(":kullanici_adi", $kullanici_adi);
            $stmt->execute();

            if($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Şifre kontrolü (Gerçek uygulamada hash kullanılmalı)
                if($sifre == $row['sifre']) {
                    // Oturum bilgilerini kaydet
                    $_SESSION['loggedin'] = true;
                    $_SESSION['personel_id'] = $row['personel_id'];
                    $_SESSION['kullanici_adi'] = $row['kullanici_adi'];
                    $_SESSION['ad_soyad'] = $row['ad'] . " " . $row['soyad'];
                    
                    // Log kaydı oluştur
                    $logger = new Logger($this->conn);
                    $logger->logKaydet(
                        $row['personel_id'],
                        'LOGIN',
                        "Kullanıcı girişi yapıldı: " . $row['kullanici_adi']
                    );
                    
                    return true;
                } else {
                    // Başarısız giriş denemesi logu
                    $logger = new Logger($this->conn);
                    $logger->logKaydet(
                        null,
                        'LOGIN_FAILED',
                        "Başarısız giriş denemesi: " . $kullanici_adi
                    );
                }
            }
            
            return false;
        } catch(PDOException $e) {
            return false;
        }
    }
}

// Login form gönderildiğinde
if($_SERVER["REQUEST_METHOD"] == "POST") {
    $database = new Database();
    $db = $database->getConnection();
    $login = new Login($db);

    $kullanici_adi = $_POST['kullanici_adi'] ?? '';
    $sifre = $_POST['sifre'] ?? '';

    if($login->login($kullanici_adi, $sifre)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $error_message = "Geçersiz kullanıcı adı veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Personel Girişi</h3>
                    </div>
                    <div class="card-body">
                        <?php if(isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="kullanici_adi" class="form-label">Kullanıcı Adı</label>
                                <input type="text" class="form-control" id="kullanici_adi" name="kullanici_adi" required>
                            </div>
                            <div class="mb-3">
                                <label for="sifre" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="sifre" name="sifre" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Giriş Yap</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 