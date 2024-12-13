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
                    <a href="/orders">
                        Orders
                        <span> > </span>
                    </a>
                </li>
                <li class="active">Order Confirmation</li>
            </ul>
        </div>
    </div>
</div>
<!-- Breadcrumb section end -->

<section class="section section--lg">
    <div class="container">
        <!-- Success Message -->
        <div class="text-center mb-5">
            <div class="success-icon mb-4">
                <svg width="64" height="64" viewBox="0 0 64 64" fill="none">
                    <circle cx="32" cy="32" r="32" fill="#00B307" />
                    <path d="M44 24L28 40L20 32" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>
            <h2 class="font-title--md mb-3">Order Placed Successfully!</h2>
            <p class="font-body--lg-400">Thank you for your order. Your order number is:</p>
            <h3 class="font-title--lg mb-4">#<?= str_pad($order['order_id'], 8, '0', STR_PAD_LEFT) ?></h3>
        </div>

        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Order Details -->
                <div class="bill-card mb-4">
                    <div class="bill-card__content">
                        <div class="bill-card__header">
                            <h2 class="bill-card__header-title font-body--xxl-500">Order Details</h2>
                        </div>
                        <div class="bill-card__body">
                            <!-- Product Info -->
                            <div class="bill-card__product">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="bill-card__product-item">
                                        <div class="bill-card__product-item-content">
                                            <div class="img-wrapper">
                                                <img src="<?= htmlspecialchars($item['media_files'] ?? '/images/products/default.png') ?>"
                                                    alt="<?= htmlspecialchars($item['product_name']) ?>" />
                                            </div>
                                            <h5 class="font-body--md-400">
                                                <?= htmlspecialchars($item['product_name']) ?>
                                                <span class="quantity">x<?= $item['quantity'] ?></span>
                                            </h5>
                                        </div>
                                        <p class="bill-card__product-price font-body--md-500">
                                            $<?= number_format($item['unit_price'] * $item['quantity'], 2) ?>
                                        </p>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Order Summary -->
                            <div class="bill-card__memo mt-4">
                                <div class="bill-card__memo-item subtotal">
                                    <p class="font-body--md-400">Subtotal:</p>
                                    <span class="font-body--md-500">$<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                                <div class="bill-card__memo-item shipping">
                                    <p class="font-body--md-400">Shipping:</p>
                                    <span class="font-body--md-500">Free</span>
                                </div>
                                <div class="bill-card__memo-item total">
                                    <p class="font-body--lg-400">Total:</p>
                                    <span class="font-body--xl-500">$<?= number_format($order['total_amount'], 2) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delivery Information -->
                <div class="bill-card mb-4">
                    <div class="bill-card__content">
                        <div class="bill-card__header">
                            <h2 class="bill-card__header-title font-body--xxl-500">Delivery Information</h2>
                        </div>
                        <div class="bill-card__body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="font-body--md-500 mb-2">Delivery Address</h5>
                                    <p class="font-body--md-400"><?= nl2br(htmlspecialchars($order['delivery_address'])) ?></p>
                                </div>
                                <?php if (!empty($order['delivery_notes'])): ?>
                                    <div class="col-md-6">
                                        <h5 class="font-body--md-500 mb-2">Delivery Notes</h5>
                                        <p class="font-body--md-400"><?= nl2br(htmlspecialchars($order['delivery_notes'])) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Status -->
                <div class="bill-card mb-4">
                    <div class="bill-card__content">
                        <div class="bill-card__header">
                            <h2 class="bill-card__header-title font-body--xxl-500">Order Status</h2>
                        </div>
                        <div class="bill-card__body">
                            <div class="order-status">
                                <span class="badge <?= $order['order_status'] === 'pending' ? 'badge-warning' : 'badge-success' ?>">
                                    <?= ucfirst($order['order_status']) ?>
                                </span>
                                <p class="font-body--md-400 mt-2">
                                    Payment Status: <span class="badge badge-info"><?= ucfirst($order['payment_status']) ?></span>
                                </p>
                                <p class="font-body--md-400">
                                    Order Date: <?= date('F j, Y, g:i a', strtotime($order['ordered_date'])) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="text-center mt-5">
                    <a href="/" class="button button--md">Continue Shopping</a>
                    <a href="/orders" class="button button--md button--outline ms-3">View All Orders</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
    .success-icon {
        margin: 0 auto;
        width: 64px;
        height: 64px;
    }

    .order-status .badge {
        font-size: 1rem;
        padding: 0.5rem 1rem;
    }

    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }

    .badge-success {
        background-color: #28a745;
        color: #fff;
    }

    .badge-info {
        background-color: #17a2b8;
        color: #fff;
    }

    .button--outline {
        background: transparent;
        border: 2px solid #00B307;
        color: #00B307;
    }

    .button--outline:hover {
        background: #00B307;
        color: #fff;
    }
</style>