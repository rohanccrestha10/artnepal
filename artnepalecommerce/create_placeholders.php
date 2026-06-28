<?php
/**
 * Create Placeholder Images
 * ARTNEPAL E-commerce Website
 * 
 * This script creates placeholder images for products
 */

// Include database configuration
require_once 'config/database.php';

// Create placeholder directory if it doesn't exist
$products_dir = 'assets/images/products';
if (!file_exists($products_dir)) {
    mkdir($products_dir, 0777, true);
}

echo "<h2>Creating Placeholder Images...</h2>";

// Get all products
$stmt = prepared_query("SELECT product_id, product_name, product_image, category_name FROM products p JOIN categories c ON p.category_id = c.category_id");
$result = mysqli_stmt_get_result($stmt);

$category_colors = [
    'Clay Products' => '#8B4513',
    'Thangka Paintings' => '#D4AF37',
    'Wooden Crafts' => '#654321',
    'Nepali Culture Arts' => '#DC143C',
    'Nepali Culture Masks' => '#FF8C00',
    'God Statues' => '#4B0082'
];

$category_icons = [
    'Clay Products' => '🏺',
    'Thangka Paintings' => '🖼️',
    'Wooden Crafts' => '🪵',
    'Nepali Culture Arts' => '🎨',
    'Nepali Culture Masks' => '🎭',
    'God Statues' => '🗿'
];

while ($product = mysqli_fetch_assoc($result)) {
    $image_path = $products_dir . '/' . $product['product_image'];
    
    if (!file_exists($image_path)) {
        // Create a simple SVG placeholder
        $color = $category_colors[$product['category_name']] ?? '#666';
        $icon = $category_icons[$product['category_name']] ?? '🎁';
        
        $svg = <<<SVG
<svg width="300" height="200" xmlns="http://www.w3.org/2000/svg">
    <rect width="300" height="200" fill="#F8F9FA"/>
    <rect width="300" height="200" fill="url(#pattern)" opacity="0.3"/>
    <text x="150" y="60" font-family="Arial, sans-serif" font-size="24" fill="$color" text-anchor="middle">$icon</text>
    <text x="150" y="90" font-family="Arial, sans-serif" font-size="14" fill="#333" text-anchor="middle" font-weight="bold">{$product['product_name']}</text>
    <text x="150" y="110" font-family="Arial, sans-serif" font-size="12" fill="#666" text-anchor="middle">{$product['category_name']}</text>
    <text x="150" y="140" font-family="Arial, sans-serif" font-size="11" fill="#999" text-anchor="middle">Product ID: {$product['product_id']}</text>
    <defs>
        <pattern id="pattern" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
            <rect x="0" y="0" width="20" height="20" fill="#E0E0E0"/>
            <circle cx="10" cy="10" r="2" fill="#D0D0D0"/>
        </pattern>
    </defs>
</svg>
SVG;
        
        file_put_contents($image_path . '.svg', $svg);
        echo "<p>✅ Created placeholder: " . htmlspecialchars($product['product_image']) . ".svg</p>";
    } else {
        echo "<p>✅ Already exists: " . htmlspecialchars($product['product_image']) . "</p>";
    }
}

echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li><a href='image_helper.php'>Check image status</a></li>";
echo "<li><a href='products.php'>View products page</a></li>";
echo "<li>Replace .svg files with real .jpg images when ready</li>";
echo "</ol>";

echo "<p><strong>Tip:</strong> You can now search online for Nepali cultural items and save them as .jpg files with the exact names shown above.</p>";
?>
