<?php
/**
 * Home Page
 * ARTNEPAL E-commerce Website
 * 
 * Main landing page showcasing Nepali cultural arts and handicrafts
 */

// Include header
require_once 'includes/header.php';

// Get featured products
$featured_products = [];
$stmt = prepared_query("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id ORDER BY RAND() LIMIT 6");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $featured_products[] = $row;
}

// Get categories
$categories = [];
$stmt = prepared_query("SELECT * FROM categories ORDER BY category_name");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}
?>

<!-- Hero Section -->
<section style="background: linear-gradient(rgba(139,69,19,0.8), rgba(212,175,55,0.8)), url('assets/images/hero-bg.jpg'); background-size: cover; background-position: center; color: white; padding: 5rem 0; text-align: center;">
    <div class="container">
        <h1 style="font-size: 3rem; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">Welcome to ARTNEPAL</h1>
        <p style="font-size: 1.5rem; margin-bottom: 2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">Discover the Rich Heritage of Nepali Cultural Arts & Handicrafts</p>
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
            <?php if (!is_logged_in()): ?>
                <a href="register.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 1rem 2rem;">Get Started</a>
                <a href="products.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Browse Products</a>
            <?php else: ?>
                <a href="products.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">Shop Now</a>
                <a href="dashboard.php" class="btn btn-secondary" style="font-size: 1.1rem; padding: 1rem 2rem;">My Dashboard</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- About Section -->
<section class="container" style="padding: 4rem 0;">
    <div style="text-align: center; margin-bottom: 3rem;">
        <h2 style="color: #8B4513; font-size: 2.5rem; margin-bottom: 1rem;">About ARTNEPAL</h2>
        <p style="color: #666; font-size: 1.1rem; max-width: 800px; margin: 0 auto;">
            ARTNEPAL is dedicated to preserving and promoting Nepal's rich cultural heritage through traditional arts and handicrafts. 
            We connect talented local artisans with art enthusiasts worldwide, ensuring that centuries-old techniques and traditions 
            continue to thrive in the modern world.
        </p>
    </div>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-top: 3rem;">
        <div style="text-align: center; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🏺</div>
            <h3 style="color: #8B4513; margin-bottom: 1rem;">Traditional Crafts</h3>
            <p style="color: #666;">Authentic handmade crafts using traditional techniques passed down through generations</p>
        </div>
        
        <div style="text-align: center; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🎨</div>
            <h3 style="color: #8B4513; margin-bottom: 1rem;">Cultural Art</h3>
            <p style="color: #666;">Stunning artworks that represent Nepal's diverse cultural and religious heritage</p>
        </div>
        
        <div style="text-align: center; padding: 2rem; background: white; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
            <h3 style="color: #8B4513; margin-bottom: 1rem;">Support Artisans</h3>
            <p style="color: #666;">Every purchase directly supports local Nepali artisans and their communities</p>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="category-section">
    <div class="container">
        <h2 class="category-title">Explore Our Categories</h2>
        <div class="products-grid">
            <?php foreach ($categories as $category): ?>
                <div class="product-card" style="cursor: pointer;" onclick="window.location.href='products.php?category=<?php echo $category['category_id']; ?>'">
                    <div style="height: 200px; background: linear-gradient(135deg, #8B4513, #D4AF37); display: flex; align-items: center; justify-content: center; color: white; font-size: 3rem;">
                        <?php
                        // Display appropriate emoji for each category
                        $category_icons = [
                            'Clay Products' => '🏺',
                            'Thangka Paintings' => '🖼️',
                            'Wooden Crafts' => '🪵',
                            'Nepali Culture Arts' => '🎨',
                            'Nepali Culture Masks' => '🎭',
                            'God Statues' => '🗿'
                        ];
                        echo $category_icons[$category['category_name']] ?? '🎁';
                        ?>
                    </div>
                    <div class="product-info">
                        <h3 class="product-name"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                        <p style="color: #666; margin-top: 0.5rem;"><?php echo htmlspecialchars($category['description']); ?></p>
                        <button class="btn btn-primary" style="margin-top: 1rem; width: 100%;">View Products</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- Featured Products Section -->
<section class="container" style="padding: 4rem 0;">
    <h2 class="category-title">Featured Products</h2>
    <div class="products-grid">
        <?php foreach ($featured_products as $product): ?>
            <div class="product-card">
                <img src="assets/images/products/<?php echo htmlspecialchars($product['product_image']); ?>" 
                     alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                     class="product-image"
                     onerror="this.src='assets/images/placeholder.jpg';">
                <div class="product-info">
                    <h3 class="product-name"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                    <p style="color: #666; font-size: 0.9rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($product['category_name']); ?></p>
                    <div class="product-price"><?php echo format_price($product['price']); ?></div>
                    <div class="product-stock">
                        <?php if ($product['stock_quantity'] > 0): ?>
                            ✅ In Stock (<?php echo $product['stock_quantity']; ?> available)
                        <?php else: ?>
                            ❌ Out of Stock
                        <?php endif; ?>
                    </div>
                    <?php if (is_logged_in()): ?>
                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button class="btn btn-primary add-to-cart-btn" 
                                    data-product-id="<?php echo $product['product_id']; ?>"
                                    style="width: 100%; margin-top: 1rem;">
                                Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="btn btn-danger" disabled style="width: 100%; margin-top: 1rem;">
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary" style="display: block; text-align: center; margin-top: 1rem;">
                            Login to Buy
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 2rem;">
        <a href="products.php" class="btn btn-primary" style="font-size: 1.1rem; padding: 1rem 2rem;">
            View All Products
        </a>
    </div>
</section>



<?php
// Include footer
require_once 'includes/footer.php';
?>
