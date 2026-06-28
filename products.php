<?php
/**
 * Products Page
 * ARTNEPAL E-commerce Website
 * 
 * This page displays products with filtering by category
 */

// Include header
require_once 'includes/header.php';

// Check if user is logged in
if (!is_logged_in()) {
    $_SESSION['error_message'] = 'Please login to browse products.';
    redirect('login.php');
}

// Get category filter
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search_query = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';

// Get categories for filter
$categories = [];
$stmt = prepared_query("SELECT * FROM categories ORDER BY category_name");
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $categories[] = $row;
}

// Build products query
$where_conditions = [];
$params = [];
$types = '';

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
    $types .= 'i';
}

if (!empty($search_query)) {
    $where_conditions[] = "(p.product_name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search_query%";
    $params[] = "%$search_query%";
    $types .= 'ss';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get products
$products = [];
$stmt = prepared_query("
    SELECT p.*, c.category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.category_id 
    $where_clause 
    ORDER BY p.created_at DESC
", $types, $params);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $products[] = $row;
}

// Get current category name
$current_category = 'All Products';
if ($category_id > 0) {
    $stmt = prepared_query("SELECT category_name FROM categories WHERE category_id = ?", "i", [$category_id]);
    $result = mysqli_stmt_get_result($stmt);
    if ($row = mysqli_fetch_assoc($result)) {
        $current_category = $row['category_name'];
    }
}
?>

<div class="container">
    <!-- Page Header -->
    <div style="text-align: center; margin: 2rem 0;">
        <h1 style="color: #8B4513; font-size: 2.5rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($current_category); ?></h1>
        <p style="color: #666;">Discover authentic Nepali cultural arts and handicrafts</p>
    </div>
    
    <!-- Search and Filter Bar -->
    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 2rem;">
        <form method="GET" action="products.php" style="display: flex; gap: 1rem; align-items: end; flex-wrap: wrap;">
            <div style="flex: 1; min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8B4513; font-weight: bold;">Search Products</label>
                <input type="text" 
                       name="search" 
                       value="<?php echo htmlspecialchars($search_query); ?>"
                       placeholder="Search by name or description..."
                       class="form-control">
            </div>
            
            <div style="min-width: 200px;">
                <label style="display: block; margin-bottom: 0.5rem; color: #8B4513; font-weight: bold;">Category</label>
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="0">All Categories</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo $category['category_id']; ?>" <?php echo $category_id == $category['category_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($category['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div>
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
            
            <?php if (!empty($search_query) || $category_id > 0): ?>
                <div>
                    <a href="products.php" class="btn btn-secondary">Clear Filters</a>
                </div>
            <?php endif; ?>
        </form>
    </div>
    
    <!-- Results Summary -->
    <div style="margin-bottom: 2rem;">
        <p style="color: #666;">
            Found <strong><?php echo count($products); ?></strong> products
            <?php if (!empty($search_query)): ?>
                for "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
            <?php endif; ?>
        </p>
    </div>
    
    <!-- Products Grid -->
    <?php if (empty($products)): ?>
        <div style="background: #FFF8DC; padding: 3rem; border-radius: 10px; text-align: center; border: 2px dashed #DEB887; margin: 2rem 0;">
            <div style="font-size: 4rem; margin-bottom: 1rem;">🔍</div>
            <h3 style="color: #8B4513; margin-bottom: 1rem;">No Products Found</h3>
            <p style="color: #666; margin-bottom: 1.5rem;">
                <?php if (!empty($search_query) || $category_id > 0): ?>
                    Try adjusting your search criteria or browse all categories.
                <?php else: ?>
                    No products are available at the moment. Please check back later.
                <?php endif; ?>
            </p>
            <a href="products.php" class="btn btn-primary">Browse All Products</a>
        </div>
    <?php else: ?>
        <div class="products-grid" style="position: relative; z-index: 1;">
            <?php foreach ($products as $product): ?>
                <div class="product-card" style="transform: translateZ(0); backface-visibility: hidden;">
                    <div class="product-image-container" style="position: relative; width: 100%; height: 200px; overflow: hidden; background: #F8F9FA;">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['product_image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['product_name']); ?>" 
                             class="product-image"
                             style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.3s ease;"
                             loading="lazy"
                             onerror="this.src='assets/images/placeholder.svg';">
                        <div class="image-overlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to bottom, transparent 50%, rgba(0,0,0,0.1) 100%); pointer-events: none;"></div>
                    </div>
                    <div class="product-info" style="padding: 1rem;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem; flex-wrap: wrap; gap: 0.5rem;">
                            <h3 class="product-name" style="flex: 1; min-width: 0; margin: 0; font-size: 1.1rem; line-height: 1.3; word-wrap: break-word; overflow-wrap: break-word;"><?php echo htmlspecialchars($product['product_name']); ?></h3>
                            <span style="background: #D4AF37; color: #8B4513; padding: 0.25rem 0.5rem; border-radius: 15px; font-size: 0.7rem; font-weight: bold; white-space: nowrap; flex-shrink: 0;">
                                <?php echo htmlspecialchars($product['category_name']); ?>
                            </span>
                        </div>
                        
                        <p style="color: #666; font-size: 0.85rem; margin-bottom: 1rem; line-height: 1.4; height: 2.8rem; overflow: hidden; word-wrap: break-word; overflow-wrap: break-word;">
                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)) . (strlen($product['description']) > 80 ? '...' : ''); ?>
                        </p>
                        
                        <div class="product-price" style="font-size: 1.25rem; color: #DC143C; font-weight: bold; margin-bottom: 0.5rem; word-wrap: break-word;"><?php echo format_price($product['price']); ?></div>
                        
                        <div class="product-stock" style="font-size: 0.8rem; margin-bottom: 1rem; word-wrap: break-word;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <?php if ($product['stock_quantity'] <= 5): ?>
                                    <span style="color: #FF8C00;">⚠️ Only <?php echo $product['stock_quantity']; ?> left!</span>
                                <?php else: ?>
                                    <span style="color: #28A745;">✅ In Stock (<?php echo $product['stock_quantity']; ?>)</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #DC3545;">❌ Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 1rem; flex-wrap: wrap;">
                            <?php if ($product['stock_quantity'] > 0): ?>
                                <button class="btn btn-primary add-to-cart-btn" 
                                        data-product-id="<?php echo $product['product_id']; ?>"
                                        style="flex: 1; padding: 0.5rem; font-size: 0.9rem; min-width: 120px;">
                                    🛒 Add to Cart
                                </button>
                            <?php else: ?>
                                <button class="btn btn-danger" disabled style="flex: 1; padding: 0.5rem; font-size: 0.9rem; min-width: 120px;">
                                    Out of Stock
                                </button>
                            <?php endif; ?>
                            
                            <button class="btn btn-secondary" 
                                    onclick="showProductDetails(<?php echo $product['product_id']; ?>)"
                                    style="padding: 0.5rem 0.75rem; font-size: 0.9rem; min-width: 40px; flex-shrink: 0;"
                                    title="View Details">
                                👁️
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Category Quick Links -->
    <section style="margin-top: 3rem; padding-top: 2rem; border-top: 2px solid #DEB887;">
        <h2 style="color: #8B4513; text-align: center; margin-bottom: 2rem;">Browse by Category</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 1rem;">
            <a href="products.php?category=0" class="btn btn-secondary" style="text-align: center;">
                🎁 All Products
            </a>
            <?php foreach ($categories as $category): ?>
                <a href="products.php?category=<?php echo $category['category_id']; ?>" 
                   class="btn btn-secondary" 
                   style="text-align: center;">
                    <?php
                    $category_icons = [
                        'Clay Products' => '🏺',
                        'Thangka Paintings' => '🖼️',
                        'Wooden Crafts' => '🪵',
                        'Nepali Culture Arts' => '🎨',
                        'Nepali Culture Masks' => '🎭',
                        'God Statues' => '🗿'
                    ];
                    echo ($category_icons[$category['category_name']] ?? '🎁') . ' ' . htmlspecialchars($category['category_name']);
                    ?>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>

<!-- Product Details Modal -->
<div id="productModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 2rem; border-radius: 10px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeProductModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #666;">×</button>
        <div id="modalContent"></div>
    </div>
</div>

<script>
function showProductDetails(productId) {
    fetch('get_product_details.php?id=' + productId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modalContent = document.getElementById('modalContent');
                modalContent.innerHTML = `
                    <h2 style="color: #8B4513; margin-bottom: 1rem;">${data.product.product_name}</h2>
                    <img src="assets/images/products/${data.product.product_image}" 
                         alt="${data.product.product_name}" 
                         style="width: 100%; height: 300px; object-fit: cover; border-radius: 5px; margin-bottom: 1rem;"
                         onerror="this.src='assets/images/placeholder.jpg';">
                    <div style="margin-bottom: 1rem;">
                        <span style="background: #D4AF37; color: #8B4513; padding: 0.5rem 1rem; border-radius: 15px; font-weight: bold;">
                            ${data.product.category_name}
                        </span>
                    </div>
                    <p style="color: #666; margin-bottom: 1.5rem; line-height: 1.6;">${data.product.description}</p>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <div style="font-size: 1.5rem; color: #DC143C; font-weight: bold;">${data.product.formatted_price}</div>
                            <div style="color: #666; margin-top: 0.5rem;">
                                ${data.product.stock_quantity > 0 ? 
                                    (data.product.stock_quantity <= 5 ? 
                                        `⚠️ Only ${data.product.stock_quantity} left!` : 
                                        `✅ ${data.product.stock_quantity} in stock`) : 
                                    '❌ Out of Stock'}
                            </div>
                        </div>
                        ${data.product.stock_quantity > 0 ? 
                            `<button class="btn btn-primary add-to-cart-btn" data-product-id="${data.product.product_id}">
                                🛒 Add to Cart
                            </button>` : 
                            `<button class="btn btn-danger" disabled>Out of Stock</button>`
                        }
                    </div>
                `;
                document.getElementById('productModal').style.display = 'flex';
            } else {
                showNotification('Error loading product details', 'error');
            }
        })
        .catch(error => {
            showNotification('Error loading product details', 'error');
        });
}

function closeProductModal() {
    document.getElementById('productModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('productModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeProductModal();
    }
});
</script>

<style>
/* Prevent scrolling and layout shift issues */
html {
    scroll-behavior: smooth;
}

body {
    overflow-x: hidden;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #DEB887;
    border-radius: 5px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.form-control:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 5px rgba(139,69,19,0.3);
}

/* Product Grid Stability */
.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
    margin: 2rem 0;
    position: relative;
}

.product-card {
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    transform: translateZ(0);
    backface-visibility: hidden;
    will-change: transform;
    contain: layout style paint;
}

.product-card:hover {
    transform: translateY(-5px) translateZ(0);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.product-image-container {
    position: relative;
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #F8F9FA;
}

.product-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
    will-change: transform;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

/* Text containment fixes */
.product-name {
    height: 2.6rem;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    line-height: 1.3;
    word-wrap: break-word;
    overflow-wrap: break-word;
    hyphens: auto;
}

.product-info {
    padding: 1rem;
    word-wrap: break-word;
    overflow-wrap: break-word;
}

/* Button stability */
.btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-block;
    text-align: center;
    box-sizing: border-box;
    white-space: nowrap;
    transform: translateZ(0);
    word-wrap: break-word;
    overflow: hidden;
    text-overflow: ellipsis;
}

.btn-primary {
    background-color: #8B4513;
    color: white;
}

.btn-primary:hover {
    background-color: #A0522D;
    transform: translateY(-2px) translateZ(0);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-secondary {
    background-color: #D4AF37;
    color: #8B4513;
}

.btn-secondary:hover {
    background-color: #FFD700;
    transform: translateY(-2px) translateZ(0);
}

.btn-danger {
    background-color: #DC143C;
    color: white;
}

.btn-danger:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

/* Modal stability */
#productModal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(5px);
}

#productModal.show {
    display: flex;
}

/* Responsive Design */
@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
    }
    
    .product-card {
        margin: 0;
    }
    
    .product-image-container {
        height: 180px;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .product-image-container {
        height: 200px;
    }
    
    .product-card {
        margin: 0 0.5rem;
    }
}

/* Performance optimizations */
.products-grid * {
    box-sizing: border-box;
}

img {
    max-width: 100%;
    height: auto;
}

/* Prevent content jump during load */
.products-grid {
    min-height: 400px;
}

.loading-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<?php
// Include footer
require_once 'includes/footer.php';
?>
