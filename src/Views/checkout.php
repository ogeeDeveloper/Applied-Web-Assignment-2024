<!-- Breadcrumb section start -->
<div class="section breedcrumb">
    <div class="breedcrumb__img-wrapper">
        <img src="/images/banner/breedcrumb.jpg" alt="breadcrumb" />
        <div class="container">
            <ul class="breedcrumb__content">
                <li>
                    <a href="/">
                        <svg width="18" height="19" viewBox="0 0 18 19" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 8L9 1L17 8V18H12V14C12 13.2044 11.6839 12.4413 11.1213 11.8787C10.5587 11.3161 9.79565 11 9 11C8.20435 11 7.44129 11.3161 6.87868 11.8787C6.31607 12.4413 6 13.2044 6 14V18H1V8Z" stroke="#808080" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        <span> > </span>
                    </a>
                </li>
                <li>
                    <a href="/cart">
                        Shopping Cart
                        <span> > </span>
                    </a>
                </li>
                <li class="active"><a href="/checkout">Checkout</a></li>
            </ul>
        </div>
    </div>
</div>
<!-- Breadcrumb section end -->

<!-- Billing Section Start -->
<section class="section billing section--xl pt-0">
    <div class="container">
        <div class="row billing__content">
            <div class="col-lg-8">
                <div class="billing__content-card">
                    <div class="billing__content-card-header">
                        <h2 class="font-body--xxxl-500">Delivery Information</h2>
                    </div>
                    <div class="billing__content-card-body">
                        <form id="checkoutForm" onsubmit="return handleCheckout(event)">
                            <div class="contact-form__content">
                                <div class="contact-form__content-group">
                                    <div class="contact-form-input">
                                        <label for="fname1">First Name</label>
                                        <input type="text" id="fname1" name="first_name"
                                            value="<?= htmlspecialchars($customer['first_name'] ?? '') ?>"
                                            required placeholder="Your first name" />
                                    </div>
                                    <div class="contact-form-input">
                                        <label for="lname2">Last Name</label>
                                        <input type="text" id="lname2" name="last_name"
                                            value="<?= htmlspecialchars($customer['last_name'] ?? '') ?>"
                                            required placeholder="Your last name" />
                                    </div>
                                </div>

                                <div class="contact-form-input">
                                    <label for="address">Delivery Address</label>
                                    <textarea id="address" name="delivery_address"
                                        required placeholder="Your delivery address"
                                        class="form-control"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                                </div>

                                <div class="contact-form__content-group">
                                    <div class="contact-form-input">
                                        <label for="phone">Phone</label>
                                        <input type="tel" id="phone" name="phone"
                                            value="<?= htmlspecialchars($customer['phone_number'] ?? '') ?>"
                                            required placeholder="Phone number" />
                                    </div>
                                    <div class="contact-form-input">
                                        <label for="email">Email</label>
                                        <input type="email" id="email" name="email"
                                            value="<?= htmlspecialchars($customer['email'] ?? '') ?>"
                                            required placeholder="Email Address" />
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Information -->
                            <div class="billing__content-card mt-4">
                                <div class="billing__content-card-header">
                                    <h2 class="font-body--xxxl-500">Additional Information</h2>
                                </div>
                                <div class="billing__content-card-body">
                                    <div class="contact-form-input contact-form-textarea">
                                        <label for="note">Order Notes <span>(Optional)</span></label>
                                        <textarea name="delivery_notes" id="note"
                                            placeholder="Notes about your order, e.g. special notes for delivery"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Summary for Mobile -->
                            <div class="d-block d-lg-none mt-4">
                                <?php include 'order-summary.php'; ?>
                            </div>

                            <div class="bill-card__payment-method mt-4">
                                <h3 class="mb-3">Payment Method</h3>
                                <div class="bill-card__payment-method-item">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio"
                                            name="payment_method" id="cash"
                                            value="cash" checked required />
                                        <label class="form-check-label" for="cash">
                                            Cash on delivery
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button class="button button--lg w-100 mt-4" type="submit">
                                Place Order
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Order Summary Sidebar -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="bill-card">
                    <div class="bill-card__content">
                        <div class="bill-card__header">
                            <h2 class="bill-card__header-title font-body--xxl-500">
                                Order Summary
                            </h2>
                        </div>
                        <div class="bill-card__body">
                            <!-- Product Info -->
                            <div class="bill-card__product">
                                <?php foreach ($cartItems as $item): ?>
                                    <div class="bill-card__product-item">
                                        <div class="bill-card__product-item-content">
                                            <div class="img-wrapper">
                                                <img src="<?= htmlspecialchars($item['media_files']) ?>"
                                                    alt="<?= htmlspecialchars($item['name']) ?>" />
                                            </div>
                                            <h5 class="font-body--md-400">
                                                <?= htmlspecialchars($item['name']) ?>
                                                <span class="quantity">x<?= $item['quantity'] ?></span>
                                            </h5>
                                        </div>
                                        <p class="bill-card__product-price font-body--md-500">
                                            $<?= number_format($item['price_at_time'] * $item['quantity'], 2) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Memo -->
                            <div class="bill-card__memo">
                                <div class="bill-card__memo-item subtotal">
                                    <p class="font-body--md-400">Subtotal:</p>
                                    <span class="font-body--md-500">$<?= number_format($total, 2) ?></span>
                                </div>
                                <div class="bill-card__memo-item shipping">
                                    <p class="font-body--md-400">Shipping:</p>
                                    <span class="font-body--md-500">Free</span>
                                </div>
                                <div class="bill-card__memo-item total">
                                    <p class="font-body--lg-400">Total:</p>
                                    <span class="font-body--xl-500">$<?= number_format($total, 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    async function handleCheckout(event) {
        event.preventDefault();

        const form = event.target;
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        try {
            const response = await fetch('/checkout/place-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                window.location.href = result.redirect;
            } else {
                alert(result.message || 'Error placing order');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error placing order');
        }

        return false;
    }
</script>