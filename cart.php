<?php
session_start();
include 'config/db.php';

// التأكد من وجود مصفوفة السلة في الجلسة
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

/* ========================================================
   أولاً: كل العمليات البرمجية (يجب أن تكون قبل الهيدر)
======================================================== */

// 1. إضافة أو تحديث منتج في السلة
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['action']) && $_POST['action'] == "add") {
    
    $pro_id = intval($_POST['pro_id']);
    $qty = isset($_POST['qty']) ? intval($_POST['qty']) : 1;
    if ($qty < 1) $qty = 1;

    $stmt = $conn->prepare("SELECT pro_name, price FROM Products WHERE pro_id=?");
    $stmt->bind_param("i", $pro_id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if ($product) {
        if (isset($_SESSION['cart'][$pro_id])) {
            $_SESSION['cart'][$pro_id]['qty'] += $qty;
        } else {
            $_SESSION['cart'][$pro_id] = [
                "name"  => $product['pro_name'],
                "price" => $product['price'],
                "qty"   => $qty
            ];
        }
    }

    header("Location: cart.php");
    exit();
}

// 2. حذف منتج معين من السلة
if (isset($_GET['remove'])) {
    $id = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: cart.php");
    exit();
}

// 3. تفريغ السلة بالكامل
if (isset($_GET['clear'])) {
    unset($_SESSION['cart']);
    header("Location: cart.php");
    exit();
}

/* ========================================================
   ثانياً: بعد انتهاء العمليات والتوجيه، نستدعي الهيدر والـ HTML
======================================================== */
include 'includes/header.php';
?>

<style>
    .cart-container { 
        background: #fff; 
        padding: 40px; 
        border-radius: 20px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
    }
    .table th { 
        background-color: #f8f9fa; 
        color: #4a5568;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
        border-top: none;
    }
    .table td {
        vertical-align: middle;
        color: #2d3748;
    }
</style>

<div class="cart-container mt-2 mb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
        <h2 class="fw-bold m-0 text-dark">
            <i class="fa fa-shopping-cart text-primary me-2"></i> Your Shopping Cart
        </h2>
        <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa fa-arrow-left me-1"></i> Continue Shopping
        </a>
    </div>

    <?php if (empty($_SESSION['cart'])): ?>
        <div class="alert alert-light text-center p-5 rounded-4 border">
            <div class="mb-4">
                <i class="fa fa-shopping-basket fa-4x text-muted opacity-50"></i>
            </div>
            <h4 class="fw-bold text-dark">Your cart is currently empty.</h4>
            <p class="text-muted mb-4">Looks like you haven't added anything yet.</p>
            <a href="products.php" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Start Shopping</a>
        </div>
    <?php else: ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Product Name</th>
                        <th class="text-center">Price</th>
                        <th class="text-center">Quantity</th>
                        <th class="text-center">Total</th>
                        <th class="text-center pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $grand_total = 0;
                    foreach ($_SESSION['cart'] as $pro_id => $item):
                        $item_total = $item['price'] * $item['qty'];
                        $grand_total += $item_total;
                    ?>
                    <tr>
                        <td class="fw-bold ps-3"><?= htmlspecialchars($item['name']) ?></td>
                        <td class="text-center">$<?= number_format($item['price'], 2) ?></td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border px-3 py-2 fs-6 rounded-pill">
                                <?= $item['qty'] ?>
                            </span>
                        </td>
                        <td class="text-center fw-bold text-success">$<?= number_format($item_total, 2) ?></td>
                        <td class="text-center pe-3">
                            <a href="cart.php?remove=<?= $pro_id ?>" class="btn btn-sm btn-outline-danger rounded-circle p-2" title="Remove Item">
                                <i class="fa fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="row mt-5 align-items-center bg-light p-4 rounded-4">
            <div class="col-md-6 mb-3 mb-md-0">
                <a href="cart.php?clear=1" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('Are you sure you want to clear your cart?');">
                    <i class="fa fa-trash-alt me-1"></i> Clear Cart
                </a>
            </div>
            <div class="col-md-6 text-md-end">
                <h4 class="fw-bold mb-3 text-dark">
                    Grand Total: <span class="text-success ms-2">$<?= number_format($grand_total, 2) ?></span>
                </h4>
                <a href="payment.php" class="btn btn-success btn-lg rounded-pill px-5 shadow-sm">
                    Proceed to Checkout <i class="fa fa-arrow-right ms-2"></i>
                </a>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>