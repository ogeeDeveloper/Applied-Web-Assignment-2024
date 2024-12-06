<?php
$title = "Manage Crops | AgriFarm";
?>

<div class="manage-crops section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="section-title">Manage Crops</h1>

                <!-- Add Crop Form -->
                <div class="add-crop-form">
                    <h2>Add New Crop</h2>
                    <form method="POST" action="/farmer/add-crop" class="dashboard__form">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="crop_type_id" class="font-body--md-500">Crop Type</label>
                                    <select id="crop_type_id" name="crop_type_id" class="form-control" required>
                                        <option value="">Select Crop Type</option>
                                        <?php foreach ($cropTypes as $type): ?>
                                            <option value="<?php echo $type['crop_id']; ?>">
                                                <?php echo htmlspecialchars($type['name']); ?> - <?php echo htmlspecialchars($type['category']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="field_location" class="font-body--md-500">Field Location</label>
                                    <input type="text" id="field_location" name="field_location" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area_size" class="font-body--md-500">Area Size</label>
                                    <input type="number" step="0.01" id="area_size" name="area_size" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="area_unit" class="font-body--md-500">Area Unit</label>
                                    <select id="area_unit" name="area_unit" class="form-control" required>
                                        <option value="hectares">Hectares</option>
                                        <option value="acres">Acres</option>
                                        <option value="square_meters">Square Meters</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="planting_date" class="font-body--md-500">Planting Date</label>
                                    <input type="date" id="planting_date" name="planting_date" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="expected_harvest_date" class="font-body--md-500">Expected Harvest Date</label>
                                    <input type="date" id="expected_harvest_date" name="expected_harvest_date" class="form-control" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="growing_method" class="font-body--md-500">Growing Method</label>
                            <select id="growing_method" name="growing_method" class="form-control">
                                <option value="traditional">Traditional</option>
                                <option value="organic">Organic</option>
                                <option value="hydroponic">Hydroponic</option>
                                <option value="greenhouse">Greenhouse</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="soil_preparation" class="font-body--md-500">Soil Preparation</label>
                            <textarea id="soil_preparation" name="soil_preparation" class="form-control" rows="2"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="font-body--md-500">Additional Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Crop</button>
                    </form>
                </div>

                <!-- Crop List -->
                <!-- Crop List -->
                <div class="dashboard-card mt-4">
                    <div class="dashboard__card-header">
                        <h2 class="dashboard__card-title font-body--xl-500">Your Crops</h2>
                    </div>
                    <div class="dashboard__table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Crop Name</th>
                                    <th>Location</th>
                                    <th>Area</th>
                                    <th>Planting Date</th>
                                    <th>Expected Harvest</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($plantings)): ?>
                                    <?php foreach ($plantings as $planting): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($planting['crop_name'] ?? $planting['name']); ?></td>
                                            <td><?php echo htmlspecialchars($planting['field_location']); ?></td>
                                            <td><?php echo htmlspecialchars($planting['area_size'] . ' ' . $planting['area_unit']); ?></td>
                                            <td><?php echo htmlspecialchars($planting['planting_date']); ?></td>
                                            <td><?php echo htmlspecialchars($planting['expected_harvest_date']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $planting['status'] === 'growing' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($planting['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group">
                                                    <a href="/farmer/edit-crop?id=<?php echo $planting['planting_id']; ?>"
                                                        class="btn btn-sm btn-secondary">Edit</a>
                                                    <?php if ($planting['status'] === 'growing' || $planting['status'] === 'planted'): ?>
                                                        <a href="/farmer/record-harvest?planting_id=<?php echo $planting['planting_id']; ?>"
                                                            class="btn btn-sm btn-success">Record Harvest</a>
                                                    <?php endif; ?>
                                                    <a href="/farmer/delete-crop?id=<?php echo $planting['planting_id']; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this crop?')">Delete</a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center">No crops found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>