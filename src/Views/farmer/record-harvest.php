<?php $title = "Record Harvest | AgriFarm"; ?>

<div class="dashboard-card">
    <h2 class="font-body--xl-500">Record Harvest</h2>
    <form method="POST" action="/farmer/record-harvest" class="dashboard__form">
        <div class="form-group">
            <label for="planting_id">Select Planting</label>
            <select name="planting_id" id="planting_id" class="form-control" required>
                <option value="">Select Planting</option>
                <?php foreach ($plantings as $planting): ?>
                    <option value="<?php echo $planting['planting_id']; ?>">
                        <?php echo htmlspecialchars($planting['crop_name'] . ' - ' . $planting['field_location']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="harvest_date">Harvest Date</label>
                    <input type="date" name="harvest_date" id="harvest_date" class="form-control" required>
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

        <div class="form-group">
            <label for="notes">Additional Notes</label>
            <textarea name="notes" id="notes" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Record Harvest</button>
    </form>
</div>