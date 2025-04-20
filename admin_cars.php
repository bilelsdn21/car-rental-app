<?php
// This file is included in admin.php which should already have session handling
// Fetch all cars
$sql = "SELECT * FROM cars ORDER BY created_at DESC";
$result = $conn->query($sql);

// Fetch all cars with correct column names
$stmt = $conn->prepare("SELECT id, image_url as image, make, model, year, category, price_per_day, COALESCE(availability, 1) as availability FROM cars ORDER BY id DESC");
$stmt->execute();
$result = $stmt->get_result();
?>

<div class="content-header">
    <h2>Car Management</h2>
    <button class="btn btn-primary" onclick="openModal('addCarModal')">Add New Car</button>
</div>

<!-- Add Car Modal -->
<div class="modal" id="addCarModal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Add New Car</h2>
        <form id="addCarForm" action="car_actions.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
            
            <div class="form-group">
                <label for="make">Make*</label>
                <input type="text" id="make" name="make" required>
            </div>
            
            <div class="form-group">
                <label for="model">Model*</label>
                <input type="text" id="model" name="model" required>
            </div>
            
            <div class="form-group">
                <label for="year">Year*</label>
                <input type="number" id="year" name="year" min="1900" max="<?php echo date('Y') + 1; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category*</label>
                <select id="category" name="category" required>
                    <option value="">Select Category</option>
                    <option value="Economy">Economy</option>
                    <option value="Compact">Compact</option>
                    <option value="Midsize">Midsize</option>
                    <option value="SUV">SUV</option>
                    <option value="Luxury">Luxury</option>
                    <option value="Van">Van</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="price">Price per Day* ($)</label>
                <input type="number" id="price" name="price" min="0" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="image">Car Image*</label>
                <input type="file" id="image" name="image" accept="image/*" required>
                <small>Max size: 5MB. Allowed types: JPG, PNG, WEBP</small>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-group">
                <label>Features</label>
                <div class="features-grid">
                    <label><input type="checkbox" name="features[]" value="AC"> Air Conditioning</label>
                    <label><input type="checkbox" name="features[]" value="GPS"> GPS Navigation</label>
                    <label><input type="checkbox" name="features[]" value="Bluetooth"> Bluetooth</label>
                    <label><input type="checkbox" name="features[]" value="USB"> USB Port</label>
                    <label><input type="checkbox" name="features[]" value="ABS"> ABS</label>
                    <label><input type="checkbox" name="features[]" value="Airbags"> Airbags</label>
                    <label><input type="checkbox" name="features[]" value="Sunroof"> Sunroof</label>
                    <label><input type="checkbox" name="features[]" value="Leather"> Leather Seats</label>
                </div>
            </div>
            
            <div class="form-group">
                <label for="availability">Availability</label>
                <select id="availability" name="availability">
                    <option value="1">Available</option>
                    <option value="0">Not Available</option>
                </select>
            </div>
            
            <button type="submit" class="btn btn-primary">Add Car</button>
        </form>
    </div>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
        ?>
    </div>
<?php endif; ?>

<!-- Cars Table -->
<div class="table-container">
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Make</th>
                <th>Model</th>
                <th>Year</th>
                <th>Category</th>
                <th>Price/Day</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $conn->prepare("SELECT id, image_url as image, make, model, year, category, price_per_day, COALESCE(availability, 1) as availability FROM cars ORDER BY id DESC");
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($car = $result->fetch_assoc()):
                $car_image = $car['image'] ?? 'assets/default-car.jpg';
                $car_name = ($car['make'] ?? '') . ' ' . ($car['model'] ?? '');
            ?>
            <tr>
                <td><?php echo (int)$car['id']; ?></td>
                <td>
                    <img src="<?php echo htmlspecialchars($car_image, ENT_QUOTES, 'UTF-8'); ?>" 
                         alt="<?php echo htmlspecialchars($car_name, ENT_QUOTES, 'UTF-8'); ?>"
                         class="car-thumbnail"
                         onerror="this.src='assets/default-car.jpg'">
                </td>
                <td class="editable" data-field="make"><?php echo htmlspecialchars($car['make'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="editable" data-field="model"><?php echo htmlspecialchars($car['model'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="editable" data-field="year"><?php echo htmlspecialchars($car['year'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="editable" data-field="category"><?php echo htmlspecialchars($car['category'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="editable" data-field="price_per_day">$<?php echo number_format((float)($car['price_per_day'] ?? 0), 2); ?></td>
                <td>
                    <select class="status-select" data-car-id="<?php echo (int)$car['id']; ?>"
                            onchange="updateCarStatus(this, <?php echo (int)$car['id']; ?>)">
                        <option value="1" <?php echo ($car['availability'] == 1) ? 'selected' : ''; ?>>Available</option>
                        <option value="0" <?php echo ($car['availability'] == 0) ? 'selected' : ''; ?>>Not Available</option>
                    </select>
                </td>
                <td class="actions">
                    <button class="btn btn-edit" onclick="editCar(<?php echo (int)$car['id']; ?>)">
                        <i class="ri-edit-line"></i>
                    </button>
                    <button class="btn btn-delete" onclick="deleteCar(<?php echo (int)$car['id']; ?>)">
                        <i class="ri-delete-bin-line"></i>
                    </button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    z-index: 1000;
}

.modal-content {
    position: relative;
    background-color: #fff;
    margin: 5% auto;
    padding: 20px;
    width: 80%;
    max-width: 600px;
    border-radius: 10px;
    max-height: 90vh;
    overflow-y: auto;
}

.close {
    position: absolute;
    right: 20px;
    top: 10px;
    font-size: 28px;
    cursor: pointer;
}

.features-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    margin-top: 5px;
}

.car-thumbnail {
    width: 100px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
}

.status-select {
    padding: 5px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.editable:hover {
    background-color: #f8f9fa;
    cursor: pointer;
}

.editing {
    padding: 0 !important;
}

.editing input {
    width: 100%;
    padding: 8px;
    border: 2px solid #4a6cf7;
    border-radius: 4px;
}
</style>

<script>
function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.className === 'modal') {
        event.target.style.display = 'none';
    }
}

document.querySelectorAll('.close').forEach(function(element) {
    element.onclick = function() {
        this.closest('.modal').style.display = 'none';
    }
});

function updateCarStatus(select, carId) {
    const status = select.value;
    fetch('car_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_status&car_id=${carId}&status=${status}&csrf_token=<?php echo generateCSRFToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('Status updated successfully', 'success');
        } else {
            showAlert('Error updating status', 'error');
            select.value = status === '1' ? '0' : '1'; // Revert selection
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating status', 'error');
        select.value = status === '1' ? '0' : '1'; // Revert selection
    });
}

function showAlert(message, type) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type}`;
    alertDiv.textContent = message;
    
    const container = document.querySelector('.content-header');
    container.parentNode.insertBefore(alertDiv, container.nextSibling);
    
    setTimeout(() => alertDiv.remove(), 3000);
}

// Make cells editable
document.querySelectorAll('.editable').forEach(cell => {
    cell.addEventListener('click', function() {
        if (!this.classList.contains('editing')) {
            const value = this.textContent;
            const field = this.dataset.field;
            const carId = this.parentElement.querySelector('.actions').getElementsByTagName('button')[0].onclick.toString().match(/\d+/)[0];
            
            this.classList.add('editing');
            this.innerHTML = `<input type="text" value="${value}" 
                                   onblur="updateField(this, '${field}', ${carId})"
                                   onkeypress="handleKeyPress(event, this)">`;
            this.querySelector('input').focus();
        }
    });
});

function handleKeyPress(event, input) {
    if (event.key === 'Enter') {
        input.blur();
    }
}

function updateField(input, field, carId) {
    const newValue = input.value;
    const cell = input.parentElement;
    
    fetch('car_actions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_field&car_id=${carId}&field=${field}&value=${newValue}&csrf_token=<?php echo generateCSRFToken(); ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            cell.classList.remove('editing');
            cell.textContent = newValue;
            showAlert('Updated successfully', 'success');
        } else {
            showAlert('Error updating field', 'error');
            cell.classList.remove('editing');
            cell.textContent = input.defaultValue;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Error updating field', 'error');
        cell.classList.remove('editing');
        cell.textContent = input.defaultValue;
    });
}

function deleteCar(carId) {
    if (confirm('Are you sure you want to delete this car?')) {
        fetch('car_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=delete&car_id=${carId}&csrf_token=<?php echo generateCSRFToken(); ?>`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const row = document.querySelector(`button[onclick="deleteCar(${carId})"]`).closest('tr');
                row.remove();
                showAlert('Car deleted successfully', 'success');
            } else {
                showAlert('Error deleting car', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting car', 'error');
        });
    }
}
</script> 