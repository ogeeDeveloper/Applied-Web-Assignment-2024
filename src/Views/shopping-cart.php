<?php
// Get cart data from controller
$cartItems = $data['cartItems'] ?? [];
$isLoggedIn = $data['isLoggedIn'] ?? false;

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += ($item['price_at_time'] ?? $item['price']) * $item['quantity'];
}
?>

<section class="shoping-cart section section--xl">
    <div class="container">
        <div class="section__head justify-content-center">
            <h2 class="section--title-four font-title--sm">My Shopping Cart</h2>
        </div>

        <?php if (empty($cartItems)): ?>
            <div class="text-center">
                <p>Your cart is empty</p>
                <a href="/shop" class="button button--md mt-4">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="row shoping-cart__content">
                <div class="col-lg-8">
                    <div class="cart-table">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th scope="col" class="cart-table-title">Product</th>
                                        <th scope="col" class="cart-table-title">Price</th>
                                        <th scope="col" class="cart-table-title">Quantity</th>
                                        <th scope="col" class="cart-table-title">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cartItems as $index => $item): ?>
                                        <tr>
                                            <td class="cart-table-item align-middle">
                                                <a href="/product-details/view?id=<?= htmlspecialchars($item['product_id']) ?>"
                                                    class="cart-table__product-item">
                                                    <div class="cart-table__product-item-img">
                                                        <img src="<?= htmlspecialchars($item['media_files']) ?>"
                                                            alt="<?= htmlspecialchars($item['name']) ?>" />
                                                    </div>
                                                    <h5 class="font-body--lg-400"><?= htmlspecialchars($item['name']) ?></h5>
                                                </a>
                                            </td>
                                            <td class="cart-table-item order-date align-middle">
                                                $<?= number_format($item['price_at_time'] ?? $item['price'], 2) ?>
                                            </td>
                                            <td class="cart-table-item order-total align-middle">
                                                <div class="counter-btn-wrapper">
                                                    <button class="counter-btn-dec counter-btn"
                                                        data-cart-id="<?= $index ?>"
                                                        onclick="updateQuantity(this, -1)">
                                                        -
                                                    </button>
                                                    <input type="number"
                                                        class="counter-btn-counter"
                                                        min="1"
                                                        max="1000"
                                                        value="<?= $item['quantity'] ?>"
                                                        data-cart-id="<?= $index ?>"
                                                        onchange="updateCartItem(this)">
                                                    <button class="counter-btn-inc counter-btn"
                                                        data-cart-id="<?= $index ?>"
                                                        onclick="updateQuantity(this, 1)">
                                                        +
                                                    </button>
                                                </div>
                                            </td>
                                            <td class="cart-table-item order-subtotal align-middle">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <p class="font-body--md-500">
                                                        $<?= number_format(($item['price_at_time'] ?? $item['price']) * $item['quantity'], 2) ?>
                                                    </p>
                                                    <button class="delete-item"
                                                        onclick="removeCartItem(<?= $index ?>)">
                                                        <svg width="24" height="25" viewBox="0 0 24 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M12 23.5C18.0748 23.5 23 18.5748 23 12.5C23 6.42525 18.0748 1.5 12 1.5C5.92525 1.5 1 6.42525 1 12.5C1 18.5748 5.92525 23.5 12 23.5Z" stroke="#CCCCCC" stroke-miterlimit="10" />
                                                            <path d="M16 8.5L8 16.5" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                            <path d="M16 16.5L8 8.5" stroke="#666666" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Cart Summary -->
                        <div class="cart-summary mt-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="cart-table-action-btn d-flex">
                                        <a href="/shop" class="button button--md button shop">Continue Shopping</a>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="cart-totals">
                                        <h4 class="mb-3">Cart Totals</h4>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Subtotal:</span>
                                            <span>$<?= number_format($subtotal, 2) ?></span>
                                        </div>
                                        <hr>
                                        <div class="d-flex justify-content-between mb-3">
                                            <strong>Total:</strong>
                                            <strong>$<?= number_format($subtotal, 2) ?></strong>
                                        </div>
                                        <?php if ($isLoggedIn): ?>
                                            <a href="/checkout" class="button button--md w-100">Proceed to Checkout</a>
                                        <?php else: ?>
                                            <a href="/login?redirect=/checkout" class="button button--md w-100">Login to Checkout</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
    // Cart functionality
    async function updateQuantity(button, change) {
        const input = button.parentElement.querySelector('input');
        const newValue = parseInt(input.value) + change;
        if (newValue >= 1) {
            input.value = newValue;
            await updateCartItem(input);
        }
    }

    async function updateCartItem(input) {
        const cartId = input.dataset.cartId;
        const quantity = parseInt(input.value);

        try {
            const response = await fetch('/cart/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: quantity
                })
            });

            const data = await response.json();
            if (data.success) {
                // Refresh the page to show updated cart
                window.location.reload();
            } else {
                alert(data.message || 'Error updating cart');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error updating cart');
        }
    }

    async function removeCartItem(cartId) {
        if (!confirm('Are you sure you want to remove this item?')) {
            return;
        }

        try {
            const response = await fetch('/cart/remove', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId
                })
            });

            const data = await response.json();
            if (data.success) {
                // Refresh the page to show updated cart
                window.location.reload();
            } else {
                alert(data.message || 'Error removing item');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error removing item');
        }
    }
</script>