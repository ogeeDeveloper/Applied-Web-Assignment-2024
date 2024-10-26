<div class="home-page">
    <section class="hero">
        <div class="container">
            <h1>Welcome to AgriKonnect</h1>
            <!-- Hero content -->
        </div>
    </section>

    <?php if (!empty($featuredProducts)): ?>
    <section class="featured-products">
        <div class="container">
            <h2>Featured Products</h2>
            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <!-- Product card -->
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>
</div>