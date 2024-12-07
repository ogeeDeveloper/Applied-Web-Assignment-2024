<?php
$title = "Record Activity | AgriFarm";
?>

<div class="record-activity section">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="section-title">Record Farm Activity</h1>

                <!-- Activity Recording Form -->
                <div class="add-activity-form">
                    <h2>Add New Activity</h2>
                    <form method="POST" action="/farmer/record-activity">
                        <div class="form-group">
                            <label for="activityType">Activity Type</label>
                            <select id="activityType" name="activity_type" class="form-control" required>
                                <option value="">Select Activity Type</option>
                                <option value="planting">Planting</option>
                                <option value="harvesting">Harvesting</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="inspection">Inspection</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="activityDate">Date</label>
                            <input type="datetime-local" id="activityDate" name="activity_date" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="cropField">Related Crop/Field</label>
                            <select id="cropField" name="crop_field" class="form-control">
                                <option value="">Select Crop/Field</option>
                                <?php foreach ($crops as $crop): ?>
                                    <option value="<?php echo $crop['id']; ?>"><?php echo htmlspecialchars($crop['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Record Activity</button>
                    </form>
                </div>

                <!-- Activity History -->
                <div class="activity-history">
                    <h2>Recent Activities</h2>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity Type</th>
                                <th>Description</th>
                                <th>Crop/Field</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($activities as $activity): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($activity['activity_date']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['activity_type']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['description']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['crop_field']); ?></td>
                                    <td><?php echo htmlspecialchars($activity['notes']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>