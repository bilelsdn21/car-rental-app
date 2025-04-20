<?php
require_once 'db.php';

// Count total users (role = user)
$user_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetch_row()[0];
// Count total agencies (role = agency)
$agency_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'agency'")->fetch_row()[0];
// Count total cars
$car_count = $conn->query("SELECT COUNT(*) FROM cars")->fetch_row()[0];
// Count total bookings
$booking_count = $conn->query("SELECT COUNT(*) FROM bookings")->fetch_row()[0];
?>

<div class="dashboard-summary mb-4 d-flex gap-4 flex-wrap">
    <div class="summary-card">
        <div class="summary-icon bg-primary text-white"><i class="fas fa-users"></i></div>
        <div class="summary-content">
            <h4>Total Users</h4>
            <p><?php echo $user_count; ?></p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon bg-info text-white"><i class="fas fa-building"></i></div>
        <div class="summary-content">
            <h4>Total Agencies</h4>
            <p><?php echo $agency_count; ?></p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon bg-success text-white"><i class="fas fa-car"></i></div>
        <div class="summary-content">
            <h4>Total Cars</h4>
            <p><?php echo $car_count; ?></p>
        </div>
    </div>
    <div class="summary-card">
        <div class="summary-icon bg-warning text-white"><i class="fas fa-calendar-check"></i></div>
        <div class="summary-content">
            <h4>Total Bookings</h4>
            <p><?php echo $booking_count; ?></p>
        </div>
    </div>
</div>

<style>
.dashboard-summary { margin-bottom: 2rem; }
.summary-card { background: #fff; border-radius: 8px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); padding: 1rem 2rem; display: flex; align-items: center; gap: 1rem; min-width: 200px; }
.summary-icon { font-size: 2rem; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center; }
.summary-content h4 { margin: 0; font-size: 1.1rem; color: #333; font-weight: 600; }
.summary-content p { margin: 0; font-size: 1.3rem; font-weight: bold; color: #222; }
</style>
