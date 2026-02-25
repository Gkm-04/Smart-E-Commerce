<?php
// ========================
// index.php
// الصفحة الرئيسية
// ========================

session_start();
include 'config/db.php';          // استخدام ملف قاعدة البيانات المركزي
include 'includes/header.php';    // هيدر موحد لكل الصفحات

// استعلام المنتجات العشوائية لعرضها في قسم "المنتجات المميزة"
$randomProducts = $conn->query("SELECT * FROM Products ORDER BY RAND() LIMIT 8");
?>

<style>
    .hero-section {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 80px 0;
        border-radius: 0 0 30px 30px;
        margin-bottom: 50px;
        box-shadow: 0 10px 30px rgba(30,60,114,0.2);
    }
    .category-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        background: #fff;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .product-card {
        border: none;
        border-radius: 16px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        overflow: hidden;
        background: #fff;
    }
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .product-img {
        height: 220px;      /* ارتفاع ثابت */
        width: 100%;        /* عرض كامل */
        /* التغيير من cover إلى contain لعرض الصورة كاملة */
        object-fit: contain; 
        background: white;  /* خلفية بيضاء للمناطق الفارغة */
        padding: 15px;      /* إضافة حواف داخلية لكي لا تلتصق الصورة بالحفة */
        border-bottom: 1px solid #f8f9fa;
    }
    .btn-custom {
        background-color: #fff;
        color: #1e3c72;
        font-weight: bold;
        border-radius: 30px;
        padding: 12px 35px;
        transition: 0.3s;
    }
    .btn-custom:hover {
        background-color: #f8f9fa;
        color: #1e3c72;
        transform: scale(1.05);
    }
</style>

<section class="hero-section text-center">
    <div class="container">
        <h1 class="fw-bold display-5 mb-3">Welcome to Smart E-Commerce</h1>
        <p class="lead mb-4 fw-light">Discover the best products, from electronics to fashion, at unbeatable prices.</p>
        <a href="products.php" class="btn btn-custom btn-lg shadow-sm">
            <i class="fa fa-shopping-bag me-2"></i> Shop Now
        </a>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <h3 class="text-center mb-5 fw-bold text-dark">Shop by Category</h3>
        <div class="row text-center justify-content-center">
            
            <?php
            // استخدام مصفوفة لترتيب الفئات برمجياً وجعل الكود أنظف
            $categories = [
                1 => ['name' => 'Electronics', 'icon' => 'fa-laptop', 'color' => 'text-primary'],
                2 => ['name' => 'Clothing', 'icon' => 'fa-shirt', 'color' => 'text-success'],
                3 => ['name' => 'Books', 'icon' => 'fa-book', 'color' => 'text-warning'],
                4 => ['name' => 'Sports', 'icon' => 'fa-futbol', 'color' => 'text-danger'],
                5 => ['name' => 'Accessories', 'icon' => 'fa-headphones', 'color' => 'text-info']
            ];
            
            foreach($categories as $id => $cat):
            ?>
            <div class="col-lg-2 col-md-4 col-sm-6 mb-4">
                <a href="products.php?category=<?= $id ?>" class="text-decoration-none text-dark">
                    <div class="card category-card p-4 h-100 d-flex flex-column align-items-center justify-content-center">
                        <i class="fa <?= $cat['icon'] ?> fa-3x <?= $cat['color'] ?> mb-3"></i>
                        <h6 class="fw-bold m-0"><?= $cat['name'] ?></h6>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>

        </div>
    </div>
</section>

<section class="py-5 bg-light rounded-top-5">
    <div class="container py-3">
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-dark m-0">Featured Products</h3>
            <a href="products.php" class="btn btn-outline-primary rounded-pill px-4">
                View All <i class="fa fa-arrow-right ms-1"></i>
            </a>
        </div>
        
        <div class="row" id="featuredProducts">
            <?php while($row = $randomProducts->fetch_assoc()): ?>
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="card product-card h-100">
                    
                    <?php
                    $imagePath = "uploads/" . $row['image'];
                    $imgSrc = (!empty($row['image']) && file_exists($imagePath)) ? $imagePath : "uploads/default.png";
                    ?>
                    <img src="<?= htmlspecialchars($imgSrc) ?>" class="card-img-top product-img" alt="<?= htmlspecialchars($row['pro_name']) ?>">
                    
                    <div class="card-body d-flex flex-column text-center">
                        <h6 class="card-title fw-bold text-truncate text-dark mb-1" title="<?= htmlspecialchars($row['pro_name']) ?>">
                            <?= htmlspecialchars($row['pro_name']) ?>
                        </h6>
                        <p class="text-success fw-bold fs-5 mb-3">$<?= number_format($row['price'], 2) ?></p>
                        
                        <form method="post" action="cart.php" class="mt-auto">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="pro_id" value="<?= $row['pro_id'] ?>">
                            <input type="hidden" name="qty" value="1">
                            <button type="submit" class="btn btn-primary w-100 rounded-pill">
                                <i class="fa fa-cart-plus me-1"></i> Add to Cart
                            </button>
                        </form>
                        
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </div>
</section>

<?php include 'includes/footer.php'; ?>