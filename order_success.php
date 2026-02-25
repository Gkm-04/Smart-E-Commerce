<?php
session_start();

// التحقق من تسجيل الدخول لضمان حماية مسار الصفحة
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?action=login");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Success - Smart E-Commerce</title>
    <meta name="description" content="Smart E-Commerce platform for electronics, clothing, books and accessories.">
    <meta name="keywords" content="Ecommerce, Online Store, Gaza, Electronics, Shopping">
    <meta name="author" content="Ghassan Meqdad">
    <meta property="og:title" content="Smart E-Commerce">
    <meta property="og:description" content="Shop smart with our online store">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { 
            background: linear-gradient(135deg, #f8f9fa 0%, #e2e8f0 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .success-card {
            background: #fff;
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            border: none;
            max-width: 550px;
            margin: auto;
        }
        .icon-wrapper {
            width: 110px;
            height: 110px;
            background-color: #d1e7dd; /* لون أخضر فاتح للخلفية */
            color: #198754; /* لون أيقونة النجاح */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px auto;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        
        /* تأثير حركة بسيط للأيقونة */
        @keyframes popIn {
            0% { transform: scale(0); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm w-100">
    <div class="container">
        <h4 class="text-primary fw-bold mb-0">SmartE-Commerce</h4>
        <div class="text-muted small">
            <i class="fa fa-user-circle me-1"></i> <?= htmlspecialchars($_SESSION['user_name']) ?>
        </div>
    </div>
</nav>

<div class="container flex-grow-1 d-flex justify-content-center align-items-center py-5">
    <div class="success-card text-center w-100">
        
        <div class="icon-wrapper">
            <i class="fa fa-check fa-4x"></i>
        </div>

        <h2 class="text-success fw-bold mb-3">Order Placed Successfully!</h2>
        <p class="text-muted fs-5 mb-4">
            Thank you for shopping with us. Your payment has been processed and your order is now being prepared for shipment.
        </p>

        <div class="d-grid gap-3 d-md-flex justify-content-md-center mt-4">
            <a href="admin.php?section=my_orders" class="btn btn-primary btn-lg px-4 rounded-pill">
                <i class="fa fa-box-open me-2"></i> View My Orders
            </a>
            <a href="products.php" class="btn btn-outline-secondary btn-lg px-4 rounded-pill">
                <i class="fa fa-shopping-bag me-2"></i> Continue Shopping
            </a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>