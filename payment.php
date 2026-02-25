<?php
session_start();
include 'config/db.php'; // تم التعديل ليتوافق مع الهيكلية

// 1. التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?action=login");
    exit();
}

// 2. التحقق من وجود منتجات في السلة
if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$message = "";
$msg_type = "danger";
$total = 0;

// حساب المجموع الكلي
foreach ($_SESSION['cart'] as $item) {
    $total += $item['price'] * $item['qty'];
}

/* ---------------- Luhn Algorithm للتحقق من صحة البطاقة ---------------- */
function validateLuhn($number) {
    $number = preg_replace('/\D/', '', $number);
    $sum = 0;
    $alt = false;
    for ($i = strlen($number) - 1; $i >= 0; $i--) {
        $n = $number[$i];
        if ($alt) {
            $n *= 2;
            if ($n > 9) $n -= 9;
        }
        $sum += $n;
        $alt = !$alt;
    }
    return ($sum % 10 == 0);
}

// معالجة طلب الدفع
if ($_SERVER['REQUEST_METHOD'] == "POST") {

    $card_number = str_replace(" ", "", $_POST['card_number']);
    $exp_month   = (int)$_POST['exp_month'];
    $exp_year    = (int)$_POST['exp_year'];
    $cvv         = $_POST['cvv'];

    if (!preg_match('/^[0-9]{16}$/', $card_number)) {
        $message = "Card number must be 16 digits. / رقم البطاقة يجب أن يكون 16 رقماً.";
    } elseif (!validateLuhn($card_number)) {
        $message = "Invalid card number. / رقم البطاقة غير صالح.";
    } elseif ($exp_month < 1 || $exp_month > 12) {
        $message = "Invalid expiration month. / شهر الانتهاء غير صالح.";
    } else {
        $current_year = date("Y");
        $current_month = date("m");

        if ($exp_year < $current_year || ($exp_year == $current_year && $exp_month < $current_month)) {
            $message = "Card expired. / البطاقة منتهية الصلاحية.";
        } elseif (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
            $message = "Invalid CVV. / رمز الأمان غير صالح.";
        } else {
            // بدء معاملة قاعدة البيانات (Transaction) لضمان تنفيذ كل الخطوات أو التراجع عنها في حال الخطأ
            $conn->begin_transaction();

            try {
                // 1️⃣ إنشاء الطلب الرئيسي
                $cust_id = $_SESSION['user_id'];
                $order_date = date("Y-m-d H:i:s");

                $stmt = $conn->prepare("INSERT INTO Orders (order_date, cust_id, total_amount, status) VALUES (?, ?, ?, 'pending')");
                $stmt->bind_param("sid", $order_date, $cust_id, $total);
                $stmt->execute();
                $order_id = $stmt->insert_id;

                // 2️⃣ إدخال عناصر الطلب (المنتجات) وتقليل المخزون
                foreach ($_SESSION['cart'] as $pro_id => $item) {
                    $qty = $item['qty'];
                    $price = $item['price'];

                    // إضافة للمنتجات المطلوبة
                    $stmt_items = $conn->prepare("INSERT INTO Order_Items (order_id, pro_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                    $stmt_items->bind_param("iiid", $order_id, $pro_id, $qty, $price);
                    $stmt_items->execute();

                    // 3️⃣ تقليل المخزون
                    $update_stock = $conn->prepare("UPDATE Products SET stock = stock - ? WHERE pro_id = ?");
                    $update_stock->bind_param("ii", $qty, $pro_id);
                    $update_stock->execute();
                }

                // تأكيد المعاملة
                $conn->commit();

                // تفريغ السلة
                unset($_SESSION['cart']);

                // عرض رسالة نجاح وتوجيه المستخدم لصفحة طلباته
                $message = "Payment Successful! Redirecting to your orders... / تم الدفع بنجاح! جاري التوجيه...";
                $msg_type = "success";
                
                echo "<script>
                    setTimeout(() => { window.location = 'admin.php?section=my_orders'; }, 2500);
                </script>";

            } catch (Exception $e) {
                $conn->rollback();
                $message = "Order failed. Please try again. / فشل إتمام الطلب، حاول مرة أخرى.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Secure Checkout - Smart E-Commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        .payment-box {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border: none;
        }
        .card-preview {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border-radius: 16px;
            padding: 25px;
            height: 210px;
            margin-bottom: 30px;
            position: relative;
            box-shadow: 0 8px 20px rgba(30,60,114,0.3);
            transition: 0.5s;
        }
        .card-number {
            font-size: 24px;
            letter-spacing: 3px;
            margin-top: 40px;
            font-family: monospace;
        }
        .card-name {
            position: absolute;
            bottom: 25px;
            left: 25px;
            text-transform: uppercase;
            font-size: 14px;
        }
        .card-exp {
            position: absolute;
            bottom: 25px;
            right: 25px;
            font-size: 14px;
        }
        .card-type {
            position: absolute;
            top: 25px;
            right: 25px;
            font-weight: bold;
            font-style: italic;
            font-size: 20px;
        }
        .card-chip {
            position: absolute;
            top: 25px;
            left: 25px;
            width: 40px;
            height: 30px;
            background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
            border-radius: 5px;
        }
        .card-input {
            height: 50px;
            border-radius: 10px;
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
        }
        .card-input:focus {
            box-shadow: none;
            border-color: #2a5298;
        }
        .pay-btn {
            height: 55px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 18px;
            background: #2a5298;
            border: none;
        }
        .pay-btn:hover { background: #1e3c72; }
    </style>
</head>
<body>

<nav class="navbar navbar-light bg-white shadow-sm mb-5">
    <div class="container">
        <h4 class="text-primary fw-bold mb-0">SmartE-Commerce</h4>
        <a href="cart.php" class="btn btn-outline-secondary btn-sm">
            <i class="fa fa-arrow-left"></i> Back to Cart
        </a>
    </div>
</nav>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="payment-box">
                <h3 class="text-center mb-1 text-dark fw-bold">Secure Checkout</h3>
                <p class="text-center text-muted mb-4"><i class="fa fa-lock text-success"></i> Your payment is encrypted and secure.</p>

                <?php if($message): ?>
                    <div class="alert alert-<?= $msg_type ?> text-center rounded"><?= $message ?></div>
                <?php endif; ?>

                <div class="card-preview" id="cardPreview">
                    <div class="card-chip"></div>
                    <div class="card-type" id="cardType"></div>
                    <div class="card-number" id="previewNumber">#### #### #### ####</div>
                    <div class="card-name" id="previewName">YOUR NAME</div>
                    <div class="card-exp" id="previewExp">MM/YY</div>
                </div>

                <form method="POST" id="paymentForm">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Cardholder Name</label>
                        <input type="text" name="card_name" id="cardName" class="form-control card-input" placeholder="e.g. John Doe" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Card Number</label>
                        <input type="text" name="card_number" id="cardNumber" class="form-control card-input" placeholder="1234 5678 9012 3456" maxlength="19" required>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Month</label>
                            <input type="number" name="exp_month" id="expMonth" class="form-control card-input" placeholder="MM" min="1" max="12" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">Year</label>
                            <input type="number" name="exp_year" id="expYear" class="form-control card-input" placeholder="YYYY" min="2024" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted small fw-bold">CVV</label>
                            <input type="password" name="cvv" class="form-control card-input" placeholder="•••" maxlength="4" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 pay-btn mt-3" <?= $msg_type == 'success' ? 'disabled' : '' ?>>
                        <span id="payText">Pay $<?= number_format($total, 2) ?></span>
                        <span class="spinner-border spinner-border-sm ms-2" id="spinner" style="display:none;"></span>
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
const cardNumber = document.getElementById("cardNumber");
const previewNumber = document.getElementById("previewNumber");
const previewName = document.getElementById("previewName");
const previewExp = document.getElementById("previewExp");
const cardType = document.getElementById("cardType");
const form = document.getElementById("paymentForm");
const spinner = document.getElementById("spinner");
const payText = document.getElementById("payText");

// تحديث رقم البطاقة ونوعها في العرض التفاعلي
cardNumber.addEventListener("input", function() {
    let value = this.value.replace(/\D/g, '');
    value = value.substring(0, 16);
    this.value = value.replace(/(.{4})/g, '$1 ').trim();
    previewNumber.textContent = this.value || "#### #### #### ####";

    if (value.startsWith("4")) {
        cardType.innerHTML = '<i class="fab fa-cc-visa"></i> VISA';
    } else if (/^5[1-5]/.test(value)) {
        cardType.innerHTML = '<i class="fab fa-cc-mastercard"></i> MC';
    } else {
        cardType.textContent = "";
    }
});

// تحديث الاسم
document.getElementById("cardName").addEventListener("input", function() {
    previewName.textContent = this.value || "YOUR NAME";
});

// تحديث تاريخ الانتهاء
document.getElementById("expMonth").addEventListener("input", updateExp);
document.getElementById("expYear").addEventListener("input", updateExp);

function updateExp() {
    let m = document.getElementById("expMonth").value;
    let y = document.getElementById("expYear").value;
    if (m.length === 1) m = "0" + m; // إضافة صفر للأشهر الفردية
    if (y.length === 4) y = y.substring(2);
    previewExp.textContent = (m && y) ? m + "/" + y : "MM/YY";
}

// إظهار علامة التحميل عند الضغط على زر الدفع
form.addEventListener("submit", function() {
    spinner.style.display = "inline-block";
    payText.textContent = "Processing...";
});
</script>

</body>
</html>