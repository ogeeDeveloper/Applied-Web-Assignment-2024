<?php
$title = "Chemical Usage Records | AgriFarm";
$currentPage = 'chemical-usage';
?>

<div class="dashboard__main-content">
    <h1 class="dashboard__section-title font-body--xxl-500">Chemical Usage Records</h1>

    <!-- Add Chemical Usage Form -->
    <div class="dashboard-card mb-4">
        <h2 class="dashboard__card-title font-body--xl-500">Record New Chemical Application</h2>
        <form method="POST" action="/farmer/record-chemical" class="dashboard__form">
            <div class="form-group">
                <label for="plantingId" class="font-body--md-500">Select Crop/Planting</label>
                <select id="plantingId" name="planting_id" class="form-control" required>
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
                        <label for="chemicalName" class="font-body--md-500">Chemical Name</label>
                        <input type="text" id="chemicalName" name="chemical_name" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="chemicalType" class="font-body--md-500">Chemical Type</label>
                        <select id="chemicalType" name="chemical_type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="pesticide">Pesticide</option>
                            <option value="fertilizer">Fertilizer</option>
                            <option value="herbicide">Herbicide</option>
                            <option value="fungicide">Fungicide</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="dateApplied" class="font-body--md-500">Date Applied</label>
                        <input type="date" id="dateApplied" name="date_applied" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="safetyPeriod" class="font-body--md-500">Safety Period (Days)</label>
                        <input type="number" id="safetyPeriod" name="safety_period_days" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="amountUsed" class="font-body--md-500">Amount Used</label>
                        <input type="number" step="0.01" id="amountUsed" name="amount_used" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="unitOfMeasurement" class="font-body--md-500">Unit</label>
                        <select id="unitOfMeasurement" name="unit_of_measurement" class="form-control" required>
                            <option value="liters">Liters</option>
                            <option value="kg">Kilograms</option>
                            <option value="grams">Grams</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="weatherConditions" class="font-body--md-500">Weather Conditions</label>
                        <input type="text" id="weatherConditions" name="weather_conditions" class="form-control">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="purpose" class="font-body--md-500">Purpose</label>
                <textarea id="purpose" name="purpose" class="form-control" rows="3"></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Record Chemical Usage</button>
        </form>
    </div>

    <!-- Chemical Usage History -->
    <div class="dashboard-card">
        <div class="dashboard__card-header">
            <h2 class="dashboard__card-title font-body--xl-500">Chemical Usage History</h2>
        </div>
        <div class="dashboard__table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th scope="col">Date Applied</th>
                        <th scope="col">Chemical Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Amount Used</th>
                        <th scope="col">Safety Period</th>
                        <th scope="col">Purpose</th>
                        <th scope="col">Weather</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['date_applied']); ?></td>
                            <td><?php echo htmlspecialchars($record['chemical_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['chemical_type']); ?></td>
                            <td><?php echo htmlspecialchars($record['amount_used'] . ' ' . $record['unit_of_measurement']); ?></td>
                            <td><?php echo htmlspecialchars($record['safety_period_days']); ?> days</td>
                            <td><?php echo htmlspecialchars($record['purpose'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($record['weather_conditions'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>