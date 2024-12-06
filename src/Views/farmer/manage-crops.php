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
                    <form method="POST" action="/add-crop">
                        <div class="form-group">
                            <label for="cropName">Crop Name</label>
                            <input type="text" id="cropName" name="crop_name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="cropType">Crop Type</label>
                            <select id="cropType" name="crop_type_id" class="form-control" required>
                                <option value="">Select Crop Type</option>
                                <option value="1">Vegetable</option>
                                <option value="2">Fruit</option>
                                <option value="3">Grain</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="plantingDate">Planting Date</label>
                            <input type="date" id="plantingDate" name="planting_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="expectedHarvestDate">Expected Harvest Date</label>
                            <input type="date" id="expectedHarvestDate" name="expected_harvest_date" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Add Crop</button>
                    </form>
                </div>

                <!-- Crop List -->
                <div class="crop-list">
                    <h2>Your Crops</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Crop Name</th>
                                <th>Crop Type</th>
                                <th>Planting Date</th>
                                <th>Harvest Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($crops as $crop): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($crop['name']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['type']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['planting_date']); ?></td>
                                    <td><?php echo htmlspecialchars($crop['harvest_date']); ?></td>
                                    <td>
                                        <a href="/edit-crop?id=<?php echo $crop['id']; ?>" class="btn btn-secondary">Edit</a>
                                        <a href="/delete-crop?id=<?php echo $crop['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>