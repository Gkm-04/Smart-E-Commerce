<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// حساب عدد العناصر في السلة لعرضها فوق أيقونة السلة
$cart_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Smart E-Commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Smart E-Commerce platform for electronics, clothing, books and accessories.">
    <meta name="keywords" content="Ecommerce, Online Store, Gaza, Electronics, Shopping">
    <meta name="author" content="Ghassan Meqdad">
    <meta name="robots" content="index, follow">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta property="og:title" content="Smart E-Commerce">
    <meta property="og:description" content="Shop smart with our online store">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh; /* لجعل الفوتر ينزل لأسفل الصفحة دائماً */
        }
        
        /* Navbar Styles */
        .navbar {
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 15px 0;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
            color: #1e3c72 !important;
        }
        .nav-link {
            font-weight: 500;
            color: #4a5568 !important;
            transition: color 0.3s;
        }
        .nav-link:hover, .nav-link.active {
            color: #1e3c72 !important;
        }
        
        /* Cart Badge */
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -10px;
            font-size: 0.70rem;
        }
        
        /* Global Card Styles for store items */
        .card {
            border: none;
            border-radius: 15px;
            transition: 0.3s;
            height: 100%;
            background: #fff;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .card-img-top {
            height: 220px;
            width: 100%;
            object-fit: contain;
            background: white;
            padding: 10px;
            border-bottom: 1px solid #f8f9fa;
        }

        /* Footer Global Style */
        .footer {
            background-color: #1E293B;
            color: white;
            padding: 20px 0;
            margin-top: auto; /* يدفعه دائماً للأسفل */
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
            <i class="fa fa-shopping-bag text-primary"></i> SmartStore
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">Products</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-4 mt-3 mt-lg-0">
                
                <a href="cart.php" class="text-dark text-decoration-none position-relative">
                    <i class="fa fa-shopping-cart fa-lg"></i>
                    <?php if($cart_count > 0): ?>
                        <span class="badge bg-danger rounded-pill cart-badge"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>

                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <a class="text-decoration-none text-dark fw-bold dropdown-toggle d-flex align-items-center gap-2" href="#" role="button" id="userMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa fa-user-circle fa-xl text-primary"></i>
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-3 rounded-3" aria-labelledby="userMenu">
                            <li>
                                <a class="dropdown-item py-2" href="admin.php?section=my_orders">
                                    <i class="fa fa-box-open me-2 text-muted"></i> My Orders
                                </a>
                            </li>
                            
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                                <li>
                                    <a class="dropdown-item py-2" href="admin.php?section=dashboard">
                                        <i class="fa fa-chart-pie me-2 text-muted"></i> Dashboard
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item py-2 text-danger" href="auth.php?action=logout">
                                    <i class="fa fa-sign-out-alt me-2"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>
                <?php else: ?>
                    <div class="d-flex gap-2">
                        <a href="auth.php?action=login" class="btn btn-outline-primary rounded-pill px-4 fw-bold">Login</a>
                        <a href="auth.php?action=register" class="btn btn-primary rounded-pill px-4 fw-bold">Sign Up</a>
                    </div>
                <?php endif; ?>
                
            </div>
        </div>
    </div>
</nav>

<div class="container py-4 flex-grow-1">