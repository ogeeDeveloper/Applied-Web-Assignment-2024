<?php
$title = "Record Harvest | AgriFarm";
$currentPage = 'manage-crops';
?>

<!-- Main content -->
<div class="dashboard__main-content">
    <h1 class="dashboard__section-title font-body--xxl-500">Record Harvest</h1>

    <div class="dashboard-card">
        <div class="dashboard__card-header">
            <h2 class="dashboard__card-title font-body--xl-500">
                Record Harvest for <?php echo htmlspecialchars($planting['crop_name']); ?>
                at <?php echo htmlspecialchars($planting['field_location']); ?>
            </h2>
        </div>

        <form method="POST" action="/farmer/record-harvest" class="dashboard__form">
            <input type="hidden" name="planting_id" value="<?php echo htmlspecialchars($planting['planting_id']); ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="harvest_date">Harvest Date</label>
                        <input type="date" name="harvest_date" id="harvest_date" class="form-control"
                            value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quality_grade">Quality Grade</label>
                        <select name="quality_grade" id="quality_grade" class="form-control" required>
                            <option value="A">Grade A</option>
                            <option value="B">Grade B</option>
                            <option value="C">Grade C</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="quantity">Harvest Quantity</label>
                        <input type="number" step="0.01" name="quantity" id="quantity" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="unit">Unit</label>
                        <select name="unit" id="unit" class="form-control" required>
                            <option value="kg">Kilograms</option>
                            <option value="tons">Tons</option>
                            <option value="pieces">Pieces</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="loss_quantity">Loss Quantity</label>
                        <input type="number" step="0.01" name="loss_quantity" id="loss_quantity" class="form-control">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="loss_reason">Loss Reason</label>
                        <input type="text" name="loss_reason" id="loss_reason" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="storage_location">Storage Location</label>
                <input type="text" name="storage_location" id="storage_location" class="form-control" required>
            </div>

            <div class="form-group">
                <label for="storage_conditions">Storage Conditions</label>
                <textarea name="storage_conditions" id="storage_conditions" class="form-control" rows="2"></textarea>
            </div>

            <div class="form-check mb-3">
                <input type="checkbox" id="create_product" name="create_product" class="form-check-input" value="1" checked>
                <label class="form-check-label" for="create_product">
                    Create product listing from harvest
                </label>
            </div>

            <div id="product_details" class="mb-3">
                <div class="form-group">
                    <label for="price_per_unit">Price per unit</label>
                    <input type="number" step="0.01" name="price_per_unit" id="price_per_unit" class="form-control" required>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Record Harvest</button>
                <a href="/farmer/manage-crops" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('create_product').addEventListener('change', function() {
        const productDetails = document.getElementById('product_details');
        const priceInput = document.getElementById('price_per_unit');
        productDetails.style.display = this.checked ? 'block' : 'none';
        priceInput.required = this.checked;
    });
</script>