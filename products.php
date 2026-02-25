<?php
session_start();
include 'config/db.php';
include 'includes/header.php';
?>

<style>
body { background: #f8f9fa; }
.card { border: none; border-radius: 14px; transition: .3s; height: 100%; min-height: 400px; }
.card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,.1); }
.filter-box { background: #fff; padding: 20px; border-radius: 14px; }
.card-img-top { height: 220px; width: 100%; object-fit: contain; background: white; padding: 10px; }
.single-product-img { object-fit: contain; max-height: 400px; width: 100%; background: #fff; padding: 20px; border-radius: 14px; }
</style>

<div class="container py-5">

<?php
/* =====================================
   1. عرض تفاصيل منتج واحد (إذا كان هناك ID)
===================================== */
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    
    $id = (int)$_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM Products WHERE pro_id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();

    if (!$product) {
        echo "<div class='alert alert-danger'>Product not found / المنتج غير موجود</div>";
    } else {
        $imagePath = "uploads/" . $product['image'];
        $imageSrc = (!empty($product['image']) && file_exists($imagePath)) ? $imagePath : "uploads/default.png";
?>
        <a href="products.php" class="btn btn-outline-secondary mb-4">
            <i class="fa fa-arrow-left"></i> Back to Products
        </a>

        <div class="row shadow-sm bg-white p-4 rounded">
            <div class="col-md-6 text-center mb-4 mb-md-0">
                <img src="<?= htmlspecialchars($imageSrc) ?>" class="img-fluid single-product-img border" alt="<?= htmlspecialchars($product['pro_name']) ?>">
            </div>

            <div class="col-md-6 d-flex flex-column justify-content-center">
                <h2 class="fw-bold text-primary"><?= htmlspecialchars($product['pro_name']) ?></h2>
                <p class="text-muted mt-3"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                <h3 class="fw-bold text-success my-3">$<?= htmlspecialchars($product['price']) ?></h3>

                <form method="POST" action="cart.php" class="mt-4">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="pro_id" value="<?= $product['pro_id'] ?>">
                    <div class="d-flex align-items-center mb-3">
                        <label class="me-3 fw-bold">Quantity:</label>
                        <input type="number" name="qty" value="1" min="1" class="form-control w-25">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fa fa-cart-plus"></i> Add to Cart
                    </button>
                </form>
            </div>
        </div>

<?php
    }

/* =====================================
   2. عرض قائمة المنتجات مع الفلاتر والبحث
===================================== */
} else {

    // المتغيرات القادمة من الرابط (الفلاتر والبحث والصفحات)
    $category = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $sort     = isset($_GET['sort']) ? $_GET['sort'] : '';
    $search   = isset($_GET['search']) ? trim($_GET['search']) : '';
    $page     = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    
    if ($page < 1) $page = 1;
    $limit = 6;
    $offset = ($page - 1) * $limit;

    // بناء استعلام قاعدة البيانات بمرونة
    $query = "SELECT * FROM Products WHERE 1";
    $params = [];
    $types = "";

    // فلتر الفئة
    if ($category > 0) {
        $query .= " AND cat_id = ?";
        $params[] = $category;
        $types .= "i";
    }

    // فلتر البحث
    if (!empty($search)) {
        $query .= " AND pro_name LIKE ?";
        $params[] = "%" . $search . "%";
        $types .= "s";
    }

    // الترتيب
    if ($sort == "low") {
        $query .= " ORDER BY price ASC";
    } elseif ($sort == "high") {
        $query .= " ORDER BY price DESC";
    } else {
        $query .= " ORDER BY pro_id DESC"; // الترتيب الافتراضي
    }

    // نظام الصفحات Pagination
    $query .= " LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= "ii";

    // التنفيذ
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
?>

    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="filter-box shadow-sm">
                <h5 class="fw-bold mb-3"><i class="fa fa-filter"></i> Filters</h5>
                <form method="GET">
                    
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Category</label>
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="0">All Categories</option>
                            <option value="1" <?= $category==1?'selected':'' ?>>Electronics</option>
                            <option value="2" <?= $category==2?'selected':'' ?>>Clothing</option>
                            <option value="3" <?= $category==3?'selected':'' ?>>Books</option>
                            <option value="4" <?= $category==4?'selected':'' ?>>Sports</option>
                            <option value="5" <?= $category==5?'selected':'' ?>>Accessories</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">Sort By Price</label>
                        <select name="sort" class="form-select" onchange="this.form.submit()">
                            <option value="">Default Sorting</option>
                            <option value="low" <?= $sort=='low'?'selected':'' ?>>Price: Low to High</option>
                            <option value="high" <?= $sort=='high'?'selected':'' ?>>Price: High to Low</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-outline-primary w-100 mt-2">Apply Filters</button>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="row">
                <?php if($result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card h-100 shadow-sm">
                            <?php
                            $imagePath = "uploads/" . $row['image'];
                            $imgSrc = (!empty($row['image']) && file_exists($imagePath)) ? $imagePath : "uploads/default.png";
                            ?>
                            <img src="<?= $imgSrc ?>" class="card-img-top border-bottom" alt="<?= htmlspecialchars($row['pro_name']) ?>">
                            
                            <div class="card-body d-flex flex-column text-center">
                                <h6 class="fw-bold"><?= htmlspecialchars($row['pro_name']) ?></h6>
                                <p class="text-success fw-bold fs-5 mb-3">$<?= $row['price'] ?></p>
                                
                                <div class="mt-auto d-flex gap-2">
                                    <a href="products.php?id=<?= $row['pro_id'] ?>" class="btn btn-outline-secondary w-50">
                                        Details
                                    </a>
                                    <form method="post" action="cart.php" class="w-50">
                                        <input type="hidden" name="action" value="add">
                                        <input type="hidden" name="pro_id" value="<?= $row['pro_id'] ?>">
                                        <button type="submit" class="btn btn-primary w-100">
                                            Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="alert alert-warning text-center">No products found matching your criteria.</div>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <?php if($page > 1): ?>
                    <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>" class="btn btn-outline-primary">&laquo; Previous</a>
                <?php else: ?>
                    <div></div>
                <?php endif; ?>
                
                <?php if($result->num_rows == $limit): ?>
                    <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>" class="btn btn-outline-primary">Next &raquo;</a>
                <?php endif; ?>
            </div>

        </div>
    </div>

<?php } ?>

</div> <?php include 'includes/footer.php'; ?>