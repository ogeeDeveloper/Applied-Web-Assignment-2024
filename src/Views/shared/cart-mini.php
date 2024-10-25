<?php
if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(dirname(__DIR__)));
}

use App\Utils\Functions;

$cartCount = Functions::getCartCount();
$cartTotal = Functions::getCartTotal();
$cartItems = $_SESSION['cart'] ?? [];

// Calculate cart total
foreach ($cartItems as $item) {
    $cartTotal += $item['price'] * $item['quantity'];
}
?>

<div class="header__cart-item" id="cart-bag">
    <div class="header__cart-item-content">
        <svg width="34" height="35" viewBox="0 0 34 35" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M11.3333 14.6667H7.08333L4.25 30.25H29.75L26.9167 14.6667H22.6667M11.3333 14.6667V10.4167C11.3333 7.28705 13.8704 4.75 17 4.75V4.75C20.1296 4.75 22.6667 7.28705 22.6667 10.4167V14.6667M11.3333 14.6667H22.6667M11.3333 14.6667V18.9167M22.6667 14.6667V18.9167" 
                  stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <?php if ($cartCount > 0): ?>
            <span class="item-number"><?php echo $cartCount; ?></span>
        <?php endif; ?>

        <div class="header__cart-item-content-info">
            <h5>Shopping cart:</h5>
            <span class="price"><?php echo Functions::formatPrice($cartTotal); ?></span>
        </div>
    </div>

    <!-- Mini Cart Dropdown -->
    <div class="shopping-cart">
        <div class="shopping-cart-top">
            <div class="shopping-cart-header">
                <h5 class="font-body--xxl-500">Shopping Cart (<?php echo $cartCount; ?>)</h5>
                <button class="close-cart">
                    <svg width="45" height="45" viewBox="0 0 45 45" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="22.5" cy="22.5" r="22.5" fill="white"/>
                        <path d="M28.75 16.25L16.25 28.75M16.25 16.25L28.75 28.75" stroke="#1A1A1A" stroke-width="1.5" 
                              stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>

            <div class="shopping-cart__product-content">
                <?php if (empty($cartItems)): ?>
                    <p class="text-center py-4">Your cart is empty</p>
                <?php else: ?>
                    <?php foreach ($cartItems as $id => $item): ?>
                        <div class="shopping-cart__product-content-item">
                            <div class="img-wrapper">
                                <img src="<?php echo Functions::h($item['image']); ?>" 
                                     alt="<?php echo Functions::h($item['name']); ?>">
                            </div>
                            <div class="text-content">
                                <h5 class="font-body--md-400"><?php echo Functions::h($item['name']); ?></h5>
                                <p><?php echo $item['quantity']; ?> x <?php echo Functions::formatPrice($item['price']); ?></p>
                            </div>
                            <button class="remove-item" data-id="<?php echo Functions::h($id); ?>">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M16 8L8 16M8 8L16 16" stroke="#666666" stroke-width="1.5" 
                                          stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="shopping-cart-bottom">
            <div class="shopping-cart-product-info">
                <span><?php echo $cartCount; ?> Product<?php echo $cartCount !== 1 ? 's' : ''; ?></span>
                <span class="product-price font-body--lg-500"><?php echo Functions::formatPrice($cartTotal); ?></span>
            </div>
            
            <?php if ($cartCount > 0): ?>
                <a href="/checkout" class="button button--lg w-100">Checkout</a>
                <a href="/cart" class="button button--lg button--disable w-100">View Cart</a>
            <?php else: ?>
                <a href="/shop" class="button button--lg w-100">Start Shopping</a>
            <?php endif; ?>
        </div>
    </div>
</div>