<?php 
// Include header and navigation
include 'header.php'; 

// Assume the cart is stored in the session
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<p>Your cart is empty.</p>";
} else {
    echo "<h2>Your Cart</h2>";
    echo "<ul class='cart-items'>";
    
    foreach ($_SESSION['cart'] as $product_id => $quantity) {
        $product = getProductById($product_id);  // Assume this function fetches product details from DB
        echo "<li>";
        echo "<img src='{$product['image_url']}' alt='{$product['name']}' />";
        echo "<span>{$product['name']}</span>";
        echo "<span>Quantity: $quantity</span>";
        echo "<span>Price: $".number_format($product['price'], 2)."</span>";
        echo "</li>";
    }

    echo "</ul>";
    echo "<a href='/checkout' class='btn'>Proceed to Checkout</a>";
}

include 'footer.php'; 
?>
