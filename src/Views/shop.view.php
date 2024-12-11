<?php
// Ensure data is passed correctly from the controller
$products = $data['products'] ?? [];
$filters = $data['filters'] ?? [];
$pageTitle = $data['pageTitle'] ?? 'Shop';
?>

<!-- breedcrumb section start  -->
<div class="section breedcrumb">
    <div class="breedcrumb__img-wrapper">
        <img src="/images/banner/breedcrumb.jpg" alt="breedcrumb" />
        <div class="container">
            <ul class="breedcrumb__content">
                <li>
                    <a href="/">Home</a>
                </li>
                <li class="active">Shop</li>
            </ul>
        </div>
    </div>
</div>

<!-- Filter -->
<div class="filter--search section-padding">
    <div class="container">
        <div class="row">
            <!-- Left Sidebar Filter -->
            <div class="col-lg-3">
                <div class="filter-sidebar">
                    <form method="GET" action="/shop" class="filter-form">
                        <div class="filter-section">
                            <h4>Categories</h4>
                            <select name="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                <option value="vegetables" <?= $filters['category'] === 'vegetables' ? 'selected' : '' ?>>Vegetables</option>
                                <option value="fruits" <?= $filters['category'] === 'fruits' ? 'selected' : '' ?>>Fruits</option>
                                <option value="grains" <?= $filters['category'] === 'grains' ? 'selected' : '' ?>>Grains</option>
                            </select>
                        </div>

                        <div class="filter-section">
                            <h4>Price Range</h4>
                            <div class="price-inputs">
                                <input type="number" name="min_price" class="form-control"
                                    placeholder="Min" value="<?= htmlspecialchars($filters['min_price'] ?? '') ?>">
                                <span>to</span>
                                <input type="number" name="max_price" class="form-control"
                                    placeholder="Max" value="<?= htmlspecialchars($filters['max_price'] ?? '') ?>">
                            </div>
                        </div>

                        <button type="submit" class="button button--md w-100">Apply Filters</button>
                    </form>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="col-lg-9">
                <!-- Sort Options -->
                <div class="filter--search-result mb-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="sort-list">
                            <form method="GET" action="/shop" class="d-flex align-items-center">
                                <!-- Preserve other filters -->
                                <?php foreach ($filters as $key => $value): ?>
                                    <?php if ($key !== 'sort_by' && $value): ?>
                                        <input type="hidden" name="<?= htmlspecialchars($key) ?>"
                                            value="<?= htmlspecialchars($value) ?>">
                                    <?php endif; ?>
                                <?php endforeach; ?>

                                <label for="sort" class="me-2">Sort by:</label>
                                <select name="sort_by" id="sort" class="form-select" onchange="this.form.submit()">
                                    <option value="latest" <?= ($filters['sort_by'] ?? '') === 'latest' ? 'selected' : '' ?>>Latest</option>
                                    <option value="low_high" <?= ($filters['sort_by'] ?? '') === 'low_high' ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="high_low" <?= ($filters['sort_by'] ?? '') === 'high_low' ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="popular" <?= ($filters['sort_by'] ?? '') === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                                </select>
                            </form>
                        </div>
                        <div class="result-found">
                            <p><?= count($products) ?> Results Found</p>
                        </div>
                    </div>
                </div>

                <!-- Products Grid -->
                <div class="row g-4">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $product): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="product-item">
                                    <div class="product-item__img">
                                        <img src="<?= htmlspecialchars($product['media_files'] ?? '/images/products/default-product.png') ?>"
                                            alt="<?= htmlspecialchars($product['name']) ?>" class="img-fluid" />
                                    </div>
                                    <div class="product-item__content">
                                        <h4 class="product-item__title">
                                            <?= htmlspecialchars($product['name']) ?>
                                        </h4>
                                        <div class="product-item__meta">
                                            <span class="price">$<?= htmlspecialchars($product['formatted_price']) ?></span>
                                            <span class="unit">/<?= htmlspecialchars($product['unit_type']) ?></span>
                                        </div>
                                        <div class="product-item__footer">
                                            <span class="badge <?= $product['stock_status']['class'] ?>">
                                                <?= htmlspecialchars($product['stock_status']['label']) ?>
                                            </span>
                                            <a href="/product-details/<?= $product['product_id'] ?>" class="button button--sm">View Details</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info text-center">
                                No products found. Try adjusting your filters.
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>