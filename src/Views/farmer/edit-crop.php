<?php
$title = "Edit Crop | AgriFarm";
$currentPage = 'manage-crops';
?>

<div class="dashboard__main-content">
    <h1 class="dashboard__section-title font-body--xxl-500">Edit Crop</h1>

    <div class="dashboard-card">
        <form method="POST" action="/farmer/edit-crop" class="dashboard__form">
            <input type="hidden" name="planting_id" value="<?php echo htmlspecialchars($planting['planting_id']); ?>">

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="crop_type_id">Crop Type</label>
                        <select id="crop_type_id" name="crop_type_id" class="form-control" required>
                            <?php foreach ($cropTypes as $type): ?>
                                <option value="<?php echo $type['crop_id']; ?>"
                                    <?php echo $type['crop_id'] == $planting['crop_type_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($type['name']); ?> -
                                    <?php echo htmlspecialchars($type['category']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <!-- Include all other fields similar to manage-crops.php but with values from $planting -->
            </div>

            <button type="submit" class="btn btn-primary">Update Crop</button>
            <a href="/farmer/manage-crops" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>