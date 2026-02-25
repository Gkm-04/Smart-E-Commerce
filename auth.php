<?php
session_start();
include 'config/db.php'; // تم التعديل ليتوافق مع هيكلية المجلدات السابقة

$message = "";
$msg_type = "info"; // لتحديد لون التنبيه (أحمر للخطأ، أخضر للنجاح)

// تحديد نوع العملية (الافتراضي: login)
$action = isset($_GET['action']) ? $_GET['action'] : 'login';

/* ===========================
   1. تسجيل الخروج (LOGOUT)
=========================== */
if ($action == "logout") {
    session_unset();
    session_destroy();
    header("Location: auth.php?action=login");
    exit();
}

/* ===========================
   منع وصول المستخدم المسجل لصفحة الدخول
=========================== */
if (isset($_SESSION['user_id']) && $action != 'logout') {
    header("Location: products.php");
    exit();
}

/* ===========================
   2. تسجيل الدخول (LOGIN)
=========================== */
if ($action == "login" && $_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Customers WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($pass, $user['password'])) {
            // حفظ بيانات المستخدم في الجلسة
            $_SESSION['user_id'] = $user['cust_id'];
            $_SESSION['user_name'] = $user['first_name'];
            $_SESSION['role'] = $user['role'];

            // التوجيه بناءً على الصلاحية
            if ($user['role'] == "admin") {
                header("Location: admin.php?section=dashboard");
            } else {
                header("Location: products.php"); // تم التعديل ليوجهه للمنتجات
            }
            exit();
        } else {
            $message = "Incorrect password! / كلمة المرور غير صحيحة";
            $msg_type = "danger";
        }
    } else {
        $message = "Email not found! / البريد الإلكتروني غير مسجل";
        $msg_type = "danger";
    }
}

/* ===========================
   3. تسجيل حساب جديد (REGISTER)
=========================== */
if ($action == "register" && $_SERVER["REQUEST_METHOD"] == "POST") {

    $first = trim($_POST['first_name']);
    $last  = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    if (empty($first) || empty($last) || empty($email) || empty($pass)) {
        $message = "All fields are required! / جميع الحقول مطلوبة";
        $msg_type = "danger";
    } else {
        // التحقق مما إذا كان البريد مسجلاً مسبقاً
        $check = $conn->prepare("SELECT cust_id FROM Customers WHERE email=?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message = "Email already exists! / البريد الإلكتروني مسجل مسبقاً";
            $msg_type = "warning";
        } else {
            // تشفير كلمة المرور
            $hashed = password_hash($pass, PASSWORD_DEFAULT);

            // الإضافة لقاعدة البيانات
            // افترضنا أن حقل الصلاحية role يأخذ القيمة الافتراضية 'customer' من قاعدة البيانات
            $stmt = $conn->prepare("INSERT INTO Customers (first_name, last_name, email, password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $first, $last, $email, $hashed);

            if ($stmt->execute()) {
                $message = "Registration successful! You can login now. / تم التسجيل بنجاح، يمكنك تسجيل الدخول الآن";
                $msg_type = "success";
                $action = "login"; // تحويل الواجهة لتسجيل الدخول
            } else {
                $message = "Error occurred! / حدث خطأ غير متوقع";
                $msg_type = "danger";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= $action == "register" ? "Register" : "Login" ?> - Smart E-Commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart E-Commerce platform for electronics, clothing, books and accessories.">
    <meta name="keywords" content="Ecommerce, Online Store, Gaza, Electronics, Shopping">
    <meta name="author" content="Ghassan Meqdad">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .auth-card { 
            background: #fff; 
            padding: 40px; 
            border-radius: 14px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            border: none;
        }
        .form-control { border-radius: 8px; padding: 12px; }
        .btn-primary { border-radius: 8px; padding: 10px; font-weight: bold; }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-light bg-white shadow-sm mb-5">
    <div class="container">
        <h4 class="text-primary fw-bold mb-0">SmartE-Commerce</h4>
        <a href="products.php" class="btn btn-outline-primary btn-sm">
            <i class="fa fa-shopping-bag"></i> Browse Products
        </a>
    </div>
</nav>

<div class="container d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="auth-card w-100" style="max-width: 450px;">
        
        <div class="text-center mb-4">
            <i class="fa <?= $action == "register" ? "fa-user-plus" : "fa-sign-in-alt" ?> fa-3x text-primary mb-3"></i>
            <h3 class="fw-bold"><?= $action == "register" ? "Create Account" : "Welcome Back!" ?></h3>
            <p class="text-muted"><?= $action == "register" ? "Join our Smart E-Commerce community" : "Please login to your account" ?></p>
        </div>

        <?php if($message): ?>
            <div class="alert alert-<?= $msg_type ?> text-center rounded"><?= $message ?></div>
        <?php endif; ?>

        <?php if($action == "login"): ?>

            <form method="POST" action="auth.php?action=login">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-envelope text-muted"></i></span>
                        <input type="email" name="email" class="form-control border-start-0" placeholder="name@example.com" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fa fa-lock text-muted"></i></span>
                        <input type="password" name="password" class="form-control border-start-0" placeholder="••••••••" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Login <i class="fa fa-arrow-right ms-1"></i>
                </button>

                <p class="text-center text-muted m-0">
                    Don't have an account? <a href="auth.php?action=register" class="fw-bold text-decoration-none">Register here</a>
                </p>
            </form>

        <?php else: ?>

            <form method="POST" action="auth.php?action=register">
                <div class="row mb-3">
                    <div class="col-6 pr-1">
                        <label class="form-label text-muted small fw-bold">First Name</label>
                        <input type="text" name="first_name" class="form-control" placeholder="John" required>
                    </div>
                    <div class="col-6 pl-1">
                        <label class="form-label text-muted small fw-bold">Last Name</label>
                        <input type="text" name="last_name" class="form-control" placeholder="Doe" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="name@example.com" required>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a strong password" required>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">
                    Create Account <i class="fa fa-user-check ms-1"></i>
                </button>

                <p class="text-center text-muted m-0">
                    Already have an account? <a href="auth.php?action=login" class="fw-bold text-decoration-none">Login here</a>
                </p>
            </form>

        <?php endif; ?>

    </div>
</div>

<?php 
// إذا كان لديك ملف فوتر مفصول يمكنك استدعاؤه هنا
// include 'includes/footer.php'; 
?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>