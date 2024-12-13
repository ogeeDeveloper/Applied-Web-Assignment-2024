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

                <div class="form-group">
                    <label>Product Images</label>
                    <div class="input-group mb-3">
                        <div class="custom-file">
                            <input type="file"
                                class="custom-file-input"
                                id="product_images"
                                name="product_images[]"
                                multiple
                                accept="image/jpeg,image/png,image/webp"
                                onchange="handleImagePreview(this)">
                            <label class="custom-file-label" for="product_images">Choose product images...</label>
                        </div>
                    </div>

                    <small class="form-text text-muted">
                        Upload up to 5 images of your product. First image will be set as the main product image.<br>
                        Supported formats: JPG, PNG, WebP. Maximum size: 10MB per image.
                    </small>

                    <div id="image_preview" class="row mt-3"></div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Record Harvest</button>
                    <a href="/farmer/manage-crops" class="btn btn-secondary">Cancel</a>
                </div>
        </form>
    </div>
</div>

<script>
    // Toggle product details visibility
    document.getElementById('create_product').addEventListener('change', function() {
        const productDetails = document.getElementById('product_details_section');
        const priceInput = document.getElementById('price_per_unit');
        const fileInput = document.getElementById('product_images');

        productDetails.style.display = this.checked ? 'block' : 'none';
        priceInput.required = this.checked;

        if (!this.checked) {
            // Clear file input and preview when product creation is disabled
            fileInput.value = '';
            document.getElementById('image_preview').innerHTML = '';
            fileInput.nextElementSibling.textContent = 'Choose product images...';
        }
    });

    function handleImagePreview(input) {
        const preview = document.getElementById('image_preview');
        const label = input.nextElementSibling;
        preview.innerHTML = '';

        if (input.files && input.files.length > 0) {
            const maxFiles = 5;
            const maxSize = 10 * 1024 * 1024; // 10MB in bytes
            const allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            const files = Array.from(input.files).slice(0, maxFiles);

            let validFiles = 0;

            files.forEach((file, index) => {
                if (!allowedTypes.includes(file.type)) {
                    alert(`File "${file.name}" is not a supported image type`);
                    return;
                }

                if (file.size > maxSize) {
                    alert(`File "${file.name}" exceeds 10MB size limit`);
                    return;
                }

                validFiles++;

                const col = document.createElement('div');
                col.className = 'col-md-4 mb-3';

                const card = document.createElement('div');
                card.className = 'card h-100';

                const img = document.createElement('img');
                img.className = 'card-img-top';
                img.style.height = '150px';
                img.style.objectFit = 'cover';

                const cardBody = document.createElement('div');
                cardBody.className = 'card-body p-2';

                const fileName = document.createElement('p');
                fileName.className = 'card-text small mb-1';
                fileName.textContent = file.name;

                const fileSize = document.createElement('small');
                fileSize.className = 'text-muted';
                fileSize.textContent = `${(file.size / (1024 * 1024)).toFixed(2)} MB`;

                const reader = new FileReader();
                reader.onload = (e) => {
                    img.src = e.target.result;
                };
                reader.onerror = () => {
                    card.innerHTML = `<div class="card-body text-danger">Error loading preview for ${file.name}</div>`;
                };
                reader.readAsDataURL(file);

                cardBody.appendChild(fileName);
                cardBody.appendChild(fileSize);
                card.appendChild(img);
                card.appendChild(cardBody);
                col.appendChild(card);
                preview.appendChild(col);
            });

            label.textContent = `${validFiles} image${validFiles !== 1 ? 's' : ''} selected`;
        } else {
            label.textContent = 'Choose product images...';
        }
    }

    // document.getElementById('product_images').addEventListener('change', function(e) {
    //     const preview = document.getElementById('image_preview');
    //     preview.innerHTML = ''; // Clear existing previews

    //     const files = e.target.files;

    //     for (let i = 0; i < Math.min(files.length, 5); i++) {
    //         const file = files[i];
    //         if (!file.type.startsWith('image/')) continue;

    //         const col = document.createElement('div');
    //         col.className = 'col-md-4 mb-3';

    //         const img = document.createElement('img');
    //         img.className = 'img-thumbnail';
    //         img.file = file;

    //         const reader = new FileReader();
    //         reader.onload = (function(aImg) {
    //             return function(e) {
    //                 aImg.src = e.target.result;
    //             };
    //         })(img);

    //         reader.readAsDataURL(file);

    //         col.appendChild(img);
    //         preview.appendChild(col);
    //     }
    // });

    // Update file input label with selected file count
    document.getElementById('product_images').addEventListener('change', function(e) {
        const label = document.querySelector('.custom-file-label');
        const fileCount = e.target.files.length;
        label.textContent = fileCount > 0 ? `${fileCount} file${fileCount > 1 ? 's' : ''} selected` : 'Choose images...';
    });

    window.addEventListener('DOMContentLoaded', function() {
        const createProduct = document.getElementById('create_product');
        const productDetails = document.getElementById('product_details_section');
        const priceInput = document.getElementById('price_per_unit');

        productDetails.style.display = createProduct.checked ? 'block' : 'none';
        priceInput.required = createProduct.checked;
    });
</script>