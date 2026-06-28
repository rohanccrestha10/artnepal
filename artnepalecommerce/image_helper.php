<?php
/**
 * Product Image Helper
 * ARTNEPAL E-commerce Website
 * 
 * This script helps you manage product images
 */

// Include database configuration
require_once 'config/database.php';

// Get all products and their required images
echo "<h2>Product Image Requirements</h2>";
echo "<table border='1' style='border-collapse: collapse; margin: 20px 0;'>";
echo "<tr><th>Product ID</th><th>Product Name</th><th>Required Image File</th><th>Status</th></tr>";

$stmt = prepared_query("SELECT product_id, product_name, product_image FROM products ORDER BY product_id");
$result = mysqli_stmt_get_result($stmt);

while ($product = mysqli_fetch_assoc($result)) {
    $image_path = "assets/images/products/" . $product['product_image'];
    $status = file_exists($image_path) ? 
        "<span style='color: green;'>✅ Exists</span>" : 
        "<span style='color: red;'>❌ Missing</span>";
    
    echo "<tr>";
    echo "<td>" . $product['product_id'] . "</td>";
    echo "<td>" . htmlspecialchars($product['product_name']) . "</td>";
    echo "<td>" . htmlspecialchars($product['product_image']) . "</td>";
    echo "<td>" . $status . "</td>";
    echo "</tr>";
}

echo "</table>";

echo "<h3>How to Add Images:</h3>";
echo "<ol>";
echo "<li>Find or create images for your products</li>";
echo "<li>Rename them to match the filenames above</li>";
echo "<li>Copy them to: <code>assets/images/products/</code></li>";
echo "<li>Refresh this page to check status</li>";
echo "</ol>";

echo "<h3>Quick Download Links (Sample Images):h3>";
echo "<p>You can search for these Nepali cultural items online:</p>";
echo "<ul>";
echo "<li>Clay pots, vases, water jugs</li>";
echo "<li>Buddha mandala, Green Tara thangka paintings</li>";
echo "<li>Wooden carved windows, prayer wheels, boxes</li>";
echo "<li>Traditional banners, wall hangings, bells</li>";
echo "<li>Cultural masks (Lakhe, Bhairava)</li>";
echo "<li>Buddha, Ganesh, Shiva statues</li>";
echo "</ul>";

echo "<p><a href='products.php'>← Back to Products</a></p>";
?>
