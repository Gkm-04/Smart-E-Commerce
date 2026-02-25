<?php
session_start();
include 'config/db.php';

// التحقق من تسجيل الدخول، وتوجيهه لصفحة الدخول الصحيحة
if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php?action=login");
    exit();
}

$section = $_GET['section'] ?? 'my_orders';
$isAdmin = ($_SESSION['role'] == 'admin');

/* منع المستخدم العادي من دخول أقسام الإدارة */
if (!$isAdmin && $section != 'my_orders') {
    header("Location: admin.php?section=my_orders");
    exit();
}

// إذا كان الأدمن يدخل لأول مرة، نوجهه للوحة الإحصائيات الافتراضية
if ($isAdmin && $section == 'my_orders' && !isset($_GET['section'])) {
    $section = 'dashboard';
}

/* ===========================
   UPDATE ORDER STATUS
=========================== */
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE Orders SET status=? WHERE order_id=?");
    $stmt->bind_param("si", $status, $order_id);
    $stmt->execute();

    header("Location: admin.php?section=orders");
    exit();
}

/* ===========================
   DELETE PRODUCT
=========================== */
if (isset($_GET['delete']) && $isAdmin) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM Products WHERE pro_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin.php?section=products");
    exit();
}

/* ===========================
   ADD / UPDATE PRODUCT
=========================== */
if (isset($_POST['save_product']) && $isAdmin) {
    $name  = $_POST['name'];
    $desc  = $_POST['description'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $cat   = $_POST['category'];
    $id    = $_POST['pro_id'];
    $image = "";

    if (!empty($_FILES['image']['name'])) {
        $image = time() . "_" . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/" . $image);
    }

    if (empty($id)) {
        $stmt = $conn->prepare("INSERT INTO Products (pro_name, description, price, stock, cat_id, image) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssdiis", $name, $desc, $price, $stock, $cat, $image);
    } else {
        if ($image != "") {
            $stmt = $conn->prepare("UPDATE Products SET pro_name=?, description=?, price=?, stock=?, cat_id=?, image=? WHERE pro_id=?");
            $stmt->bind_param("ssdiisi", $name, $desc, $price, $stock, $cat, $image, $id);
        } else {
            $stmt = $conn->prepare("UPDATE Products SET pro_name=?, description=?, price=?, stock=?, cat_id=? WHERE pro_id=?");
            $stmt->bind_param("ssdiis", $name, $desc, $price, $stock, $cat, $id);
        }
    }

    $stmt->execute();
    header("Location: admin.php?section=products");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Smart E-Commerce</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; overflow-x: hidden; } /* يمنع التمرير الأفقي لكامل الصفحة */
        .sidebar { min-height: 100vh; background-color: #1E293B; box-shadow: 2px 0 10px rgba(0,0,0,0.1); }
        .sidebar a { color: #cbd5e1; text-decoration: none; padding: 12px 20px; display: block; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background-color: #334155; color: #fff; }
        .dashboard-card { border: none; border-radius: 12px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .content-area { padding: 30px; }
    </style>
</head>
<body>

<div class="container-fluid p-0">
    <div class="row g-0"> <div class="sidebar col-md-3 col-lg-2 p-3 text-white">
            <h4 class="text-center fw-bold py-3 mb-4 border-bottom border-secondary">Smart Admin</h4>
            
            <?php if ($isAdmin): ?>
                <a href="admin.php?section=dashboard" class="<?= $section=='dashboard'?'active':'' ?>">
                    <i class="fa fa-chart-pie me-2"></i> Dashboard
                </a>
                <a href="admin.php?section=orders" class="<?= $section=='orders'?'active':'' ?>">
                    <i class="fa fa-shopping-cart me-2"></i> All Orders
                </a>
                <a href="admin.php?section=products" class="<?= $section=='products'?'active':'' ?>">
                    <i class="fa fa-box me-2"></i> Manage Products
                </a>
            <?php endif; ?>

            <a href="admin.php?section=my_orders" class="<?= $section=='my_orders'?'active':'' ?>">
                <i class="fa fa-list-alt me-2"></i> My Orders
            </a>
            
            <hr class="border-secondary my-4">
            
            <a href="products.php" class="text-info">
                <i class="fa fa-store me-2"></i> Back to Store
            </a>
            <a href="auth.php?action=logout" class="text-danger mt-2">
                <i class="fa fa-sign-out-alt me-2"></i> Logout
            </a>
        </div>

        <div class="col-md-9 col-lg-10 content-area"> <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold m-0 text-dark">
                    <?= ucfirst(str_replace('_', ' ', $section)) ?>
                </h2>
                <div class="text-muted">
                    <i class="fa fa-user-circle me-1"></i> Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>
                </div>
            </div>

            <?php if ($section == "dashboard" && $isAdmin): ?>
                <?php
                $total_orders = $conn->query("SELECT COUNT(*) as cnt FROM Orders")->fetch_assoc()['cnt'];
                $total_products = $conn->query("SELECT COUNT(*) as cnt FROM Products")->fetch_assoc()['cnt'];
                $total_users = $conn->query("SELECT COUNT(*) as cnt FROM Customers")->fetch_assoc()['cnt'];
                $total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM Orders")->fetch_assoc()['revenue'];
                ?>
                <div class="row">
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-primary text-white p-4">
                            <h6><i class="fa fa-shopping-cart me-2"></i>Total Orders</h6>
                            <h2 class="m-0 fw-bold"><?= $total_orders ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-success text-white p-4">
                            <h6><i class="fa fa-box me-2"></i>Total Products</h6>
                            <h2 class="m-0 fw-bold"><?= $total_products ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-warning text-dark p-4">
                            <h6><i class="fa fa-users me-2"></i>Total Users</h6>
                            <h2 class="m-0 fw-bold"><?= $total_users ?></h2>
                        </div>
                    </div>
                    <div class="col-md-3 mb-4">
                        <div class="card dashboard-card bg-info text-white p-4">
                            <h6><i class="fa fa-dollar-sign me-2"></i>Total Revenue</h6>
                            <h2 class="m-0 fw-bold">$<?= number_format($total_revenue, 2) ?></h2>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section == "orders" && $isAdmin): ?>
                <?php
                $orders = $conn->query("
                    SELECT o.order_id, o.order_date, o.status, o.total_amount, c.first_name, c.last_name
                    FROM Orders o LEFT JOIN Customers c ON o.cust_id = c.cust_id ORDER BY o.order_date DESC
                ");
                ?>
                <div class="card dashboard-card p-4">
                    <div class="table-responsive"> <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($o = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td class="fw-bold">#<?= $o['order_id'] ?></td>
                                    <td><?= htmlspecialchars($o['first_name'] . ' ' . $o['last_name']) ?></td>
                                    <td><?= date('Y-m-d H:i', strtotime($o['order_date'])) ?></td>
                                    <td class="fw-bold text-success">$<?= number_format($o['total_amount'], 2) ?></td>
                                    <td>
                                        <span class="badge bg-<?= $o['status']=='delivered'?'success':($o['status']=='pending'?'warning text-dark':($o['status']=='cancelled'?'danger':'primary')) ?>">
                                            <?= ucfirst($o['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                            <select name="status" class="form-select form-select-sm" style="width: 120px;">
                                                <option value="pending" <?= $o['status']=='pending'?'selected':'' ?>>Pending</option>
                                                <option value="shipped" <?= $o['status']=='shipped'?'selected':'' ?>>Shipped</option>
                                                <option value="delivered" <?= $o['status']=='delivered'?'selected':'' ?>>Delivered</option>
                                                <option value="cancelled" <?= $o['status']=='cancelled'?'selected':'' ?>>Cancelled</option>
                                            </select>
                                            <button name="update_status" class="btn btn-sm btn-primary"><i class="fa fa-save"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section == "products" && $isAdmin): ?>
                <?php
                $edit_data = null;
                if (isset($_GET['edit'])) {
                    $edit_id = intval($_GET['edit']);
                    $edit_data = $conn->query("SELECT * FROM Products WHERE pro_id=$edit_id")->fetch_assoc();
                }
                $products = $conn->query("SELECT p.*, c.cat_name FROM Products p LEFT JOIN Categories c ON p.cat_id=c.cat_id ORDER BY p.pro_id DESC");
                $cats = $conn->query("SELECT * FROM Categories");
                ?>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <div class="card dashboard-card p-4">
                            <h5 class="mb-3 fw-bold"><?= $edit_data ? "Edit Product" : "Add New Product" ?></h5>
                            <form method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="pro_id" value="<?= $edit_data['pro_id'] ?? '' ?>">
                                <div class="mb-2"><input class="form-control" name="name" placeholder="Product Name" value="<?= $edit_data['pro_name'] ?? '' ?>" required></div>
                                <div class="mb-2"><textarea class="form-control" name="description" rows="3" placeholder="Description"><?= $edit_data['description'] ?? '' ?></textarea></div>
                                <div class="row mb-2">
                                    <div class="col"><input type="number" step="0.01" class="form-control" name="price" placeholder="Price ($)" value="<?= $edit_data['price'] ?? '' ?>" required></div>
                                    <div class="col"><input type="number" class="form-control" name="stock" placeholder="Stock Qty" value="<?= $edit_data['stock'] ?? '' ?>" required></div>
                                </div>
                                <div class="mb-2">
                                    <select name="category" class="form-select">
                                        <?php while ($c = $cats->fetch_assoc()): ?>
                                            <option value="<?= $c['cat_id'] ?>" <?= isset($edit_data['cat_id']) && $edit_data['cat_id']==$c['cat_id'] ? 'selected' : '' ?>><?= $c['cat_name'] ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="mb-3"><input type="file" name="image" class="form-control" <?= !$edit_data ? 'required' : '' ?>></div>
                                <?php if ($edit_data && $edit_data['image']): ?>
                                    <div class="mb-3"><small>Current Image:</small><br><img src="uploads/<?= $edit_data['image'] ?>" width="80" class="rounded"></div>
                                <?php endif; ?>
                                <button name="save_product" class="btn btn-<?= $edit_data ? 'warning' : 'success' ?> w-100">
                                    <i class="fa <?= $edit_data ? 'fa-edit' : 'fa-plus' ?>"></i> <?= $edit_data ? "Update Product" : "Add Product" ?>
                                </button>
                                <?php if ($edit_data): ?>
                                    <a href="admin.php?section=products" class="btn btn-light w-100 mt-2">Cancel</a>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="card dashboard-card p-4">
                            <div class="table-responsive"> <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $products->fetch_assoc()): ?>
                                        <tr>
                                            <td>
                                                <?php $img = !empty($row['image']) ? "uploads/".$row['image'] : "uploads/default.png"; ?>
                                                <img src="<?= $img ?>" width="50" height="50" class="rounded object-fit-cover">
                                            </td>
                                            <td class="fw-bold"><?= htmlspecialchars($row['pro_name']) ?></td>
                                            <td><?= $row['cat_name'] ?></td>
                                            <td class="text-success fw-bold">$<?= $row['price'] ?></td>
                                            <td><?= $row['stock'] ?></td>
                                            <td>
                                                <a href="admin.php?section=products&edit=<?= $row['pro_id'] ?>" class="btn btn-sm btn-outline-warning"><i class="fa fa-edit"></i></a>
                                                <a href="admin.php?section=products&delete=<?= $row['pro_id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure?')"><i class="fa fa-trash"></i></a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($section == "my_orders"): ?>
                <?php
                $cust_id = $_SESSION['user_id'];
                $query = "
                    SELECT o.order_id, o.order_date, o.status, o.total_amount, 
                           oi.pro_id, oi.quantity, oi.unit_price, p.pro_name
                    FROM Orders o JOIN Order_Items oi ON o.order_id = oi.order_id
                    JOIN Products p ON oi.pro_id = p.pro_id WHERE o.cust_id=? ORDER BY o.order_date DESC
                ";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $cust_id);
                $stmt->execute();
                $result = $stmt->get_result();

                // تجميع البيانات لتسهيل عرضها
                $my_orders = [];
                while ($row = $result->fetch_assoc()) {
                    $oid = $row['order_id'];
                    if (!isset($my_orders[$oid])) {
                        $my_orders[$oid] = [
                            'date' => $row['order_date'],
                            'status' => $row['status'],
                            'total' => $row['total_amount'],
                            'items' => []
                        ];
                    }
                    $my_orders[$oid]['items'][] = $row;
                }
                ?>

                <?php if (empty($my_orders)): ?>
                    <div class="alert alert-info text-center py-5">
                        <i class="fa fa-box-open fa-3x mb-3 text-muted"></i>
                        <h4>You haven't placed any orders yet.</h4>
                        <a href="products.php" class="btn btn-primary mt-3">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                    <?php foreach ($my_orders as $order_id => $order_data): ?>
                        <div class="col-12 mb-4">
                            <div class="card dashboard-card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center p-3">
                                    <div>
                                        <h5 class="m-0 fw-bold">Order #<?= $order_id ?></h5>
                                        <small class="text-muted"><i class="fa fa-calendar-alt"></i> <?= date('F j, Y, g:i a', strtotime($order_data['date'])) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-<?= $order_data['status']=='delivered'?'success':($order_data['status']=='pending'?'warning text-dark':($order_data['status']=='cancelled'?'danger':'primary')) ?> fs-6 mb-1">
                                            <?= ucfirst($order_data['status']) ?>
                                        </span>
                                        <h5 class="m-0 text-success fw-bold">Total: $<?= number_format($order_data['total'], 2) ?></h5>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive"> <table class="table table-borderless m-0 mb-0">
                                            <thead class="table-light border-bottom">
                                                <tr>
                                                    <th class="ps-4">Product</th>
                                                    <th class="text-center">Qty</th>
                                                    <th class="text-center">Price</th>
                                                    <th class="text-end pe-4">Subtotal</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($order_data['items'] as $item): ?>
                                                <tr class="border-bottom">
                                                    <td class="ps-4"><?= htmlspecialchars($item['pro_name']) ?></td>
                                                    <td class="text-center"><?= $item['quantity'] ?></td>
                                                    <td class="text-center">$<?= number_format($item['unit_price'], 2) ?></td>
                                                    <td class="text-end pe-4 fw-bold">$<?= number_format($item['quantity'] * $item['unit_price'], 2) ?></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>