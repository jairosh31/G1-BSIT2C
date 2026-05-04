<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
?>

<nav class="navbar navbar-dark booking-topbar d-lg-none">
  <div class="container py-2">
    <div class="d-flex align-items-center justify-content-between w-100">
      <button class="navbar-toggler booking-topbar__toggler" type="button" data-bs-toggle="offcanvas"
        data-bs-target="#bookingSidebar" aria-controls="bookingSidebar" aria-label="Toggle sidebar">
        <span class="navbar-toggler-icon"></span>
      </button>

      <a class="booking-topbar__brand text-decoration-none" href="dashboard.php">Booking</a>

      <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
    </div>
  </div>
</nav>

